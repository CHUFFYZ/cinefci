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

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadMovies() {
    try {
        const res = await fetch('api.php?action=movies_list');
        allMovies = await res.json();
        renderMoviesGrid();
    } catch (err) {
        console.error('Error al cargar películas:', err);
        showMessage('Error al cargar películas', 'error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR GRID DE PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
function renderMoviesGrid() {
    const grid = document.getElementById('moviesGrid');
    
    if (allMovies.length === 0) {
        grid.innerHTML = '<div class="empty-message">No hay películas registradas</div>';
        return;
    }
    
    grid.innerHTML = allMovies.map(movie => `
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
