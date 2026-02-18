async function openModal(movieId) {
    const movie = currentMovies.find(m => m.id == movieId);
    if (!movie) return;

    document.getElementById('modalPoster').src = movie.poster_large || movie.poster;
    document.getElementById('modalTitle').textContent = movie.titulo;
    document.getElementById('modalSummary').textContent = movie.resumen;

    // Mostrar categorías
    const categoriesContainer = document.getElementById('modalCategories');
    if (movie.categorias && movie.categorias.length > 0) {
        categoriesContainer.innerHTML = movie.categorias.map(cat => 
            `<span class="category-tag">${cat}</span>`
        ).join('');
    } else {
        categoriesContainer.innerHTML = '';
    }

    const trailerIframe = document.getElementById('modalTrailer');
    const trailerContainer = document.getElementById('modalTrailer'); // Asume que existe este contenedor padre. Si no, usa trailerIframe directamente.

    if (movie.trailer && movie.trailer.trim() !== '') {  // Verifica si hay trailer válido (no undefined, null o vacío)
        let trailerUrl = movie.trailer;
        if (trailerUrl.includes('youtube.com/watch?v=')) {
            trailerUrl = trailerUrl.replace('watch?v=', 'embed/');
        } else if (trailerUrl.includes('youtu.be/')) {
            trailerUrl = trailerUrl.replace('youtu.be/', 'youtube.com/embed/');
        }

        // Parámetros base (sin autoplay)
        const baseParams = '?enablejsapi=1&rel=0&controls=0&showinfo=0&iv_load_policy=3&modestbranding=1';
        trailerUrl += baseParams;

        trailerIframe.src = trailerUrl;  // Carga sin autoplay
        trailerIframe.dataset.baseSrc = trailerUrl;

        if (trailerContainer) {
            trailerContainer.style.display = 'block';  // Muestra el contenedor si estaba oculto (asumiendo que por default es visible)
        }

        setupTrailerObserver();
    } else {
        // Si no hay trailer, limpia el src y oculta el contenedor
        trailerIframe.src = '';
        if (trailerContainer) {
            trailerContainer.style.display = 'none';  // O alternativamente: trailerContainer.style.width = '0'; trailerContainer.style.height = '0';
        }
        // No llamamos a setupTrailerObserver() porque no hay video
    }

    updateAverageRating(movie);
    renderStars(movie);
    document.getElementById('movieModal').classList.add('active');
}
let trailerObserver = null;

function setupTrailerObserver() {
    const trailer = document.getElementById('modalTrailer');
    if (!trailer || !trailer.dataset.baseSrc) return;

    // Limpia src a la versión sin autoplay (por si quedó con autoplay)
    trailer.src = trailer.dataset.baseSrc;

    // Si ya hay observer viejo, desconéctalo
    if (trailerObserver) {
        trailerObserver.disconnect();
    }

    // Crea nuevo observer
    trailerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Agrega autoplay solo cuando entra en vista
                const autoplayUrl = trailer.dataset.baseSrc + '&autoplay=1';
                trailer.src = autoplayUrl;  // Recarga con autoplay → reproduce
                trailerObserver.unobserve(trailer);  // Una vez reproducido, no más
            }
        });
    }, {
        threshold: 0.6  // ~60% visible para activar
    });

    trailerObserver.observe(trailer);
}
function closeModal() {
    const modal = document.getElementById('movieModal');
    const trailer = document.getElementById('modalTrailer');
    const trailerContainer = document.getElementById('trailerContainer');

    if (trailer && trailer.contentWindow) {
        // Pausa con postMessage (más suave, no resetea progreso)
        trailer.contentWindow.postMessage(
            JSON.stringify({
                event: 'command',
                func: 'pauseVideo',
                args: []
            }),
            '*'
        );
    }

    // Limpia src para "descargar" y evitar fondo
    if (trailer) {
        trailer.src = '';
    }

    // Resetea el display del contenedor a default (para la próxima apertura)
    if (trailerContainer) {
        trailerContainer.style.display = '';  // O 'block' si es tu default
    }

    // Desconecta observer si existe
    if (trailerObserver) {
        trailerObserver.disconnect();
        trailerObserver = null;
    }

    modal.classList.remove('active');
}