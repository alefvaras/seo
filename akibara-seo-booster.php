<?php
/**
 * Plugin Name: Akibara SEO Booster
 * Plugin URI: https://akibara.cl
 * Description: Plugin para mejorar el SEO de productos a 100 en Rank Math. Agrega enlaces externos por editorial, power words, y optimiza descripciones.
 * Version: 1.0.0
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
 */

if (!defined('ABSPATH')) {
    exit;
}

class Akibara_SEO_Booster {

    /**
     * Mapeo de editoriales a sus sitios web oficiales
     * Formato: 'nombre_editorial' => [
     *     'url' => 'sitio_oficial',
     *     'name' => 'Nombre para mostrar',
     *     'anchor_text' => 'Texto del enlace'
     * ]
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
     * Power words para SEO en español
     * Organizadas por categoría
     */
    private $power_words = [
        'urgencia' => ['Nuevo', 'Exclusivo', 'Últimas Unidades', 'Novedad', 'Ahora'],
        'valor' => ['Comprar', 'Oferta', 'Mejor Precio', 'Original', 'Edición'],
        'calidad' => ['Premium', 'Oficial', 'Auténtico', 'Colección', 'Especial'],
        'accion' => ['Descubre', 'Consigue', 'Hazte con', 'Llévate', 'Adquiere']
    ];

    /**
     * Plantillas de descripción extendida por género
     */
    private $description_templates = [
        'shonen' => [
            'intro' => 'Sumérgete en una emocionante aventura con {title}, un manga shonen que te mantendrá al borde de tu asiento.',
            'content' => 'Esta obra maestra del manga combina acción trepidante, personajes memorables y una historia que no podrás dejar de leer. Ideal para fans del género shonen que buscan una experiencia de lectura intensa y emocionante.',
            'features' => 'Este tomo incluye ilustraciones de alta calidad, traducción profesional al español y papel de primera calidad para una experiencia de lectura óptima.',
        ],
        'seinen' => [
            'intro' => 'Descubre {title}, una obra madura y profunda que explora temas complejos con una narrativa excepcional.',
            'content' => 'Este manga seinen ofrece una experiencia de lectura sofisticada, con tramas elaboradas y desarrollo de personajes que te harán reflexionar. Perfecto para lectores adultos que buscan historias con profundidad.',
            'features' => 'Edición de coleccionista con papel de alta calidad, traducción cuidada y extras exclusivos.',
        ],
        'shojo' => [
            'intro' => '{title} es una historia cautivadora que te enamorará desde la primera página.',
            'content' => 'Este manga shojo combina romance, drama y personajes entrañables en una historia que toca el corazón. Una lectura perfecta para quienes buscan emociones y momentos inolvidables.',
            'features' => 'Edición especial con ilustraciones a color, papel premium y traducción oficial.',
        ],
        'default' => [
            'intro' => 'Descubre {title}, una obra imprescindible para cualquier amante del manga.',
            'content' => 'Esta edición oficial trae una de las mejores historias del género, con una narrativa cautivadora y un arte visual impresionante que te transportará a un mundo único.',
            'features' => 'Incluye traducción profesional al español, papel de alta calidad y encuadernación resistente para tu colección.',
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

        // Hooks de frontend para mostrar enlaces externos
        add_filter('woocommerce_product_description', [$this, 'append_external_link_to_description'], 10, 1);
        add_action('woocommerce_after_single_product_summary', [$this, 'display_external_editorial_link'], 15);

        // Hook para modificar el contenido del producto al guardar
        add_action('woocommerce_process_product_meta', [$this, 'maybe_enhance_product_on_save'], 50, 1);

        // AJAX handlers
        add_action('wp_ajax_akibara_process_products', [$this, 'ajax_process_products']);
        add_action('wp_ajax_akibara_get_product_stats', [$this, 'ajax_get_product_stats']);
    }

    /**
     * Agregar menú de administración
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

    /**
     * Estilos de administración
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'tools_page_akibara-seo-booster') {
            return;
        }

        wp_add_inline_style('wp-admin', '
            .akibara-seo-wrap { max-width: 1200px; margin: 20px auto; }
            .akibara-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
            .akibara-card h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .akibara-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
            .akibara-stat-box { background: #f8f9fa; padding: 15px; border-radius: 4px; text-align: center; }
            .akibara-stat-box .number { font-size: 32px; font-weight: bold; color: #2271b1; }
            .akibara-stat-box .label { color: #666; font-size: 14px; }
            .akibara-progress { height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden; margin: 10px 0; }
            .akibara-progress-bar { height: 100%; background: linear-gradient(90deg, #2271b1, #135e96); transition: width 0.3s; }
            .akibara-log { background: #1d2327; color: #fff; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; }
            .akibara-log .success { color: #00d084; }
            .akibara-log .error { color: #ff6b6b; }
            .akibara-log .info { color: #72aee6; }
            .akibara-btn { padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; }
            .akibara-btn-primary { background: #2271b1; color: #fff; border: none; }
            .akibara-btn-primary:hover { background: #135e96; }
            .akibara-btn-secondary { background: #f0f0f1; color: #1d2327; border: 1px solid #c3c4c7; }
            .akibara-editorial-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; }
            .akibara-editorial-item { padding: 10px; background: #f8f9fa; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
            .akibara-options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 768px) { .akibara-options-grid { grid-template-columns: 1fr; } }
        ');
    }

    /**
     * Renderizar página de administración
     */
    public function render_admin_page() {
        $stats = $this->get_product_stats();
        ?>
        <div class="wrap akibara-seo-wrap">
            <h1>🚀 Akibara SEO Booster</h1>
            <p>Optimiza tus productos para alcanzar 100/100 en Rank Math SEO</p>

            <!-- Estadísticas -->
            <div class="akibara-card">
                <h2>📊 Estadísticas de Productos</h2>
                <div class="akibara-stats">
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['total']); ?></div>
                        <div class="label">Total Productos</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['without_external_links']); ?></div>
                        <div class="label">Sin Enlaces Externos</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['short_content']); ?></div>
                        <div class="label">Contenido Corto (&lt;600 palabras)</div>
                    </div>
                    <div class="akibara-stat-box">
                        <div class="number"><?php echo esc_html($stats['without_power_words']); ?></div>
                        <div class="label">Sin Power Words</div>
                    </div>
                </div>
            </div>

            <!-- Editoriales Configuradas -->
            <div class="akibara-card">
                <h2>🏢 Editoriales Configuradas</h2>
                <p>Estos son los enlaces externos que se agregarán según la editorial del producto:</p>
                <div class="akibara-editorial-grid">
                    <?php foreach ($this->editorial_links as $slug => $data): ?>
                        <div class="akibara-editorial-item">
                            <span><strong><?php echo esc_html($data['name']); ?></strong></span>
                            <a href="<?php echo esc_url($data['url']); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html(parse_url($data['url'], PHP_URL_HOST)); ?> ↗
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Opciones de Optimización -->
            <div class="akibara-card">
                <h2>⚙️ Opciones de Optimización</h2>
                <form id="akibara-seo-form" method="post">
                    <?php wp_nonce_field('akibara_seo_action', 'akibara_nonce'); ?>

                    <div class="akibara-options-grid">
                        <div>
                            <h3>Enlaces Externos</h3>
                            <label>
                                <input type="checkbox" name="add_external_links" value="1" checked>
                                Agregar enlaces externos según editorial (dofollow)
                            </label>
                            <p class="description">Agrega un enlace al sitio oficial de la editorial en la descripción del producto.</p>
                        </div>

                        <div>
                            <h3>Power Words en Título</h3>
                            <label>
                                <input type="checkbox" name="add_power_words" value="1" checked>
                                Agregar power words al título SEO
                            </label>
                            <p class="description">Agrega palabras como "Comprar", "Nuevo", "Oferta" al título SEO.</p>
                            <select name="power_word_type">
                                <option value="auto">Automático (según stock)</option>
                                <option value="comprar">Comprar [Título]</option>
                                <option value="nuevo">Nuevo: [Título]</option>
                                <option value="oferta">[Título] - Oferta</option>
                            </select>
                        </div>

                        <div>
                            <h3>Expandir Descripción</h3>
                            <label>
                                <input type="checkbox" name="expand_description" value="1" checked>
                                Expandir descripciones cortas
                            </label>
                            <p class="description">Agrega contenido SEO-friendly a productos con menos de 600 palabras.</p>
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
                            <label>Límite de productos:</label>
                            <input type="number" name="limit" value="50" min="1" max="500">
                        </div>
                    </div>

                    <hr>

                    <p>
                        <button type="submit" name="action" value="preview" class="akibara-btn akibara-btn-secondary">
                            👁️ Vista Previa
                        </button>
                        <button type="submit" name="action" value="process" class="akibara-btn akibara-btn-primary">
                            🚀 Ejecutar Optimización
                        </button>
                    </p>
                </form>
            </div>

            <!-- Resultados/Log -->
            <div class="akibara-card" id="akibara-results" style="display: none;">
                <h2>📝 Resultados</h2>
                <div class="akibara-progress">
                    <div class="akibara-progress-bar" id="akibara-progress" style="width: 0%"></div>
                </div>
                <div id="akibara-progress-text">Procesando...</div>
                <div class="akibara-log" id="akibara-log"></div>
            </div>

            <!-- Guía de Uso -->
            <div class="akibara-card">
                <h2>📖 Guía para llegar a 100/100 en Rank Math</h2>
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
                            <td>❌ No se han encontrado enlaces externos</td>
                            <td>✅ Agrega enlace dofollow a editorial oficial</td>
                            <td>+5</td>
                        </tr>
                        <tr>
                            <td>❌ 0 enlaces salientes dofollow</td>
                            <td>✅ El enlace a editorial es dofollow</td>
                            <td>+5</td>
                        </tr>
                        <tr>
                            <td>❌ Título sin power word</td>
                            <td>✅ Agrega "Comprar", "Nuevo", etc.</td>
                            <td>+5</td>
                        </tr>
                        <tr>
                            <td>❌ Contenido menor a 600 palabras</td>
                            <td>✅ Expande descripción con plantillas SEO</td>
                            <td>+10</td>
                        </tr>
                    </tbody>
                </table>
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
     * Obtener estadísticas de productos
     */
    public function get_product_stats() {
        global $wpdb;

        $stats = [
            'total' => 0,
            'without_external_links' => 0,
            'short_content' => 0,
            'without_power_words' => 0
        ];

        // Total de productos
        $stats['total'] = (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'product' AND post_status = 'publish'
        ");

        // Productos sin enlaces externos (aproximado - verifica si tiene el meta)
        $stats['without_external_links'] = (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} p
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm
                WHERE pm.post_id = p.ID
                AND pm.meta_key = '_akibara_external_link_added'
            )
        ");

        // Productos con contenido corto
        $products = $wpdb->get_results("
            SELECT ID, post_content FROM {$wpdb->posts}
            WHERE post_type = 'product' AND post_status = 'publish'
        ");

        foreach ($products as $product) {
            $word_count = str_word_count(strip_tags($product->post_content));
            if ($word_count < 600) {
                $stats['short_content']++;
            }
        }

        // Productos sin power words en título SEO
        $power_words_pattern = implode('|', array_merge(...array_values($this->power_words)));
        $stats['without_power_words'] = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_title'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND (pm.meta_value IS NULL OR pm.meta_value NOT REGEXP %s)
        ", $power_words_pattern));

        return $stats;
    }

    /**
     * AJAX: Procesar productos
     */
    public function ajax_process_products() {
        check_ajax_referer('akibara_seo_action', 'akibara_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permisos insuficientes']);
        }

        $is_preview = ($_POST['process_action'] ?? 'preview') === 'preview';
        $add_external_links = isset($_POST['add_external_links']);
        $add_power_words = isset($_POST['add_power_words']);
        $expand_description = isset($_POST['expand_description']);
        $power_word_type = sanitize_text_field($_POST['power_word_type'] ?? 'auto');
        $filter_brand = intval($_POST['filter_brand'] ?? 0);
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
        $total = count($product_ids);

        $log[] = ['type' => 'info', 'message' => ($is_preview ? '🔍 MODO VISTA PREVIA' : '🚀 EJECUTANDO OPTIMIZACIÓN')];
        $log[] = ['type' => 'info', 'message' => "Procesando {$total} productos..."];

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;

            $product_name = $product->get_name();
            $changes = [];

            // 1. Agregar enlace externo
            if ($add_external_links) {
                $result = $this->add_external_link_to_product($product_id, $is_preview);
                if ($result) {
                    $changes[] = $result;
                }
            }

            // 2. Agregar power word al título SEO
            if ($add_power_words) {
                $result = $this->add_power_word_to_title($product_id, $power_word_type, $is_preview);
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
                $log[] = [
                    'type' => 'success',
                    'message' => "✅ [{$product_id}] {$product_name}: " . implode(', ', $changes)
                ];
                $processed++;
            }
        }

        $log[] = ['type' => 'info', 'message' => "---"];
        $log[] = ['type' => 'success', 'message' => "📊 Resumen: {$processed}/{$total} productos " . ($is_preview ? 'serían modificados' : 'modificados')];

        if ($is_preview) {
            $log[] = ['type' => 'info', 'message' => "💡 Ejecuta 'Optimización' para aplicar los cambios"];
        }

        wp_send_json_success(['log' => $log, 'processed' => $processed, 'total' => $total]);
    }

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
                "\n\n<p class=\"editorial-link\">📚 Conoce más sobre esta editorial: <a href=\"%s\" target=\"_blank\" rel=\"noopener\">%s</a></p>",
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

        return "Enlace externo → {$link_data['name']}";
    }

    /**
     * Agregar power word al título SEO
     */
    public function add_power_word_to_title($product_id, $type = 'auto', $preview = false) {
        $current_title = get_post_meta($product_id, 'rank_math_title', true);
        $product = wc_get_product($product_id);

        if (!$product) return null;

        // Si no hay título SEO, usar el nombre del producto
        if (empty($current_title)) {
            $current_title = $product->get_name();
        }

        // Verificar si ya tiene power word
        $all_power_words = array_merge(...array_values($this->power_words));
        foreach ($all_power_words as $pw) {
            if (stripos($current_title, $pw) !== false) {
                return null; // Ya tiene power word
            }
        }

        // Seleccionar power word según tipo
        $power_word = 'Comprar';
        $new_title = '';

        switch ($type) {
            case 'comprar':
                $new_title = "Comprar {$current_title}";
                $power_word = 'Comprar';
                break;
            case 'nuevo':
                $new_title = "Nuevo: {$current_title}";
                $power_word = 'Nuevo';
                break;
            case 'oferta':
                $new_title = "{$current_title} - Oferta";
                $power_word = 'Oferta';
                break;
            case 'auto':
            default:
                // Lógica automática basada en el producto
                if ($product->is_on_sale()) {
                    $new_title = "{$current_title} - Oferta";
                    $power_word = 'Oferta';
                } elseif ($product->get_stock_quantity() !== null && $product->get_stock_quantity() < 5) {
                    $new_title = "{$current_title} - Últimas Unidades";
                    $power_word = 'Últimas Unidades';
                } else {
                    $new_title = "Comprar {$current_title}";
                    $power_word = 'Comprar';
                }
                break;
        }

        if (!$preview) {
            update_post_meta($product_id, 'rank_math_title', $new_title);
        }

        return "Power word: '{$power_word}'";
    }

    /**
     * Expandir descripción del producto
     */
    public function expand_product_description($product_id, $preview = false) {
        $post = get_post($product_id);
        $content = $post->post_content;
        $word_count = str_word_count(strip_tags($content));

        // Solo expandir si tiene menos de 600 palabras
        if ($word_count >= 600) {
            return null;
        }

        // Determinar el género/categoría del producto
        $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
        $genre = 'default';

        if (!is_wp_error($categories)) {
            foreach (['shonen', 'seinen', 'shojo', 'josei', 'kodomo'] as $g) {
                if (in_array($g, $categories)) {
                    $genre = $g;
                    break;
                }
            }
        }

        // Usar plantilla según género
        $template = $this->description_templates[$genre] ?? $this->description_templates['default'];
        $product_name = get_the_title($product_id);

        // Construir contenido adicional
        $additional_content = "\n\n<!-- SEO Content Added by Akibara SEO Booster -->\n";
        $additional_content .= "<div class=\"seo-description\">\n";
        $additional_content .= "<h3>Sobre este manga</h3>\n";
        $additional_content .= "<p>" . str_replace('{title}', $product_name, $template['intro']) . "</p>\n";
        $additional_content .= "<p>" . $template['content'] . "</p>\n";
        $additional_content .= "<h3>Características de esta edición</h3>\n";
        $additional_content .= "<p>" . $template['features'] . "</p>\n";

        // Agregar información adicional basada en atributos del producto
        $product = wc_get_product($product_id);
        if ($product) {
            $additional_content .= "<h3>Detalles del producto</h3>\n";
            $additional_content .= "<ul>\n";

            // Obtener autor si existe
            $autor = $product->get_attribute('pa_autor');
            if ($autor) {
                $additional_content .= "<li><strong>Autor:</strong> {$autor}</li>\n";
            }

            // Editorial
            $brands = wp_get_post_terms($product_id, 'product_brand', ['fields' => 'names']);
            if (!empty($brands)) {
                $additional_content .= "<li><strong>Editorial:</strong> " . implode(', ', $brands) . "</li>\n";
            }

            // SKU
            $sku = $product->get_sku();
            if ($sku) {
                $additional_content .= "<li><strong>ISBN/SKU:</strong> {$sku}</li>\n";
            }

            $additional_content .= "</ul>\n";
        }

        $additional_content .= "</div>\n";

        if (!$preview) {
            $new_content = $content . $additional_content;
            wp_update_post([
                'ID' => $product_id,
                'post_content' => $new_content
            ]);

            update_post_meta($product_id, '_akibara_description_expanded', [
                'original_words' => $word_count,
                'date' => current_time('mysql')
            ]);
        }

        $new_word_count = str_word_count(strip_tags($content . $additional_content));
        return "Descripción: {$word_count} → ~{$new_word_count} palabras";
    }

    /**
     * Normalizar slug de editorial
     */
    private function normalize_slug($slug) {
        // Convertir variaciones comunes
        $slug = strtolower($slug);
        $slug = str_replace(['_', ' '], '-', $slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        return $slug;
    }

    /**
     * Hook: Mostrar enlace externo en descripción del producto (frontend)
     */
    public function append_external_link_to_description($description) {
        global $product;

        if (!$product || !is_product()) {
            return $description;
        }

        $link_data = get_post_meta($product->get_id(), '_akibara_external_link_added', true);

        if (!empty($link_data) && isset($link_data['url'])) {
            // El enlace ya está en el contenido, no agregarlo de nuevo
            return $description;
        }

        return $description;
    }

    /**
     * Hook: Mostrar enlace de editorial después del resumen del producto
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

        // Buscar en mapeo
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
            echo '<p style="margin: 0;"><strong>📚 Editorial:</strong> ';
            echo '<a href="' . esc_url($link_data['url']) . '" target="_blank" rel="noopener">' . esc_html($link_data['name']) . '</a>';
            echo '</p></div>';
        }
    }

    /**
     * Hook: Mejorar producto automáticamente al guardar (opcional)
     */
    public function maybe_enhance_product_on_save($product_id) {
        // Verificar si el auto-enhance está habilitado
        if (!get_option('akibara_seo_auto_enhance', false)) {
            return;
        }

        // Agregar enlace externo si no tiene
        if (!get_post_meta($product_id, '_akibara_external_link_added', true)) {
            $this->add_external_link_to_product($product_id);
        }
    }

    /**
     * Manejar acciones de administración
     */
    public function handle_admin_actions() {
        // Placeholder para acciones futuras
    }
}

// Inicializar plugin
function akibara_seo_booster_init() {
    if (class_exists('WooCommerce')) {
        new Akibara_SEO_Booster();
    }
}
add_action('plugins_loaded', 'akibara_seo_booster_init');

// Activación del plugin
register_activation_hook(__FILE__, function() {
    // Crear opciones por defecto
    add_option('akibara_seo_auto_enhance', false);
    add_option('akibara_seo_version', '1.0.0');
});

// Desactivación del plugin
register_deactivation_hook(__FILE__, function() {
    // Limpiar opciones si es necesario
});
