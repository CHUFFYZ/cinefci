PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE peliculas (id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, poster TEXT, poster_large TEXT, resumen TEXT, votos INTEGER, veces_ganadora INTEGER DEFAULT 0, trailer TEXT);
INSERT INTO peliculas VALUES(1,'Sonic','https://img.aullidos.com/imagenes/caratulas/sonic-poster.jpg','https://image.tmdb.org/t/p/original/stmYfCUGd8Iy6kAMBr6AmWqx8Bq.jpg','Tom Wachowski, el sheriff de la ciudad de Green Hills, viajará a San Francisco para ayudar a Sonic, un erizo azul antropomórfico que corre a velocidades supersónicas, en su batalla contra el maligno Dr. Robotnik y sus aliados.',0,0,'https://www.youtube.com/watch?v=MsaAnA2EZQg');
INSERT INTO peliculas VALUES(2,'Zombieland','https://cartelesmix.es/images/CartelesB/bienvenidosazombieland0902.jpg','https://cartelesmix.es/images/CartelesB/bienvenidosazombieland0912.jpg','En un mundo plagado de zombis, Columbus es un joven que se encuentra aterrorizado por la situación y cuya cobardía precisamente le ha permitido que sus sesos aún se mantengan en su cabeza. Sin embargo, se verá forzado a sacar el poco valor del que dispone para unirse a Tallahassee, un cazador de muertos vivientes. En su camino se tropezarán con un par de hermanas.',0,0,NULL);
INSERT INTO peliculas VALUES(3,'Drive','https://image.tmdb.org/t/p/original/emDGeFBaDuIBeIScXAF41IO2pVQ.jpg','https://pics.filmaffinity.com/drive-841533318-large.jpg','Un misterioso doble de acción de Hollywood se pone a trabajar como conductor de huida y se encuentra en problemas cuando ayuda a su vecina.',0,1,'https://www.youtube.com/watch?v=spXZwUOISVs');
INSERT INTO peliculas VALUES(4,'Toy Story','https://pics.filmaffinity.com/toy_story-626273371-large.jpg','https://wallpapers.com/images/hd/toy-story-movie-poster-fnfiewbbxeades1x.jpg','Woody, el juguete favorito de Andy, se siente amenazado por la inesperada llegada de Buzz Lightyear, el guardián del espacio.',0,0,'https://www.youtube.com/watch?v=VhCDgv4x_pU');
INSERT INTO peliculas VALUES(5,'Tron (1982)','https://image.tmdb.org/t/p/original/7s9zwO13CSXgjWRyACuOVWK5fz0.jpg','https://pics.filmaffinity.com/tron-666190208-large.jpg','Flynn, un ordenador que inventa vídeojuegos, se encuentra a merced de la malvada fuerza humana que contesta al panel de control principal -la presencia de un poderoso corrupto ordenador que ha radiado Flynn dentro de su juego mortal. Allí, ladrones electrónicos y la imparable carrera «Cycle Lights». Con la ayuda de sus amigos, Alan y Lora, la esperanza de Flynn es activar a Tron, el valiente y fiable programa, en una heroica batalla por salvar a la humanidad!',0,0,NULL);
INSERT INTO peliculas VALUES(6,'Batman Azteca','https://assets-prd.ignimgs.com/2025/07/24/aztec-batman-v-dd-ka-tt-2000x3000-300dpi-en-1753324456041.jpg','https://i1.wp.com/www.comicsbeat.com/wp-content/uploads/2022/06/Batman-Azteca.jpg?resize=1068%2C601&ssl=1','Yohualli Coatl vive una tragedia cuando su padre es asesinado por españoles. Yohualli escapa a Tenochtitlán para advertir a Moctezuma. En el templo de Tzinacan, el dios murciélago, Yohualli se entrena con su mentor, para vengar la muerte de su padre.',0,0,NULL);
INSERT INTO peliculas VALUES(7,'Mulan','https://i.etsystatic.com/36587870/r/il/d02a16/4774920421/il_1080xN.4774920421_dths.jpg','https://images3.alphacoders.com/112/1129058.jpg','El ejército de los Hunos, encabezado por el malvado Shun Yiu, quiere conquistar China. El emperador, para impedírselo, ha mandado a filas a todos los varones con el fin de proteger el imperio. Por otra parte, Mulán es una chica joven y valiente que vive en una aldea. Su padre está enfermo pero a pesar de ello quiere luchar por su país. Mulán no lo va a consentir y se fugará de casa con la intención de hacerse pasar por un chico y combatir en lugar de su padre.',0,0,NULL);
INSERT INTO peliculas VALUES(8,'Jeepers Creepers','https://th.bing.com/th/id/R.79cd7992e9a0de1d81baff4ff8bb3ad5?rik=mZUJ6jDpBo0tVQ&pid=ImgRaw&r=0','https://posterspy.com/wp-content/uploads/2022/11/PosterJeepersCreepers4.jpg','Trish Jenner y su hermano menor Derry cruzan EE.UU. en coche, en un viaje largo y aburrido cuya monotonía sólo es rota por sus continuas discusiones. De pronto, en mitad de ninguna parte, descubren una iglesia abandonada, cuyo tejado está cubierto por una espesa bandada de cuervos, y ven como un misterioso personaje arroja un bulto al interior de una gran boca del alcantarillado.En ese momento comienzan una huída aterradora, perseguidos por una de las criaturas más letales que se pueda imaginar.',0,0,NULL);
INSERT INTO peliculas VALUES(9,'Intensa Mente','https://mx.web.img3.acsta.net/pictures/17/08/07/21/43/466493.jpg','https://2.bp.blogspot.com/-LiuzwX6IjnI/V2lKTeob0KI/AAAAAAAAE9A/3hbb9L8c8DUFXcdl6BaTOyfCRI2WnZt_wCLcB/s1600/s7.jpeg','iley acaba de nacer y en el centro de control de su pequeña mente solo hay sitio para Alegría. Poco después aparece Tristeza y, más tarde, Ira, Miedo y Asco. Las cinco emociones tendrán que ayudar a la niña cuando, ya con 11 años, su familia se mude desde su idílico pueblo del Medio Oeste estadounidense a la enorme e intimidante ciudad de San Francisco. Tras una serie de acontecimientos, Alegría y Tristeza tendrán que trabajar juntas para salvar a Riley.',0,0,'https://www.youtube.com/watch?v=o1uhzfrBwXA');
INSERT INTO peliculas VALUES(10,'La Mansion Encantada(2003)','https://images.justwatch.com/poster/101336709/s718/la-mansion-encantada.jpg','https://okdiario.com/img/2022/05/02/la-mansion-encantada-disney-990x556.jpg','Agobiados por la presencia de fantasmas en una mansión que compraron recientemente, una mujer y su hijo contratan a un sacerdote, un guía turístico, un historiador y un psíquico para que les ayuden a exorcizar el lugar.',0,0,NULL);
INSERT INTO peliculas VALUES(11,'Como si Fuera la Primera Vez','https://4.bp.blogspot.com/-DJD9pAM7yrI/VVpNuZi5zPI/AAAAAAAAALI/wtjhHzPgaHk/s1600/como-si-fuera-la-primera-vezsubtitulada-poster-en-alta-resolucion-hd-adam-sandler-drew-barrymore-rob-schneider.jpg','https://th.bing.com/th/id/R.4b7ec365472b666bbc3d50b7c76d9c4a?rik=oFowu1DQ8d1DVw&pid=ImgRaw&r=0','Henry, biólogo marino, no tiene la mínima intención de comprometerse con nadie, hasta que conoce a Lucy, la chica de sus sueños. Sin embargo, hay un pequeño problema, la joven se levanta cada mañana sin recordar absolutamente nada del día anterior.',0,0,NULL);
INSERT INTO peliculas VALUES(12,'A todos los chicos de los que me enamoré','https://i.pinimg.com/originals/bd/2a/ba/bd2abadbef87c8878d29f52dfb812e64.jpg','https://tse4.mm.bing.net/th/id/OIP.hmji_fcx-mn4Ku5GswRINQHaEK?cb=defcachec2&rs=1&pid=ImgDetMain&o=7&rm=3','Cinco amores secretos. Cinco cartas íntimas de amor. Lara Jean no iba a mandarlas, pero se enviaron y su vida cambiará totalmente.',0,0,NULL);
INSERT INTO peliculas VALUES(13,'Madagascar','https://educayaprende.com/wp-content/uploads/2014/06/madagascar_poster.jpg','https://eskipaper.com/images/madagascar-1.jpg','Un grupo de animales que pasaron toda su vida en un zoológico de Nueva York terminan por error en la selva de Madagascar y no tienen más remedio que aprender a sobrevivir en la naturaleza.',0,0,NULL);
INSERT INTO peliculas VALUES(14,'El Libro de la Vida','https://www.aceprensa.com/wp-content/uploads/2015/02/228326-0-683x1024.jpg','https://prod-ripcut-delivery.disney-plus.net/v1/variant/disney/56999552AC1781372B11FE194946E24B17CC46DC21F8FE7A675B2CF30C125F77/scale?width=1200&aspectRatio=1.78&format=jpeg','El Día de los Muertos, unos revoltosos niños acuden a un museo. Allí, su guía les enseña el ''Libro de la vida'', que guarda todas las historias, incluida la de Manolo y Joaquín, amigos inseparables hasta que rivalizan por el amor de María.',0,0,'https://www.youtube.com/watch?v=JvIvF8ST8CY');
INSERT INTO peliculas VALUES(15,'Tom y Jerry','https://image.tmdb.org/t/p/original/8XZI9QZ7Pm3fVkigWJPbrXCMzjq.jpg','https://www.mundiario.com/asset/thumbnail,1920,1080,center,center/media/cineseries/images/2022/10/26/2022102609020637828.png','Jerry se registra en un hotel de Nueva York el día de una boda importante. Esto obliga al dueño a contratar a Tom para deshacerse de Jerry. Su rivalidad comienza y el jugo del gato y el ratón amenaza la estabilidad del hotel y la boda.',0,0,NULL);
INSERT INTO peliculas VALUES(16,'Shrek','https://tse1.mm.bing.net/th/id/OIP.m-2jqBTdRWpz53skdJGElAHaLH?cb=defcachec2&rs=1&pid=ImgDetMain&o=7&rm=3','https://image.tmdb.org/t/p/original/2Biv1mSzaaxWOqvcnDZn4GcYEAb.jpg','Hace mucho tiempo, en una lejana ciénaga, vivía un ogro llamado Shrek. Un día, su preciada soledad se ve interrumpida por un montón de personajes de cuento de hadas que invaden su casa. Todos fueron desterrados de su reino por el malvado Lord Farquaad. Decidido a devolverles su reino y recuperar la soledad de su ciénaga, Shrek llega a un acuerdo con Lord Farquaad y va a rescatar a la princesa Fiona, la futura esposa del rey. Sin embargo, la princesa esconde un oscuro secreto.',0,0,NULL);
CREATE TABLE votos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id)
);
CREATE TABLE calificaciones (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    calificacion  INTEGER NOT NULL CHECK(calificacion BETWEEN 1 AND 5),
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id)
);
CREATE TABLE categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);
INSERT INTO categorias VALUES(1,'Acción');
INSERT INTO categorias VALUES(2,'Romance');
INSERT INTO categorias VALUES(3,'Comedia');
INSERT INTO categorias VALUES(4,'Drama');
INSERT INTO categorias VALUES(5,'Terror');
INSERT INTO categorias VALUES(6,'Fantasía');
INSERT INTO categorias VALUES(7,'Ciencia Ficción');
INSERT INTO categorias VALUES(8,'Animación');
INSERT INTO categorias VALUES(9,'Aventura');
INSERT INTO categorias VALUES(10,'Suspenso');
INSERT INTO categorias VALUES(21,'basura');
INSERT INTO categorias VALUES(33,'Preferidas por el desarrollador');
INSERT INTO categorias VALUES(82,'De Temporada');
CREATE TABLE pelicula_categorias (
    pelicula_id INTEGER NOT NULL,
    categoria_id INTEGER NOT NULL,
    PRIMARY KEY (pelicula_id, categoria_id),
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);
INSERT INTO pelicula_categorias VALUES(2,1);
INSERT INTO pelicula_categorias VALUES(2,9);
INSERT INTO pelicula_categorias VALUES(2,3);
INSERT INTO pelicula_categorias VALUES(2,5);
INSERT INTO pelicula_categorias VALUES(6,1);
INSERT INTO pelicula_categorias VALUES(6,8);
INSERT INTO pelicula_categorias VALUES(6,9);
INSERT INTO pelicula_categorias VALUES(6,10);
INSERT INTO pelicula_categorias VALUES(7,1);
INSERT INTO pelicula_categorias VALUES(7,8);
INSERT INTO pelicula_categorias VALUES(7,9);
INSERT INTO pelicula_categorias VALUES(7,4);
INSERT INTO pelicula_categorias VALUES(7,6);
INSERT INTO pelicula_categorias VALUES(8,10);
INSERT INTO pelicula_categorias VALUES(8,5);
INSERT INTO pelicula_categorias VALUES(10,9);
INSERT INTO pelicula_categorias VALUES(10,3);
INSERT INTO pelicula_categorias VALUES(10,6);
INSERT INTO pelicula_categorias VALUES(10,5);
INSERT INTO pelicula_categorias VALUES(13,8);
INSERT INTO pelicula_categorias VALUES(13,9);
INSERT INTO pelicula_categorias VALUES(13,3);
INSERT INTO pelicula_categorias VALUES(15,8);
INSERT INTO pelicula_categorias VALUES(15,9);
INSERT INTO pelicula_categorias VALUES(15,3);
INSERT INTO pelicula_categorias VALUES(5,1);
INSERT INTO pelicula_categorias VALUES(5,9);
INSERT INTO pelicula_categorias VALUES(5,7);
INSERT INTO pelicula_categorias VALUES(5,6);
INSERT INTO pelicula_categorias VALUES(16,8);
INSERT INTO pelicula_categorias VALUES(16,9);
INSERT INTO pelicula_categorias VALUES(16,3);
INSERT INTO pelicula_categorias VALUES(16,6);
INSERT INTO pelicula_categorias VALUES(16,33);
INSERT INTO pelicula_categorias VALUES(16,2);
INSERT INTO pelicula_categorias VALUES(12,3);
INSERT INTO pelicula_categorias VALUES(12,82);
INSERT INTO pelicula_categorias VALUES(12,4);
INSERT INTO pelicula_categorias VALUES(12,2);
INSERT INTO pelicula_categorias VALUES(12,21);
INSERT INTO pelicula_categorias VALUES(11,3);
INSERT INTO pelicula_categorias VALUES(11,82);
INSERT INTO pelicula_categorias VALUES(11,4);
INSERT INTO pelicula_categorias VALUES(11,2);
INSERT INTO pelicula_categorias VALUES(1,1);
INSERT INTO pelicula_categorias VALUES(1,9);
INSERT INTO pelicula_categorias VALUES(1,7);
INSERT INTO pelicula_categorias VALUES(1,3);
INSERT INTO pelicula_categorias VALUES(1,6);
INSERT INTO pelicula_categorias VALUES(3,1);
INSERT INTO pelicula_categorias VALUES(3,4);
INSERT INTO pelicula_categorias VALUES(3,33);
INSERT INTO pelicula_categorias VALUES(3,10);
INSERT INTO pelicula_categorias VALUES(4,8);
INSERT INTO pelicula_categorias VALUES(4,9);
INSERT INTO pelicula_categorias VALUES(4,3);
INSERT INTO pelicula_categorias VALUES(4,6);
INSERT INTO pelicula_categorias VALUES(9,8);
INSERT INTO pelicula_categorias VALUES(9,9);
INSERT INTO pelicula_categorias VALUES(9,3);
INSERT INTO pelicula_categorias VALUES(9,4);
INSERT INTO pelicula_categorias VALUES(9,6);
INSERT INTO pelicula_categorias VALUES(14,8);
INSERT INTO pelicula_categorias VALUES(14,3);
INSERT INTO pelicula_categorias VALUES(14,82);
INSERT INTO pelicula_categorias VALUES(14,6);
INSERT INTO pelicula_categorias VALUES(14,33);
INSERT INTO pelicula_categorias VALUES(14,2);
CREATE TABLE suspensiones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id INTEGER NOT NULL UNIQUE,
    fecha_suspension DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_finalizacion DATETIME NOT NULL,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
);
INSERT INTO suspensiones VALUES(1,3,'2026-02-14 10:05:38','2026-03-13T08:00');
CREATE TABLE configuracion_texto (
    clave TEXT PRIMARY KEY,
    valor TEXT NOT NULL DEFAULT '',
    descripcion TEXT,
    ultima_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO configuracion_texto VALUES('titulo_catalogo','Catálogo 15 Febrero','Título principal que aparece en la página de inicio','2026-02-17 20:23:53');
INSERT INTO configuracion_texto VALUES('subtitulo_header','Tu voz en el cine','Subtítulo debajo del logo en el header','2026-02-17 20:23:53');
INSERT INTO configuracion_texto VALUES('texto_bienvenida','Bienvenidos al mejor lugar para votar por películas','Mensaje opcional en la parte superior','2026-02-17 20:23:53');
INSERT INTO configuracion_texto VALUES('logo_cargando','CINE-FCI','Palabras que aparecen al Cargar la pagina','2026-02-17 20:23:53');
INSERT INTO configuracion_texto VALUES('texto_cargando','Dia del amor y la Amistad','Palabras que aparecen al Cargar la pagina','2026-02-17 20:23:53');
INSERT INTO configuracion_texto VALUES('texto_logo','CINE-FCI','Nombre del sistema que funciona como logo','2026-02-17 20:23:53');
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('peliculas',16);
INSERT INTO sqlite_sequence VALUES('calificaciones',19);
INSERT INTO sqlite_sequence VALUES('votos',41);
INSERT INTO sqlite_sequence VALUES('categorias',121);
INSERT INTO sqlite_sequence VALUES('suspensiones',1);
COMMIT;
