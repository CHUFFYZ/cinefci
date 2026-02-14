<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/panel.css">

    <!-- Estilos extra para la sección de chat en el panel -->
    <style>
    /* ── SECCIÓN CHAT CONTROL ── */
    .chat-control-section {
        background: linear-gradient(135deg, #1a1a1a 0%, #2f2f2f 100%);
        border-radius: 20px;
        padding: 36px 40px;
        margin-bottom: 30px;
        border: 2px solid rgba(229, 9, 20, 0.25);
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        animation: fadeIn 0.6s ease-out;
    }

    .chat-control-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
    }

    .chat-control-title {
        font-family: 'Bebas Neue', cursive;
        font-size: 2rem;
        letter-spacing: 0.1em;
        position: relative;
        padding-left: 25px;
    }
    .chat-control-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 32px;
        background: var(--primary);
        box-shadow: 0 0 12px var(--primary);
    }

    /* Toggle switch */
    .chat-toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .chat-toggle-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #8c8c8c;
    }
    .chat-toggle-status {
        font-size: 1rem;
        font-weight: 700;
        min-width: 100px;
        transition: color 0.3s;
    }
    .chat-toggle-status.active { color: #00ff88; }
    .chat-toggle-status.inactive { color: #8c8c8c; }

    /* Switch visual */
    .toggle-switch {
        position: relative;
        width: 58px;
        height: 30px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-track {
        position: absolute;
        inset: 0;
        background: #444;
        border-radius: 30px;
        transition: background 0.3s;
    }
    .toggle-thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 24px;
        height: 24px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.3s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.4);
    }
    .toggle-switch input:checked ~ .toggle-track { background: #00ff88; }
    .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(28px); }

    /* Info cards de chat */
    .chat-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
        margin-top: 4px;
    }
    .chat-info-card {
        background: rgba(0,0,0,0.3);
        border-radius: 12px;
        padding: 16px 18px;
        border: 1px solid rgba(255,255,255,0.06);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .chat-info-card-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #8c8c8c;
    }
    .chat-info-card-value {
        font-family: 'Bebas Neue', cursive;
        font-size: 1.8rem;
        letter-spacing: 0.05em;
        line-height: 1;
        color: var(--gold);
    }
    .chat-info-card-sub {
        font-size: 11px;
        color: #8c8c8c;
        font-weight: 300;
    }

    /* Botón limpiar chat */
    .chat-clear-btn {
        background: transparent;
        border: 1px solid rgba(229,9,20,0.4);
        color: #e50914;
        padding: 9px 20px;
        border-radius: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .chat-clear-btn:hover {
        background: rgba(229,9,20,0.15);
        border-color: var(--primary);
    }

    /* Config Firebase badge */
    .firebase-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 20px;
        margin-top: 16px;
    }
    .firebase-status-badge.connected {
        background: rgba(0,255,136,0.1);
        color: #00ff88;
        border: 1px solid rgba(0,255,136,0.25);
    }
    .firebase-status-badge.disconnected {
        background: rgba(229,9,20,0.1);
        color: #e50914;
        border: 1px solid rgba(229,9,20,0.25);
    }
    .firebase-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .firebase-status-badge.connected .firebase-status-dot { background: #00ff88; animation: pulse 2s infinite; }
    .firebase-status-badge.disconnected .firebase-status-dot { background: #e50914; }
    @keyframes pulse {
        0%,100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Config Firebase form (inline en el panel) */
    .firebase-config-panel {
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(229,9,20,0.2);
        border-radius: 12px;
        padding: 20px;
        margin-top: 16px;
        display: none;
    }
    .firebase-config-panel.show { display: block; }
    .firebase-config-panel h4 {
        font-family: 'Bebas Neue', cursive;
        font-size: 1.2rem;
        letter-spacing: 0.08em;
        color: var(--primary);
        margin-bottom: 12px;
    }
    .fcb-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    .fcb-field label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #8c8c8c;
        margin-bottom: 5px;
    }
    .fcb-field input {
        width: 100%;
        background: #1a1a1a;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 7px;
        padding: 9px 12px;
        color: #fff;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        outline: none;
        transition: border-color 0.2s;
    }
    .fcb-field input:focus { border-color: var(--primary); }
    .fcb-field input::placeholder { color: #555; }
    .fcb-save-btn {
        background: linear-gradient(135deg, #e50914, #b00710);
        border: none;
        border-radius: 8px;
        padding: 11px 24px;
        color: #fff;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .fcb-save-btn:hover { opacity: 0.85; }

    .config-toggle-btn {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.15);
        color: #8c8c8c;
        padding: 6px 14px;
        border-radius: 7px;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s;
        margin-left: 10px;
    }
    .config-toggle-btn:hover {
        border-color: rgba(255,255,255,0.3);
        color: #fff;
    }

    @media (max-width: 768px) {
        .chat-control-section { padding: 24px 20px; }
        .chat-control-header { flex-direction: column; align-items: flex-start; }
    }
    </style>
</head>
<body>
    <!-- MENÚ LATERAL -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Administración</h3>
            <button class="close-sidebar" onclick="toggleSidebar()">×</button>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item" onclick="openAdminPage('agregar')">
                <span class="icon">➕</span>
                Agregar Película
            </a>
            <a href="#" class="nav-item" onclick="openAdminPage('modificar')">
                <span class="icon">✏️</span>
                Modificar Película
            </a>
            <a href="#" class="nav-item" onclick="openAdminPage('suspender')">
                <span class="icon">⏸️</span>
                Suspender Película
            </a>
            <a href="#" class="nav-item" onclick="openAdminPage('eliminar')">
                <span class="icon">🗑️</span>
                Eliminar Película
            </a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-item" target="_blank">
                <span class="icon">🎬</span>
                Ver Catálogo
            </a>
            <div class="nav-divider"></div>
            <a href="config_texto.php" class="nav-item" target="_blank">
                <span class="icon">⚙️</span>
                Configuración
            </a>
        </nav>
    </div>

    <!-- OVERLAY PARA CERRAR MENÚ -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <header>
        <div class="header-content">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div class="logo-container">
                <img class="logo-icon" src="image/logo/logo.png" alt="">
                <div>
                    <div class="logo-text">CINE-FCI</div>
                    <div class="subtitle">Panel de Control</div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- ══════════════════════════════════════════════════
             CONTROL DEL CHAT EN VIVO
        ══════════════════════════════════════════════════ -->
        <div class="chat-control-section">
            <div class="chat-control-header">
                <div class="chat-control-title">💬 Chat en Vivo</div>

                <div class="chat-toggle-wrapper">
                    <span class="chat-toggle-label">Estado:</span>
                    <span class="chat-toggle-status" id="chat-status-label">cargando...</span>
                    <label class="toggle-switch" title="Habilitar / deshabilitar chat">
                        <input type="checkbox" id="chat-enabled-toggle" onchange="toggleChatEnabled()">
                        <div class="toggle-track"></div>
                        <div class="toggle-thumb"></div>
                    </label>
                    <button class="chat-clear-btn" onclick="clearChatMessages()">🗑 Borrar mensajes</button>
                </div>
            </div>

            <!-- Info de uso -->
            <div class="chat-info-grid">
                <div class="chat-info-card">
                    <div class="chat-info-card-label">Usuarios en línea</div>
                    <div class="chat-info-card-value" id="chat-online-count">—</div>
                    <div class="chat-info-card-sub">ahora mismo</div>
                </div>
                <div class="chat-info-card">
                    <div class="chat-info-card-label">Mensajes totales</div>
                    <div class="chat-info-card-value" id="chat-msg-count">—</div>
                    <div class="chat-info-card-sub">en el historial</div>
                </div>
                <div class="chat-info-card">
                    <div class="chat-info-card-label">Límite</div>
                    <div class="chat-info-card-value">100</div>
                    <div class="chat-info-card-sub">últimos mensajes</div>
                </div>
                <div class="chat-info-card">
                    <div class="chat-info-card-label">Plan Firebase</div>
                    <div class="chat-info-card-value" style="font-size:1.1rem; padding-top:4px;">FREE</div>
                    <div class="chat-info-card-sub">1 GB almacenamiento</div>
                </div>
            </div>

            <!-- Firebase status -->
            <div id="firebase-panel-status" style="margin-top:16px; display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                <span class="firebase-status-badge disconnected" id="firebase-badge">
                    <span class="firebase-status-dot"></span>
                    Firebase: sin configurar
                </span>
                <button class="config-toggle-btn" onclick="toggleFirebaseConfigPanel()">
                    ⚙ Configurar Firebase
                </button>
            </div>

            <!-- Form de configuración Firebase -->
            <div class="firebase-config-panel" id="firebase-config-panel">
                <h4>🔥 Credenciales Firebase</h4>
                <p style="font-size:12px; color:#8c8c8c; margin-bottom:14px; line-height:1.6;">
                    Ve a <a href="https://console.firebase.google.com" target="_blank" style="color:#e50914;">console.firebase.google.com</a>
                    → Tu proyecto → Configuración → Tu app web
                </p>
                <div class="fcb-grid">
                    <div class="fcb-field">
                        <label>API Key</label>
                        <input id="panel-fcb-apiKey" placeholder="AIzaSy...">
                    </div>
                    <div class="fcb-field">
                        <label>Auth Domain</label>
                        <input id="panel-fcb-authDomain" placeholder="mi-proyecto.firebaseapp.com">
                    </div>
                    <div class="fcb-field">
                        <label>Database URL *</label>
                        <input id="panel-fcb-databaseURL" placeholder="https://mi-proyecto-default-rtdb.firebaseio.com">
                    </div>
                    <div class="fcb-field">
                        <label>Project ID *</label>
                        <input id="panel-fcb-projectId" placeholder="mi-proyecto">
                    </div>
                </div>
                <button class="fcb-save-btn" onclick="savePanelFirebaseConfig()">Guardar y conectar 🚀</button>
            </div>
        </div>

        <!-- WINNER SECTION -->
        <div class="winner-section" id="winnerSection">
            <div class="no-winner">
                <div style="font-size: 3rem; margin-bottom: 10px;">🏆</div>
                Aún no hay votos registrados
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="reset-btn" onclick="resetVotos()">🔄 Resetear Votos</button>
        </div>
        
        <!-- STATS SECTION -->
        <div class="stats-header">
            <h2 class="section-title">Ranking de Películas</h2>
            <button class="refresh-btn" onclick="loadStats()">
                🔄 Actualizar
            </button>
        </div>

        <div class="movies-list" id="moviesList">
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">No hay datos de votación disponibles</div>
            </div>
        </div>
    </main>

<!-- ══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ -->
<script>
// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN GLOBAL
// ═══════════════════════════════════════════════════════════════════════════
const API_URL = 'api.php';

function getBrowserId() {
    let bid = localStorage.getItem('browser_id');
    if (!bid) {
        bid = 'bid_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('browser_id', bid);
    }
    return bid;
}
const BROWSER_ID = getBrowserId();

// ═══════════════════════════════════════════════════════════════════════════
// MENÚ LATERAL
// ═══════════════════════════════════════════════════════════════════════════
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

function openAdminPage(page) {
    const urls = {
        'agregar': 'admin-agregar.php',
        'modificar': 'admin-modificar.php',
        'suspender': 'admin-suspender.php',
        'eliminar': 'admin-eliminar.php'
    };
    if (urls[page]) window.open(urls[page], '_blank');
    toggleSidebar();
}

// ═══════════════════════════════════════════════════════════════════════════
// ESTADÍSTICAS DE PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadStats() {
    try {
        const response = await fetch(`${API_URL}?action=stats`);
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        const data = await response.json();
        renderWinner(data.ganador);
        renderMoviesList(data.peliculas, data.mas_votada);
    } catch (err) {
        console.error('Error al cargar estadísticas:', err);
        showErrorMessage("No se pudieron cargar los datos del servidor");
    }
}

function renderWinner(ganador) {
    const section = document.getElementById('winnerSection');
    if (!ganador || ganador.votos <= 0) {
        section.innerHTML = `
            <div class="no-winner">
                <div style="font-size: 3rem; margin-bottom: 10px;">🏆</div>
                Aún no hay votos registrados
            </div>`;
        return;
    }
    section.innerHTML = `
        <div class="winner-badge">⭐ Película Ganadora</div>
        <div class="winner-title">${ganador.titulo || ganador.title || 'Sin título'}</div>
        <div class="winner-votes">
            <strong>${ganador.votos}</strong> ${ganador.votos === 1 ? 'voto' : 'votos'}
        </div>`;
}

function renderMoviesList(peliculas, masVotada) {
    const container = document.getElementById('moviesList');
    if (!peliculas || peliculas.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">No hay datos de votación disponibles</div>
            </div>`;
        return;
    }
    container.innerHTML = peliculas.map((movie, index) => {
        const position = index + 1;
        const avg = movie.promedio || 0;
        const countRatings = movie.total_calificaciones || 0;
        const isMasVotada = masVotada && masVotada.id === movie.id && masVotada.veces_ganadora > 0;
        return `
            <div class="movie-item ${isMasVotada ? 'mas-votada' : ''}">
                <div class="movie-position">${position}</div>
                <div class="movie-name">
                    ${movie.titulo || movie.title || 'Sin título'}
                    ${isMasVotada ? '<span class="badge-mas-votada">🏆 Más Votada (' + masVotada.veces_ganadora + ' victorias)</span>' : ''}
                </div>
                <div class="movie-rating">
                    ${avg > 0
                        ? `<span class="stars">★</span><span>${avg}</span><span style="opacity:0.5;">(${countRatings})</span>`
                        : '<span style="opacity:0.4;">Sin calificaciones</span>'}
                </div>
                <div class="movie-votes">
                    <div class="vote-count">${movie.votos || 0}</div>
                    <div style="font-size:0.8rem;opacity:0.7;margin-top:2px;">
                        ${movie.votos === 1 ? 'voto' : 'votos'}
                    </div>
                </div>
            </div>`;
    }).join('');
}

function showErrorMessage(msg) {
    document.getElementById('moviesList').innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon" style="font-size:3rem;">⚠️</div>
            <div class="empty-state-text">${msg}</div>
        </div>`;
}

async function resetVotos() {
    if (!confirm('¿Estás seguro de que quieres eliminar TODOS los votos? Esta acción no se puede deshacer.')) return;
    try {
        const res = await fetch('api.php?action=reset_votes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            alert('La tabla de votos ha sido vaciada.');
            loadStats();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error('Error al resetear:', err);
        alert('No se pudo conectar con el servidor para resetear.');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CONTROL DEL CHAT — Firebase
// ═══════════════════════════════════════════════════════════════════════════
function getFirebaseConfig() {
    try {
        const raw = localStorage.getItem('cinefci_firebase_config');
        return raw ? JSON.parse(raw) : null;
    } catch { return null; }
}

function toggleFirebaseConfigPanel() {
    const panel = document.getElementById('firebase-config-panel');
    panel.classList.toggle('show');
    if (panel.classList.contains('show')) loadFirebaseConfigIntoForm();
}

function loadFirebaseConfigIntoForm() {
    const config = getFirebaseConfig();
    if (!config) return;
    document.getElementById('panel-fcb-apiKey').value      = config.apiKey      || '';
    document.getElementById('panel-fcb-authDomain').value  = config.authDomain  || '';
    document.getElementById('panel-fcb-databaseURL').value = config.databaseURL || '';
    document.getElementById('panel-fcb-projectId').value   = config.projectId   || '';
}

window.savePanelFirebaseConfig = function() {
    const apiKey      = document.getElementById('panel-fcb-apiKey').value.trim();
    const authDomain  = document.getElementById('panel-fcb-authDomain').value.trim();
    const databaseURL = document.getElementById('panel-fcb-databaseURL').value.trim();
    const projectId   = document.getElementById('panel-fcb-projectId').value.trim();

    if (!apiKey || !databaseURL || !projectId) {
        alert('Completa al menos: API Key, Database URL y Project ID');
        return;
    }

    localStorage.setItem('cinefci_firebase_config', JSON.stringify({ apiKey, authDomain, databaseURL, projectId }));
    document.getElementById('firebase-config-panel').classList.remove('show');
    initFirebasePanel();
};

// ── Firebase panel instance ─────────────────────────────────────────────────
let panelDb = null;

async function initFirebasePanel() {
    const config = getFirebaseConfig();
    const badge = document.getElementById('firebase-badge');

    if (!config || !config.databaseURL) {
        badge.className = 'firebase-status-badge disconnected';
        badge.innerHTML = '<span class="firebase-status-dot"></span>Firebase: sin configurar';
        return;
    }

    try {
        // Importar Firebase dinámicamente
        const { initializeApp, getApps } = await import("https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js");
        const { getDatabase, ref, onValue, set, remove } = await import("https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js");

        // Evitar inicializar dos veces
        const appName = 'cinefci-panel';
        let app;
        const existing = getApps().find(a => a.name === appName);
        if (existing) {
            app = existing;
        } else {
            app = initializeApp(config, appName);
        }

        panelDb = getDatabase(app);

        badge.className = 'firebase-status-badge connected';
        badge.innerHTML = '<span class="firebase-status-dot"></span>Firebase: conectado ✓';

        // Escuchar estado del chat (enabled/disabled)
        onValue(ref(panelDb, 'chat_config/enabled'), snap => {
            const enabled = snap.exists() ? snap.val() : true;
            const toggle = document.getElementById('chat-enabled-toggle');
            const label  = document.getElementById('chat-status-label');
            toggle.checked = enabled;
            label.textContent = enabled ? '🟢 Activo' : '🔴 Desactivado';
            label.className = `chat-toggle-status ${enabled ? 'active' : 'inactive'}`;
        });

        // Contar usuarios en línea
        onValue(ref(panelDb, 'chat_presence'), snap => {
            const count = snap.exists() ? Object.keys(snap.val()).length : 0;
            document.getElementById('chat-online-count').textContent = count;
        });

        // Contar mensajes totales
        onValue(ref(panelDb, 'chat_messages'), snap => {
            const count = snap.exists() ? Object.keys(snap.val()).length : 0;
            document.getElementById('chat-msg-count').textContent = count;
        });

        // Guardar ref para uso en toggles
        window._panelFirebaseRef  = ref;
        window._panelFirebaseDb   = panelDb;
        window._panelFirebaseSet  = set;
        window._panelFirebaseRemove = remove;

    } catch (err) {
        console.error('[Panel Chat] Error Firebase:', err);
        badge.className = 'firebase-status-badge disconnected';
        badge.innerHTML = '<span class="firebase-status-dot"></span>Firebase: error de conexión';
    }
}

// ── Toggle habilitar / deshabilitar chat ─────────────────────────────────────
window.toggleChatEnabled = function() {
    if (!window._panelFirebaseRef || !window._panelFirebaseDb) {
        alert('Primero configura y conecta Firebase.');
        document.getElementById('chat-enabled-toggle').checked =
            !document.getElementById('chat-enabled-toggle').checked;
        return;
    }
    const enabled = document.getElementById('chat-enabled-toggle').checked;
    window._panelFirebaseSet(
        window._panelFirebaseRef(window._panelFirebaseDb, 'chat_config/enabled'),
        enabled
    ).then(() => {
        const label = document.getElementById('chat-status-label');
        label.textContent = enabled ? '🟢 Activo' : '🔴 Desactivado';
        label.className = `chat-toggle-status ${enabled ? 'active' : 'inactive'}`;
        showPanelNotif(enabled ? '✅ Chat habilitado' : '🔇 Chat deshabilitado');
    }).catch(err => {
        console.error('Error al cambiar estado del chat:', err);
        alert('Error al actualizar. Verifica la configuración de Firebase.');
    });
};

// ── Borrar todos los mensajes ────────────────────────────────────────────────
window.clearChatMessages = function() {
    if (!window._panelFirebaseRef || !window._panelFirebaseDb) {
        alert('Primero configura y conecta Firebase.');
        return;
    }
    if (!confirm('¿Borrar TODOS los mensajes del chat? Esta acción no se puede deshacer.')) return;

    window._panelFirebaseRemove(
        window._panelFirebaseRef(window._panelFirebaseDb, 'chat_messages')
    ).then(() => {
        showPanelNotif('🗑 Mensajes eliminados correctamente');
        document.getElementById('chat-msg-count').textContent = '0';
    }).catch(err => {
        console.error('Error al limpiar chat:', err);
        alert('No se pudieron borrar los mensajes. Revisa los permisos de Firebase.');
    });
};

// ── Notificación del panel ───────────────────────────────────────────────────
function showPanelNotif(msg) {
    let notif = document.getElementById('panel-notif');
    if (!notif) {
        notif = document.createElement('div');
        notif.id = 'panel-notif';
        notif.style.cssText = `
            position:fixed; top:90px; right:30px; z-index:9999;
            background:linear-gradient(135deg,#e50914,#b00710);
            color:#fff; padding:13px 22px; border-radius:8px;
            font-family:'Montserrat',sans-serif; font-weight:600; font-size:14px;
            box-shadow:0 8px 25px rgba(229,9,20,0.4);
            opacity:0; transform:translateX(400px);
            transition: all 0.3s ease;
        `;
        document.body.appendChild(notif);
    }
    notif.textContent = msg;
    setTimeout(() => { notif.style.opacity='1'; notif.style.transform='translateX(0)'; }, 50);
    setTimeout(() => { notif.style.opacity='0'; notif.style.transform='translateX(400px)'; }, 3000);
}

// ═══════════════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════════════
window.addEventListener('load', () => {
    loadStats();
    setInterval(loadStats, 8000);
    initFirebasePanel();
});

window.loadStats = loadStats;
</script>
</body>
</html>
