(function() {
    try {
        var cached = localStorage.getItem('cinefci_textos');
        if (cached) {
            var texts = JSON.parse(cached).data;
            var logo  = document.querySelector('.loading-logo');
            var sub   = document.getElementById('loading-logo');
            // Forzar opacity 0 inline antes de que el CSS cargue
            if (logo) { logo.style.opacity = '0'; if (texts.logo_cargando) logo.textContent = texts.logo_cargando; }
            if (sub)  { sub.style.opacity  = '0'; if (texts.texto_cargando) sub.textContent = texts.texto_cargando; }
        }
    } catch(e) {}
})();
// ═══════════════════════════════════════════════════════════════════════════
// TEXTOS CONFIGURABLES - Con caché local para carga instantánea
// ═══════════════════════════════════════════════════════════════════════════
let configTexts = {};

const CACHE_KEY    = 'cinefci_textos';
const CACHE_MAX_MS = 5 * 60 * 1000; // 5 minutos antes de refrescar del servidor

// ── Aplicar textos al DOM ────────────────────────────────────────────────
function applyTexts(texts) {
    if (!texts) return;
    configTexts = texts;

    const set = (selector, key) => {
        const el = document.querySelector(selector);
        if (el && texts[key] !== undefined && texts[key] !== '') {
            el.textContent = texts[key];
        }
    };

    set('.loading-logo',    'logo_cargando');
    set('#loading-logo',    'texto_cargando');
    set('.logo-text',       'texto_logo');
    set('.header-subtitle', 'subtitulo_header');
    set('.section-title',   'titulo_catalogo');
}

// ── Cargar desde el servidor ─────────────────────────────────────────────
async function fetchTextsFromServer() {
    const res  = await fetch('php/back-end/textos-coneccion.php?action=get_config');
    const data = await res.json();

    // Guardar en caché con timestamp
    localStorage.setItem(CACHE_KEY, JSON.stringify({
        timestamp: Date.now(),
        data: data
    }));

    return data;
}

// ── Cargar textos (caché primero, servidor en segundo plano) ─────────────
async function loadConfigTexts() {
    try {
        const cached = localStorage.getItem(CACHE_KEY);

        if (cached) {
            const parsed = JSON.parse(cached);
            const age    = Date.now() - parsed.timestamp;

            // Aplicar caché inmediatamente (sin esperar red)
            applyTexts(parsed.data);

            // Si el caché expiró, actualizar en segundo plano silenciosamente
            if (age > CACHE_MAX_MS) {
                fetchTextsFromServer()
                    .then(data => applyTexts(data))
                    .catch(() => {}); // Si falla, ya tenemos el caché aplicado
            }

        } else {
            // Primera visita: esperar la respuesta del servidor
            const data = await fetchTextsFromServer();
            applyTexts(data);
        }

    } catch (err) {
        console.warn('No se pudieron cargar textos configurables:', err.message);
        // La página sigue funcionando con los textos por defecto del HTML
    }
}

// ── Función para forzar refresco (útil tras guardar cambios en el panel) ─
function refreshConfigTexts() {
    localStorage.removeItem(CACHE_KEY);
    return loadConfigTexts();
}

// ═══════════════════════════════════════════════════════════════════════════
// INICIO
// ═══════════════════════════════════════════════════════════════════════════
window.addEventListener('load', async () => {
    await loadConfigTexts();
    await fetchCategories();
    await fetchMovies();
});
