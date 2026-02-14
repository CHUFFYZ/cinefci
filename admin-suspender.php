<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suspender Película - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>⏸️ Suspender Película</h1>
            <a href="panel.php" class="back-btn">← Volver al Panel</a>
        </div>

        <div class="info-box">
            <strong>ℹ️ Importante:</strong>
            <p>Las películas suspendidas aparecerán en escala de grises y NO permitirán votos, pero SÍ se podrán calificar.</p>
        </div>

        <!-- LISTA DE PELÍCULAS -->
        <div class="movies-table-container">
            <h3>Películas Disponibles</h3>
            <table class="movies-table" id="moviesTable">
                <thead>
                    <tr>
                        <th>Película</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>

        <div id="message" class="message"></div>
    </div>

    <!-- MODAL PARA SUSPENDER -->
    <div class="modal" id="suspendModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Suspender: <span id="movieTitle"></span></h3>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            <form id="suspendForm">
                <input type="hidden" id="suspend_movie_id">
                
                <div class="form-group">
                    <label for="fecha_fin">Fecha y hora de finalización *</label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" required>
                    <small class="form-hint">La película quedará suspendida hasta esta fecha</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        ⏸️ Suspender Película
                    </button>
                    <button type="button" class="btn-secondary" onclick="closeModal()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
let allMovies = [];

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadMovies() {
    try {
        const res = await fetch('api.php?action=movies_with_suspensions');
        allMovies = await res.json();
        renderMoviesTable();
    } catch (err) {
        console.error('Error al cargar películas:', err);
        showMessage('Error al cargar películas', 'error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function renderMoviesTable() {
    const tbody = document.querySelector('#moviesTable tbody');
    
    if (allMovies.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No hay películas</td></tr>';
        return;
    }
    
    tbody.innerHTML = allMovies.map(movie => {
        const isSuspended = movie.suspendida;
        const suspensionEnd = movie.fecha_suspension ? new Date(movie.fecha_suspension) : null;
        
        let statusHTML = '';
        if (isSuspended && suspensionEnd) {
            const now = new Date();
            const timeLeft = suspensionEnd - now;
            
            if (timeLeft > 0) {
                statusHTML = `<span class="status-suspended">⏸️ Suspendida hasta ${formatDate(suspensionEnd)}</span>`;
            } else {
                statusHTML = `<span class="status-expired">⏱️ Suspensión vencida</span>`;
            }
        } else {
            statusHTML = `<span class="status-active">✓ Activa</span>`;
        }
        
        return `
            <tr>
                <td><strong>${movie.titulo}</strong></td>
                <td>${statusHTML}</td>
                <td>
                    ${!isSuspended ? 
                        `<button class="btn-action suspend" onclick="openSuspendModal(${movie.id}, '${movie.titulo}')">⏸️ Suspender</button>` :
                        `<button class="btn-action resume" onclick="removeSuspension(${movie.id})">▶️ Reactivar</button>`
                    }
                </td>
            </tr>
        `;
    }).join('');
}

// ═══════════════════════════════════════════════════════════════════════════
// ABRIR MODAL PARA SUSPENDER
// ═══════════════════════════════════════════════════════════════════════════
function openSuspendModal(movieId, movieTitle) {
    document.getElementById('suspend_movie_id').value = movieId;
    document.getElementById('movieTitle').textContent = movieTitle;
    
    // Establecer fecha mínima (hoy)
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('fecha_fin').min = now.toISOString().slice(0, 16);
    
    document.getElementById('suspendModal').classList.add('active');
}

function closeModal() {
    document.getElementById('suspendModal').classList.remove('active');
    document.getElementById('suspendForm').reset();
}

// ═══════════════════════════════════════════════════════════════════════════
// ENVIAR SUSPENSIÓN
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('suspendForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const movieId = document.getElementById('suspend_movie_id').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
    try {
        const res = await fetch('api.php?action=suspend_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pelicula_id: movieId,
                fecha_finalizacion: fechaFin
            })
        });
        
        const result = await res.json();
        
        if (result.success) {
            showMessage('✓ Película suspendida exitosamente', 'success');
            closeModal();
            await loadMovies();
        } else {
            showMessage('Error: ' + result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showMessage('Error al conectar con el servidor', 'error');
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// QUITAR SUSPENSIÓN
// ═══════════════════════════════════════════════════════════════════════════
async function removeSuspension(movieId) {
    if (!confirm('¿Estás seguro de que quieres reactivar esta película?')) {
        return;
    }
    
    try {
        const res = await fetch('api.php?action=unsuspend_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: movieId })
        });
        
        const result = await res.json();
        
        if (result.success) {
            showMessage('✓ Película reactivada exitosamente', 'success');
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
// FORMATEAR FECHA
// ═══════════════════════════════════════════════════════════════════════════
function formatDate(date) {
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
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

// Actualizar cada 30 segundos
setInterval(loadMovies, 30000);
</script>
</body>
</html>
