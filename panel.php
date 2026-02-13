<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <link rel="stylesheet" href="css/panel.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img class="logo-icon" src="image/logo/logo.png" alt="">
                <div>
                    <div class="logo-text">CINE-FCI</div>
                    <div class="subtitle">Panel de Control</div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- WINNER SECTION -->
        <div class="winner-section" id="winnerSection">
            <div class="no-winner">
                <div style="font-size: 3rem; margin-bottom: 10px;">🏆</div>
                Aún no hay votos registrados
            </div>
        </div>
        <button class="reset-btn" onclick="resetVotos()"> 🔄 Resetear Votos</button>
        
        <!-- STATS SECTION -->
        <div class="stats-header">
            <h2 class="section-title">Ranking de Películas</h2>
            <button class="refresh-btn" onclick="loadStats()">
                🔄 Actualizar
            </button>
        </div>

        <div class="movies-list" id="moviesList">
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">No hay datos de votación disponibles</div>
            </div>
        </div>
    </main>

    <script>
    // Configuración - ajusta la URL según dónde esté tu archivo PHP
    const API_URL = 'api.php';  // o './api.php' o 'https://tudominio.com/api.php'

    // Generar un identificador único por navegador (para browser_id)
    function getBrowserId() {
        let bid = localStorage.getItem('browser_id');
        if (!bid) {
            bid = 'bid_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('browser_id', bid);
        }
        return bid;
    }

    const BROWSER_ID = getBrowserId();

    // ── Cargar estadísticas desde el servidor ──────────────────────────────
    async function loadStats() {
        try {
            const url = `${API_URL}?action=stats`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error en la respuesta del servidor');

            const data = await response.json();

            renderWinner(data.ganador);
            renderMoviesList(data.peliculas);

        } catch (err) {
            console.error('Error al cargar estadísticas:', err);
            showErrorMessage("No se pudieron cargar los datos del servidor");
        }
    }

    // ── Mostrar ganador ────────────────────────────────────────────────────
    function renderWinner(ganador) {
        const section = document.getElementById('winnerSection');

        if (!ganador || ganador.votos <= 0) {
            section.innerHTML = `
                <div class="no-winner">
                    <div style="font-size: 3rem; margin-bottom: 10px;">🏆</div>
                    Aún no hay votos registrados
                </div>
            `;
            return;
        }

        section.innerHTML = `
            <div class="winner-badge">
                ⭐ Película Ganadora
            </div>
            <div class="winner-title">${ganador.titulo || ganador.title || 'Sin título'}</div>
            <div class="winner-votes">
                <strong>${ganador.votos}</strong> ${ganador.votos === 1 ? 'voto' : 'votos'}
            </div>
        `;
    }

    // ── Renderizar ranking de películas ────────────────────────────────────
    function renderMoviesList(peliculas) {
        const container = document.getElementById('moviesList');

        if (!peliculas || peliculas.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <div class="empty-state-text">No hay datos de votación disponibles</div>
                </div>
            `;
            return;
        }

        // Ya vienen ordenadas por votos DESC desde el PHP (action=stats)
        container.innerHTML = peliculas.map((movie, index) => {
            const position = index + 1;
            const avg = movie.promedio || 0;
            const countRatings = movie.total_calificaciones || 0;

            return `
                <div class="movie-item">
                    <div class="movie-position">${position}</div>
                    <div class="movie-name">${movie.titulo || movie.title || 'Sin título'}</div>
                    <div class="movie-rating">
                        ${avg > 0 ? `
                            <span class="stars">★</span>
                            <span>${avg}</span>
                            <span style="opacity: 0.5;">(${countRatings})</span>
                        ` : '<span style="opacity: 0.4;">Sin calificaciones</span>'}
                    </div>
                    <div class="movie-votes">
                        <div class="vote-count">${movie.votos || 0}</div>
                        <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 2px;">
                            ${movie.votos === 1 ? 'voto' : 'votos'}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function showErrorMessage(msg) {
        const container = document.getElementById('moviesList');
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon" style="font-size: 3rem;">⚠️</div>
                <div class="empty-state-text">${msg}</div>
            </div>
        `;
    }

    // ── Inicialización ─────────────────────────────────────────────────────
    window.addEventListener('load', () => {
        loadStats();
        // Actualización automática cada 8 segundos (evita sobrecarga)
        setInterval(loadStats, 8000);
    });

    async function resetVotos() {
        // Pedimos confirmación porque esto no se puede deshacer
        if (!confirm('¿Estás seguro de que quieres eliminar TODOS los votos? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const res = await fetch('api.php?action=reset_votes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
                // No necesitamos enviar body porque resetea todo globalmente
            });

            const data = await res.json();

            if (data.success) {
                alert('La tabla de votos ha sido vaciada.');
                loadStats(); // Refrescamos el panel inmediatamente
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error('Error al resetear:', err);
            alert('No se pudo conectar con el servidor para resetear.');
        }
    }
    // Botón de actualizar manual
    window.loadStats = loadStats;  // para que el onclick del botón lo encuentre
</script>
</body>
</html>