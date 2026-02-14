<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Película - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
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
        document.getElementById('resumen').value = currentMovie.resumen;
        document.getElementById('veces_ganadora').value = currentMovie.veces_ganadora || 0;
        
        // Renderizar categorías
        renderCategories(currentMovie.categorias || []);
        
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
// LIMITAR MÁXIMO 5 CATEGORÍAS
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
