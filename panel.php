<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/panel2.css">

   
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
            <a href="#" class="nav-item" onclick="openAdminPage('ocultar')">
                <span class="icon">👁️</span>
                Ocultar de Cartelera
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
            <a href="config_texto.php" class="nav-item" >
                <span class="icon">⚙️</span>
                Configuración
            </a>
            <a href="admin-cartelera.php" class="nav-item">
                <span class="icon">🎬</span>
                Config. Cartelera
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
        <button class="reset-btn">
            <a href="logout.php" style="color:white;">Cerrar sesión (<?= currentAdmin() ?>)</a>
        </button>
        
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
                <button class="firebase-status-badge config-toggle-btn" onclick="toggleFirebaseConfigPanel()">
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
        'ocultar': 'admin-ocultar.php',
        'eliminar': 'admin-eliminar.php'
    };
    if (urls[page]) window.open(urls[page], '_self');
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
