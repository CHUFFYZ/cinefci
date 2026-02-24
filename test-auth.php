<?php
require_once __DIR__ . '/auth.php';

echo '<pre>';
echo 'Ruta buscada: ' . __DIR__ . '/.env' . PHP_EOL;
echo 'Archivo existe: ' . (file_exists(__DIR__ . '/.env') ? 'SÍ' : 'NO') . PHP_EOL;
echo 'Contenido raw:' . PHP_EOL;
if (file_exists(__DIR__ . '/.env')) {
    echo htmlspecialchars(file_get_contents(__DIR__ . '/.env'));
} else {
    echo 'ARCHIVO NO ENCONTRADO';
}
echo '</pre>';
