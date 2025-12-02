<?php
/**
 * Plugin Name: Akibara SEO Booster
 * Plugin URI: https://akibara.cl
 * Description: Plugin para mejorar el SEO de productos a 100 en Rank Math. Agrega enlaces externos por editorial y optimiza descripciones con contenido contextual.
 * Version: 2.2.0
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
        'ivrea-arg' => 'ivrea-argentina',
        'panini-esp' => 'panini-espana',
        'ivrea' => 'ivrea-espana',
        'panini' => 'panini-espana',
        'arechi' => 'arechi-manga',
        'planeta' => 'planeta-espana',
        'norma' => 'norma-editorial',
        'milky' => 'milky-way',
        'milkyway' => 'milky-way',
        'ecc' => 'ecc-ediciones',
        'distrito' => 'distrito-manga',
        'babylon' => 'ediciones-babylon',
        'ooso' => 'ooso-comics',
        'kitsune' => 'kitsune-books',
        'satori' => 'satori-ediciones',
    ];

    /**
     * Mapeo de editoriales a sus sitios web oficiales
     */
    private $editorial_links = [
        'ivrea-espana' => [
            'url' => 'https://ivrea.es',
            'name' => 'Ivrea España',
            'anchor_text' => 'Ivrea España (Editorial Oficial)'
        ],
        'ivrea-argentina' => [
            'url' => 'https://www.ivrea.com.ar',
            'name' => 'Ivrea Argentina',
            'anchor_text' => 'Ivrea Argentina (Editorial Oficial)'
        ],
        'panini-espana' => [
            'url' => 'https://www.panini.es/shp_esp_es/comics.html',
            'name' => 'Panini España',
            'anchor_text' => 'Panini España (Editorial Oficial)'
        ],
        'panini-argentina' => [
            'url' => 'https://www.paninicomics.com.ar',
            'name' => 'Panini Argentina',
            'anchor_text' => 'Panini Argentina (Editorial Oficial)'
        ],
        'arechi-manga' => [
            'url' => 'https://www.arechimanga.com',
            'name' => 'Arechi Manga',
            'anchor_text' => 'Arechi Manga (Editorial Oficial)'
        ],
        'planeta-espana' => [
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
            <h1>Akibara SEO Booster v2.2</h1>
            <p>Optimiza tus productos para alcanzar 100/100 en Rank Math SEO</p>

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
                            <input type="number" name="limit" value="50" min="1" max="500">
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
        $filter_brand = intval($_POST['filter_brand'] ?? 0);
        $filter_status = sanitize_text_field($_POST['filter_status'] ?? '');
        $limit = min(500, max(1, intval($_POST['limit'] ?? 50)));

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

            // 1. Corregir texto contextual
            if ($fix_contextual) {
                $result = $this->fix_contextual_text($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 2. Agregar enlace externo
            if ($add_external_links) {
                $result = $this->add_external_link_to_product($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 3. Expandir descripción
            if ($expand_description) {
                $result = $this->expand_product_description($product_id, $is_preview);
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
        $additional_content = "\n\n<!-- SEO Content Added by Akibara SEO Booster v2.2 -->\n";
        $additional_content .= "<div class=\"seo-description\">\n";

        // Contenido SEO sin encabezados ni detalles del producto
        $additional_content .= "<p>" . str_replace('{title}', $product_name, $template['intro']) . "</p>\n";
        $additional_content .= "<p>" . $template['content'] . "</p>\n";
        $additional_content .= "<h3>Características de esta edición</h3>\n";
        $additional_content .= "<p>" . $template['features'] . "</p>\n";

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
