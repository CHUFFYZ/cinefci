<?php
// Asegúrate de que la carpeta 'db' exista en tu proyecto
if (!file_exists('db')) {
    mkdir('db', 0777, true);
}

$db = new PDO('sqlite:db/peliculas.db');
// Configurar para que PDO muestre errores si algo falla
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS votos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS calificaciones (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    calificacion  INTEGER NOT NULL CHECK(calificacion BETWEEN 1 AND 5),
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id)
)");
/*
// --- ESTO ES LO QUE FALTABA: CREAR LA TABLA SI NO EXISTE ---
$db->exec("CREATE TABLE IF NOT EXISTS peliculas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT,
    poster TEXT,
    poster_large TEXT,
    resumen TEXT,
    votos INTEGER DEFAULT 0
)");
//[ "El Padrino", "https://...small.jpg", "https://...large.jpg", "Sinopsis..." ],
$pelis = [
    [ "Sonic", "https://img.aullidos.com/imagenes/caratulas/sonic-poster.jpg", "https://image.tmdb.org/t/p/original/stmYfCUGd8Iy6kAMBr6AmWqx8Bq.jpg", "Tom Wachowski, el sheriff de la ciudad de Green Hills, viajará a San Francisco para ayudar a Sonic, un erizo azul antropomórfico que corre a velocidades supersónicas, en su batalla contra el maligno Dr. Robotnik y sus aliados." ],
    [ "Zombieland", "https://cartelesmix.es/images/CartelesB/bienvenidosazombieland0902.jpg", "https://cartelesmix.es/images/CartelesB/bienvenidosazombieland0912.jpg", "En un mundo plagado de zombis, Columbus es un joven que se encuentra aterrorizado por la situación y cuya cobardía precisamente le ha permitido que sus sesos aún se mantengan en su cabeza. Sin embargo, se verá forzado a sacar el poco valor del que dispone para unirse a Tallahassee, un cazador de muertos vivientes. En su camino se tropezarán con un par de hermanas." ],
    [ "Drive", "https://image.tmdb.org/t/p/original/emDGeFBaDuIBeIScXAF41IO2pVQ.jpg", "https://pics.filmaffinity.com/drive-841533318-large.jpg", "Un misterioso doble de acción de Hollywood se pone a trabajar como conductor de huida y se encuentra en problemas cuando ayuda a su vecina." ],
    [ "Toy Story", "https://pics.filmaffinity.com/toy_story-626273371-large.jpg", "https://wallpapers.com/images/hd/toy-story-movie-poster-fnfiewbbxeades1x.jpg", "Woody, el juguete favorito de Andy, se siente amenazado por la inesperada llegada de Buzz Lightyear, el guardián del espacio." ],
    [ "Tron (1992)", "https://image.tmdb.org/t/p/original/7s9zwO13CSXgjWRyACuOVWK5fz0.jpg", "https://pics.filmaffinity.com/tron-666190208-large.jpg", "Flynn, un ordenador que inventa vídeojuegos, se encuentra a merced de la malvada fuerza humana que contesta al panel de control principal -la presencia de un poderoso corrupto ordenador que ha radiado Flynn dentro de su juego mortal. Allí, ladrones electrónicos y la imparable carrera «Cycle Lights». Con la ayuda de sus amigos, Alan y Lora, la esperanza de Flynn es activar a Tron, el valiente y fiable programa, en una heroica batalla por salvar a la humanidad!" ],
    [ "Batman Azteca", "https://assets-prd.ignimgs.com/2025/07/24/aztec-batman-v-dd-ka-tt-2000x3000-300dpi-en-1753324456041.jpg", "https://i1.wp.com/www.comicsbeat.com/wp-content/uploads/2022/06/Batman-Azteca.jpg?resize=1068%2C601&ssl=1", "Yohualli Coatl vive una tragedia cuando su padre es asesinado por españoles. Yohualli escapa a Tenochtitlán para advertir a Moctezuma. En el templo de Tzinacan, el dios murciélago, Yohualli se entrena con su mentor, para vengar la muerte de su padre." ],
    [ "Mulan", "https://i.etsystatic.com/36587870/r/il/d02a16/4774920421/il_1080xN.4774920421_dths.jpg", "https://images3.alphacoders.com/112/1129058.jpg", "El ejército de los Hunos, encabezado por el malvado Shun Yiu, quiere conquistar China. El emperador, para impedírselo, ha mandado a filas a todos los varones con el fin de proteger el imperio. Por otra parte, Mulán es una chica joven y valiente que vive en una aldea. Su padre está enfermo pero a pesar de ello quiere luchar por su país. Mulán no lo va a consentir y se fugará de casa con la intención de hacerse pasar por un chico y combatir en lugar de su padre." ],
    [ "Jeepers Creepers", "https://th.bing.com/th/id/R.79cd7992e9a0de1d81baff4ff8bb3ad5?rik=mZUJ6jDpBo0tVQ&pid=ImgRaw&r=0", "https://posterspy.com/wp-content/uploads/2022/11/PosterJeepersCreepers4.jpg", "Trish Jenner y su hermano menor Derry cruzan EE.UU. en coche, en un viaje largo y aburrido cuya monotonía sólo es rota por sus continuas discusiones. De pronto, en mitad de ninguna parte, descubren una iglesia abandonada, cuyo tejado está cubierto por una espesa bandada de cuervos, y ven como un misterioso personaje arroja un bulto al interior de una gran boca del alcantarillado.En ese momento comienzan una huída aterradora, perseguidos por una de las criaturas más letales que se pueda imaginar." ],
    [ "Intensa Mente", "https://mx.web.img3.acsta.net/pictures/17/08/07/21/43/466493.jpg", "https://2.bp.blogspot.com/-LiuzwX6IjnI/V2lKTeob0KI/AAAAAAAAE9A/3hbb9L8c8DUFXcdl6BaTOyfCRI2WnZt_wCLcB/s1600/s7.jpeg", "iley acaba de nacer y en el centro de control de su pequeña mente solo hay sitio para Alegría. Poco después aparece Tristeza y, más tarde, Ira, Miedo y Asco. Las cinco emociones tendrán que ayudar a la niña cuando, ya con 11 años, su familia se mude desde su idílico pueblo del Medio Oeste estadounidense a la enorme e intimidante ciudad de San Francisco. Tras una serie de acontecimientos, Alegría y Tristeza tendrán que trabajar juntas para salvar a Riley." ],
    [ "La Mansion Encantada(2003)", "https://images.justwatch.com/poster/101336709/s718/la-mansion-encantada.jpg", "https://okdiario.com/img/2022/05/02/la-mansion-encantada-disney-990x556.jpg", "Agobiados por la presencia de fantasmas en una mansión que compraron recientemente, una mujer y su hijo contratan a un sacerdote, un guía turístico, un historiador y un psíquico para que les ayuden a exorcizar el lugar." ],
    [ "Como si Fuera la Primera Vez", "https://4.bp.blogspot.com/-DJD9pAM7yrI/VVpNuZi5zPI/AAAAAAAAALI/wtjhHzPgaHk/s1600/como-si-fuera-la-primera-vezsubtitulada-poster-en-alta-resolucion-hd-adam-sandler-drew-barrymore-rob-schneider.jpg", "https://th.bing.com/th/id/R.4b7ec365472b666bbc3d50b7c76d9c4a?rik=oFowu1DQ8d1DVw&pid=ImgRaw&r=0", "Henry, biólogo marino, no tiene la mínima intención de comprometerse con nadie, hasta que conoce a Lucy, la chica de sus sueños. Sin embargo, hay un pequeño problema, la joven se levanta cada mañana sin recordar absolutamente nada del día anterior." ],
    [ "A todos los chicos de los que me enamoré", "https://i.pinimg.com/originals/bd/2a/ba/bd2abadbef87c8878d29f52dfb812e64.jpg", "https://tse4.mm.bing.net/th/id/OIP.hmji_fcx-mn4Ku5GswRINQHaEK?cb=defcachec2&rs=1&pid=ImgDetMain&o=7&rm=3", "Cinco amores secretos. Cinco cartas íntimas de amor. Lara Jean no iba a mandarlas, pero se enviaron y su vida cambiará totalmente." ],
    [ "Madagascar", "https://educayaprende.com/wp-content/uploads/2014/06/madagascar_poster.jpg", "https://eskipaper.com/images/madagascar-1.jpg", "Un grupo de animales que pasaron toda su vida en un zoológico de Nueva York terminan por error en la selva de Madagascar y no tienen más remedio que aprender a sobrevivir en la naturaleza." ],
    [ "El Libro de la Vida", "https://th.bing.com/th/id/R.de782a6f510fd5993a703e0a741f29a5?rik=q8p1BYhj%2bszIbA&riu=http%3a%2f%2fes.web.img2.acsta.net%2fpictures%2f15%2f01%2f26%2f13%2f38%2f226702.jpg&ehk=p3L%2fC8WVYrsV8nd7EkI1zP90gDw30QPrOEWAixloNMQ%3d&risl=&pid=ImgRaw&r=0", "https://prod-ripcut-delivery.disney-plus.net/v1/variant/disney/56999552AC1781372B11FE194946E24B17CC46DC21F8FE7A675B2CF30C125F77/scale?width=1200&aspectRatio=1.78&format=jpeg", "El Día de los Muertos, unos revoltosos niños acuden a un museo. Allí, su guía les enseña el 'Libro de la vida', que guarda todas las historias, incluida la de Manolo y Joaquín, amigos inseparables hasta que rivalizan por el amor de María." ],
    [ "Tom y Jerry", "https://image.tmdb.org/t/p/original/8XZI9QZ7Pm3fVkigWJPbrXCMzjq.jpg", "https://th.bing.com/th/id/R.f6e0a8b7cc4757bf2a1a470abb411d2b?rik=hi5c3jPtZEglqA&riu=http%3a%2f%2fblancica.com%2fimages%2f2021%2ftomjerry_video.jpg&ehk=Q3WTSx8LP%2bSSDRIB2fPq6KOSTDnlgrkzCLds0%2bSS4Eg%3d&risl=&pid=ImgRaw&r=0", "Jerry se registra en un hotel de Nueva York el día de una boda importante. Esto obliga al dueño a contratar a Tom para deshacerse de Jerry. Su rivalidad comienza y el jugo del gato y el ratón amenaza la estabilidad del hotel y la boda." ]
];
*/
// Preparamos la inserción
/*$stmt = $db->prepare("INSERT OR IGNORE INTO peliculas (titulo, poster, poster_large, resumen) VALUES (?,?,?,?)");*/
/*
foreach ($pelis as $p) {
    $stmt->execute($p);
}
*/
echo "Base de datos actualizada y películas insertadas con éxito.";