<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Película - CineFCI</title>
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
</style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>➕ Agregar Nueva Película</h1>
            <a href="panel.php" class="back-btn">← Volver al Panel</a>
        </div>

        <form id="addMovieForm" class="admin-form">
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
                <div class="form-group">
                    <label for="trailer">URL Trailer *</label>
                    <input type="url" id="trailer" name="trailer" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="resumen">Resumen / Sinopsis *</label>
                <textarea id="resumen" name="resumen" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Categorías (máximo 10) *</label>
                <div class="admin-search-bar" style="margin-bottom:10px;">
                    <span class="admin-search-icon">🔍</span>
                    <input type="text" class="admin-search-input" id="catSearch" placeholder="Buscar categoría..." oninput="filterCats()">
                    <button class="admin-search-clear" id="catSearchClear" onclick="clearCatSearch()">✕</button>
                </div>
                <div class="categories-grid" id="categoriesGrid">
                    <!-- Se llenarán dinámicamente -->
                </div>
                <small class="form-hint">Selecciona entre 1 y 10 categorías</small>
            </div>

            <!-- SELLO NUEVA -->
            <div class="form-group" style="background:rgba(0,200,83,0.06); border:1px solid rgba(0,200,83,0.25); border-radius:12px; padding:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:14px;">
                    <input type="checkbox" id="marcar_nueva" onchange="toggleNuevaOptions()" style="width:18px;height:18px;accent-color:#00c853;">
                    <span style="font-weight:700; color:#00c853;">✨ Marcar como "Nuevo en Cartelera"</span>
                </label>
                <div id="nuevaOptions" style="display:none;">
                    <p style="font-size:12px; color:#8c8c8c; margin-bottom:12px;">Elige hasta cuándo mostrar el sello. Si no seleccionas fecha, se puede quitar manualmente desde el panel.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;" id="duracionBtns">
                        <button type="button" class="dur-btn" onclick="setNuevaDuration(3)">3 días</button>
                        <button type="button" class="dur-btn" onclick="setNuevaDuration(7)">1 semana</button>
                        <button type="button" class="dur-btn" onclick="setNuevaDuration(14)">2 semanas</button>
                        <button type="button" class="dur-btn" onclick="setNuevaDuration(30)">1 mes</button>
                        <button type="button" class="dur-btn" onclick="setNuevaDuration('custom')">Fecha exacta</button>
                        <button type="button" class="dur-btn dur-indef" onclick="setNuevaDuration(null)">Sin límite</button>
                    </div>
                    <div id="nuevaCustomDate" style="display:none;">
                        <input type="datetime-local" id="nueva_fecha_fin" style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.15); border-radius:7px; padding:9px 12px; color:#fff; font-family:'Montserrat',sans-serif; font-size:12px; width:100%;">
                    </div>
                </div>
            </div>

            <!-- SELLO PRÓXIMAMENTE -->
            <div class="form-group" style="background:rgba(255,140,0,0.06); border:1px solid rgba(255,140,0,0.3); border-radius:12px; padding:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:14px;">
                    <input type="checkbox" id="marcar_proximamente" onchange="toggleProximamenteOptions()" style="width:18px;height:18px;accent-color:#ff8c00;">
                    <span style="font-weight:700; color:#ff8c00;">🎬 Marcar como "Próximamente"</span>
                </label>
                <div id="proximamenteOptions" style="display:none;">
                    <p style="font-size:12px; color:#8c8c8c; margin-bottom:12px;">La película no aparecerá en la cartelera general ni podrá recibir votos. Solo se verá en el filtro "Próximamente".</p>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;" id="duracionProxBtns">
                        <button type="button" class="dur-btn prox-dur-btn" onclick="setProxDuration(7)">1 semana</button>
                        <button type="button" class="dur-btn prox-dur-btn" onclick="setProxDuration(14)">2 semanas</button>
                        <button type="button" class="dur-btn prox-dur-btn" onclick="setProxDuration(30)">1 mes</button>
                        <button type="button" class="dur-btn prox-dur-btn" onclick="setProxDuration(60)">2 meses</button>
                        <button type="button" class="dur-btn prox-dur-btn" onclick="setProxDuration('custom')">Fecha exacta</button>
                        <button type="button" class="dur-btn dur-indef prox-dur-btn" onclick="setProxDuration(null)">Sin límite</button>
                    </div>
                    <div id="proxCustomDate" style="display:none;">
                        <input type="datetime-local" id="prox_fecha_fin" style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.15); border-radius:7px; padding:9px 12px; color:#fff; font-family:'Montserrat',sans-serif; font-size:12px; width:100%;">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    ✓ Guardar Película
                </button>
                <button type="button" class="btn-secondary" onclick="window.close()">
                    Cancelar
                </button>
            </div>
        </form>

        <div id="message" class="message"></div>
    </div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// NUEVA BADGE
// ═══════════════════════════════════════════════════════════════════════════
let nuevaDuracion = undefined;
let proxDuracion  = undefined;

function toggleNuevaOptions() {
    const checked = document.getElementById('marcar_nueva').checked;
    document.getElementById('nuevaOptions').style.display = checked ? 'block' : 'none';
    if (!checked) nuevaDuracion = undefined;
}

function toggleProximamenteOptions() {
    const checked = document.getElementById('marcar_proximamente').checked;
    document.getElementById('proximamenteOptions').style.display = checked ? 'block' : 'none';
    if (!checked) proxDuracion = undefined;
}

function setNuevaDuration(val) {
    document.querySelectorAll('.dur-btn:not(.prox-dur-btn)').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('nuevaCustomDate').style.display = 'none';
    if (val === null) { nuevaDuracion = null; }
    else if (val === 'custom') { nuevaDuracion = 'custom'; document.getElementById('nuevaCustomDate').style.display = 'block'; const now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); document.getElementById('nueva_fecha_fin').min = now.toISOString().slice(0,16); }
    else { const d = new Date(); d.setDate(d.getDate() + parseInt(val)); nuevaDuracion = d.toISOString().slice(0,19).replace('T',' '); }
}

function setProxDuration(val) {
    document.querySelectorAll('.prox-dur-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('proxCustomDate').style.display = 'none';
    if (val === null) { proxDuracion = null; }
    else if (val === 'custom') { proxDuracion = 'custom'; document.getElementById('proxCustomDate').style.display = 'block'; const now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); document.getElementById('prox_fecha_fin').min = now.toISOString().slice(0,16); }
    else { const d = new Date(); d.setDate(d.getDate() + parseInt(val)); proxDuracion = d.toISOString().slice(0,19).replace('T',' '); }
}

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR CATEGORÍAS + BUSCADOR
// ═══════════════════════════════════════════════════════════════════════════
let allCategoriesRaw = [];

async function loadCategories() {
    try {
        const res = await fetch('api.php?action=categorias');
        allCategoriesRaw = await res.json();
        renderCatGrid(allCategoriesRaw);
    } catch (err) {
        console.error('Error al cargar categorías:', err);
        showMessage('Error al cargar categorías', 'error');
    }
}

function renderCatGrid(cats) {
    const grid = document.getElementById('categoriesGrid');
    grid.innerHTML = cats.map(cat => `
        <label class="category-checkbox">
            <input type="checkbox" name="categorias[]" value="${cat.id}" onchange="limitCategories()">
            <span>${cat.nombre}</span>
        </label>
    `).join('');
}

function filterCats() {
    const q = document.getElementById('catSearch').value.toLowerCase().trim();
    document.getElementById('catSearchClear').style.display = q ? 'block' : 'none';
    const filtered = q ? allCategoriesRaw.filter(c => c.nombre.toLowerCase().includes(q)) : allCategoriesRaw;
    renderCatGrid(filtered);
}

function clearCatSearch() {
    document.getElementById('catSearch').value = '';
    document.getElementById('catSearchClear').style.display = 'none';
    renderCatGrid(allCategoriesRaw);
}

// ═══════════════════════════════════════════════════════════════════════════
// LIMITAR MÁXIMO  10 CATEGORÍAS
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
document.getElementById('addMovieForm').addEventListener('submit', async (e) => {
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
        titulo: formData.get('titulo'),
        poster: formData.get('poster'),
        poster_large: formData.get('poster_large'),
        trailer: formData.get('trailer'),
        resumen: formData.get('resumen'),
        categorias: categorias
    };
    
    try {
        const res = await fetch('api.php?action=add_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (result.success) {
            showMessage('✓ Película agregada exitosamente', 'success');
            
            // Si está marcada como nueva, guardar el badge
            const marcarNueva = document.getElementById('marcar_nueva').checked;
            if (marcarNueva) {
                let fechaFin = nuevaDuracion;
                if (nuevaDuracion === 'custom') {
                    fechaFin = document.getElementById('nueva_fecha_fin').value || null;
                }
                await fetch('api.php?action=set_new_badge', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pelicula_id: result.id, fecha_fin: fechaFin })
                });
            }

            // Si está marcada como próximamente, guardar el badge
            const marcarProx = document.getElementById('marcar_proximamente').checked;
            if (marcarProx) {
                let fechaFinProx = proxDuracion;
                if (proxDuracion === 'custom') {
                    fechaFinProx = document.getElementById('prox_fecha_fin').value || null;
                }
                await fetch('api.php?action=set_proximamente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pelicula_id: result.id, fecha_fin: fechaFinProx })
                });
            }

            setTimeout(() => {
                e.target.reset();
                document.getElementById('nuevaOptions').style.display = 'none';
                document.getElementById('marcar_nueva').checked = false;
                document.getElementById('proximamenteOptions').style.display = 'none';
                document.getElementById('marcar_proximamente').checked = false;
                nuevaDuracion = undefined;
                proxDuracion  = undefined;
                limitCategories();
            }, 1500);
        } else {
            showMessage('Error: ' + result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showMessage('Error al conectar con el servidor', 'error');
    }
});

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
window.addEventListener('load', loadCategories);
</script>
</body>
</html>
