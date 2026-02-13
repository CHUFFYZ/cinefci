<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineFCI - Catálogo de Películas</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div   class="Luffy">
        <a href="https://www.facebook.com/share/1GUid3qsoS/" target="_blank">By Creador UNIMAP .</a>
    </div>
<!-- PANTALLA CARGA -->
<div class="loading-screen" id="loadingScreen">
    <div class="loading-logo">CINE-FCI</div>
    <div class="loading-bar">
        <div class="loading-progress"></div>
    </div>
</div>

<!-- HEADER -->
<header>
    <div class="logo-container">
        <img class="logo-icon" src="image/logo/logo.png" alt="">
        <div>
            <div class="logo-text">CINE-FCI</div>
            <div class="header-subtitle"> Tu voz en el cine</div>
        </div>
    </div>
</header>


<main>
    <h1 class="section-title">Catálogo</h1>
    <div class="movies-grid" id="moviesGrid">
        <div class="loading">Cargando películas...</div>
    </div>
</main>

<!-- MODAL -->
    <div class="modal" id="movieModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">×</button>
            <img class="modal-poster" id="modalPoster" src="" alt="">
            <div class="modal-info">
                <h2 class="modal-title" id="modalTitle"></h2>
                <p class="modal-summary" id="modalSummary"></p>
                <div class="rating-section">
                    <div class="rating-title">Califica esta película</div>
                    <div class="stars" id="starsContainer"></div>
                    <div class="average-rating" id="averageRating"></div>
                </div>
            </div>
        </div>
    </div>

<script>
// Generar o recuperar browser_id (único por navegador)
let browserId = localStorage.getItem('browserId');
if (!browserId) {
    browserId = 'bid_' + Date.now() + Math.random().toString(36).substring(2, 10);
    localStorage.setItem('browserId', browserId);
}

let currentMovies = [];  // guardaremos la lista de películas con sus promedios aquí

// ────────────────────────────────────────────────
// CARGAR PELÍCULAS DESDE EL SERVIDOR
// ────────────────────────────────────────────────
async function fetchMovies() {
    const res = await fetch(`api.php?action=list&browser_id=${encodeURIComponent(browserId)}`);
    currentMovies = await res.json();
    renderMovies(currentMovies);
}

function renderMovies(movies) {
    const grid = document.getElementById('moviesGrid');
    grid.innerHTML = movies.map(m => `
        <div class="movie-card">
            <img src="${m.poster}" alt="${m.titulo}" class="movie-poster" 
                 onclick="openModal(${m.id})">
            <div class="movie-info">
                <div class="movie-title">${m.titulo}</div>
                <button class="vote-btn ${m.ya_voto ? 'voted' : ''}" 
                    onclick="voteForMovie(${m.id}, event)"
                    ${m.ya_voto ? 'disabled' : ''}>
                ${m.ya_voto ? '★ Tu voto' : 'Votar por esta'}
            </button>
            </div>
        </div>
    `).join('');
}


// ────────────────────────────────────────────────
// VOTAR (solo una vez por navegador) - ACTUALIZADO
// ────────────────────────────────────────────────
async function voteForMovie(id, e) {
    e.stopPropagation();
    const btn = e.target;
    btn.disabled = true;

    try {
        const res = await fetch('api.php?action=vote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pelicula_id: id, browser_id: browserId })
        });

        const data = await res.json();

        if (data.success) {
            // Refrescamos TODO el catálogo para que se quite el "Votado" 
            // de la película anterior y se ponga en la nueva.
            await fetchMovies(); 
        } else {
            alert(data.message);
            btn.disabled = false;
        }
    } catch (err) {
        console.error(err);
        btn.disabled = false;
    }
}
// ────────────────────────────────────────────────
// CALIFICAR CON ESTRELLAS (1 a 5)
// ────────────────────────────────────────────────
async function rateMovie(id, rating) {
    try {
        const res = await fetch('api.php?action=rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                pelicula_id: id, 
                rating, 
                browser_id: browserId 
            })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            // Éxito → refrescar
            await refreshMovieInModal(id);
            // Opcional: mostrar notificación temporal
            alert('¡Gracias por tu calificación!');  // ← puedes cambiar por un toast bonito después
        } else {
            alert(data.message || 'No se pudo guardar la calificación');
        }
    } catch (err) {
        console.error(err);
        alert('Error al conectar con el servidor');
    }
}

// ────────────────────────────────────────────────
// ABRIR MODAL Y CARGAR DATOS DE LA PELÍCULA
// ────────────────────────────────────────────────
async function openModal(movieId) {
    const movie = currentMovies.find(m => m.id == movieId);
    if (!movie) return;

    document.getElementById('modalPoster').src = movie.poster_large || movie.poster;
    document.getElementById('modalTitle').textContent = movie.titulo;
    document.getElementById('modalSummary').textContent = movie.resumen;

    // Mostrar promedio y conteo
    updateAverageRating(movie);

    // Renderizar estrellas (marcar las del usuario si ya calificó)
    renderStars(movie);

    document.getElementById('movieModal').classList.add('active');
}

function closeModal() {
    document.getElementById('movieModal').classList.remove('active');
}

// ────────────────────────────────────────────────
// ESTRELLAS INTERACTIVAS
// ────────────────────────────────────────────────
function renderStars(movie) {
    const container = document.getElementById('starsContainer');
    container.innerHTML = '';

    // Calificación del usuario actual (si existe)
    const userRating = movie.user_rating || 0; // vendrá del servidor en refresh

    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('span');
        star.className = 'star';
        star.textContent = '★';
        star.onclick = () => rateMovie(movie.id, i);

        if (i <= userRating) {
            star.classList.add('active');
        }

        container.appendChild(star);
    }
}

// ────────────────────────────────────────────────
// MOSTRAR PROMEDIO Y CANTIDAD DE CALIFICACIONES
// ────────────────────────────────────────────────
function updateAverageRating(movie) {
    const container = document.getElementById('averageRating');

    if (!movie.promedio || movie.promedio == 0) {
        container.textContent = 'Sé el primero en calificar esta película';
        return;
    }

    const total = movie.total_calificaciones || 0;
    container.innerHTML = `
        Calificación promedio: <strong>${parseFloat(movie.promedio).toFixed(1)}</strong> ★ 
        (${total} ${total === 1 ? 'calificación' : 'calificaciones'})
    `;
}

// ────────────────────────────────────────────────
// REFRESCAR SOLO LA PELÍCULA ACTUAL (para actualizar después de votar/calificar)
// ────────────────────────────────────────────────
async function refreshMovieInModal(movieId) {
    try {
        const res = await fetch(`api.php?action=list&browser_id=${encodeURIComponent(browserId)}`);
        //                                           ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        const all = await res.json();
        const updatedMovie = all.find(m => m.id == movieId);

        if (updatedMovie) {
            // Actualizar en el array global
            const idx = currentMovies.findIndex(m => m.id == movieId);
            if (idx !== -1) currentMovies[idx] = updatedMovie;

            // Refrescar visuales del modal
            updateAverageRating(updatedMovie);
            renderStars(updatedMovie);

            // También podemos actualizar el botón de voto si es necesario
            renderMovies(currentMovies);
        }
    } catch (e) {
        console.error('No se pudo refrescar película');
    }
}

// ────────────────────────────────────────────────
// CERRAR MODAL AL HACER CLICK FUERA
// ────────────────────────────────────────────────
document.getElementById('movieModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ────────────────────────────────────────────────
// INICIALIZAR
// ────────────────────────────────────────────────
window.addEventListener('load', () => {
    fetchMovies();

    // Simular tiempo de carga (puedes quitar o ajustar)
    setTimeout(() => {
        document.getElementById('loadingScreen')?.classList.add('hidden');
    }, 1800);
});
</script>
</body>
</html>