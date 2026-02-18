<?php
require_once __DIR__ . '/turso.php';

// Cargar textos configurables de Turso
$logo_cargando    = 'CINE-FCI';
$texto_cargando   = '';
$texto_logo       = '';
$subtitulo_header = '';
$titulo_catalogo  = '';

try {
    $db   = new TursoDB(TURSO_URL, TURSO_TOKEN);
    $stmt = $db->query("SELECT clave, valor FROM configuracion_texto");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        switch ($row['clave']) {
            case 'logo_cargando':    $logo_cargando    = $row['valor']; break;
            case 'texto_cargando':   $texto_cargando   = $row['valor']; break;
            case 'texto_logo':       $texto_logo       = $row['valor']; break;
            case 'subtitulo_header': $subtitulo_header = $row['valor']; break;
            case 'titulo_catalogo':  $titulo_catalogo  = $row['valor']; break;
        }
    }
} catch (Exception $e) {
    // Si falla Turso, se usan los valores por defecto de arriba
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineFCI - Catálogo de Películas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/index.css">
    <meta name="theme-color" content="#e50914">
    <link rel="apple-touch-icon" href="image/logo/logo2.svg">
    <link rel="icon" type="image/svg" href="image/logo/logo2.svg">
    <link rel="shortcut icon" type="image/svg" href="image/logo/logo2.svg">
    <link rel="manifest" href="manifest.json">

    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('CINE-FCI: Listo para instalar'))
            .catch(err => console.log('Error de registro PWA', err));
        });
    }
    </script>
</head>
<style>
    
</style>
<body>
    <div class="Luffy">
        <a href="https://www.facebook.com/share/1GUid3qsoS/" target="_blank">By Creador UNIMAP</a>
    </div>

<!-- PANTALLA CARGA -->
<div class="loading-screen" id="loadingScreen">
    <div class="loading-logo"><?= htmlspecialchars($logo_cargando) ?></div>
    <div id="loading-logo" ><?= htmlspecialchars($texto_cargando) ?></div>
    <div class="loading-bar">
        <div class="loading-progress"></div>
    </div>
</div>

<!-- HEADER -->
<header>
    <div class="logo-container">
        <img class="logo-icon" src="image/logo/logo.png" alt="">
        <div>
            <h1 class="logo-text"><?= htmlspecialchars($texto_logo) ?></h1>
            <h1 class="header-subtitle"><?= htmlspecialchars($subtitulo_header) ?></h1>
        </div>
    </div>
</header>

<main>
    <h1 class="section-title"><?= htmlspecialchars($titulo_catalogo) ?></h1>
    
    <!-- FILTROS DE CATEGORÍA -->
    <div class="filter-section">
        <button class="filter-btn active" onclick="filterByCategory('')">Todas</button>
        <button class="filter-btn" onclick="sortMovies('az')">A-Z</button>
        <button class="filter-btn" onclick="sortMovies('popularidad')">Pop</button>
        <button id="expandFiltersBtn" class="expand-btn" onclick="toggleFilters()">
            <img src="image/iconos/flecha-abajo.png" alt="Más filtros" class="expand-icon">
        </button>
        <div id="categoryFilters"></div>
    </div>

    <div class="movies-grid" id="moviesGrid">
        <div class="loading">Cargando películas...</div>
    </div>
</main>

<!-- MODAL -->
<div class="modal" id="movieModal">
    <div class="modal-content">
        <button class="close-modal" onclick="closeModal()">×</button>
        <div class="galeria">
            <img class="modal-poster" id="modalPoster" src="" alt="">
            
            <iframe class="modal-trailer" id="modalTrailer" src="" alt="" title="Trailer" frameborder="0" allow="autoplay">
            </iframe>
        </div>
        <div class="modal-info">
            <h2 class="modal-title" id="modalTitle"></h2>
            <div class="modal-categories" id="modalCategories"></div>
            <p class="modal-summary" id="modalSummary"></p>
            <div class="rating-section">
                <div class="rating-title">Califica esta película</div>
                <div class="stars" id="starsContainer"></div>
                <div class="average-rating" id="averageRating"></div>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════
     CHAT POPUP
══════════════════════════════════════════════════════════════ -->

<!-- Bolita flotante -->
<button id="chat-bubble" onclick="toggleChat()" title="Chat en vivo" aria-label="Abrir chat">
    <!-- Ícono burbuja -->
    <svg id="chat-icon-open" width="26" height="26" viewBox="0 0 24 24" fill="white">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
    </svg>
    <!-- Ícono X (cuando está abierto) -->
    <svg id="chat-icon-close" width="22" height="22" viewBox="0 0 24 24" fill="white" style="display:none;">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>
    <span id="chat-unread"></span>
</button>

<!-- Panel del chat -->
<div id="chat-popup">
    <!-- Header -->
    <div class="chat-header">
        <div class="chat-header-avatar">🎬</div>
        <div class="chat-header-info">
            <div class="chat-header-title">Chat CINE-FCI</div>
            <div class="chat-header-sub" id="chat-online-sub">Cargando...</div>
        </div>
        <button class="chat-close-btn" onclick="toggleChat()">×</button>
    </div>

    <!-- Chat deshabilitado por admin -->
    <div id="chat-disabled-msg">
        <span>🔇</span>
        <p>El chat está desactivado temporalmente por el administrador.</p>
    </div>

    <!-- Setup nombre (primera vez) -->
    <div id="chat-name-setup" style="display:none;">
        <p>¡Bienvenido al chat de <strong>CINE-FCI</strong>!<br>Elige un nombre para que tus amigos te vean.</p>
        <input class="chat-name-input" id="chat-name-input" type="text" 
               placeholder="Tu apodo..." maxlength="20"
               onkeydown="if(event.key==='Enter') saveChatName()">
        <button class="chat-name-btn" onclick="saveChatName()">Entrar al Chat 🎬</button>
    </div>

    <!-- Mensajes -->
    <div id="chat-messages-area" style="display:none;"></div>

    <!-- Typing -->
    <div id="chat-typing" style="display:none;"></div>

    <!-- Mi nombre (footer) -->
    <div class="chat-my-name" id="chat-my-name-footer" style="display:none;">
        Chateando como: <strong onclick="resetChatName()">cargando...</strong>
    </div>

    <!-- Input -->
    <div class="chat-input-area" id="chat-input-area" style="display:none;">
        <input class="chat-input" id="chat-message-input" 
               type="text" placeholder="¿Qué película vemos? 🍿" 
               maxlength="300"
               onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); sendChatMessage(); }"
               oninput="onChatTyping()">
        <button class="chat-send-btn" onclick="sendChatMessage()" title="Enviar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
        </button>
    </div>
</div>

<!-- Firebase config overlay eliminado: config hardcodeada en el script -->


<!-- ══════════════════════════════════════════════════════════════
     SCRIPTS PRINCIPALES
══════════════════════════════════════════════════════════════ -->
<script>
// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN GLOBAL
// ═══════════════════════════════════════════════════════════════════════════
let browserId = localStorage.getItem('browserId');
if (!browserId) {
    browserId = 'bid_' + Date.now() + Math.random().toString(36).substring(2, 10);
    localStorage.setItem('browserId', browserId);
}

let currentMovies = [];
let allCategories = [];
let currentFilter = '';
let masVotadaGlobal = null;

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR CATEGORÍAS
// ═══════════════════════════════════════════════════════════════════════════
async function fetchCategories() {
    try {
        const res = await fetch('api.php?action=categorias');
        allCategories = await res.json();
        renderCategoryFilters();
    } catch (err) {
        console.error('Error al cargar categorías:', err);
    }
}

function checkFilterLimit() {
    const container = document.getElementById('categoryFilters');
    const expandBtn = document.getElementById('expandFiltersBtn');
    const buttons = container.getElementsByTagName('button');
    const limit = 5;

    if (buttons.length > limit) {
        expandBtn.style.display = 'inline-flex';
        container.classList.add('collapsed');
    } else {
        expandBtn.style.display = 'none';
        container.classList.remove('collapsed');
        container.classList.remove('expanded');
    }
}

function toggleFilters() {
    const container = document.getElementById('categoryFilters');
    const expandBtn = document.getElementById('expandFiltersBtn');
    
    if (container.classList.contains('collapsed')) {
        container.classList.remove('collapsed');
        container.classList.add('expanded');
        expandBtn.classList.add('rotated');
    } else {
        container.classList.remove('expanded');
        container.classList.add('collapsed');
        expandBtn.classList.remove('rotated');
    }
}

function renderCategoryFilters() {
    const container = document.getElementById('categoryFilters');
    if (!container) return;
    
    container.innerHTML = allCategories.map(cat => `
        <button class="filter-btn" onclick="filterByCategory('${cat.nombre}')">${cat.nombre}</button>
    `).join('');
    checkFilterLimit();
}

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR PELÍCULAS DESDE EL SERVIDOR
// ═══════════════════════════════════════════════════════════════════════════
async function fetchMovies(categoria = '') {
    try {
        let url = `api.php?action=list&browser_id=${encodeURIComponent(browserId)}`;
        if (categoria) url += `&categoria=${encodeURIComponent(categoria)}`;
        
        const res = await fetch(url);
        currentMovies = await res.json();
        await fetchMasVotada();
        renderMovies(currentMovies);
    } catch (err) {
        console.error('Error al cargar películas:', err);
        document.getElementById('moviesGrid').innerHTML = '<div class="error">Error al cargar películas</div>';
    }
}

async function fetchMasVotada() {
    try {
        const res = await fetch('api.php?action=stats');
        const data = await res.json();
        masVotadaGlobal = data.mas_votada;
    } catch (err) {
        console.error('Error al obtener más votada:', err);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
function renderMovies(movies) {
    const grid = document.getElementById('moviesGrid');
    
    if (!movies || movies.length === 0) {
        grid.innerHTML = '<div class="empty-message">No hay películas en esta categoría</div>';
        return;
    }
    
    grid.innerHTML = movies.map(m => {
        const isSuspended = m.suspendida && m.fecha_suspension;
        const isMasVotada = masVotadaGlobal && masVotadaGlobal.id === m.id && masVotadaGlobal.veces_ganadora > 0;
        
        let timeRemaining = '';
        if (isSuspended) {
            const now = new Date();
            const end = new Date(m.fecha_suspension);
            const diff = end - now;
            
            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                if (days > 0) timeRemaining = `${days}d ${hours}h`;
                else if (hours > 0) timeRemaining = `${hours}h ${minutes}m`;
                else timeRemaining = `${minutes}m`;
            }
        }
        /*${isSuspended ? 'suspended' : ''} */
        /*
        return `
            <div class="movie-card ">
                ${isMasVotada ? '<div class="ribbon-badge">🏆 Más Votada</div>' : ''}
                ${isSuspended ? `<div class="suspension-badge">⏸ Suspendida<br><span class="countdown">${timeRemaining}</span></div>` : ''}
                <img src="${m.poster}" alt="${m.titulo}" class="movie-poster ${isSuspended ? 'suspended' : ''}" 
                     onerror="this.src='image/no-poster.png'" onclick="openModal(${m.id})">
                <div class="movie-info">
                    <div class="movie-title">${m.titulo}</div>
                    <button class="vote-btn ${m.ya_voto ? 'voted' : ''} ${isSuspended ? 'disabled' : ''}" 
                            onclick="voteForMovie(${m.id}, event)"
                            ${m.ya_voto || isSuspended ? 'disabled' : ''}>
                        ${isSuspended ? '⏸ Suspendida' : (m.ya_voto ? `★ Tu voto (${m.votos})` : `Votar por esta (${m.votos})`)}
                    </button>
                </div>
            </div>
        `;*/
        return `
            <div class="movie-card ">
                ${isMasVotada ? '<div class="ribbon-badge">🏆 Más Votada</div>' : ''}
                <img src="${m.poster}" alt="${m.titulo}" class="movie-poster ${isSuspended ? 'suspended' : ''}" 
                     onerror="this.src='image/no-poster.png'" onclick="openModal(${m.id})">
                <div class="movie-info">
                    <div class="movie-title">${m.titulo}</div>
                    <button class="vote-btn ${m.ya_voto ? 'voted' : ''} ${isSuspended ? 'disabled' : ''}" 
                            onclick="voteForMovie(${m.id}, event)"
                            ${m.ya_voto || isSuspended ? 'disabled' : ''}>
                        ${isSuspended ? `⏸ Suspendida: (${timeRemaining})`: (m.ya_voto ? `★ Tu voto (${m.votos})` : `Votar por esta (${m.votos})`)}
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// ═══════════════════════════════════════════════════════════════════════════
// FILTRAR
// ═══════════════════════════════════════════════════════════════════════════
function filterByCategory(categoria) {
    currentFilter = categoria;
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    fetchMovies(categoria);
    const container = document.getElementById('categoryFilters');
    if (container.classList.contains('expanded')) toggleFilters();
}
// ═══════════════════════════════════════════════════════════════════════════
// ORDENAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
function sortMovies(criterio) {
    // Marcamos el botón como activo visualmente
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    if (criterio === 'az') {
        // Ordenar de la A a la Z por el título
        currentMovies.sort((a, b) => a.titulo.localeCompare(b.titulo));
    } 
    else if (criterio === 'popularidad') {
        // Ordenar de mayor a menor número de votos
        // Asegúrate de que 'votos' sea tratado como número
        currentMovies.sort((a, b) => Number(b.votos) - Number(a.votos));
    }

    // Volvemos a pintar las películas ya ordenadas
    renderMovies(currentMovies);
}
// ═══════════════════════════════════════════════════════════════════════════
// VOTAR
// ═══════════════════════════════════════════════════════════════════════════
async function voteForMovie(id, e) {
    e.stopPropagation();
    const btn = e.target;
    btn.disabled = true;

    try {
        const res = await fetch('api.php?action=vote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: id, browser_id: browserId })
        });

        const data = await res.json();

        if (data.success) {
            await fetchMovies(currentFilter);
        } else {
            alert(data.message || data.error);
            btn.disabled = false;
        }
    } catch (err) {
        console.error(err);
        alert('Error al conectar con el servidor');
        btn.disabled = false;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CALIFICAR CON ESTRELLAS
// ═══════════════════════════════════════════════════════════════════════════
async function rateMovie(id, rating) {
    try {
        const res = await fetch('api.php?action=rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: id, rating, browser_id: browserId })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            await refreshMovieInModal(id);
            showNotification('¡Gracias por tu calificación!');
        } else {
            alert(data.message || 'No se pudo guardar la calificación');
        }
    } catch (err) {
        console.error(err);
        alert('Error al conectar con el servidor');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// MODAL
// ═══════════════════════════════════════════════════════════════════════════


function renderStars(movie) {
    const container = document.getElementById('starsContainer');
    container.innerHTML = '';
    const userRating = movie.user_rating || 0;
    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('span');
        star.className = 'star';
        star.textContent = '★';
        star.onclick = () => rateMovie(movie.id, i);
        if (i <= userRating) star.classList.add('active');
        container.appendChild(star);
    }
}

function updateAverageRating(movie) {
    const container = document.getElementById('averageRating');
    if (!movie.promedio || movie.promedio == 0) {
        container.textContent = 'Sé el primero en calificar esta película';
        return;
    }
    const total = movie.total_calificaciones || 0;
    container.innerHTML = `
        Calificación promedio: <strong>${parseFloat(movie.promedio).toFixed(1)}</strong> ★ 
        (${total} ${total === 1 ? 'calificación' : 'calificaciones'})
    `;
}

async function refreshMovieInModal(movieId) {
    try {
        const res = await fetch(`api.php?action=list&browser_id=${encodeURIComponent(browserId)}`);
        const all = await res.json();
        const updatedMovie = all.find(m => m.id == movieId);
        if (updatedMovie) {
            const idx = currentMovies.findIndex(m => m.id == movieId);
            if (idx !== -1) currentMovies[idx] = updatedMovie;
            updateAverageRating(updatedMovie);
            renderStars(updatedMovie);
            renderMovies(currentMovies);
        }
    } catch (e) {
        console.error('No se pudo refrescar película');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// NOTIFICACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function showNotification(message) {
    const notif = document.createElement('div');
    notif.className = 'notification';
    notif.textContent = message;
    document.body.appendChild(notif);
    setTimeout(() => notif.classList.add('show'), 100);
    setTimeout(() => {
        notif.classList.remove('show');
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// Actualizar suspensiones
setInterval(() => {
    const suspendedCards = document.querySelectorAll('.movie-card.suspended');
    if (suspendedCards.length > 0) fetchMovies(currentFilter);
}, 60000);

document.getElementById('movieModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

window.addEventListener('load', async () => {
    await fetchCategories();
    await fetchMovies();
    setTimeout(() => {
        document.getElementById('loadingScreen')?.classList.add('hidden');
    }, 1800);
});
</script>
<script src="js/textos-coneccion.js"></script>
<script src="js/modal/video.js" ></script>


<!-- ══════════════════════════════════════════════════════════════
     CHAT SCRIPT — Firebase Realtime Database
══════════════════════════════════════════════════════════════ -->
<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, push, onValue, query, limitToLast, set, onDisconnect, remove, serverTimestamp, off }
    from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

// ─── CONFIGURACIÓN FIREBASE ────────────────────────────────────────────────
const FIREBASE_CONFIG = {
    apiKey:            "AIzaSyCtm6i-IdkN3dSvTF6W8qUO4gl4W79MjJ0",
    authDomain:        "chat-cine-fci.firebaseapp.com",
    databaseURL:       "https://chat-cine-fci-default-rtdb.firebaseio.com",
    projectId:         "chat-cine-fci",
    storageBucket:     "chat-cine-fci.firebasestorage.app",
    messagingSenderId: "749023571400",
    appId:             "1:749023571400:web:d542b989586802498b24fd",
    measurementId:     "G-88GKPYS5TB"
};

// ─── ESTADO ────────────────────────────────────────────────────────────────
let db             = null;
let chatEnabled    = true;
let chatOpen       = false;
let chatReady      = false;
let myName         = '';
let myColor        = '';
let unreadCount    = 0;
let typingTimeout  = null;
let lastMessageGroup  = null;
let lastMessageSender = null;

// FIX: guardar referencia al listener activo para poder cancelarlo
let activeMessagesRef  = null;
let activeMessagesHandler = null;

const USER_COLORS = [
    '#ff6b6b','#ffd166','#06d6a0','#4ecdc4','#a8dadc',
    '#ffb347','#c77dff','#48cae4','#f4a261','#e9c46a'
];

// ─── INICIALIZAR ───────────────────────────────────────────────────────────
function initChat() {
    try {
        const app = initializeApp(FIREBASE_CONFIG, 'cinefci-chat');
        db = getDatabase(app);
        chatReady = true;

        onValue(ref(db, 'chat_config/enabled'), snap => {
            chatEnabled = snap.exists() ? snap.val() : true;
            updateChatUI();
        });

        // FIX: presencia se inicia solo si ya tenemos nombre
        if (myName) setupPresence();
        
        // FIX: el contador de online se escucha siempre, sin depender del nombre
        listenOnlineCount();
        listenMessages();
        listenTyping();

        console.log('[CineFCI Chat] Firebase conectado ✓');
    } catch (err) {
        console.error('[CineFCI Chat] Error Firebase:', err);
    }
}

// ─── PRESENCIA (solo cuando hay nombre) ───────────────────────────────────
function setupPresence() {
    if (!db || !myName) return;
    const presenceRef = ref(db, `chat_presence/${browserId}`);
    set(presenceRef, { name: myName, ts: serverTimestamp() });
    onDisconnect(presenceRef).remove();
}

// FIX: contador de online separado de setupPresence para que funcione siempre
function listenOnlineCount() {
    if (!db) return;
    onValue(ref(db, 'chat_presence'), snap => {
        const count = snap.exists() ? Object.keys(snap.val()).length : 0;
        const el = document.getElementById('chat-online-sub');
        if (el) el.textContent = count > 0
            ? `${count} persona${count > 1 ? 's' : ''} en línea`
            : 'Chat en vivo';
    });
}

// ─── MENSAJES ──────────────────────────────────────────────────────────────
function listenMessages() {
    if (!db) return;

    // FIX: cancelar listener anterior si existe antes de crear uno nuevo
    if (activeMessagesRef && activeMessagesHandler) {
        off(activeMessagesRef, 'value', activeMessagesHandler);
    }

    activeMessagesRef = query(ref(db, 'chat_messages'), limitToLast(100));
    let firstLoad = true;

    activeMessagesHandler = (snap) => {
        const container = document.getElementById('chat-messages-area');
        if (!container) return;

        if (firstLoad) {
            container.innerHTML = '';
            lastMessageGroup  = null;
            lastMessageSender = null;

            if (snap.exists()) {
                const msgs = Object.values(snap.val());
                msgs.sort((a, b) => (a.ts || 0) - (b.ts || 0));
                msgs.forEach(m => renderMessage(m, false));
            } else {
                // FIX: también limpia el DOM cuando no hay mensajes (tras borrar)
                container.innerHTML = `
                    <div class="chat-msg-system" style="margin-top:20px;">
                        <span>🎬 ¡Bienvenidos! Decidan qué película ver</span>
                    </div>`;
            }
            firstLoad = false;
            scrollToBottom();
        } else {
            if (snap.exists()) {
                const msgs = Object.values(snap.val());
                msgs.sort((a, b) => (a.ts || 0) - (b.ts || 0));
                const last = msgs[msgs.length - 1];
                renderMessage(last, true);
                if (!chatOpen) {
                    unreadCount++;
                    updateUnreadBadge();
                }
            } else {
                // FIX: si el admin borró todos los mensajes, limpiar DOM
                container.innerHTML = `
                    <div class="chat-msg-system" style="margin-top:20px;">
                        <span>🎬 ¡Bienvenidos! Decidan qué película ver</span>
                    </div>`;
                lastMessageGroup  = null;
                lastMessageSender = null;
            }
        }
    };

    onValue(activeMessagesRef, activeMessagesHandler);
}

function renderMessage(msg, animated) {
    const container = document.getElementById('chat-messages-area');
    if (!container) return;

    const isMine     = msg.bid === browserId;
    const isSystem   = msg.type === 'system';
    const senderName = msg.name || 'Anónimo';

    if (isSystem) {
        const div = document.createElement('div');
        div.className = 'chat-msg-system';
        div.innerHTML = `<span>${msg.text}</span>`;
        container.appendChild(div);
        scrollToBottom();
        return;
    }

    const time         = msg.ts ? formatTime(msg.ts) : '';
    const sameAsBefore = lastMessageSender === (isMine ? '__me__' : senderName);

    if (sameAsBefore && lastMessageGroup) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-msg-bubble';
        if (animated) bubble.style.animation = 'none';
        bubble.textContent = msg.text;
        const timeEl = lastMessageGroup.querySelector('.chat-msg-time');
        if (timeEl) lastMessageGroup.insertBefore(bubble, timeEl);
        else lastMessageGroup.appendChild(bubble);
        if (timeEl) timeEl.textContent = time;
    } else {
        const group = document.createElement('div');
        group.className = `chat-msg-group ${isMine ? 'mine' : 'other'}`;

        if (!isMine) {
            const senderEl = document.createElement('div');
            senderEl.className   = 'chat-msg-sender';
            senderEl.textContent = senderName;
            senderEl.style.color = msg.color || '#8c8c8c';
            group.appendChild(senderEl);
        }

        const bubble = document.createElement('div');
        bubble.className   = 'chat-msg-bubble';
        bubble.textContent = msg.text;
        group.appendChild(bubble);

        const timeEl = document.createElement('div');
        timeEl.className   = 'chat-msg-time';
        timeEl.textContent = time;
        group.appendChild(timeEl);

        container.appendChild(group);
        lastMessageGroup  = group;
        lastMessageSender = isMine ? '__me__' : senderName;
    }

    scrollToBottom();
}

function scrollToBottom() {
    const c = document.getElementById('chat-messages-area');
    if (c) c.scrollTop = c.scrollHeight;
}

function formatTime(ts) {
    const d = new Date(ts);
    return `${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
}

// ─── ENVIAR MENSAJE ────────────────────────────────────────────────────────
window.sendChatMessage = function() {
    const input = document.getElementById('chat-message-input');
    const text  = input.value.trim();
    if (!text || !db || !chatEnabled) return;

    push(ref(db, 'chat_messages'), {
        bid:   browserId,
        name:  myName,
        color: myColor,
        text:  text,
        ts:    Date.now(),
        type:  'chat'
    });

    input.value = '';
    clearTyping();
};

// ─── TYPING ────────────────────────────────────────────────────────────────
window.onChatTyping = function() {
    if (!db || !myName) return;
    set(ref(db, `chat_typing/${browserId}`), { name: myName, ts: Date.now() });
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(clearTyping, 2500);
};

function clearTyping() {
    if (!db) return;
    remove(ref(db, `chat_typing/${browserId}`));
}

function listenTyping() {
    if (!db) return;
    onValue(ref(db, 'chat_typing'), snap => {
        const el = document.getElementById('chat-typing');
        if (!el) return;
        if (!snap.exists()) { el.textContent = ''; return; }

        const now    = Date.now();
        const typers = Object.entries(snap.val())
            .filter(([bid, v]) => bid !== browserId && (now - (v.ts || 0)) < 3000)
            .map(([, v]) => v.name);

        if (typers.length === 0)      el.textContent = '';
        else if (typers.length === 1) el.textContent = `${typers[0]} está escribiendo...`;
        else                          el.textContent = `${typers.join(', ')} están escribiendo...`;
    });
}

// ─── NOMBRE DEL USUARIO ────────────────────────────────────────────────────
function loadUserName() {
    const saved = localStorage.getItem('cinefci_chat_name');
    if (saved) {
        myName  = saved;
        myColor = localStorage.getItem('cinefci_chat_color') || randomColor();
        localStorage.setItem('cinefci_chat_color', myColor);
        return true;
    }
    return false;
}

function randomColor() {
    return USER_COLORS[Math.floor(Math.random() * USER_COLORS.length)];
}

window.saveChatName = function() {
    const input = document.getElementById('chat-name-input');
    const name  = input.value.trim();
    if (!name || name.length < 2) {
        input.style.borderColor = '#e50914';
        input.focus();
        return;
    }
    myName  = name;
    myColor = randomColor();
    localStorage.setItem('cinefci_chat_name', myName);
    localStorage.setItem('cinefci_chat_color', myColor);

    setupPresence();

    if (db) {
        push(ref(db, 'chat_messages'), {
            type: 'system',
            text: `👋 ${myName} se unió al chat`,
            ts:   Date.now()
        });
    }

    updateChatUI();

    // FIX: resetear DOM y re-escuchar (listenMessages cancela el listener anterior)
    lastMessageGroup  = null;
    lastMessageSender = null;
    document.getElementById('chat-messages-area').innerHTML = '';
    listenMessages();
};

window.resetChatName = function() {
    if (!confirm('¿Cambiar tu nombre en el chat?')) return;
    localStorage.removeItem('cinefci_chat_name');
    localStorage.removeItem('cinefci_chat_color');
    myName  = '';
    myColor = '';
    lastMessageGroup  = null;
    lastMessageSender = null;
    updateChatUI();
};

// ─── ACTUALIZAR UI ─────────────────────────────────────────────────────────
function updateChatUI() {
    const disabledMsg  = document.getElementById('chat-disabled-msg');
    const nameSetup    = document.getElementById('chat-name-setup');
    const messagesArea = document.getElementById('chat-messages-area');
    const typingEl     = document.getElementById('chat-typing');
    const inputArea    = document.getElementById('chat-input-area');
    const nameFooter   = document.getElementById('chat-my-name-footer');

    disabledMsg.style.display  = 'none';
    nameSetup.style.display    = 'none';
    messagesArea.style.display = 'none';
    typingEl.style.display     = 'none';
    inputArea.style.display    = 'none';
    nameFooter.style.display   = 'none';

    if (!chatEnabled) {
        disabledMsg.style.display = 'flex';
        return;
    }
    if (!myName) {
        nameSetup.style.display = 'flex';
        return;
    }

    messagesArea.style.display = 'flex';
    typingEl.style.display     = 'block';
    inputArea.style.display    = 'flex';
    nameFooter.style.display   = 'flex';
    nameFooter.querySelector('strong').textContent = myName;
    nameFooter.querySelector('strong').style.color = myColor;

    scrollToBottom();
}

// ─── TOGGLE CHAT ───────────────────────────────────────────────────────────
window.toggleChat = function() {
    chatOpen = !chatOpen;
    const popup     = document.getElementById('chat-popup');
    const iconOpen  = document.getElementById('chat-icon-open');
    const iconClose = document.getElementById('chat-icon-close');

    if (chatOpen) {
        if (!chatReady) initChat();
        if (loadUserName()) updateChatUI();

        popup.classList.add('open');
        iconOpen.style.display  = 'none';
        iconClose.style.display = 'block';

        unreadCount = 0;
        updateUnreadBadge();
        setTimeout(scrollToBottom, 100);
    } else {
        popup.classList.remove('open');
        iconOpen.style.display  = 'block';
        iconClose.style.display = 'none';
    }
};

function updateUnreadBadge() {
    const badge = document.getElementById('chat-unread');
    if (unreadCount > 0) {
        badge.textContent   = unreadCount > 9 ? '9+' : unreadCount;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// ─── INIT AUTOMÁTICO ───────────────────────────────────────────────────────
(function autoInit() {
    loadUserName();
    initChat();
})();

document.addEventListener('DOMContentLoaded', () => {
    const trailer = document.getElementById('modalTrailer');
    if (!trailer) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && trailer.dataset.baseSrc) {
                trailer.src = trailer.dataset.baseSrc + '&autoplay=1';
                observer.unobserve(trailer);
            }
        });
    }, { threshold: 0.6 });

    observer.observe(trailer);
});
</script>


</body>
</html>
