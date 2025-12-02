<?php
/**
 * Plugin Name: Akibara SEO Booster
 * Plugin URI: https://akibara.cl
 * Description: Plugin para mejorar el SEO de productos a 100 en Rank Math. Agrega enlaces externos por editorial y optimiza descripciones con contenido contextual.
 * Version: 2.4.0
 * Author: Akibara Dev Team
 * Author URI: https://akibara.cl
 * License: GPL v2 or later
 * Text Domain: akibara-seo-booster
 *
 * INSTRUCCIONES:
 * 1. Subir este archivo a wp-content/plugins/
 * 2. Activar el plugin en WordPress
 * 3. Ir a Herramientas > Akibara SEO Booster
 * 4. Ejecutar las optimizaciones
 *
 * CAMBIOS v2.4.0:
 * - Eliminada sección "Características de esta edición" del contenido expandido
 * - Aumentado límite de productos de 500 a 10000 para procesar todos los productos
 * - Valor por defecto del límite cambiado de 50 a 500
 *
 * CAMBIOS v2.3.0:
 * - Nueva función para limpiar secciones obsoletas de productos
 * - Detecta y remueve: "Sobre este manga (Preventa)", "Detalles del producto", etc.
 * - Nueva estadística "Secciones Obsoletas" en el panel de administración
 * - Checkbox "Limpiar Secciones Obsoletas" en opciones de optimización
 *
 * CAMBIOS v2.2.0:
 * - Aliases para slugs cortos de editoriales (ivrea-arg, panini-esp, etc.)
 * - Verificación exhaustiva contra base de datos real
 * - Confirmado: PREVENTA_CATEGORY_ID = 215 correcto
 * - Confirmado: Meta keys YITH/WC Pre-Orders correctas
 *
 * CAMBIOS v2.1.0:
 * - Soporte para Comics y Manhwa
 * - Templates por tipo de producto (manga/comics/manhwa)
 * - URLs contextuales (/product-category/manga/, /comics/, /manga/manhwa/)
 *
 * CAMBIOS v2.0.0:
 * - Detección inteligente de productos en preventa vs disponibles
 * - Texto contextual diferente según estado del producto
 * - Removida funcionalidad de power words (se maneja con badges)
 * - Corregido el texto "Explora más títulos..." para ser contextual
 */

if (!defined('ABSPATH')) {
    exit;
}

class Akibara_SEO_Booster {

    /**
     * ID de la categoría de Preventa
     */
    const PREVENTA_CATEGORY_ID = 215;
    const PREVENTA_CATEGORY_SLUG = 'preventa';

    /**
     * Mapeo de aliases de slugs a slugs canónicos
     * Esto permite matchear slugs cortos o variantes encontradas en la BD
     */
    private $slug_aliases = [
        // España
        'ivrea-esp' => 'ivrea-espana',
        'ivrea' => 'ivrea-espana',
        'panini-esp' => 'panini-espana',
        'panini' => 'panini-espana',
        'arechi' => 'arechi-manga',
        'planeta' => 'planeta-comic',
        'planeta-espana' => 'planeta-comic',
        'norma' => 'norma-editorial',
        'milky' => 'milky-way',
        'milkyway' => 'milky-way',
        'ecc' => 'ecc-ediciones',
        'distrito' => 'distrito-manga',
        'babylon' => 'ediciones-babylon',
        'ooso' => 'ooso-comics',
        'kitsune' => 'kitsune-books',
        'satori' => 'satori-ediciones',
        'selecta' => 'selecta-vision',
        'dolmen' => 'dolmen-editorial',
        'cupula' => 'la-cupula',
        'ponent' => 'ponent-mon',
        'fandogamia-editorial' => 'fandogamia',
        'now-comics' => 'now-evolution',
        'spaceman' => 'spaceman-project',
        'herder' => 'herder-editorial',
        'penguin-espana' => 'penguin-random-house-espana',
        'prh-espana' => 'penguin-random-house-espana',
        'planeta-libros' => 'planeta-libros-espana',
        'sm' => 'sm-espana',
        'gigamesh' => 'ediciones-gigamesh',
        'nocturna' => 'nocturna-ediciones',
        'alianza' => 'alianza-editorial',
        'tusquets' => 'tusquets-editores',
        'seix' => 'seix-barral',
        // Argentina
        'ivrea-arg' => 'ivrea-argentina',
        'panini-arg' => 'panini-argentina',
        'ovni' => 'ovni-press',
        'utopia' => 'utopia-editorial',
        'larp' => 'larp-editores',
        'deux-editorial' => 'deux',
        'planeta-arg' => 'planeta-argentina',
        'penguin-argentina' => 'penguin-random-house-argentina',
        'prh-argentina' => 'penguin-random-house-argentina',
        'ateneo' => 'el-ateneo',
        'siglo-xxi' => 'siglo-xxi-argentina',
        'godot' => 'ediciones-godot',
        'hotel-ideas' => 'hotel-de-las-ideas',
        // México
        'panini-mx' => 'panini-mexico',
        'panini-mex' => 'panini-mexico',
        'kamite-manga' => 'kamite',
        'smash' => 'smash-manga',
        'vid-manga' => 'vid',
        'editorial-vid' => 'vid',
        'distrito-mx' => 'distrito-manga-mexico',
        'penguin-mexico' => 'penguin-random-house-mexico',
        'prh-mexico' => 'penguin-random-house-mexico',
        'planeta-mx' => 'planeta-mexico',
        'oceano' => 'oceano-mexico',
        'fce' => 'fondo-cultura-economica',
        'fondo-cultura' => 'fondo-cultura-economica',
        'sexto' => 'sexto-piso',
        'almadia-editorial' => 'almadia',
        'era-editorial' => 'era',
        'tusquets-mx' => 'tusquets-mexico',
        'anagrama' => 'anagrama-mexico',
        'grijalbo' => 'grijalbo-mexico',
        'televisa' => 'editorial-televisa',
    ];

    /**
     * Mapeo de editoriales a sus sitios web oficiales
     * Incluye editoriales de España, Argentina y México
     */
    private $editorial_links = [
        // =====================================================
        // ESPAÑA - Manga y Cómics
        // =====================================================
        'ivrea-espana' => [
            'url' => 'https://ivrea.es',
            'name' => 'Ivrea España',
            'anchor_text' => 'Ivrea España (Editorial Oficial)'
        ],
        'panini-espana' => [
            'url' => 'https://www.panini.es/shp_esp_es/comics.html',
            'name' => 'Panini España',
            'anchor_text' => 'Panini España (Editorial Oficial)'
        ],
        'arechi-manga' => [
            'url' => 'https://www.arechimanga.com',
            'name' => 'Arechi Manga',
            'anchor_text' => 'Arechi Manga (Editorial Oficial)'
        ],
        'planeta-comic' => [
            'url' => 'https://www.planetacomic.com',
            'name' => 'Planeta Cómic',
            'anchor_text' => 'Planeta Cómic (Editorial Oficial)'
        ],
        'norma-editorial' => [
            'url' => 'https://www.normaeditorial.com',
            'name' => 'Norma Editorial',
            'anchor_text' => 'Norma Editorial (Editorial Oficial)'
        ],
        'milky-way' => [
            'url' => 'https://www.milkywayediciones.com',
            'name' => 'Milky Way Ediciones',
            'anchor_text' => 'Milky Way Ediciones (Editorial Oficial)'
        ],
        'ecc-ediciones' => [
            'url' => 'https://www.ecccomics.com',
            'name' => 'ECC Ediciones',
            'anchor_text' => 'ECC Ediciones (Editorial Oficial)'
        ],
        'distrito-manga' => [
            'url' => 'https://www.distritomanga.com',
            'name' => 'Distrito Manga',
            'anchor_text' => 'Distrito Manga (Editorial Oficial)'
        ],
        'ediciones-babylon' => [
            'url' => 'https://edicionesbabylon.es',
            'name' => 'Ediciones Babylon',
            'anchor_text' => 'Ediciones Babylon (Editorial Oficial)'
        ],
        'ooso-comics' => [
            'url' => 'https://oosocomics.com',
            'name' => 'OOSO Comics',
            'anchor_text' => 'OOSO Comics (Editorial Oficial)'
        ],
        'kitsune-books' => [
            'url' => 'https://www.kitsunemanga.com',
            'name' => 'Kitsune Books',
            'anchor_text' => 'Kitsune Books (Editorial Oficial)'
        ],
        'satori-ediciones' => [
            'url' => 'https://satoriediciones.com',
            'name' => 'Satori Ediciones',
            'anchor_text' => 'Satori Ediciones (Editorial Oficial)'
        ],
        'selecta-vision' => [
            'url' => 'https://www.selecta-vision.com',
            'name' => 'Selecta Visión',
            'anchor_text' => 'Selecta Visión (Editorial Oficial)'
        ],
        'dolmen-editorial' => [
            'url' => 'https://www.dolmeneditorial.com',
            'name' => 'Dolmen Editorial',
            'anchor_text' => 'Dolmen Editorial (Editorial Oficial)'
        ],
        'salvat' => [
            'url' => 'https://www.salvat.com',
            'name' => 'Salvat',
            'anchor_text' => 'Salvat (Editorial Oficial)'
        ],
        'la-cupula' => [
            'url' => 'https://www.lacupula.com',
            'name' => 'La Cúpula',
            'anchor_text' => 'La Cúpula (Editorial Oficial)'
        ],
        'astiberri' => [
            'url' => 'https://www.astiberri.com',
            'name' => 'Astiberri',
            'anchor_text' => 'Astiberri (Editorial Oficial)'
        ],
        'ponent-mon' => [
            'url' => 'https://www.ponentmon.com',
            'name' => 'Ponent Mon',
            'anchor_text' => 'Ponent Mon (Editorial Oficial)'
        ],
        'dibbuks' => [
            'url' => 'https://www.dibbuks.es',
            'name' => 'Dibbuks',
            'anchor_text' => 'Dibbuks (Editorial Oficial)'
        ],
        'tomodomo' => [
            'url' => 'https://tomodomo.net',
            'name' => 'Tomodomo',
            'anchor_text' => 'Tomodomo (Editorial Oficial)'
        ],
        'fandogamia' => [
            'url' => 'https://www.fandogamia.com',
            'name' => 'Fandogamia',
            'anchor_text' => 'Fandogamia (Editorial Oficial)'
        ],
        'moztros' => [
            'url' => 'https://www.moztros.es',
            'name' => 'Moztros',
            'anchor_text' => 'Moztros (Editorial Oficial)'
        ],
        'letrablanka' => [
            'url' => 'https://letrablanka.com',
            'name' => 'Letrablanka',
            'anchor_text' => 'Letrablanka (Editorial Oficial)'
        ],
        'now-evolution' => [
            'url' => 'https://www.nowevolution.es',
            'name' => 'Now Evolution',
            'anchor_text' => 'Now Evolution (Editorial Oficial)'
        ],
        'spaceman-project' => [
            'url' => 'https://www.spacemanproject.com',
            'name' => 'Spaceman Project',
            'anchor_text' => 'Spaceman Project (Editorial Oficial)'
        ],
        'herder-editorial' => [
            'url' => 'https://www.herdereditorial.com',
            'name' => 'Herder Editorial',
            'anchor_text' => 'Herder Editorial (Editorial Oficial)'
        ],
        'penguin-random-house-espana' => [
            'url' => 'https://www.penguinlibros.com',
            'name' => 'Penguin Random House España',
            'anchor_text' => 'Penguin Random House España (Editorial Oficial)'
        ],
        'planeta-libros-espana' => [
            'url' => 'https://www.planetadelibros.com',
            'name' => 'Planeta de Libros',
            'anchor_text' => 'Planeta de Libros (Editorial Oficial)'
        ],
        'anaya' => [
            'url' => 'https://www.anayainfantilyjuvenil.com',
            'name' => 'Anaya',
            'anchor_text' => 'Anaya (Editorial Oficial)'
        ],
        'sm-espana' => [
            'url' => 'https://es.literaturasm.com',
            'name' => 'SM España',
            'anchor_text' => 'SM España (Editorial Oficial)'
        ],
        'ediciones-gigamesh' => [
            'url' => 'https://www.gigamesh.com',
            'name' => 'Ediciones Gigamesh',
            'anchor_text' => 'Ediciones Gigamesh (Editorial Oficial)'
        ],
        'nova' => [
            'url' => 'https://www.penguinlibros.com/es/nova',
            'name' => 'Nova',
            'anchor_text' => 'Nova (Editorial Oficial)'
        ],
        'minotauro' => [
            'url' => 'https://www.planetadelibros.com/editorial/minotauro/18',
            'name' => 'Minotauro',
            'anchor_text' => 'Minotauro (Editorial Oficial)'
        ],
        'timun-mas' => [
            'url' => 'https://www.planetadelibros.com/editorial/timun-mas/35',
            'name' => 'Timun Mas',
            'anchor_text' => 'Timun Mas (Editorial Oficial)'
        ],
        'nocturna-ediciones' => [
            'url' => 'https://www.nocturnaediciones.com',
            'name' => 'Nocturna Ediciones',
            'anchor_text' => 'Nocturna Ediciones (Editorial Oficial)'
        ],
        'alianza-editorial' => [
            'url' => 'https://www.alianzaeditorial.es',
            'name' => 'Alianza Editorial',
            'anchor_text' => 'Alianza Editorial (Editorial Oficial)'
        ],
        'tusquets-editores' => [
            'url' => 'https://www.tusquetseditores.com',
            'name' => 'Tusquets Editores',
            'anchor_text' => 'Tusquets Editores (Editorial Oficial)'
        ],
        'seix-barral' => [
            'url' => 'https://www.planetadelibros.com/editorial/seix-barral/5',
            'name' => 'Seix Barral',
            'anchor_text' => 'Seix Barral (Editorial Oficial)'
        ],
        // =====================================================
        // ARGENTINA - Manga, Cómics y Libros
        // =====================================================
        'ivrea-argentina' => [
            'url' => 'https://www.ivrea.com.ar',
            'name' => 'Ivrea Argentina',
            'anchor_text' => 'Ivrea Argentina (Editorial Oficial)'
        ],
        'panini-argentina' => [
            'url' => 'https://www.paninicomics.com.ar',
            'name' => 'Panini Argentina',
            'anchor_text' => 'Panini Argentina (Editorial Oficial)'
        ],
        'ovni-press' => [
            'url' => 'https://www.ovnipress.com.ar',
            'name' => 'Ovni Press',
            'anchor_text' => 'Ovni Press (Editorial Oficial)'
        ],
        'utopia-editorial' => [
            'url' => 'https://www.utopiaeditorial.com.ar',
            'name' => 'Utopía Editorial',
            'anchor_text' => 'Utopía Editorial (Editorial Oficial)'
        ],
        'larp-editores' => [
            'url' => 'https://www.larpeditores.com',
            'name' => 'Larp Editores',
            'anchor_text' => 'Larp Editores (Editorial Oficial)'
        ],
        'deux' => [
            'url' => 'https://www.deuxeditora.com.ar',
            'name' => 'Deux',
            'anchor_text' => 'Deux (Editorial Oficial)'
        ],
        'planeta-argentina' => [
            'url' => 'https://www.planetadelibros.com.ar',
            'name' => 'Planeta Argentina',
            'anchor_text' => 'Planeta Argentina (Editorial Oficial)'
        ],
        'penguin-random-house-argentina' => [
            'url' => 'https://www.penguinlibros.com/ar',
            'name' => 'Penguin Random House Argentina',
            'anchor_text' => 'Penguin Random House Argentina (Editorial Oficial)'
        ],
        'sudamericana' => [
            'url' => 'https://www.penguinlibros.com/ar/editorial/sudamericana',
            'name' => 'Sudamericana',
            'anchor_text' => 'Sudamericana (Editorial Oficial)'
        ],
        'emece' => [
            'url' => 'https://www.planetadelibros.com.ar/editorial/emece/14',
            'name' => 'Emecé',
            'anchor_text' => 'Emecé (Editorial Oficial)'
        ],
        'el-ateneo' => [
            'url' => 'https://www.editorialelateneo.com.ar',
            'name' => 'El Ateneo',
            'anchor_text' => 'El Ateneo (Editorial Oficial)'
        ],
        'siglo-xxi-argentina' => [
            'url' => 'https://sigloxxieditores.com.ar',
            'name' => 'Siglo XXI Argentina',
            'anchor_text' => 'Siglo XXI Argentina (Editorial Oficial)'
        ],
        'ediciones-godot' => [
            'url' => 'https://www.edicionesgodot.com.ar',
            'name' => 'Ediciones Godot',
            'anchor_text' => 'Ediciones Godot (Editorial Oficial)'
        ],
        'hotel-de-las-ideas' => [
            'url' => 'https://hoteldelasideas.com.ar',
            'name' => 'Hotel de las Ideas',
            'anchor_text' => 'Hotel de las Ideas (Editorial Oficial)'
        ],
        'maten-al-mensajero' => [
            'url' => 'https://www.matenalmensajero.com.ar',
            'name' => 'Maten al Mensajero',
            'anchor_text' => 'Maten al Mensajero (Editorial Oficial)'
        ],
        // =====================================================
        // MÉXICO - Manga, Cómics y Libros
        // =====================================================
        'panini-mexico' => [
            'url' => 'https://www.panini.com.mx',
            'name' => 'Panini México',
            'anchor_text' => 'Panini México (Editorial Oficial)'
        ],
        'kamite' => [
            'url' => 'https://www.kamite.com.mx',
            'name' => 'Kamite',
            'anchor_text' => 'Kamite (Editorial Oficial)'
        ],
        'smash-manga' => [
            'url' => 'https://www.smashmanga.com',
            'name' => 'Smash Manga',
            'anchor_text' => 'Smash Manga (Editorial Oficial)'
        ],
        'vid' => [
            'url' => 'https://www.vid.com.mx',
            'name' => 'Editorial Vid',
            'anchor_text' => 'Editorial Vid (Editorial Oficial)'
        ],
        'distrito-manga-mexico' => [
            'url' => 'https://www.distritomanga.com.mx',
            'name' => 'Distrito Manga México',
            'anchor_text' => 'Distrito Manga México (Editorial Oficial)'
        ],
        'penguin-random-house-mexico' => [
            'url' => 'https://www.penguinlibros.com/mx',
            'name' => 'Penguin Random House México',
            'anchor_text' => 'Penguin Random House México (Editorial Oficial)'
        ],
        'planeta-mexico' => [
            'url' => 'https://www.planetadelibros.com.mx',
            'name' => 'Planeta México',
            'anchor_text' => 'Planeta México (Editorial Oficial)'
        ],
        'oceano-mexico' => [
            'url' => 'https://oceano.mx',
            'name' => 'Océano México',
            'anchor_text' => 'Océano México (Editorial Oficial)'
        ],
        'fondo-cultura-economica' => [
            'url' => 'https://www.fondodeculturaeconomica.com',
            'name' => 'Fondo de Cultura Económica',
            'anchor_text' => 'Fondo de Cultura Económica (Editorial Oficial)'
        ],
        'sexto-piso' => [
            'url' => 'https://sextopiso.mx',
            'name' => 'Sexto Piso',
            'anchor_text' => 'Sexto Piso (Editorial Oficial)'
        ],
        'almadia' => [
            'url' => 'https://www.almadia.com.mx',
            'name' => 'Almadía',
            'anchor_text' => 'Almadía (Editorial Oficial)'
        ],
        'era' => [
            'url' => 'https://www.edicionesera.com.mx',
            'name' => 'Ediciones Era',
            'anchor_text' => 'Ediciones Era (Editorial Oficial)'
        ],
        'tusquets-mexico' => [
            'url' => 'https://www.planetadelibros.com.mx/editorial/tusquets-editores-mexico/64',
            'name' => 'Tusquets México',
            'anchor_text' => 'Tusquets México (Editorial Oficial)'
        ],
        'anagrama-mexico' => [
            'url' => 'https://www.anagrama-ed.es',
            'name' => 'Anagrama',
            'anchor_text' => 'Anagrama (Editorial Oficial)'
        ],
        'grijalbo-mexico' => [
            'url' => 'https://www.penguinlibros.com/mx/editorial/grijalbo',
            'name' => 'Grijalbo México',
            'anchor_text' => 'Grijalbo México (Editorial Oficial)'
        ],
        'editorial-televisa' => [
            'url' => 'https://www.televisa.com/editorial',
            'name' => 'Editorial Televisa',
            'anchor_text' => 'Editorial Televisa (Editorial Oficial)'
        ]
    ];

    /**
     * Plantillas de descripción extendida por género/tipo
     * Incluye: Manga (shonen, seinen, shojo, josei, kodomo), Comics, Manhwa
     */
    private $description_templates = [
        // MANGA - Géneros
        'shonen' => [
            'type' => 'manga',
            'intro' => 'Sumérgete en una emocionante aventura con {title}, un manga shonen que te mantendrá al borde de tu asiento.',
            'content' => 'Esta obra maestra del manga combina acción trepidante, personajes memorables y una historia que no podrás dejar de leer. Ideal para fans del género shonen que buscan una experiencia de lectura intensa y emocionante.',
            'features' => 'Este tomo incluye ilustraciones de alta calidad, traducción profesional al español y papel de primera calidad para una experiencia de lectura óptima.',
        ],
        'seinen' => [
            'type' => 'manga',
            'intro' => 'Descubre {title}, una obra madura y profunda que explora temas complejos con una narrativa excepcional.',
            'content' => 'Este manga seinen ofrece una experiencia de lectura sofisticada, con tramas elaboradas y desarrollo de personajes que te harán reflexionar. Perfecto para lectores adultos que buscan historias con profundidad.',
            'features' => 'Edición de coleccionista con papel de alta calidad, traducción cuidada y extras exclusivos.',
        ],
        'shojo' => [
            'type' => 'manga',
            'intro' => '{title} es una historia cautivadora que te enamorará desde la primera página.',
            'content' => 'Este manga shojo combina romance, drama y personajes entrañables en una historia que toca el corazón. Una lectura perfecta para quienes buscan emociones y momentos inolvidables.',
            'features' => 'Edición especial con ilustraciones a color, papel premium y traducción oficial.',
        ],
        'josei' => [
            'type' => 'manga',
            'intro' => '{title} es una obra que explora las complejidades de la vida adulta con sensibilidad y realismo.',
            'content' => 'Este manga josei ofrece historias maduras y emotivas, con personajes complejos y situaciones que resonarán con lectores adultos. Perfecto para quienes buscan narrativas sofisticadas y emocionalmente profundas.',
            'features' => 'Edición cuidada con traducción profesional, papel de calidad superior y encuadernación elegante.',
        ],
        'kodomo' => [
            'type' => 'manga',
            'intro' => '{title} es una aventura perfecta para los lectores más jóvenes de la familia.',
            'content' => 'Este manga kodomo ofrece historias divertidas y educativas, con personajes entrañables y mensajes positivos. Ideal para iniciar a los más pequeños en el mundo del manga.',
            'features' => 'Edición apta para todas las edades, con ilustraciones coloridas y texto fácil de leer.',
        ],
        // COMICS
        'comics' => [
            'type' => 'comics',
            'intro' => 'Descubre {title}, un cómic imprescindible para cualquier coleccionista.',
            'content' => 'Esta edición trae una de las mejores historias del mundo del cómic, con arte espectacular y una narrativa que te atrapará desde la primera página. Perfecto para fans del género que buscan calidad en cada viñeta.',
            'features' => 'Incluye impresión de alta calidad, colores vibrantes, papel premium y encuadernación resistente para tu colección.',
        ],
        // MANHWA
        'manhwa' => [
            'type' => 'manhwa',
            'intro' => 'Sumérgete en {title}, un manhwa coreano que te cautivará con su estilo único.',
            'content' => 'Este manhwa ofrece una experiencia de lectura diferente, con el distintivo estilo artístico coreano y narrativas que combinan lo mejor de oriente y occidente. Una obra que destaca por su originalidad y calidad visual.',
            'features' => 'Edición oficial con traducción profesional al español, formato de lectura tradicional y papel de alta calidad.',
        ],
        // DEFAULT - Manga genérico
        'manga' => [
            'type' => 'manga',
            'intro' => 'Descubre {title}, una obra imprescindible para cualquier amante del manga.',
            'content' => 'Esta edición oficial trae una de las mejores historias del género, con una narrativa cautivadora y un arte visual impresionante que te transportará a un mundo único.',
            'features' => 'Incluye traducción profesional al español, papel de alta calidad y encuadernación resistente para tu colección.',
        ],
        // DEFAULT - Genérico
        'default' => [
            'type' => 'general',
            'intro' => 'Descubre {title}, una obra imprescindible para tu colección.',
            'content' => 'Esta edición oficial ofrece una experiencia de lectura excepcional, con narrativa cautivadora y arte visual impresionante.',
            'features' => 'Incluye traducción profesional al español, papel de alta calidad y encuadernación resistente.',
        ]
    ];

    /**
     * Constructor
     */
    public function __construct() {
        // Hooks de administración
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // Hooks de frontend
        add_action('woocommerce_after_single_product_summary', [$this, 'display_external_editorial_link'], 15);

        // Hook para modificar el contenido del producto al guardar
        add_action('woocommerce_process_product_meta', [$this, 'maybe_enhance_product_on_save'], 50, 1);

        // AJAX handlers
        add_action('wp_ajax_akibara_process_products', [$this, 'ajax_process_products']);
        add_action('wp_ajax_akibara_get_product_stats', [$this, 'ajax_get_product_stats']);
        add_action('wp_ajax_akibara_fix_contextual_text', [$this, 'ajax_fix_contextual_text']);
    }

    /**
     * =====================================================
     * DETECCIÓN DE ESTADO DEL PRODUCTO (PREVENTA/DISPONIBLE)
     * =====================================================
     */

    /**
     * Verificar si un producto está en preventa
     * Revisa múltiples fuentes: categoría, meta de YITH Pre-Order, etc.
     *
     * @param int $product_id ID del producto
     * @return bool True si está en preventa
     */
    public function is_product_preorder($product_id) {
        // 1. Verificar por categoría "Preventa"
        if (has_term(self::PREVENTA_CATEGORY_SLUG, 'product_cat', $product_id)) {
            return true;
        }

        // 2. Verificar por term_id de categoría Preventa
        if (has_term(self::PREVENTA_CATEGORY_ID, 'product_cat', $product_id)) {
            return true;
        }

        // 3. Verificar meta keys de YITH Pre-Order
        $yith_preorder = get_post_meta($product_id, '_ywpo_preorder', true);
        if ($yith_preorder === 'yes') {
            return true;
        }

        // 4. Verificar si tiene fecha de pre-order de YITH
        $yith_preorder_date = get_post_meta($product_id, '_ywpo_for_sale_date', true);
        if (!empty($yith_preorder_date)) {
            // Si la fecha es futura, está en preventa
            if (strtotime($yith_preorder_date) > time()) {
                return true;
            }
        }

        // 5. Verificar meta key alternativa de YITH
        $yith_release_date = get_post_meta($product_id, '_yith_pre_order_release_dat', true);
        if (!empty($yith_release_date)) {
            return true;
        }

        // 6. Verificar plugin WooCommerce Pre-Orders
        $wc_preorder = get_post_meta($product_id, '_wc_pre_orders_enabled', true);
        if ($wc_preorder === 'yes') {
            return true;
        }

        // 7. Verificar por fecha de disponibilidad
        $availability_date = get_post_meta($product_id, '_wc_pre_orders_availability_datetime', true);
        if (!empty($availability_date) && strtotime($availability_date) > time()) {
            return true;
        }

        return false;
    }

    /**
     * Obtener el estado del producto como texto
     *
     * @param int $product_id ID del producto
     * @return string 'preventa' o 'disponible'
     */
    public function get_product_status($product_id) {
        return $this->is_product_preorder($product_id) ? 'preventa' : 'disponible';
    }

    /**
     * Obtener la fecha de disponibilidad de un producto en preventa
     *
     * @param int $product_id ID del producto
     * @return string|null Fecha formateada o null
     */
    public function get_preorder_date($product_id) {
        // Intentar obtener de YITH
        $date = get_post_meta($product_id, '_ywpo_for_sale_date', true);
        if (!empty($date)) {
            return date_i18n('j \d\e F \d\e Y', strtotime($date));
        }

        $date = get_post_meta($product_id, '_yith_pre_order_release_dat', true);
        if (!empty($date)) {
            return date_i18n('j \d\e F \d\e Y', strtotime($date));
        }

        // Intentar obtener de WC Pre-Orders
        $date = get_post_meta($product_id, '_wc_pre_orders_availability_datetime', true);
        if (!empty($date)) {
            return date_i18n('j \d\e F \d\e Y', strtotime($date));
        }

        return null;
    }

    /**
     * =====================================================
     * DETECCIÓN DE TIPO DE PRODUCTO (MANGA/COMICS/MANHWA)
     * =====================================================
     */

    /**
     * Detectar el tipo principal del producto
     *
     * @param int $product_id ID del producto
     * @return array ['type' => 'manga|comics|manhwa', 'genre' => 'shonen|seinen|...', 'label' => 'Manga|Comics|Manhwa']
     */
    public function get_product_type($product_id) {
        $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);

        if (is_wp_error($categories)) {
            return ['type' => 'general', 'genre' => 'default', 'label' => 'producto'];
        }

        // Verificar si es Comics
        if (in_array('comics', $categories)) {
            return ['type' => 'comics', 'genre' => 'comics', 'label' => 'cómics'];
        }

        // Verificar si es Manhwa
        if (in_array('manhwa', $categories)) {
            return ['type' => 'manhwa', 'genre' => 'manhwa', 'label' => 'manhwa'];
        }

        // Verificar si es Manga (con género específico)
        if (in_array('manga', $categories)) {
            // Buscar género específico
            foreach (['shonen', 'seinen', 'shojo', 'josei', 'kodomo'] as $genre) {
                if (in_array($genre, $categories)) {
                    return ['type' => 'manga', 'genre' => $genre, 'label' => 'manga'];
                }
            }
            return ['type' => 'manga', 'genre' => 'manga', 'label' => 'manga'];
        }

        // Verificar géneros directamente (sin categoría padre manga)
        foreach (['shonen', 'seinen', 'shojo', 'josei', 'kodomo'] as $genre) {
            if (in_array($genre, $categories)) {
                return ['type' => 'manga', 'genre' => $genre, 'label' => 'manga'];
            }
        }

        return ['type' => 'general', 'genre' => 'default', 'label' => 'producto'];
    }

    /**
     * =====================================================
     * GENERACIÓN DE CONTENIDO CONTEXTUAL
     * =====================================================
     */

    /**
     * Generar el texto final contextual según el estado y TIPO del producto
     *
     * IMPORTANTE: Este es el texto que aparece al final de la descripción
     * - Si es PREVENTA: NO mencionar "visita nuestras preventas" (ya es preventa)
     * - Si es DISPONIBLE: Puede mencionar preventas
     * - Adaptar enlaces según tipo: manga, comics, manhwa
     *
     * @param int $product_id ID del producto
     * @return string HTML del texto contextual
     */
    public function generate_contextual_footer($product_id) {
        $is_preorder = $this->is_product_preorder($product_id);
        $preorder_date = $this->get_preorder_date($product_id);
        $product_type = $this->get_product_type($product_id);

        // Determinar el enlace y texto según el tipo de producto
        $type_links = [
            'manga' => [
                'url' => '/product-category/manga/',
                'text' => 'colección de manga'
            ],
            'comics' => [
                'url' => '/product-category/comics/',
                'text' => 'colección de cómics'
            ],
            'manhwa' => [
                'url' => '/product-category/manga/manhwa/',
                'text' => 'colección de manhwa'
            ],
            'general' => [
                'url' => '/tienda/',
                'text' => 'catálogo'
            ]
        ];

        $link_data = $type_links[$product_type['type']] ?? $type_links['general'];

        if ($is_preorder) {
            // PRODUCTO EN PREVENTA
            $text = '<p class="akibara-contextual-footer">';

            if ($preorder_date) {
                $text .= "Este producto estará disponible a partir del <strong>{$preorder_date}</strong>. ";
            }

            $text .= 'Explora más títulos en nuestra <a href="' . $link_data['url'] . '">' . $link_data['text'] . '</a>.';
            $text .= '</p>';
        } else {
            // PRODUCTO DISPONIBLE
            $text = '<p class="akibara-contextual-footer">';
            $text .= 'Explora más títulos en nuestra <a href="' . $link_data['url'] . '">' . $link_data['text'] . '</a> ';
            $text .= 'o visita nuestras <a href="/product-category/preventa/">preventas</a>.';
            $text .= '</p>';
        }

        return $text;
    }

    /**
     * Verificar si el contenido tiene el texto problemático
     * "visita nuestras preventas" en un producto que ES preventa
     *
     * @param int $product_id ID del producto
     * @return bool True si hay inconsistencia
     */
    public function has_contextual_text_issue($product_id) {
        $post = get_post($product_id);
        $content = $post->post_content;

        $is_preorder = $this->is_product_preorder($product_id);
        $has_preventa_link = (
            stripos($content, 'visita nuestras preventas') !== false ||
            stripos($content, '/product-category/preventa/') !== false ||
            stripos($content, 'preventas</a>') !== false
        );

        // Si es preventa Y tiene link a preventas = problema
        if ($is_preorder && $has_preventa_link) {
            return true;
        }

        return false;
    }

    /**
     * Corregir el texto contextual de un producto
     *
     * @param int $product_id ID del producto
     * @param bool $preview Solo verificar, no modificar
     * @return string|null Descripción del cambio o null
     */
    public function fix_contextual_text($product_id, $preview = false) {
        if (!$this->has_contextual_text_issue($product_id)) {
            return null;
        }

        $post = get_post($product_id);
        $content = $post->post_content;
        $original_content = $content;

        // Patrones a buscar y reemplazar
        $patterns = [
            // Patrón completo con enlaces
            '/<p[^>]*>Explora más títulos en nuestra <a[^>]*>colección de manga<\/a> o visita nuestras <a[^>]*>preventas<\/a>\.<\/p>/i',
            // Patrón sin tags de párrafo
            '/Explora más títulos en nuestra <a[^>]*>colección de manga<\/a> o visita nuestras <a[^>]*>preventas<\/a>\./i',
            // Patrón con texto simple
            '/Explora más títulos en nuestra colección de manga o visita nuestras preventas\./i',
        ];

        // Generar el nuevo texto contextual
        $new_text = $this->generate_contextual_footer($product_id);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $new_text, $content);
                break;
            }
        }

        // Si el contenido no cambió, no hay nada que hacer
        if ($content === $original_content) {
            return null;
        }

        if (!$preview) {
            wp_update_post([
                'ID' => $product_id,
                'post_content' => $content
            ]);

            update_post_meta($product_id, '_akibara_contextual_text_fixed', [
                'date' => current_time('mysql'),
                'status' => $this->get_product_status($product_id)
            ]);
        }

        return "Texto contextual corregido (es preventa, no debe mencionar preventas)";
    }

    /**
     * =====================================================
     * LIMPIEZA DE SECCIONES OBSOLETAS
     * =====================================================
     */

    /**
     * Verificar si un producto tiene secciones obsoletas del formato antiguo
     * Detecta: "Sobre este manga (Preventa)", "Detalles del producto", texto contextual
     *
     * @param int $product_id ID del producto
     * @return bool True si tiene contenido obsoleto
     */
    public function has_legacy_sections($product_id) {
        $post = get_post($product_id);
        if (!$post) {
            return false;
        }
        $content = $post->post_content;

        // Patrones de secciones obsoletas a detectar
        $legacy_patterns = [
            // Encabezado "Sobre este manga/cómic/manhwa" con o sin "(Preventa)"
            '/<h[23]>Sobre este (manga|cómic|comic|manhwa|producto)(\s*\(Preventa\))?<\/h[23]>/i',
            // Sección "Detalles del producto" con lista
            '/<h[23]>Detalles del producto<\/h[23]>/i',
            // Párrafo de fecha de preventa
            '/<p><strong>Disponible a partir del [^<]+<\/strong><\/p>/i',
            // Lista con Estado: Preventa o Disponible
            '/<li><strong>Estado:<\/strong>\s*(Preventa|Disponible)<\/li>/i',
            // Texto contextual "Explora más títulos..." con link a preventas
            '/Explora más títulos en nuestra.*?preventas<\/a>\./is',
            // Footer contextual viejo
            '/<p[^>]*class=["\']?akibara-contextual-footer["\']?[^>]*>.*?<\/p>/is',
            // Formatos antiguos con Editorial:
            '/<li><strong>Editorial:<\/strong>\s*[^<]+<\/li>/i',
            '/<p><strong>Editorial:<\/strong>\s*[^<]+<\/p>/i',
            '/<p>Editorial:\s*[^<]+<\/p>/i',
            '/<(div|span)[^>]*>Editorial:\s*[^<]+<\/(div|span)>/i',
        ];

        foreach ($legacy_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limpiar secciones obsoletas de un producto
     *
     * @param int $product_id ID del producto
     * @param bool $preview Solo verificar, no modificar
     * @return string|null Descripción del cambio o null
     */
    public function clean_legacy_sections($product_id, $preview = false) {
        if (!$this->has_legacy_sections($product_id)) {
            return null;
        }

        $post = get_post($product_id);
        $content = $post->post_content;
        $original_content = $content;
        $changes = [];

        // 1. Remover encabezado "Sobre este manga/cómic/manhwa" con o sin "(Preventa)"
        $pattern = '/<h[23]>Sobre este (manga|cómic|comic|manhwa|producto)(\s*\(Preventa\))?<\/h[23]>\s*/i';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $changes[] = 'Removido "Sobre este..."';
        }

        // 2. Remover párrafo de fecha de preventa
        $pattern = '/<p><strong>Disponible a partir del [^<]+<\/strong><\/p>\s*/i';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $changes[] = 'Removida fecha de preventa';
        }

        // 3. Remover sección completa "Detalles del producto" con su lista
        $pattern = '/<h[23]>Detalles del producto<\/h[23]>\s*<ul>.*?<\/ul>\s*/is';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $changes[] = 'Removido "Detalles del producto"';
        }

        // 4. Remover footer contextual con clase
        $pattern = '/<p[^>]*class=["\']?akibara-contextual-footer["\']?[^>]*>.*?<\/p>\s*/is';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $changes[] = 'Removido footer contextual';
        }

        // 5. Remover texto "Explora más títulos..." (varias variantes)
        $patterns = [
            '/<p>Explora más títulos en nuestra.*?preventas<\/a>\.<\/p>\s*/is',
            '/<p>Explora más títulos en nuestra.*?manga<\/a>\.<\/p>\s*/is',
            '/Explora más títulos en nuestra <a[^>]*>[^<]+<\/a>( o visita nuestras <a[^>]*>preventas<\/a>)?\.\s*/is',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '', $content);
                $changes[] = 'Removido texto "Explora más..."';
                break;
            }
        }

        // 6. Remover item de lista con Editorial (formato antiguo)
        $editorial_patterns = [
            // Formato: <li><strong>Editorial:</strong> Nombre</li>
            '/<li><strong>Editorial:<\/strong>\s*[^<]+<\/li>\s*/i',
            // Formato: <p><strong>Editorial:</strong> Nombre</p>
            '/<p><strong>Editorial:<\/strong>\s*[^<]+<\/p>\s*/i',
            // Formato: <p>Editorial: Nombre</p>
            '/<p>Editorial:\s*[^<]+<\/p>\s*/i',
            // Formato en div o span
            '/<(div|span)[^>]*>Editorial:\s*[^<]+<\/(div|span)>\s*/i',
        ];
        foreach ($editorial_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '', $content);
                $changes[] = 'Removido "Editorial:" antiguo';
            }
        }

        // Si no hay cambios, retornar null
        if (empty($changes) || $content === $original_content) {
            return null;
        }

        // Limpiar espacios en blanco múltiples y líneas vacías
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        if (!$preview) {
            wp_update_post([
                'ID' => $product_id,
                'post_content' => $content
            ]);

            // Si se eliminó "Editorial:" antiguo, limpiar metadata para permitir re-añadir enlace
            if (in_array('Removido "Editorial:" antiguo', $changes)) {
                delete_post_meta($product_id, '_akibara_external_link_added');
            }

            update_post_meta($product_id, '_akibara_legacy_cleaned', [
                'date' => current_time('mysql'),
                'changes' => $changes
            ]);
        }

        return implode(', ', $changes);
    }

    /**
     * Verificar si un producto tiene metadata huérfana
     *
     * @param int $product_id ID del producto
     * @return bool True si tiene metadata huérfana
     */
    public function has_orphan_metadata($product_id) {
        $post = get_post($product_id);
        if (!$post) {
            return false;
        }

        $content = $post->post_content;

        // Verificar meta de enlace externo huérfano
        $external_link_meta = get_post_meta($product_id, '_akibara_external_link_added', true);
        if ($external_link_meta) {
            $has_editorial_link = preg_match('/<p[^>]*class=["\']?editorial-link["\']?/i', $content) ||
                                  preg_match('/Conoce más sobre esta editorial/i', $content);
            if (!$has_editorial_link) {
                return true;
            }
        }

        // Verificar meta de descripción expandida huérfana
        $description_meta = get_post_meta($product_id, '_akibara_description_expanded', true);
        if ($description_meta) {
            $has_seo_content = preg_match('/<div[^>]*class=["\']?seo-description["\']?/i', $content) ||
                               preg_match('/SEO Content Added by Akibara/i', $content);
            if (!$has_seo_content) {
                return true;
            }
        }

        // Verificar meta de texto contextual huérfano
        $contextual_meta = get_post_meta($product_id, '_akibara_contextual_text_fixed', true);
        if ($contextual_meta) {
            $has_contextual = preg_match('/<p[^>]*class=["\']?akibara-contextual-footer["\']?/i', $content);
            if (!$has_contextual) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limpiar metadata huérfana de productos
     * Elimina meta keys que ya no tienen contenido asociado en el producto
     *
     * @param int $product_id ID del producto
     * @param bool $preview Solo verificar, no modificar
     * @return string|null Descripción del cambio o null
     */
    public function clean_orphan_metadata($product_id, $preview = false) {
        $post = get_post($product_id);
        if (!$post) {
            return null;
        }

        $content = $post->post_content;
        $cleaned = [];

        // Verificar si tiene meta de enlace externo pero no tiene el enlace en contenido
        $external_link_meta = get_post_meta($product_id, '_akibara_external_link_added', true);
        if ($external_link_meta) {
            // Buscar si el contenido tiene el enlace editorial
            $has_editorial_link = preg_match('/<p[^>]*class=["\']?editorial-link["\']?/i', $content) ||
                                  preg_match('/Conoce más sobre esta editorial/i', $content);

            if (!$has_editorial_link) {
                if (!$preview) {
                    delete_post_meta($product_id, '_akibara_external_link_added');
                }
                $cleaned[] = 'Meta enlace externo huérfano';
            }
        }

        // Verificar si tiene meta de descripción expandida pero no tiene el contenido
        $description_meta = get_post_meta($product_id, '_akibara_description_expanded', true);
        if ($description_meta) {
            $has_seo_content = preg_match('/<div[^>]*class=["\']?seo-description["\']?/i', $content) ||
                               preg_match('/SEO Content Added by Akibara/i', $content);

            if (!$has_seo_content) {
                if (!$preview) {
                    delete_post_meta($product_id, '_akibara_description_expanded');
                }
                $cleaned[] = 'Meta descripción expandida huérfana';
            }
        }

        // Verificar si tiene meta de texto contextual pero no tiene el contenido
        $contextual_meta = get_post_meta($product_id, '_akibara_contextual_text_fixed', true);
        if ($contextual_meta) {
            $has_contextual = preg_match('/<p[^>]*class=["\']?akibara-contextual-footer["\']?/i', $content);

            if (!$has_contextual) {
                if (!$preview) {
                    delete_post_meta($product_id, '_akibara_contextual_text_fixed');
                }
                $cleaned[] = 'Meta texto contextual huérfano';
            }
        }

        if (empty($cleaned)) {
            return null;
        }

        return implode(', ', $cleaned);
    }

    /**
     * =====================================================
     * AGREGAR MENÚ DE ADMINISTRACIÓN
     * =====================================================
     */

    public function add_admin_menu() {
        add_management_page(
            'Akibara SEO Booster',
            'Akibara SEO Booster',
            'manage_options',
            'akibara-seo-booster',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_admin_styles($hook) {
        if ($hook !== 'tools_page_akibara-seo-booster') {
            return;
        }

        wp_add_inline_style('wp-admin', '
            .akibara-seo-wrap { max-width: 1200px; margin: 20px auto; }
            .akibara-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
            .akibara-card h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .akibara-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin: 20px 0; }
            .akibara-stat-box { background: #f8f9fa; padding: 15px; border-radius: 4px; text-align: center; }
            .akibara-stat-box .number { font-size: 32px; font-weight: bold; color: #2271b1; }
            .akibara-stat-box .label { color: #666; font-size: 13px; }
            .akibara-stat-box.warning .number { color: #dba617; }
            .akibara-stat-box.error .number { color: #d63638; }
            .akibara-stat-box.success .number { color: #00a32a; }
            .akibara-progress { height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden; margin: 10px 0; }
            .akibara-progress-bar { height: 100%; background: linear-gradient(90deg, #2271b1, #135e96); transition: width 0.3s; }
            .akibara-log { background: #1d2327; color: #fff; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; }
            .akibara-log .success { color: #00d084; }
            .akibara-log .error { color: #ff6b6b; }
            .akibara-log .info { color: #72aee6; }
            .akibara-log .warning { color: #f0b849; }
            .akibara-btn { padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 10px; }
            .akibara-btn-primary { background: #2271b1; color: #fff; border: none; }
            .akibara-btn-primary:hover { background: #135e96; }
            .akibara-btn-secondary { background: #f0f0f1; color: #1d2327; border: 1px solid #c3c4c7; }
            .akibara-btn-warning { background: #dba617; color: #fff; border: none; }
            .akibara-editorial-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; }
            .akibara-editorial-item { padding: 10px; background: #f8f9fa; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
            .akibara-options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 768px) { .akibara-options-grid { grid-template-columns: 1fr; } }
            .akibara-alert { padding: 15px; border-radius: 4px; margin-bottom: 15px; }
            .akibara-alert-warning { background: #fcf9e8; border-left: 4px solid #dba617; }
            .akibara-alert-info { background: #e7f5fe; border-left: 4px solid #2271b1; }
        ');
    }

    /**
     * Renderizar página de administración
     */
    public function render_admin_page() {
        $stats = $this->get_product_stats();
        ?>
        <div class="wrap akibara-seo-wrap">
            <h1>Akibara SEO Booster v2.4</h1>
            <p>Optimiza tus productos para alcanzar 100/100 en Rank Math SEO</p>

            <!-- Alerta si hay secciones obsoletas -->
            <?php if ($stats['legacy_sections'] > 0): ?>
            <div class="akibara-alert akibara-alert-warning">
                <strong>Atención:</strong> Se encontraron <strong><?php echo $stats['legacy_sections']; ?></strong> productos con secciones obsoletas
                ("Sobre este manga", "Detalles del producto", etc.). Marca "Limpiar Secciones Obsoletas" y ejecuta la optimización.
            </div>
            <?php endif; ?>

            <!-- Alerta si hay problemas de texto contextual -->
            <?php if ($stats['contextual_issues'] > 0): ?>
            <div class="akibara-alert akibara-alert-warning">
                <strong>Atención:</strong> Se encontraron <strong><?php echo $stats['contextual_issues']; ?></strong> productos en preventa
                que mencionan "visita nuestras preventas" incorrectamente. Usa el botón "Corregir Texto Contextual" para arreglarlos.
            </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="akibara-card">
                <h2>Estadísticas de Productos</h2>
                <div class="akibara-stats">
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['total']); ?></div>
                        <div class="label">Total Productos</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['preorder']); ?></div>
                        <div class="label">En Preventa</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['available']); ?></div>
                        <div class="label">Disponibles</div>
                    </div>
                    <div class="akibara-stat-box <?php echo $stats['contextual_issues'] > 0 ? 'error' : 'success'; ?>">
                        <div class="number"><?php echo esc_html($stats['contextual_issues']); ?></div>
                        <div class="label">Texto Contextual Incorrecto</div>
                    </div>
                    <div class="akibara-stat-box <?php echo $stats['legacy_sections'] > 0 ? 'error' : 'success'; ?>">
                        <div class="number"><?php echo esc_html($stats['legacy_sections']); ?></div>
                        <div class="label">Secciones Obsoletas</div>
                    </div>
                    <div class="akibara-stat-box <?php echo $stats['orphan_metadata'] > 0 ? 'error' : 'success'; ?>">
                        <div class="number"><?php echo esc_html($stats['orphan_metadata']); ?></div>
                        <div class="label">Metadata Huérfana</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['without_external_links']); ?></div>
                        <div class="label">Sin Enlaces Externos</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['short_content']); ?></div>
                        <div class="label">Contenido Corto</div>
                    </div>
                </div>
            </div>

            <!-- Editoriales Configuradas -->
            <div class="akibara-card">
                <h2>Editoriales Configuradas</h2>
                <p>Enlaces externos que se agregarán según la editorial del producto:</p>
                <div class="akibara-editorial-grid">
                    <?php foreach ($this->editorial_links as $slug => $data): ?>
                        <div class="akibara-editorial-item">
                            <span><strong><?php echo esc_html($data['name']); ?></strong></span>
                            <a href="<?php echo esc_url($data['url']); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html(parse_url($data['url'], PHP_URL_HOST)); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Opciones de Optimización -->
            <div class="akibara-card">
                <h2>Opciones de Optimización</h2>
                <form id="akibara-seo-form" method="post">
                    <?php wp_nonce_field('akibara_seo_action', 'akibara_nonce'); ?>

                    <div class="akibara-options-grid">
                        <div>
                            <h3>Enlaces Externos</h3>
                            <label>
                                <input type="checkbox" name="add_external_links" value="1" checked>
                                Agregar enlaces externos según editorial (dofollow)
                            </label>
                            <p class="description">Agrega un enlace al sitio oficial de la editorial.</p>
                        </div>

                        <div>
                            <h3>Expandir Descripción</h3>
                            <label>
                                <input type="checkbox" name="expand_description" value="1" checked>
                                Expandir descripciones cortas (&lt;600 palabras)
                            </label>
                            <p class="description">Agrega contenido SEO-friendly contextual.</p>
                        </div>

                        <div>
                            <h3>Corregir Texto Contextual</h3>
                            <label>
                                <input type="checkbox" name="fix_contextual" value="1" checked>
                                Corregir texto según estado (preventa/disponible)
                            </label>
                            <p class="description">Arregla el texto "Explora más títulos..." para que sea coherente.</p>
                        </div>

                        <div>
                            <h3>Limpiar Secciones Obsoletas</h3>
                            <label>
                                <input type="checkbox" name="clean_legacy" value="1" checked>
                                Remover contenido obsoleto del formato antiguo
                            </label>
                            <p class="description">Elimina "Sobre este manga (Preventa)", "Detalles del producto", "Editorial:", etc.</p>
                        </div>

                        <div>
                            <h3>Limpiar Metadata Huérfana</h3>
                            <label>
                                <input type="checkbox" name="clean_orphan_meta" value="1" checked>
                                Eliminar registros de base de datos sin contenido asociado
                            </label>
                            <p class="description">Limpia metadata de productos donde el contenido fue eliminado manualmente.</p>
                        </div>

                        <div>
                            <h3>Filtros</h3>
                            <label>Procesar solo productos de:</label>
                            <select name="filter_brand">
                                <option value="">Todas las editoriales</option>
                                <?php
                                $brands = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => false]);
                                if (!is_wp_error($brands)) {
                                    foreach ($brands as $brand) {
                                        echo '<option value="' . esc_attr($brand->term_id) . '">' . esc_html($brand->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <br><br>
                            <label>Estado del producto:</label>
                            <select name="filter_status">
                                <option value="">Todos</option>
                                <option value="preorder">Solo Preventa</option>
                                <option value="available">Solo Disponibles</option>
                            </select>
                            <br><br>
                            <label>Límite de productos:</label>
                            <input type="number" name="limit" value="500" min="1" max="10000">
                        </div>
                    </div>

                    <hr>

                    <p>
                        <button type="submit" name="action" value="preview" class="akibara-btn akibara-btn-secondary">
                            Vista Previa
                        </button>
                        <button type="submit" name="action" value="process" class="akibara-btn akibara-btn-primary">
                            Ejecutar Optimización
                        </button>
                    </p>
                </form>
            </div>

            <!-- Resultados/Log -->
            <div class="akibara-card" id="akibara-results" style="display: none;">
                <h2>Resultados</h2>
                <div class="akibara-progress">
                    <div class="akibara-progress-bar" id="akibara-progress" style="width: 0%"></div>
                </div>
                <div id="akibara-progress-text">Procesando...</div>
                <div class="akibara-log" id="akibara-log"></div>
            </div>

            <!-- Guía -->
            <div class="akibara-card">
                <h2>Guía para llegar a 100/100 en Rank Math</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Error Rank Math</th>
                            <th>Solución del Plugin</th>
                            <th>Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>No se han encontrado enlaces externos</td>
                            <td>Agrega enlace dofollow a editorial oficial</td>
                            <td>+5</td>
                        </tr>
                        <tr>
                            <td>0 enlaces salientes dofollow</td>
                            <td>El enlace a editorial es dofollow</td>
                            <td>+5</td>
                        </tr>
                        <tr>
                            <td>Contenido menor a 600 palabras</td>
                            <td>Expande descripción con plantillas SEO contextuales</td>
                            <td>+10</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <div class="akibara-alert akibara-alert-info">
                    <strong>Nota:</strong> Los power words (Comprar, Nuevo, Oferta) se manejan con badges visuales,
                    no se agregan al título SEO.
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#akibara-seo-form').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var action = $('button[type="submit"]:focus').val() || 'preview';

                $('#akibara-results').show();
                $('#akibara-log').html('');
                $('#akibara-progress').css('width', '0%');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData + '&action=akibara_process_products&process_action=' + action,
                    success: function(response) {
                        if (response.success) {
                            $('#akibara-progress').css('width', '100%');
                            $('#akibara-progress-text').text('¡Completado!');

                            response.data.log.forEach(function(item) {
                                var cssClass = item.type || 'info';
                                $('#akibara-log').append('<div class="' + cssClass + '">' + item.message + '</div>');
                            });

                            // Recargar la página después de ejecutar (no en preview) para actualizar estadísticas
                            if (action === 'execute') {
                                $('#akibara-log').append('<div class="info">Recargando página para actualizar estadísticas...</div>');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            $('#akibara-log').append('<div class="error">Error: ' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#akibara-log').append('<div class="error">Error de conexión</div>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * =====================================================
     * ESTADÍSTICAS
     * =====================================================
     */

    public function get_product_stats() {
        global $wpdb;

        $stats = [
            'total' => 0,
            'preorder' => 0,
            'available' => 0,
            'contextual_issues' => 0,
            'legacy_sections' => 0,
            'orphan_metadata' => 0,
            'without_external_links' => 0,
            'short_content' => 0
        ];

        // Obtener todos los productos publicados
        $product_ids = $wpdb->get_col("
            SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'product' AND post_status = 'publish'
        ");

        $stats['total'] = count($product_ids);

        foreach ($product_ids as $product_id) {
            // Contar preventa vs disponible
            if ($this->is_product_preorder($product_id)) {
                $stats['preorder']++;
            } else {
                $stats['available']++;
            }

            // Contar problemas de texto contextual
            if ($this->has_contextual_text_issue($product_id)) {
                $stats['contextual_issues']++;
            }

            // Contar productos con secciones obsoletas
            if ($this->has_legacy_sections($product_id)) {
                $stats['legacy_sections']++;
            }

            // Contar productos con metadata huérfana
            if ($this->has_orphan_metadata($product_id)) {
                $stats['orphan_metadata']++;
            }

            // Sin enlaces externos
            if (!get_post_meta($product_id, '_akibara_external_link_added', true)) {
                $stats['without_external_links']++;
            }

            // Contenido corto
            $post = get_post($product_id);
            $word_count = str_word_count(strip_tags($post->post_content));
            if ($word_count < 600) {
                $stats['short_content']++;
            }
        }

        return $stats;
    }

    /**
     * =====================================================
     * PROCESAMIENTO AJAX
     * =====================================================
     */

    public function ajax_process_products() {
        check_ajax_referer('akibara_seo_action', 'akibara_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permisos insuficientes']);
        }

        $is_preview = ($_POST['process_action'] ?? 'preview') === 'preview';
        $add_external_links = isset($_POST['add_external_links']);
        $expand_description = isset($_POST['expand_description']);
        $fix_contextual = isset($_POST['fix_contextual']);
        $clean_legacy = isset($_POST['clean_legacy']);
        $clean_orphan_meta = isset($_POST['clean_orphan_meta']);
        $filter_brand = intval($_POST['filter_brand'] ?? 0);
        $filter_status = sanitize_text_field($_POST['filter_status'] ?? '');
        $limit = min(10000, max(1, intval($_POST['limit'] ?? 500)));

        $log = [];
        $processed = 0;

        // Query de productos
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids'
        ];

        if ($filter_brand) {
            $args['tax_query'] = [[
                'taxonomy' => 'product_brand',
                'field' => 'term_id',
                'terms' => $filter_brand
            ]];
        }

        $product_ids = get_posts($args);

        // Filtrar por estado si es necesario
        if ($filter_status === 'preorder') {
            $product_ids = array_filter($product_ids, [$this, 'is_product_preorder']);
        } elseif ($filter_status === 'available') {
            $product_ids = array_filter($product_ids, function($id) {
                return !$this->is_product_preorder($id);
            });
        }

        $product_ids = array_values($product_ids);
        $total = count($product_ids);

        $log[] = ['type' => 'info', 'message' => ($is_preview ? 'MODO VISTA PREVIA' : 'EJECUTANDO OPTIMIZACIÓN')];
        $log[] = ['type' => 'info', 'message' => "Procesando {$total} productos..."];

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;

            $product_name = $product->get_name();
            $status = $this->get_product_status($product_id);
            $changes = [];

            // 1. Limpiar secciones obsoletas (debe ejecutarse primero)
            if ($clean_legacy) {
                $result = $this->clean_legacy_sections($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 2. Corregir texto contextual
            if ($fix_contextual) {
                $result = $this->fix_contextual_text($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 3. Agregar enlace externo
            if ($add_external_links) {
                $result = $this->add_external_link_to_product($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 4. Expandir descripción
            if ($expand_description) {
                $result = $this->expand_product_description($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 5. Limpiar metadata huérfana
            if ($clean_orphan_meta) {
                $result = $this->clean_orphan_metadata($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            if (!empty($changes)) {
                $status_label = $status === 'preventa' ? '[PREVENTA]' : '[DISPONIBLE]';
                $log[] = [
                    'type' => 'success',
                    'message' => "{$status_label} [{$product_id}] {$product_name}: " . implode(', ', $changes)
                ];
                $processed++;
            }
        }

        $log[] = ['type' => 'info', 'message' => "---"];
        $log[] = ['type' => 'success', 'message' => "Resumen: {$processed}/{$total} productos " . ($is_preview ? 'serían modificados' : 'modificados')];

        if ($is_preview) {
            $log[] = ['type' => 'info', 'message' => "Ejecuta 'Optimización' para aplicar los cambios"];
        }

        wp_send_json_success(['log' => $log, 'processed' => $processed, 'total' => $total]);
    }

    /**
     * =====================================================
     * FUNCIONES DE OPTIMIZACIÓN
     * =====================================================
     */

    /**
     * Agregar enlace externo basado en la editorial
     */
    public function add_external_link_to_product($product_id, $preview = false) {
        // Verificar si ya tiene enlace agregado
        if (get_post_meta($product_id, '_akibara_external_link_added', true)) {
            return null;
        }

        // Obtener la editorial del producto
        $brands = wp_get_post_terms($product_id, 'product_brand', ['fields' => 'all']);
        if (empty($brands) || is_wp_error($brands)) {
            return null;
        }

        $brand = $brands[0];
        $brand_slug = $this->normalize_slug($brand->slug);

        // Buscar en nuestro mapeo
        if (!isset($this->editorial_links[$brand_slug])) {
            // Intentar búsqueda parcial
            $found = false;
            foreach ($this->editorial_links as $key => $data) {
                if (stripos($brand->name, str_replace('-', ' ', $key)) !== false ||
                    stripos(str_replace('-', ' ', $key), $brand->name) !== false) {
                    $brand_slug = $key;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return null;
            }
        }

        $link_data = $this->editorial_links[$brand_slug];

        if (!$preview) {
            // Crear el HTML del enlace
            $link_html = sprintf(
                "\n\n<p class=\"editorial-link\">Conoce más sobre esta editorial: <a href=\"%s\" target=\"_blank\" rel=\"noopener\">%s</a></p>",
                esc_url($link_data['url']),
                esc_html($link_data['anchor_text'])
            );

            // Obtener contenido actual
            $post = get_post($product_id);
            $content = $post->post_content;

            // Agregar enlace al final del contenido
            $new_content = $content . $link_html;

            // Actualizar producto
            wp_update_post([
                'ID' => $product_id,
                'post_content' => $new_content
            ]);

            // Marcar como procesado
            update_post_meta($product_id, '_akibara_external_link_added', [
                'url' => $link_data['url'],
                'brand' => $brand->name,
                'date' => current_time('mysql')
            ]);
        }

        return "Enlace externo: {$link_data['name']}";
    }

    /**
     * Expandir descripción del producto con contenido contextual
     */
    public function expand_product_description($product_id, $preview = false) {
        $post = get_post($product_id);
        $content = $post->post_content;
        $word_count = str_word_count(strip_tags($content));

        // Solo expandir si tiene menos de 600 palabras
        if ($word_count >= 600) {
            return null;
        }

        // Ya fue expandido?
        if (get_post_meta($product_id, '_akibara_description_expanded', true)) {
            return null;
        }

        // Detectar género del producto para usar plantilla correcta
        $product_type_info = $this->get_product_type($product_id);
        $genre = $product_type_info['genre'];

        // Usar plantilla según género/tipo
        $template = $this->description_templates[$genre] ?? $this->description_templates['default'];
        $product_name = get_the_title($product_id);

        // Construir contenido adicional
        $additional_content = "\n\n<!-- SEO Content Added by Akibara SEO Booster v2.4 -->\n";
        $additional_content .= "<div class=\"seo-description\">\n";

        // Contenido SEO sin encabezados ni detalles del producto
        $additional_content .= "<p>" . str_replace('{title}', $product_name, $template['intro']) . "</p>\n";
        $additional_content .= "<p>" . $template['content'] . "</p>\n";

        $additional_content .= "</div>\n";

        if (!$preview) {
            // Primero, remover cualquier texto contextual viejo
            $content = preg_replace(
                '/<p[^>]*class=["\']?akibara-contextual-footer["\']?[^>]*>.*?<\/p>/is',
                '',
                $content
            );

            // También remover el texto viejo sin clase
            $content = preg_replace(
                '/<p>Explora más títulos en nuestra.*?(?:preventas|manga)<\/a>\.<\/p>/is',
                '',
                $content
            );

            $new_content = $content . $additional_content;

            wp_update_post([
                'ID' => $product_id,
                'post_content' => $new_content
            ]);

            update_post_meta($product_id, '_akibara_description_expanded', [
                'original_words' => $word_count,
                'date' => current_time('mysql'),
                'status' => $this->get_product_status($product_id)
            ]);
        }

        $new_word_count = str_word_count(strip_tags($content . $additional_content));
        return "Descripción expandida: {$word_count} → ~{$new_word_count} palabras";
    }

    /**
     * Normalizar slug de editorial y resolver aliases
     *
     * @param string $slug Slug original
     * @return string Slug normalizado y resuelto
     */
    private function normalize_slug($slug) {
        $slug = strtolower($slug);
        $slug = str_replace(['_', ' '], '-', $slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

        // Resolver alias si existe
        if (isset($this->slug_aliases[$slug])) {
            return $this->slug_aliases[$slug];
        }

        // Buscar match parcial en aliases
        foreach ($this->slug_aliases as $alias => $canonical) {
            if (strpos($slug, $alias) !== false || strpos($alias, $slug) !== false) {
                return $canonical;
            }
        }

        return $slug;
    }

    /**
     * Hook: Mostrar enlace de editorial en frontend
     */
    public function display_external_editorial_link() {
        global $product;

        if (!$product) {
            return;
        }

        $brands = wp_get_post_terms($product->get_id(), 'product_brand', ['fields' => 'all']);
        if (empty($brands) || is_wp_error($brands)) {
            return;
        }

        $brand = $brands[0];
        $brand_slug = $this->normalize_slug($brand->slug);

        $link_data = null;
        if (isset($this->editorial_links[$brand_slug])) {
            $link_data = $this->editorial_links[$brand_slug];
        } else {
            foreach ($this->editorial_links as $key => $data) {
                if (stripos($brand->name, str_replace('-', ' ', $key)) !== false) {
                    $link_data = $data;
                    break;
                }
            }
        }

        if ($link_data) {
            echo '<div class="editorial-info" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">';
            echo '<p style="margin: 0;"><strong>Editorial:</strong> ';
            echo '<a href="' . esc_url($link_data['url']) . '" target="_blank" rel="noopener">' . esc_html($link_data['name']) . '</a>';
            echo '</p></div>';
        }
    }

    public function maybe_enhance_product_on_save($product_id) {
        if (!get_option('akibara_seo_auto_enhance', false)) {
            return;
        }

        if (!get_post_meta($product_id, '_akibara_external_link_added', true)) {
            $this->add_external_link_to_product($product_id);
        }
    }

    public function handle_admin_actions() {
        // Placeholder
    }
}

// Inicializar plugin
function akibara_seo_booster_init() {
    if (class_exists('WooCommerce')) {
        new Akibara_SEO_Booster();
    }
}
add_action('plugins_loaded', 'akibara_seo_booster_init');

// Activación
register_activation_hook(__FILE__, function() {
    add_option('akibara_seo_auto_enhance', false);
    add_option('akibara_seo_version', '2.2.0');
});

// Desactivación
register_deactivation_hook(__FILE__, function() {
    // Limpieza si es necesario
});
