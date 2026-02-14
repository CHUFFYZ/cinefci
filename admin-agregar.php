<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Película - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
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
            </div>

            <div class="form-group">
                <label for="resumen">Resumen / Sinopsis *</label>
                <textarea id="resumen" name="resumen" rows="5" required></textarea>
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
// CARGAR CATEGORÍAS
// ═══════════════════════════════════════════════════════════════════════════
async function loadCategories() {
    try {
        const res = await fetch('api.php?action=categorias');
        const categories = await res.json();
        
        const grid = document.getElementById('categoriesGrid');
        grid.innerHTML = categories.map(cat => `
            <label class="category-checkbox">
                <input type="checkbox" name="categorias[]" value="${cat.id}" onchange="limitCategories()">
                <span>${cat.nombre}</span>
            </label>
        `).join('');
    } catch (err) {
        console.error('Error al cargar categorías:', err);
        showMessage('Error al cargar categorías', 'error');
    }
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
            setTimeout(() => {
                e.target.reset();
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
