<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Película - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
<style>
.dur-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    color: #ccc;
    padding: 7px 14px;
    border-radius: 7px;
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.dur-btn:hover, .dur-btn.active {
    background: rgba(0,200,83,0.2);
    border-color: #00c853;
    color: #00e676;
}
.dur-btn.dur-indef.active {
    background: rgba(100,100,255,0.2);
    border-color: #7986cb;
    color: #9fa8da;
}
.nueva-status-active { color: #00e676; font-weight: 700; font-size: 13px; }
.nueva-status-none   { color: #8c8c8c; font-size: 13px; }
</style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>✏️ Modificar Película</h1>
            <a href="panel.php" class="back-btn">← Volver al Panel</a>
        </div>

        <!-- SELECTOR DE PELÍCULA -->
        <div class="selector-section">
            <label for="movieSelector">Selecciona una película:</label>
            <select id="movieSelector" onchange="loadMovieData()">
                <option value="">-- Selecciona --</option>
            </select>
        </div>

        <!-- FORMULARIO DE EDICIÓN -->
        <form id="editMovieForm" class="admin-form" style="display: none;">
            <input type="hidden" id="movie_id" name="movie_id">
            
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="poster">URL Poster (pequeño) *</label>
                    <input type="url" id="poster" name="poster" required>
                </div>

                <div class="form-group">
                    <label for="poster_large">URL Poster (grande) *</label>
                    <input type="url" id="poster_large" name="poster_large" required>
                </div>
            </div>

            <div class="form-group">
                    <label for="trailer">URL Trailer *</label>
                    <input type="url" id="trailer" name="trailer" required>
            </div>

            <div class="form-group">
                <label for="resumen">Resumen / Sinopsis *</label>
                <textarea id="resumen" name="resumen" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label for="veces_ganadora">Veces Ganadora (historial)</label>
                <input type="number" id="veces_ganadora" name="veces_ganadora" min="0" value="0">
                <small class="form-hint">Contador manual de victorias históricas</small>
            </div>

            <div class="form-group">
                <label>Categorías (máximo 10) *</label>
                <div class="categories-grid" id="categoriesGrid">
                    <!-- Se llenarán dinámicamente -->
                </div>
                <small class="form-hint">Selecciona entre 1 y 10 categorías</small>
            </div>

            <!-- SELLO NUEVA -->
            <div class="form-group" style="background:rgba(0,200,83,0.06); border:1px solid rgba(0,200,83,0.25); border-radius:12px; padding:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                    <span style="font-weight:700; color:#00c853;">✨ Sello "Nuevo en Cartelera"</span>
                    <span id="nuevaStatusText" class="nueva-status-none">Sin sello activo</span>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;" id="duracionBtns">
                    <button type="button" class="dur-btn" onclick="setNuevaDuration(3)">3 días</button>
                    <button type="button" class="dur-btn" onclick="setNuevaDuration(7)">1 semana</button>
                    <button type="button" class="dur-btn" onclick="setNuevaDuration(14)">2 semanas</button>
                    <button type="button" class="dur-btn" onclick="setNuevaDuration(30)">1 mes</button>
                    <button type="button" class="dur-btn" onclick="setNuevaDuration('custom')">Fecha exacta</button>
                    <button type="button" class="dur-btn dur-indef" onclick="setNuevaDuration(null)">Sin límite</button>
                </div>
                <div id="nuevaCustomDate" style="display:none; margin-bottom:10px;">
                    <input type="datetime-local" id="nueva_fecha_fin" style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.15); border-radius:7px; padding:9px 12px; color:#fff; font-family:'Montserrat',sans-serif; font-size:12px; width:100%;">
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" id="btnActivarNueva" onclick="activarNuevaBadge()" style="background:rgba(0,200,83,0.2); border:1px solid #00c853; color:#00e676; padding:8px 16px; border-radius:7px; font-family:'Montserrat',sans-serif; font-size:12px; font-weight:700; cursor:pointer;">✨ Aplicar sello</button>
                    <button type="button" id="btnQuitarNueva"  onclick="quitarNuevaBadge()"  style="background:rgba(229,9,20,0.1); border:1px solid rgba(229,9,20,0.4); color:#e50914; padding:8px 16px; border-radius:7px; font-family:'Montserrat',sans-serif; font-size:12px; font-weight:700; cursor:pointer; display:none;">✕ Quitar sello</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    ✓ Guardar Cambios
                </button>
                <button type="button" class="btn-secondary" onclick="cancelEdit()">
                    Cancelar
                </button>
            </div>
        </form>

        <div id="message" class="message"></div>
    </div>

<script>
let allCategories = [];
let currentMovie = null;
let nuevaDuracion = undefined;
let currentNuevaBadge = null; // datos del badge actual si existe

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR LISTA DE PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadMoviesList() {
    try {
        const res = await fetch('api.php?action=movies_list');
        const movies = await res.json();
        
        const selector = document.getElementById('movieSelector');
        selector.innerHTML = '<option value="">-- Selecciona --</option>' + 
            movies.map(m => `<option value="${m.id}">${m.titulo}</option>`).join('');
    } catch (err) {
        console.error('Error al cargar películas:', err);
        showMessage('Error al cargar lista de películas', 'error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR CATEGORÍAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadCategories() {
    try {
        const res = await fetch('api.php?action=categorias');
        allCategories = await res.json();
    } catch (err) {
        console.error('Error al cargar categorías:', err);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR DATOS DE PELÍCULA SELECCIONADA
// ═══════════════════════════════════════════════════════════════════════════
async function loadMovieData() {
    const movieId = document.getElementById('movieSelector').value;
    
    if (!movieId) {
        document.getElementById('editMovieForm').style.display = 'none';
        return;
    }
    
    try {
        const res = await fetch(`api.php?action=get_movie&id=${movieId}`);
        currentMovie = await res.json();
        
        if (currentMovie.error) {
            showMessage('Error al cargar película', 'error');
            return;
        }
        
        // Llenar formulario
        document.getElementById('movie_id').value = currentMovie.id;
        document.getElementById('titulo').value = currentMovie.titulo;
        document.getElementById('poster').value = currentMovie.poster;
        document.getElementById('poster_large').value = currentMovie.poster_large;
        document.getElementById('trailer').value = currentMovie.trailer
        document.getElementById('resumen').value = currentMovie.resumen;
        document.getElementById('veces_ganadora').value = currentMovie.veces_ganadora || 0;
        
        // Renderizar categorías
        renderCategories(currentMovie.categorias || []);
        
        // Cargar estado del badge nueva
        await loadNuevaBadgeStatus(movieId);
        
        document.getElementById('editMovieForm').style.display = 'block';
    } catch (err) {
        console.error('Error:', err);
        showMessage('Error al cargar datos de la película', 'error');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR CATEGORÍAS
// ═══════════════════════════════════════════════════════════════════════════
function renderCategories(selectedCategories) {
    const grid = document.getElementById('categoriesGrid');
    
    grid.innerHTML = allCategories.map(cat => {
        const isChecked = selectedCategories.includes(cat.nombre);
        return `
            <label class="category-checkbox">
                <input type="checkbox" name="categorias[]" value="${cat.id}" 
                    ${isChecked ? 'checked' : ''} onchange="limitCategories()">
                <span>${cat.nombre}</span>
            </label>
        `;
    }).join('');
}

// ═══════════════════════════════════════════════════════════════════════════
// LIMITAR MÁXIMO 10 CATEGORÍAS
// ═══════════════════════════════════════════════════════════════════════════
function limitCategories() {
    const checkboxes = document.querySelectorAll('input[name="categorias[]"]');
    const checked = document.querySelectorAll('input[name="categorias[]"]:checked');
    
    if (checked.length >= 10) {
        checkboxes.forEach(cb => {
            if (!cb.checked) {
                cb.disabled = true;
            }
        });
    } else {
        checkboxes.forEach(cb => cb.disabled = false);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// ENVIAR FORMULARIO
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('editMovieForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const categorias = [];
    
    document.querySelectorAll('input[name="categorias[]"]:checked').forEach(cb => {
        categorias.push(cb.value);
    });
    
    if (categorias.length === 0) {
        showMessage('Debes seleccionar al menos una categoría', 'error');
        return;
    }
    
    const data = {
        id: formData.get('movie_id'),
        titulo: formData.get('titulo'),
        poster: formData.get('poster'),
        poster_large: formData.get('poster_large'),
        trailer: formData.get('trailer'),
        resumen: formData.get('resumen'),
        veces_ganadora: parseInt(formData.get('veces_ganadora')) || 0,
        categorias: categorias
    };
    
    try {
        const res = await fetch('api.php?action=update_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (result.success) {
            showMessage('✓ Película actualizada exitosamente', 'success');
            await loadMoviesList();
        } else {
            showMessage('Error: ' + result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showMessage('Error al conectar con el servidor', 'error');
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// NUEVA BADGE
// ═══════════════════════════════════════════════════════════════════════════
async function loadNuevaBadgeStatus(movieId) {
    try {
        const res = await fetch('api.php?action=movies_with_new');
        const movies = await res.json();
        const movie = movies.find(m => String(m.id) === String(movieId));
        currentNuevaBadge = movie || null;

        const statusEl  = document.getElementById('nuevaStatusText');
        const btnQuitar = document.getElementById('btnQuitarNueva');

        if (movie && (movie.es_nueva === 1 || movie.es_nueva === '1')) {
            const fin = movie.fecha_fin_nueva;
            statusEl.className = 'nueva-status-active';
            statusEl.textContent = fin ? `✨ Activo hasta ${new Date(fin).toLocaleString('es-MX')}` : '✨ Activo (sin límite)';
            btnQuitar.style.display = 'inline-block';
        } else {
            statusEl.className = 'nueva-status-none';
            statusEl.textContent = 'Sin sello activo';
            btnQuitar.style.display = 'none';
        }
    } catch (e) { console.error(e); }
}

function setNuevaDuration(val) {
    document.querySelectorAll('.dur-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('nuevaCustomDate').style.display = 'none';

    if (val === null) {
        nuevaDuracion = null;
    } else if (val === 'custom') {
        nuevaDuracion = 'custom';
        document.getElementById('nuevaCustomDate').style.display = 'block';
        const now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('nueva_fecha_fin').min = now.toISOString().slice(0,16);
    } else {
        const d = new Date();
        d.setDate(d.getDate() + parseInt(val));
        nuevaDuracion = d.toISOString().slice(0,19).replace('T',' ');
    }
}

async function activarNuevaBadge() {
    const movieId = document.getElementById('movie_id').value;
    if (!movieId) { showMessage('Selecciona una película primero', 'error'); return; }

    let fechaFin = nuevaDuracion;
    if (nuevaDuracion === 'custom') {
        fechaFin = document.getElementById('nueva_fecha_fin').value || null;
    }
    if (nuevaDuracion === undefined) {
        showMessage('Selecciona una duración para el sello', 'error'); return;
    }

    try {
        const res = await fetch('api.php?action=set_new_badge', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: movieId, fecha_fin: fechaFin })
        });
        const result = await res.json();
        if (result.success) {
            showMessage('✨ Sello "Nuevo" aplicado', 'success');
            await loadNuevaBadgeStatus(movieId);
        } else { showMessage('Error: ' + result.message, 'error'); }
    } catch (e) { showMessage('Error al conectar', 'error'); }
}

async function quitarNuevaBadge() {
    const movieId = document.getElementById('movie_id').value;
    if (!movieId) return;
    if (!confirm('¿Quitar el sello "Nuevo" de esta película?')) return;
    try {
        const res = await fetch('api.php?action=remove_new_badge', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: movieId })
        });
        const result = await res.json();
        if (result.success) {
            showMessage('Sello quitado correctamente', 'success');
            await loadNuevaBadgeStatus(movieId);
        } else { showMessage('Error: ' + result.message, 'error'); }
    } catch (e) { showMessage('Error al conectar', 'error'); }
}

// ═══════════════════════════════════════════════════════════════════════════
// CANCELAR EDICIÓN
// ═══════════════════════════════════════════════════════════════════════════
function cancelEdit() {
    document.getElementById('movieSelector').value = '';
    document.getElementById('editMovieForm').style.display = 'none';
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
window.addEventListener('load', async () => {
    await loadCategories();
    await loadMoviesList();
});
</script>
</body>
</html>
