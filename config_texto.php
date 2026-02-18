<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Textos - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
    <!-- Puedes copiar los estilos extra de config-card que te di antes -->
</head>
<style>
        /* Estilos adicionales específicos para esta página */
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .config-card {
            background: var(--gray);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.35);
            border: 1px solid rgba(229,9,20,0.12);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .config-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(229,9,20,0.18);
        }

        .config-card label {
            display: block;
            margin-bottom: 8px;
            color: var(--gold);
            font-weight: 600;
            font-size: 1.05rem;
        }

        .config-card small {
            display: block;
            margin-top: 6px;
            color: var(--light-gray);
            font-size: 0.82rem;
            line-height: 1.4;
        }

        .config-card textarea {
            min-height: 80px;
            resize: vertical;
        }

        @media (max-width: 768px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1>⚙️ Configuración de Textos</h1>
        <a href="panel.php" class="back-btn">← Volver al Panel</a>
    </div>

    <div id="message" class="message" style="display:none;"></div>

    <div class="config-grid" id="configGrid">
        <!-- Se llenan con JS -->
    </div>

    <div class="form-actions" style="margin-top:40px;">
        <button id="saveAllBtn" class="btn-primary">Guardar Todos los Cambios</button>
        <a href="panel.php" class="btn-secondary" style="text-decoration:none; padding:14px 30px;">Cancelar</a>
    </div>
</div>

<script>
// ────────────────────────────────────────────────
// CARGAR TODAS LAS CONFIGURACIONES
// ────────────────────────────────────────────────
async function loadConfigs() {
    try {
        const res = await fetch('php/back-end/textos-coneccion.php?action=get_config');
        const configs = await res.json();

        const grid = document.getElementById('configGrid');
        grid.innerHTML = '';

        if (Object.keys(configs).length === 0) {
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--light-gray);">No hay textos configurables aún.</p>';
            return;
        }

        Object.entries(configs).forEach(([clave, valor]) => {
            const card = document.createElement('div');
            card.className = 'config-card';
            card.innerHTML = `
                <label>${clave}</label>
                <textarea rows="3" data-clave="${clave}">${valor || ''}</textarea>
                <small>Última modificación: pendiente</small>
            `;
            grid.appendChild(card);
        });
    } catch (err) {
        console.error(err);
        showMessage('Error al cargar configuraciones', 'error');
    }
}

// ────────────────────────────────────────────────
// GUARDAR TODOS LOS CAMBIOS
// ────────────────────────────────────────────────
document.getElementById('saveAllBtn').addEventListener('click', async () => {
    const textareas = document.querySelectorAll('.config-card textarea');
    let successCount = 0;
    let total = textareas.length;

    for (const ta of textareas) {
        const clave = ta.dataset.clave;
        const valor = ta.value.trim();

        try {
            const res = await fetch('php/back-end/textos-coneccion.php?action=save_config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clave, valor })
            });
            const result = await res.json();
            if (result.success) successCount++;
        } catch (err) {
            console.error(`Error guardando ${clave}:`, err);
        }
    }

    if (successCount === total) {
        showMessage('✓ Todos los cambios guardados', 'success');
    } else if (successCount > 0) {
        showMessage(`✓ ${successCount}/${total} guardados correctamente`, 'success');
    } else {
        showMessage('Error al guardar cambios', 'error');
    }
});

// ────────────────────────────────────────────────
// MOSTRAR MENSAJE TEMPORAL
// ────────────────────────────────────────────────
function showMessage(text, type) {
    const msg = document.getElementById('message');
    msg.textContent = text;
    msg.className = `message ${type}`;
    msg.style.display = 'block';
    setTimeout(() => msg.style.display = 'none', 5000);
}

// INICIALIZAR
window.addEventListener('load', loadConfigs);
</script>
</body>
</html>