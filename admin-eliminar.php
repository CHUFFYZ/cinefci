<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Película - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🗑️ Eliminar Película</h1>
            <a href="panel.php" class="back-btn">← Volver al Panel</a>
        </div>

        <div class="warning-box">
            <strong>⚠️ ADVERTENCIA:</strong>
            <p>Esta acción NO se puede deshacer. Se eliminarán todos los votos, calificaciones y datos relacionados con la película.</p>
        </div>

        <!-- BUSCADOR + FILTROS -->
        <div class="admin-search-bar">
            <span class="admin-search-icon">🔍</span>
            <input type="text" class="admin-search-input" id="searchInput" placeholder="Buscar película por nombre..." oninput="applyFilters()">
            <button class="admin-search-clear" id="searchClear" onclick="clearSearch()">✕</button>
        </div>
        <div class="admin-cat-filters" id="catFilters">
            <span class="admin-cat-label">Categoría:</span>
            <button class="admin-cat-btn active" data-cat="" onclick="setCat(this,'')">Todas</button>
        </div>
        <p style="font-size:11px;color:#555;margin-bottom:12px;" id="filterCount"></p>

        <!-- LISTA DE PELÍCULAS -->
        <div class="movies-grid-delete" id="moviesGrid">
            <!-- Se llenará dinámicamente -->
        </div>

        <div id="message" class="message"></div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN -->
    <div class="modal" id="deleteModal">
        <div class="modal-content danger">
            <div class="modal-header">
                <h3>⚠️ Confirmar Eliminación</h3>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar esta película?</p>
                <h4 id="deleteMovieTitle"></h4>
                <p><strong>Esta acción es permanente y no se puede deshacer.</strong></p>
                
                <div class="confirmation-input">
                    <label>Escribe "ELIMINAR" para confirmar:</label>
                    <input type="text" id="confirmText" placeholder="ELIMINAR">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-danger" onclick="confirmDelete()">
                    🗑️ Eliminar Definitivamente
                </button>
                <button class="btn-secondary" onclick="closeModal()">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

<script>
let allMovies = [];
let movieToDelete = null;
let currentCat = '';

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadMovies() {
    try {
        const [movRes, catRes] = await Promise.all([
            fetch('api.php?action=movies_list'),
            fetch('api.php?action=categorias')
        ]);
        allMovies = await movRes.json();
        const cats = await catRes.json();
        buildCatFilters(cats);
        applyFilters();
    } catch (err) {
        console.error('Error al cargar películas:', err);
        showMessage('Error al cargar películas', 'error');
    }
}

function buildCatFilters(cats) {
    const container = document.getElementById('catFilters');
    const extra = cats.map(c => `<button class="admin-cat-btn" data-cat="${c.nombre}" onclick="setCat(this,'${c.nombre.replace(/'/g,"\'")}')">${c.nombre}</button>`).join('');
    container.innerHTML = `<span class="admin-cat-label">Categoría:</span><button class="admin-cat-btn active" data-cat="" onclick="setCat(this,'')">Todas</button>${extra}`;
}

function setCat(btn, cat) {
    currentCat = cat;
    document.querySelectorAll('#catFilters .admin-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchClear').style.display = 'none';
    applyFilters();
}

function applyFilters() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.getElementById('searchClear').style.display = q ? 'block' : 'none';
    const filtered = allMovies.filter(m => {
        const matchQ = !q || m.titulo.toLowerCase().includes(q);
        const matchCat = !currentCat || (m.categorias && m.categorias.includes(currentCat));
        return matchQ && matchCat;
    });
    const count = document.getElementById('filterCount');
    count.textContent = filtered.length < allMovies.length ? `Mostrando ${filtered.length} de ${allMovies.length} películas` : '';
    renderMoviesGrid(filtered);
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR GRID DE PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
function renderMoviesGrid(movies) {
    if (movies === undefined) movies = allMovies;
    const grid = document.getElementById('moviesGrid');
    
    if (movies.length === 0) {
        grid.innerHTML = '<div class="empty-message admin-no-results">No se encontraron películas</div>';
        return;
    }
    
    grid.innerHTML = movies.map(movie => `
        <div class="movie-card-delete">
            <img src="${movie.poster}" alt="${movie.titulo}" class="movie-poster-small">
            <div class="movie-info-delete">
                <h4>${movie.titulo}</h4>
                <button class="btn-danger-small" onclick="openDeleteModal(${movie.id}, '${movie.titulo.replace(/'/g, "\\'")}')">
                    🗑️ Eliminar
                </button>
            </div>
        </div>
    `).join('');
}

// ═══════════════════════════════════════════════════════════════════════════
// ABRIR MODAL DE CONFIRMACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function openDeleteModal(movieId, movieTitle) {
    movieToDelete = movieId;
    document.getElementById('deleteMovieTitle').textContent = movieTitle;
    document.getElementById('confirmText').value = '';
    document.getElementById('deleteModal').classList.add('active');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
    movieToDelete = null;
}

// ═══════════════════════════════════════════════════════════════════════════
// CONFIRMAR Y ELIMINAR
// ═══════════════════════════════════════════════════════════════════════════
async function confirmDelete() {
    const confirmText = document.getElementById('confirmText').value.trim();
    
    if (confirmText !== 'ELIMINAR') {
        showMessage('Debes escribir "ELIMINAR" para confirmar', 'error');
        return;
    }
    
    if (!movieToDelete) {
        showMessage('Error: No se seleccionó ninguna película', 'error');
        return;
    }
    
    try {
        const res = await fetch('api.php?action=delete_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: movieToDelete })
        });
        
        const result = await res.json();
        
        if (result.success) {
            showMessage('✓ Película eliminada exitosamente', 'success');
            closeModal();
            await loadMovies();
        } else {
            showMessage('Error: ' + result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showMessage('Error al conectar con el servidor', 'error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// MOSTRAR MENSAJES
// ═══════════════════════════════════════════════════════════════════════════
function showMessage(text, type) {
    const msg = document.getElementById('message');
    msg.textContent = text;
    msg.className = 'message ' + type;
    msg.style.display = 'block';
    
    setTimeout(() => {
        msg.style.display = 'none';
    }, 5000);
}

// ═══════════════════════════════════════════════════════════════════════════
// INICIALIZAR
// ═══════════════════════════════════════════════════════════════════════════
window.addEventListener('load', loadMovies);
</script>
</body>
</html>
