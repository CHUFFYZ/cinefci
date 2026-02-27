<?php
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Cartelera - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Secciones tipo panel ── */
        .cc-section {
            background: linear-gradient(135deg, #1a1a1a 0%, #2f2f2f 100%);
            border-radius: 20px;
            padding: 36px 40px;
            margin-bottom: 30px;
            border: 2px solid rgba(229,9,20,0.25);
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            animation: fadeInUp 0.5s ease-out;
        }
        .cc-section.green-border { border-color: rgba(0,200,83,0.3); }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .cc-title {
            font-family: 'Bebas Neue', cursive;
            font-size: 2rem;
            letter-spacing: 0.1em;
            position: relative;
            padding-left: 22px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .cc-title::before {
            content:'';
            position:absolute; left:0; top:50%;
            transform:translateY(-50%);
            width:5px; height:30px;
            background: var(--primary);
            box-shadow: 0 0 10px var(--primary);
            border-radius: 3px;
        }
        .cc-title.green::before { background:#00e676; box-shadow: 0 0 10px #00e676; }

        .cc-subtitle {
            font-size:11px; font-weight:700; letter-spacing:1.5px;
            text-transform:uppercase; color:#8c8c8c;
            margin-bottom:12px; margin-top:22px;
        }
        .cc-subtitle:first-of-type { margin-top:0; }

        /* Grupo de opciones */
        .cc-option-group { display:flex; flex-wrap:wrap; gap:10px; }

        /* Botones de opción */
        .cc-opt {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15);
            color: #aaa;
            padding: 10px 20px;
            border-radius: 9px;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .cc-opt:hover { border-color: rgba(229,9,20,0.5); color:#fff; }
        .cc-opt.active {
            background: rgba(229,9,20,0.2);
            border-color: #e50914;
            color:#fff;
            box-shadow: 0 0 12px rgba(229,9,20,0.3);
        }

        /* Select */
        .cc-select {
            background:#141414;
            border:1px solid rgba(255,255,255,0.15);
            border-radius:9px;
            color:#fff;
            padding: 10px 16px;
            font-family:'Montserrat', sans-serif;
            font-size:13px;
            outline:none;
            min-width:240px;
            transition: border-color 0.2s;
        }
        .cc-select:focus { border-color: #e50914; }

        /* Lista de destacadas */
        .dest-list { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; min-height:40px; }
        .dest-item {
            display:flex; align-items:center; gap:12px;
            background: rgba(229,9,20,0.06);
            border: 1px solid rgba(229,9,20,0.22);
            border-radius:10px;
            padding:11px 16px;
            transition: background 0.2s;
        }
        .dest-item:hover { background: rgba(229,9,20,0.1); }
        .dest-pos {
            font-family:'Bebas Neue',cursive;
            font-size:1.4rem; color:#e50914;
            min-width:30px; line-height:1;
        }
        .dest-thumb {
            width:38px; height:52px; object-fit:cover;
            border-radius:5px; flex-shrink:0;
            background:#111;
        }
        .dest-name { flex:1; font-weight:600; font-size:13px; color:#e5e5e5; }
        .dest-actions { display:flex; gap:6px; }
        .dest-btn {
            background:transparent;
            border:1px solid rgba(255,255,255,0.1);
            color:#888; padding:5px 9px; border-radius:6px;
            font-size:12px; cursor:pointer;
            font-family:'Montserrat',sans-serif;
            transition:all 0.2s;
        }
        .dest-btn:hover { border-color:rgba(255,255,255,0.3); color:#fff; }
        .dest-btn.remove { border-color:rgba(229,9,20,0.35); color:#e50914; }
        .dest-btn.remove:hover { background:rgba(229,9,20,0.15); }

        /* Add row */
        .add-dest-row {
            display:flex; gap:10px; align-items:center; flex-wrap:wrap;
            margin-top:8px;
        }

        /* Save button */
        .save-btn {
            background:linear-gradient(135deg,#e50914,#b00710);
            border:none; border-radius:9px; padding:11px 28px;
            color:#fff; font-family:'Montserrat',sans-serif;
            font-size:13px; font-weight:700; cursor:pointer;
            transition:opacity 0.2s, transform 0.2s;
            text-transform:uppercase; letter-spacing:0.05em;
        }
        .save-btn:hover { opacity:0.88; transform:translateY(-1px); }

        /* Nuevas cards */
        .nuevas-card {
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:12px;
            background:rgba(0,200,83,0.07);
            border:1px solid rgba(0,200,83,0.2);
            border-radius:10px; padding:14px 18px; margin-bottom:10px;
        }
        .nuevas-card-title { font-weight:700; font-size:14px; color:#fff; }
        .nuevas-card-sub { font-size:11px; color:#8c8c8c; margin-top:3px; }
        .nuevas-cd { font-family:'Bebas Neue',cursive; font-size:1.4rem; color:#00e676; letter-spacing:0.05em; }
        .nuevas-cd-label { font-size:10px; color:#8c8c8c; margin-top:1px; }
        .btn-quitar {
            margin-top:4px; background:rgba(229,9,20,0.1);
            border:1px solid rgba(229,9,20,0.35); color:#e50914;
            padding:5px 12px; border-radius:6px; font-size:11px; font-weight:700;
            cursor:pointer; font-family:'Montserrat',sans-serif;
            transition:all 0.2s;
        }
        .btn-quitar:hover { background:rgba(229,9,20,0.25); }
        .btn-refresh {
            background:transparent;
            border:1px solid rgba(0,200,83,0.4); color:#00c853;
            padding:9px 18px; border-radius:8px;
            font-family:'Montserrat',sans-serif; font-size:12px; font-weight:700;
            cursor:pointer; transition:all 0.2s;
        }
        .btn-refresh:hover { background:rgba(0,200,83,0.1); }

        /* Notif toast */
        #cc-notif {
            position:fixed; top:24px; right:24px; z-index:9999;
            background:linear-gradient(135deg,#e50914,#b00710);
            color:#fff; padding:13px 22px; border-radius:9px;
            font-family:'Montserrat',sans-serif; font-weight:600; font-size:14px;
            box-shadow:0 8px 25px rgba(229,9,20,0.4);
            opacity:0; transform:translateX(420px);
            transition:all 0.3s ease;
            pointer-events:none;
        }
        #cc-notif.show { opacity:1; transform:translateX(0); }

        /* Ancho extra para este panel */
        .admin-container { max-width: 860px; }

        @media(max-width:640px) {
            .cc-section { padding:24px 18px; }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1>🎬 Config. de Cartelera</h1>
        <a href="panel.php" class="back-btn">← Volver al Panel</a>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECCIÓN 1 — CONFIGURACIÓN DE VISUALIZACIÓN
    ══════════════════════════════════════════════════════ -->
    <div class="cc-section">
        <div class="cc-title">
            Visualización de la Cartelera
            <button class="save-btn" onclick="saveConfig()">💾 Guardar cambios</button>
        </div>

        <!-- Filtro inicial -->
        <div class="cc-subtitle">Filtro al cargar la página</div>
        <div class="cc-option-group" id="filtroGrp">
            <button class="cc-opt active" data-val="todas"       onclick="pick('filtroGrp',this,'todas')">📽 Todas</button>
            <button class="cc-opt"        data-val="nuevas"      onclick="pick('filtroGrp',this,'nuevas')">🆕 Nuevas primero</button>
            <button class="cc-opt"        data-val="popularidad" onclick="pick('filtroGrp',this,'popularidad')">🔥 Más populares</button>
            <button class="cc-opt"        data-val="categoria"   onclick="pick('filtroGrp',this,'categoria')">🏷 Por categoría</button>
        </div>

        <!-- Selector categoría (visible solo si se elige "categoria") -->
        <div id="catBox" style="display:none; margin-top:14px;">
            <label style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#8c8c8c;display:block;margin-bottom:7px;">Categoría inicial</label>
            <select id="catSel" class="cc-select">
                <option value="">-- Elige una categoría --</option>
            </select>
        </div>

        <!-- Orden -->
        <div class="cc-subtitle" style="margin-top:24px;">Orden de las películas</div>
        <div class="cc-option-group" id="ordenGrp">
            <button class="cc-opt active" data-val="defecto"     onclick="pick('ordenGrp',this,'defecto')">📌 Defecto</button>
            <button class="cc-opt"        data-val="az"          onclick="pick('ordenGrp',this,'az')">A → Z</button>
            <button class="cc-opt"        data-val="za"          onclick="pick('ordenGrp',this,'za')">Z → A</button>
            <button class="cc-opt"        data-val="popularidad" onclick="pick('ordenGrp',this,'popularidad')">🔥 Más votadas</button>
            <button class="cc-opt"        data-val="nuevas"      onclick="pick('ordenGrp',this,'nuevas')">🆕 Nuevas</button>
        </div>

        <!-- Películas prioritarias -->
        <div class="cc-subtitle" style="margin-top:28px;">Películas prioritarias (aparecen primero en cartelera)</div>
        <p style="font-size:12px; color:#666; margin-bottom:14px; line-height:1.6;">
            Las películas de esta lista se colocan al inicio de la cartelera, respetando el orden que definas aquí. Usa ▲▼ para reordenar.
        </p>

        <div class="dest-list" id="destList">
            <div style="color:#666; font-size:12px; font-style:italic; padding:6px 0;">Cargando...</div>
        </div>

        <div class="add-dest-row">
            <select id="addDestSel" class="cc-select" style="flex:1; min-width:200px;">
                <option value="">-- Agregar película prioritaria --</option>
            </select>
            <button class="save-btn" style="padding:10px 18px;" onclick="addDest()">➕ Agregar</button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECCIÓN 2 — NUEVAS EN CARTELERA
    ══════════════════════════════════════════════════════ -->
    <div class="cc-section green-border">
        <div class="cc-title green" style="color:#00e676;">
            Nuevas en Cartelera
            <button class="btn-refresh" onclick="loadNuevas()">🔄 Actualizar</button>
        </div>
        <div id="nuevasList">
            <div style="color:#8c8c8c; font-size:13px;">Cargando...</div>
        </div>
    </div>
</div>

<!-- Toast notificación -->
<div id="cc-notif"></div>

<script>
// ══════════════════════════════════════════════════════════════════
// ESTADO
// ══════════════════════════════════════════════════════════════════
let config    = { filtro_inicial:'todas', orden:'defecto', categoria_inicial:'', destacadas:[] };
let allMovies = [];
let allCats   = [];
let cdInterval = null;

// ══════════════════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════════════════
window.addEventListener('load', async () => {
    const [cfgRes, movRes, catRes] = await Promise.all([
        fetch('api.php?action=get_cartelera_config').then(r=>r.json()).catch(()=>({})),
        fetch('api.php?action=movies_list').then(r=>r.json()).catch(()=>[]),
        fetch('api.php?action=categorias').then(r=>r.json()).catch(()=>[])
    ]);

    config    = { ...config, ...cfgRes };
    allMovies = movRes;
    allCats   = catRes;

    // Aplicar estado UI
    activateBtn('filtroGrp', config.filtro_inicial || 'todas');
    activateBtn('ordenGrp',  config.orden          || 'defecto');

    // Categorías
    const catSel = document.getElementById('catSel');
    catSel.innerHTML = '<option value="">-- Elige una categoría --</option>' +
        allCats.map(c=>`<option value="${c.nombre}" ${c.nombre===config.categoria_inicial?'selected':''}>${c.nombre}</option>`).join('');
    document.getElementById('catBox').style.display = config.filtro_inicial==='categoria' ? 'block' : 'none';

    // Selector agregar
    document.getElementById('addDestSel').innerHTML =
        '<option value="">-- Agregar película prioritaria --</option>' +
        allMovies.map(m=>`<option value="${m.id}" data-poster="${m.poster||''}">${m.titulo}</option>`).join('');

    // Sincronizar destacadas: el API devuelve objetos {posicion, pelicula_id, titulo, poster}
    // nos quedamos con esa estructura
    if (!Array.isArray(config.destacadas)) config.destacadas = [];

    renderDest();
    loadNuevas();
});

// ══════════════════════════════════════════════════════════════════
// BOTONES OPCIONES
// ══════════════════════════════════════════════════════════════════
function activateBtn(groupId, val) {
    document.querySelectorAll(`#${groupId} .cc-opt`).forEach(b=>{
        b.classList.toggle('active', b.dataset.val === val);
    });
}

window.pick = function(groupId, btn, val) {
    activateBtn(groupId, val);
    if (groupId === 'filtroGrp') {
        config.filtro_inicial = val;
        document.getElementById('catBox').style.display = val==='categoria' ? 'block' : 'none';
    } else {
        config.orden = val;
    }
};

// ══════════════════════════════════════════════════════════════════
// PELÍCULAS DESTACADAS
// ══════════════════════════════════════════════════════════════════
function renderDest() {
    const list = document.getElementById('destList');
    const dest = config.destacadas;
    if (!dest || dest.length === 0) {
        list.innerHTML = '<div style="color:#555; font-size:12px; font-style:italic; padding:8px 0;">Ninguna película prioritaria. Agrega una abajo.</div>';
        return;
    }
    list.innerHTML = dest.map((d, i) => {
        const poster = d.poster || '';
        const titulo = d.titulo || ('ID ' + d.pelicula_id);
        return `
        <div class="dest-item" id="dest-${i}">
            <span class="dest-pos">#${i+1}</span>
            ${poster ? `<img class="dest-thumb" src="${poster}" alt="" onerror="this.style.display='none'">` : ''}
            <span class="dest-name">${titulo}</span>
            <div class="dest-actions">
                ${i > 0                   ? `<button class="dest-btn" onclick="moveDest(${i},-1)">▲</button>` : ''}
                ${i < dest.length - 1     ? `<button class="dest-btn" onclick="moveDest(${i},1)">▼</button>`  : ''}
                <button class="dest-btn remove" onclick="removeDest(${i})">✕ Quitar</button>
            </div>
        </div>`;
    }).join('');
}

window.addDest = function() {
    const sel = document.getElementById('addDestSel');
    const id  = parseInt(sel.value);
    if (!id) return;

    if (config.destacadas.find(d => parseInt(d.pelicula_id) === id)) {
        notif('⚠ Esa película ya está en la lista prioritaria');
        return;
    }

    const opt    = sel.options[sel.selectedIndex];
    const titulo = opt.text;
    const poster = opt.dataset.poster || '';

    config.destacadas.push({ pelicula_id: id, titulo, poster });
    sel.value = '';
    renderDest();
};

window.removeDest = function(idx) {
    config.destacadas.splice(idx, 1);
    renderDest();
};

window.moveDest = function(idx, dir) {
    const arr = config.destacadas;
    const nw  = idx + dir;
    if (nw < 0 || nw >= arr.length) return;
    [arr[idx], arr[nw]] = [arr[nw], arr[idx]];
    renderDest();
};

// ══════════════════════════════════════════════════════════════════
// GUARDAR CONFIG
// ══════════════════════════════════════════════════════════════════
window.saveConfig = async function() {
    config.categoria_inicial = document.getElementById('catSel').value;

    const payload = {
        filtro_inicial:    config.filtro_inicial,
        orden:             config.orden,
        categoria_inicial: config.categoria_inicial,
        destacadas:        config.destacadas.map(d => d.pelicula_id)
    };

    try {
        const res  = await fetch('api.php?action=save_cartelera_config', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        notif(data.success ? '✅ Configuración guardada correctamente' : ('❌ ' + (data.message||'Error')));
    } catch(e) {
        notif('❌ Error de conexión al guardar');
    }
};

// ══════════════════════════════════════════════════════════════════
// NUEVAS EN CARTELERA
// ══════════════════════════════════════════════════════════════════
async function loadNuevas() {
    try {
        const res    = await fetch('api.php?action=movies_with_new');
        const movies = await res.json();
        const activas = movies.filter(m => m.es_nueva === 1 || m.es_nueva === '1');
        renderNuevas(activas);
    } catch(e) {
        document.getElementById('nuevasList').innerHTML = '<div style="color:#e50914;font-size:13px;">Error al cargar</div>';
    }
}

function renderNuevas(activas) {
    const container = document.getElementById('nuevasList');
    if (activas.length === 0) {
        container.innerHTML = '<div style="color:#8c8c8c;font-size:13px;font-style:italic;">No hay películas marcadas como nuevas en este momento.</div>';
        return;
    }

    container.innerHTML = activas.map(m => {
        const fin    = m.fecha_fin_nueva;
        const hasFin = fin !== null && fin !== '';
        return `
        <div class="nuevas-card">
            <div>
                <div class="nuevas-card-title">${m.titulo}</div>
                <div class="nuevas-card-sub">Desde: ${m.fecha_inicio ? new Date(m.fecha_inicio).toLocaleString('es-MX') : '—'}</div>
            </div>
            <div style="text-align:right;">
                ${hasFin
                    ? `<div class="nuevas-cd" id="cd-${m.id}" data-fin="${fin}">calculando...</div>
                       <div class="nuevas-cd-label">tiempo restante</div>`
                    : `<div style="font-size:12px;color:#7986cb;font-weight:700;">🔒 Sin límite</div>`
                }
                <button class="btn-quitar" onclick="quitarNueva(${m.id})">✕ Quitar sello</button>
            </div>
        </div>`;
    }).join('');

    // Contador regresivo
    if (cdInterval) clearInterval(cdInterval);
    cdInterval = setInterval(() => {
        document.querySelectorAll('[data-fin]').forEach(el => {
            const diff = new Date(el.dataset.fin) - new Date();
            if (diff <= 0) {
                el.textContent = '¡Expirado!'; el.style.color='#e50914';
            } else {
                const d=Math.floor(diff/86400000), h=Math.floor((diff%86400000)/3600000),
                      m=Math.floor((diff%3600000)/60000), s=Math.floor((diff%60000)/1000);
                el.textContent = d>0 ? `${d}d ${h}h ${m}m` : `${h}h ${m}m ${s}s`;
            }
        });
    }, 1000);
}

window.quitarNueva = async function(movieId) {
    if (!confirm('¿Quitar el sello "Nuevo" de esta película?')) return;
    try {
        const res  = await fetch('api.php?action=remove_new_badge', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ pelicula_id: movieId })
        });
        const data = await res.json();
        if (data.success) { notif('✕ Sello "Nuevo" quitado'); loadNuevas(); }
    } catch(e) { notif('❌ Error al quitar el sello'); }
};

// ══════════════════════════════════════════════════════════════════
// TOAST
// ══════════════════════════════════════════════════════════════════
function notif(msg) {
    const el = document.getElementById('cc-notif');
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(()=>el.classList.remove('show'), 3200);
}
</script>
</body>
</html>
