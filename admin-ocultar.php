<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocultar de Cartelera - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .duration-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }
        .duration-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15);
            color: #ccc;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .duration-btn:hover, .duration-btn.active {
            background: rgba(229,9,20,0.2);
            border-color: #e50914;
            color: #fff;
        }
        .duration-btn.indefinido.active {
            background: rgba(255,165,0,0.2);
            border-color: orange;
            color: orange;
        }
        .custom-date-group {
            display: none;
        }
        .custom-date-group.show {
            display: block;
        }
        .status-hidden {
            color: #ff9800;
            font-weight: 600;
        }
        .status-hidden-indef {
            color: #ff5722;
            font-weight: 600;
        }
        .btn-action.unhide {
            background: linear-gradient(135deg, #ff9800, #e65100);
        }
        .btn-action.unhide:hover {
            opacity: 0.85;
        }
        .hide-info-banner {
            background: rgba(255, 152, 0, 0.08);
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #ccc;
            line-height: 1.6;
        }
        .hide-info-banner strong {
            color: #ff9800;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>👁️ Ocultar de Cartelera</h1>
            <a href="panel.php" class="back-btn">← Volver al Panel</a>
        </div>

        <div class="hide-info-banner">
            <strong>👁️ Sobre esta función:</strong><br>
            Las películas ocultas <strong>no aparecerán en la cartelera pública</strong> para los usuarios.
            Puedes ocultarlas por un tiempo determinado (se mostrarán automáticamente al vencer) 
            o de forma <strong>indefinida</strong> (solo se muestran al darle a "Desocultar" manualmente).
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

        <!-- LISTA DE PELÍCULAS -->
        <div class="movies-table-container">
            <h3>Películas en Cartelera <span class="admin-filter-count" id="filterCount"></span></h3>
            <table class="movies-table" id="moviesTable">
                <thead>
                    <tr>
                        <th>Película</th>
                        <th>Estado en Cartelera</th>
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

    <!-- MODAL PARA OCULTAR -->
    <div class="modal" id="hideModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ocultar: <span id="movieTitle"></span></h3>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            <form id="hideForm">
                <input type="hidden" id="hide_movie_id">

                <div class="form-group">
                    <label>¿Por cuánto tiempo?</label>
                    <div class="duration-options">
                        <button type="button" class="duration-btn" onclick="setDuration(1)">1 día</button>
                        <button type="button" class="duration-btn" onclick="setDuration(3)">3 días</button>
                        <button type="button" class="duration-btn" onclick="setDuration(7)">1 semana</button>
                        <button type="button" class="duration-btn" onclick="setDuration(14)">2 semanas</button>
                        <button type="button" class="duration-btn" onclick="setDuration(30)">1 mes</button>
                        <button type="button" class="duration-btn" onclick="setDuration('custom')">Personalizado</button>
                        <button type="button" class="duration-btn indefinido" onclick="setDuration('indefinido')">🔒 Indefinido</button>
                    </div>
                </div>

                <div class="form-group custom-date-group" id="customDateGroup">
                    <label for="fecha_fin">Fecha y hora de fin *</label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin">
                    <small class="form-hint">La película se mostrará automáticamente a partir de esta fecha</small>
                </div>

                <div class="form-group" id="indefinidoInfo" style="display:none;">
                    <div style="background:rgba(255,87,34,0.1); border:1px solid rgba(255,87,34,0.3); border-radius:8px; padding:12px 16px; font-size:13px; color:#ff8a65;">
                        🔒 La película quedará oculta indefinidamente y <strong>solo se mostrará</strong> al hacer clic en "Desocultar" manualmente.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="confirmHideBtn" disabled>
                        👁️ Ocultar Película
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
let selectedDuration = null;
let currentCat = '';

// ═══════════════════════════════════════════════════════════════════════════
// CARGAR PELÍCULAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadMovies() {
    try {
        const [movRes, catRes] = await Promise.all([
            fetch('api.php?action=movies_with_hidden'),
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
    count.textContent = filtered.length < allMovies.length ? `(${filtered.length} de ${allMovies.length})` : '';
    renderMoviesTable(filtered);
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDERIZAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function renderMoviesTable(movies) {
    if (movies === undefined) movies = allMovies;
    const tbody = document.querySelector('#moviesTable tbody');

    if (movies.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;" class="admin-no-results">No se encontraron películas</td></tr>';
        return;
    }

    tbody.innerHTML = movies.map(movie => {
        // Turso devuelve números como strings: forzar comparación correcta
        const isHidden = movie.oculta === 1 || movie.oculta === '1' || movie.oculta === true;
        const isIndefinido = isHidden && !movie.fecha_fin_oculta;
        const fechaFin = movie.fecha_fin_oculta ? new Date(movie.fecha_fin_oculta) : null;

        let statusHTML = '';
        if (isHidden) {
            if (isIndefinido) {
                statusHTML = `<span class="status-hidden-indef">🔒 Oculta indefinidamente</span>`;
            } else {
                const now = new Date();
                const expired = fechaFin && fechaFin < now;
                if (expired) {
                    statusHTML = `<span class="status-expired">⏱️ Ocultación vencida</span>`;
                } else {
                    statusHTML = `<span class="status-hidden">👁️ Oculta hasta ${formatDate(fechaFin)}</span>`;
                }
            }
        } else {
            statusHTML = `<span class="status-active">✓ Visible en cartelera</span>`;
        }

        return `
            <tr>
                <td><strong>${movie.titulo}</strong></td>
                <td>${statusHTML}</td>
                <td>
                    ${!isHidden
                        ? `<button class="btn-action suspend" onclick="openHideModal(${movie.id}, '${escHtml(movie.titulo)}')">👁️ Ocultar</button>`
                        : `<button class="btn-action unhide" onclick="unhideMovie(${movie.id})">🟢 Desocultar</button>`
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function escHtml(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ═══════════════════════════════════════════════════════════════════════════
// ABRIR MODAL
// ═══════════════════════════════════════════════════════════════════════════
function openHideModal(movieId, movieTitle) {
    document.getElementById('hide_movie_id').value = movieId;
    document.getElementById('movieTitle').textContent = movieTitle;

    // Reset
    selectedDuration = null;
    document.querySelectorAll('.duration-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('customDateGroup').classList.remove('show');
    document.getElementById('indefinidoInfo').style.display = 'none';
    document.getElementById('fecha_fin').value = '';
    document.getElementById('confirmHideBtn').disabled = true;

    // Min date
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('fecha_fin').min = now.toISOString().slice(0, 16);

    document.getElementById('hideModal').classList.add('active');
}

function closeModal() {
    document.getElementById('hideModal').classList.remove('active');
    document.getElementById('hideForm').reset();
    selectedDuration = null;
}

// ═══════════════════════════════════════════════════════════════════════════
// SELECCIONAR DURACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function setDuration(value) {
    selectedDuration = value;
    document.querySelectorAll('.duration-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    const customGroup = document.getElementById('customDateGroup');
    const indefinidoInfo = document.getElementById('indefinidoInfo');
    const confirmBtn = document.getElementById('confirmHideBtn');

    customGroup.classList.remove('show');
    indefinidoInfo.style.display = 'none';
    confirmBtn.disabled = false;

    if (value === 'custom') {
        customGroup.classList.add('show');
        confirmBtn.disabled = true; // se habilita cuando elige fecha
    } else if (value === 'indefinido') {
        indefinidoInfo.style.display = 'block';
    }
    // Para días numéricos, ya está listo
}

// Habilitar botón cuando se elige fecha personalizada
document.getElementById('fecha_fin').addEventListener('change', function() {
    document.getElementById('confirmHideBtn').disabled = !this.value;
});

// ═══════════════════════════════════════════════════════════════════════════
// ENVIAR OCULTACIÓN
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('hideForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const movieId = document.getElementById('hide_movie_id').value;

    let fechaFin = null;

    if (selectedDuration === null) {
        showMessage('Selecciona una duración', 'error');
        return;
    }

    if (selectedDuration === 'indefinido') {
        fechaFin = null; // indefinido
    } else if (selectedDuration === 'custom') {
        fechaFin = document.getElementById('fecha_fin').value;
        if (!fechaFin) {
            showMessage('Selecciona una fecha de fin', 'error');
            return;
        }
    } else {
        // Calcular fecha a partir de días
        const d = new Date();
        d.setDate(d.getDate() + parseInt(selectedDuration));
        // Formatear como YYYY-MM-DD HH:MM:SS
        fechaFin = d.toISOString().slice(0, 19).replace('T', ' ');
    }

    try {
        const res = await fetch('api.php?action=hide_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pelicula_id: movieId,
                fecha_fin: fechaFin
            })
        });

        const result = await res.json();

        if (result.success) {
            showMessage('✓ Película ocultada de la cartelera', 'success');
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
// DESOCULTAR
// ═══════════════════════════════════════════════════════════════════════════
async function unhideMovie(movieId) {
    if (!confirm('¿Mostrar esta película en la cartelera nuevamente?')) return;

    try {
        const res = await fetch('api.php?action=unhide_movie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: movieId })
        });

        const result = await res.json();

        if (result.success) {
            showMessage('✓ Película visible en cartelera nuevamente', 'success');
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
// UTILIDADES
// ═══════════════════════════════════════════════════════════════════════════
function formatDate(date) {
    return date.toLocaleString('es-MX', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
}

function showMessage(text, type) {
    const msg = document.getElementById('message');
    msg.textContent = text;
    msg.className = 'message ' + type;
    msg.style.display = 'block';
    setTimeout(() => { msg.style.display = 'none'; }, 5000);
}

window.addEventListener('load', loadMovies);
setInterval(loadMovies, 30000);
</script>
</body>
</html>
