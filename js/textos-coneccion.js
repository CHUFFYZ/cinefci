// Variable global para textos
let configTexts = {};

// Cargar al inicio
async function loadConfigTexts() {
    try {
        const res = await fetch('php/back-end/textos-coneccion.php?action=get_config');
        configTexts = await res.json();
        
        // Actualizar título
        const tituloElement = document.querySelector('.section-title');
        if (tituloElement && configTexts.titulo_catalogo) {
            tituloElement.textContent = configTexts.titulo_catalogo;
        }
        document.querySelector('.header-subtitle').textContent = configTexts.subtitulo_header
        document.querySelector('.loading-logo').textContent = configTexts.logo_cargando
        document.querySelector('#loading-logo').textContent = configTexts.texto_cargando
        document.querySelector('.logo-text').textContent = configTexts.texto_logo
        // Puedes hacer lo mismo con otros elementos
        // ej: document.querySelector('.header-subtitle').textContent = configTexts.subtitulo_header || 'Tu voz en el cine';
    } catch (err) {
        console.error('No se pudo cargar textos configurables', err);
    }
}

// Llama esto al cargar la página
window.addEventListener('load', async () => {
    await loadConfigTexts();     // ← nuevo
    await fetchCategories();
    await fetchMovies();
    // ... resto
});