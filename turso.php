<?php
// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN TURSO
// ═══════════════════════════════════════════════════════════════════════════
define('TURSO_URL',   'https://cinefci-bychuffy.aws-us-east-1.turso.io');
define('TURSO_TOKEN', 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NzEzNjkyODUsImlkIjoiNzhhZmZiM2EtYzNmYy00NWQwLTgwZWEtZjRjOGQ0NDQ2NjM1IiwicmlkIjoiY2I3YTc4NzUtMDllMS00NGM5LWI4OWEtNzc0ODQyYTdkMTkwIn0.8Cs_zHfIqa998uGAjgSgfpflD5gspw_QvT_RiDfVlHPQV1Rb0MJuSQI5dIl7GEDrRKZ5tYsqGF8ucfgBDM3QCg');

// ═══════════════════════════════════════════════════════════════════════════
// CLASE TURSO - Emula la interfaz PDO para mínimos cambios en el código
// ═══════════════════════════════════════════════════════════════════════════
class TursoDB {
    private string $url;
    private string $token;
    private array  $transaction = [];
    private bool   $inTransaction = false;

    public function __construct(string $url, string $token) {
        $this->url   = rtrim($url, '/') . '/v2/pipeline';
        $this->token = $token;
    }

    // ── Ejecutar una o varias sentencias contra la API HTTP de Turso ────────
    private function pipeline(array $requests): array {
        $payload = json_encode(['requests' => $requests]);

        $ch = curl_init($this->url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
        ]);
        $raw  = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new Exception('Error cURL conectando a Turso');
        }

        $decoded = json_decode($raw, true);
        if (!isset($decoded['results'])) {
            throw new Exception('Respuesta inesperada de Turso: ' . $raw);
        }

        foreach ($decoded['results'] as $result) {
            if (($result['type'] ?? '') === 'error') {
                throw new Exception('Turso error: ' . ($result['error']['message'] ?? json_encode($result)));
            }
        }

        return $decoded['results'];
    }

    // ── Convertir parámetros posicionales ? a named :p0, :p1 … ────────────
    private function buildRequest(string $sql, array $params = []): array {
        $args = [];
        foreach ($params as $i => $val) {
            if (is_null($val)) {
                $args[] = ['type' => 'null'];
            } elseif (is_int($val) || is_float($val)) {
                $args[] = ['type' => 'integer', 'value' => (string)$val];
            } else {
                $args[] = ['type' => 'text', 'value' => (string)$val];
            }
        }

        // Reemplazar ? por parámetros posicionales de libSQL
        $idx = 0;
        $sql = preg_replace_callback('/\?/', function() use (&$idx) {
            return '?' ; // libSQL acepta ? directamente
        }, $sql);

        return [
            'type' => 'execute',
            'stmt' => [
                'sql'  => $sql,
                'args' => $args,
            ],
        ];
    }

    // ── exec(): para sentencias sin parámetros (CREATE, DELETE sin bind…) ──
    public function exec(string $sql): void {
        if ($this->inTransaction) {
            $this->transaction[] = $this->buildRequest($sql);
            return;
        }
        $this->pipeline([$this->buildRequest($sql)]);
    }

    // ── prepare() devuelve un TursoStatement ────────────────────────────────
    public function prepare(string $sql): TursoStatement {
        return new TursoStatement($sql, $this);
    }

    // ── query() para SELECT sin parámetros ──────────────────────────────────
    public function query(string $sql): TursoStatement {
        $stmt = new TursoStatement($sql, $this);
        $stmt->execute([]);
        return $stmt;
    }

    // ── Transacciones ────────────────────────────────────────────────────────
    public function beginTransaction(): void {
        $this->inTransaction = true;
        $this->transaction   = [];
    }

    public function commit(): void {
        if (!$this->inTransaction) return;

        $requests   = $this->transaction;
        $requests[] = ['type' => 'close'];
        $this->pipeline($requests);

        $this->inTransaction = false;
        $this->transaction   = [];
    }

    public function rollBack(): void {
        $this->inTransaction = false;
        $this->transaction   = [];
    }

    public function inTransaction(): bool {
        return $this->inTransaction;
    }

    // ── Ejecutar directamente (usado por TursoStatement) ────────────────────
    public function executeStatement(string $sql, array $params): array {
        if ($this->inTransaction) {
            // Dentro de transacción: encolar y devolver vacío (no hay resultado aún)
            $this->transaction[] = $this->buildRequest($sql, $params);
            return [];
        }
        $results = $this->pipeline([$this->buildRequest($sql, $params)]);
        return $results[0]['response']['result'] ?? [];
    }

    // ── lastInsertId: necesita consulta extra a Turso ────────────────────────
    public function lastInsertId(): string {
        $results = $this->pipeline([[
            'type' => 'execute',
            'stmt' => ['sql' => 'SELECT last_insert_rowid() as id', 'args' => []],
        ]]);
        $rows = $results[0]['response']['result']['rows'] ?? [];
        return (string)($rows[0][0]['value'] ?? 0);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CLASE TursoStatement - Emula PDOStatement
// ═══════════════════════════════════════════════════════════════════════════
class TursoStatement {
    private string  $sql;
    private TursoDB $db;
    private array   $result   = [];
    private int     $cursor   = 0;
    private int     $affected = 0;
    private array   $cols     = [];

    public function __construct(string $sql, TursoDB $db) {
        $this->sql = $sql;
        $this->db  = $db;
    }

    public function execute(array $params = []): bool {
        $result = $this->db->executeStatement($this->sql, $params);

        $this->cols     = array_column($result['cols'] ?? [], 'name');
        $this->affected = (int)($result['affected_row_count'] ?? 0);
        $this->cursor   = 0;
        $this->result   = [];

        foreach ($result['rows'] ?? [] as $row) {
            $assoc = [];
            foreach ($this->cols as $i => $col) {
                $cell       = $row[$i];
                $assoc[$col] = ($cell['type'] === 'null') ? null : $cell['value'];
            }
            $this->result[] = $assoc;
        }

        return true;
    }

    public function fetch(int $mode = 0): mixed {
        if ($this->cursor >= count($this->result)) return false;
        return $this->result[$this->cursor++];
    }

    public function fetchAll(int $mode = 0): array {
        // PDO::FETCH_COLUMN
        if ($mode === 7) {
            return array_column($this->result, $this->cols[0] ?? null);
        }
        return $this->result;
    }

    public function rowCount(): int {
        return $this->affected ?: count($this->result);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CONSTANTES FETCH (usamos las de PDO nativo que ya existen en PHP)
// ═══════════════════════════════════════════════════════════════════════════
// PDO::FETCH_ASSOC  = 2  (ya existe)
// PDO::FETCH_COLUMN = 7  (ya existe)
