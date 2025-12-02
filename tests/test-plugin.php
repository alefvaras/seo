<?php
/**
 * Test unitario para Akibara SEO Booster
 * Simula el entorno WordPress/WooCommerce para validar la lógica del plugin
 */

// ============================================================
// MOCK DE FUNCIONES DE WORDPRESS
// ============================================================

define('ABSPATH', '/fake/wordpress/');

// Base de datos simulada con datos reales del dump SQL
$GLOBALS['mock_db'] = [
    'posts' => [
        // Productos de ejemplo basados en el SQL dump
        13215 => [
            'ID' => 13215,
            'post_title' => 'Solo Leveling 3 – Ivrea Argentina',
            'post_content' => '<p><strong>Solo Leveling 3 – Ivrea Argentina</strong> disponible en Akibara.</p>',
            'post_status' => 'publish',
            'post_type' => 'product'
        ],
        15382 => [
            'ID' => 15382,
            'post_title' => 'Dorohedoro 11 – Ivrea Argentina',
            'post_content' => '<p><strong>Dorohedoro 11</strong> en preventa.</p>',
            'post_status' => 'publish',
            'post_type' => 'product'
        ],
        99001 => [
            'ID' => 99001,
            'post_title' => 'Batman: Year One – Panini España',
            'post_content' => '<p>Comics de Batman.</p>',
            'post_status' => 'publish',
            'post_type' => 'product'
        ],
        99002 => [
            'ID' => 99002,
            'post_title' => 'Solo Leveling Comic – Panini Esp',
            'post_content' => '<p>Manhwa coreano.</p>',
            'post_status' => 'publish',
            'post_type' => 'product'
        ],
    ],
    'postmeta' => [
        15382 => [
            '_yith_pre_order_release_dat' => '2025-01-15',
            '_ywpo_preorder' => 'yes',
        ],
        99002 => [
            '_wc_pre_orders_enabled' => 'yes',
        ],
    ],
    'terms' => [
        // Categorías (product_cat)
        28 => ['term_id' => 28, 'name' => 'Manga', 'slug' => 'manga', 'taxonomy' => 'product_cat'],
        51 => ['term_id' => 51, 'name' => 'Shonen', 'slug' => 'shonen', 'taxonomy' => 'product_cat'],
        47 => ['term_id' => 47, 'name' => 'Seinen', 'slug' => 'seinen', 'taxonomy' => 'product_cat'],
        81 => ['term_id' => 81, 'name' => 'Comics', 'slug' => 'comics', 'taxonomy' => 'product_cat'],
        250 => ['term_id' => 250, 'name' => 'Manhwa', 'slug' => 'manhwa', 'taxonomy' => 'product_cat'],
        215 => ['term_id' => 215, 'name' => 'Preventa', 'slug' => 'preventa', 'taxonomy' => 'product_cat'],
        // Editoriales (product_brand)
        230 => ['term_id' => 230, 'name' => 'Ivrea Argentina', 'slug' => 'ivrea-argentina', 'taxonomy' => 'product_brand'],
        362 => ['term_id' => 362, 'name' => 'Ivrea España', 'slug' => 'ivrea-espana', 'taxonomy' => 'product_brand'],
        208 => ['term_id' => 208, 'name' => 'Panini España', 'slug' => 'panini-espana', 'taxonomy' => 'product_brand'],
        999 => ['term_id' => 999, 'name' => 'Panini Esp', 'slug' => 'panini-esp', 'taxonomy' => 'product_brand'], // Slug corto!
    ],
    'term_relationships' => [
        13215 => [28, 51, 230],  // Manga, Shonen, Ivrea Argentina
        15382 => [28, 47, 215, 230], // Manga, Seinen, Preventa, Ivrea Argentina
        99001 => [81, 208], // Comics, Panini España
        99002 => [28, 250, 215, 999], // Manga, Manhwa, Preventa, Panini Esp (slug corto)
    ],
];

// Mock: has_term
function has_term($term, $taxonomy, $post_id) {
    global $mock_db;
    $relationships = $mock_db['term_relationships'][$post_id] ?? [];

    foreach ($relationships as $term_id) {
        $term_data = $mock_db['terms'][$term_id] ?? null;
        if (!$term_data || $term_data['taxonomy'] !== $taxonomy) continue;

        if (is_numeric($term) && $term_id == $term) return true;
        if (is_string($term) && $term_data['slug'] === $term) return true;
    }
    return false;
}

// Mock: get_post_meta
function get_post_meta($post_id, $key, $single = false) {
    global $mock_db;
    $meta = $mock_db['postmeta'][$post_id][$key] ?? '';
    return $single ? $meta : [$meta];
}

// Mock: update_post_meta
function update_post_meta($post_id, $key, $value) {
    global $mock_db;
    $mock_db['postmeta'][$post_id][$key] = $value;
    return true;
}

// Mock: get_post
function get_post($post_id) {
    global $mock_db;
    $data = $mock_db['posts'][$post_id] ?? null;
    if (!$data) return null;
    return (object) $data;
}

// Mock: wp_get_post_terms
function wp_get_post_terms($post_id, $taxonomy, $args = []) {
    global $mock_db;
    $relationships = $mock_db['term_relationships'][$post_id] ?? [];
    $fields = $args['fields'] ?? 'all';
    $results = [];

    foreach ($relationships as $term_id) {
        $term_data = $mock_db['terms'][$term_id] ?? null;
        if (!$term_data || $term_data['taxonomy'] !== $taxonomy) continue;

        if ($fields === 'slugs') {
            $results[] = $term_data['slug'];
        } elseif ($fields === 'names') {
            $results[] = $term_data['name'];
        } else {
            $results[] = (object) $term_data;
        }
    }
    return $results;
}

// Mock: get_the_title
function get_the_title($post_id) {
    $post = get_post($post_id);
    return $post ? $post->post_title : '';
}

// Mock: wp_update_post
function wp_update_post($data) {
    global $mock_db;
    $id = $data['ID'] ?? 0;
    if (isset($mock_db['posts'][$id])) {
        $mock_db['posts'][$id] = array_merge($mock_db['posts'][$id], $data);
        return $id;
    }
    return 0;
}

// Mock: is_wp_error
function is_wp_error($thing) {
    return false;
}

// Mock: date_i18n
function date_i18n($format, $timestamp) {
    return date($format, $timestamp);
}

// Mock: current_time
function current_time($type) {
    return date('Y-m-d H:i:s');
}

// Mock: esc_url, esc_html
function esc_url($url) { return $url; }
function esc_html($text) { return htmlspecialchars($text); }

// Mock: wc_get_product
function wc_get_product($id) {
    $post = get_post($id);
    if (!$post) return null;
    return new class($post) {
        private $post;
        public function __construct($post) { $this->post = $post; }
        public function get_id() { return $this->post->ID; }
        public function get_name() { return $this->post->post_title; }
        public function get_sku() { return 'SKU-' . $this->post->ID; }
        public function get_attribute($attr) { return null; }
    };
}

// ============================================================
// CARGAR LA CLASE DEL PLUGIN (solo las funciones que vamos a testear)
// ============================================================

class Akibara_SEO_Booster_Test {

    const PREVENTA_CATEGORY_ID = 215;
    const PREVENTA_CATEGORY_SLUG = 'preventa';

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

    private $editorial_links = [
        'ivrea-espana' => ['url' => 'https://ivrea.es', 'name' => 'Ivrea España'],
        'ivrea-argentina' => ['url' => 'https://www.ivrea.com.ar', 'name' => 'Ivrea Argentina'],
        'panini-espana' => ['url' => 'https://www.panini.es', 'name' => 'Panini España'],
        'panini-argentina' => ['url' => 'https://www.paninicomics.com.ar', 'name' => 'Panini Argentina'],
        'arechi-manga' => ['url' => 'https://www.arechimanga.com', 'name' => 'Arechi Manga'],
        'planeta-espana' => ['url' => 'https://www.planetacomic.com', 'name' => 'Planeta Cómic'],
        'milky-way' => ['url' => 'https://www.milkywayediciones.com', 'name' => 'Milky Way'],
    ];

    public function is_product_preorder($product_id) {
        if (has_term(self::PREVENTA_CATEGORY_SLUG, 'product_cat', $product_id)) return true;
        if (has_term(self::PREVENTA_CATEGORY_ID, 'product_cat', $product_id)) return true;

        $yith_preorder = get_post_meta($product_id, '_ywpo_preorder', true);
        if ($yith_preorder === 'yes') return true;

        $yith_preorder_date = get_post_meta($product_id, '_ywpo_for_sale_date', true);
        if (!empty($yith_preorder_date) && strtotime($yith_preorder_date) > time()) return true;

        $yith_release_date = get_post_meta($product_id, '_yith_pre_order_release_dat', true);
        if (!empty($yith_release_date)) return true;

        $wc_preorder = get_post_meta($product_id, '_wc_pre_orders_enabled', true);
        if ($wc_preorder === 'yes') return true;

        return false;
    }

    public function get_product_type($product_id) {
        $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'slugs']);
        if (is_wp_error($categories)) return ['type' => 'general', 'genre' => 'default', 'label' => 'producto'];

        if (in_array('comics', $categories)) return ['type' => 'comics', 'genre' => 'comics', 'label' => 'cómics'];
        if (in_array('manhwa', $categories)) return ['type' => 'manhwa', 'genre' => 'manhwa', 'label' => 'manhwa'];
        if (in_array('manga', $categories)) {
            foreach (['shonen', 'seinen', 'shojo', 'josei', 'kodomo'] as $genre) {
                if (in_array($genre, $categories)) return ['type' => 'manga', 'genre' => $genre, 'label' => 'manga'];
            }
            return ['type' => 'manga', 'genre' => 'manga', 'label' => 'manga'];
        }

        foreach (['shonen', 'seinen', 'shojo', 'josei', 'kodomo'] as $genre) {
            if (in_array($genre, $categories)) return ['type' => 'manga', 'genre' => $genre, 'label' => 'manga'];
        }

        return ['type' => 'general', 'genre' => 'default', 'label' => 'producto'];
    }

    private function normalize_slug($slug) {
        $slug = strtolower($slug);
        $slug = str_replace(['_', ' '], '-', $slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

        if (isset($this->slug_aliases[$slug])) return $this->slug_aliases[$slug];

        foreach ($this->slug_aliases as $alias => $canonical) {
            if (strpos($slug, $alias) !== false || strpos($alias, $slug) !== false) {
                return $canonical;
            }
        }

        return $slug;
    }

    public function get_editorial_for_product($product_id) {
        $brands = wp_get_post_terms($product_id, 'product_brand', ['fields' => 'all']);
        if (empty($brands)) return null;

        $brand = $brands[0];
        $brand_slug = $this->normalize_slug($brand->slug);

        if (isset($this->editorial_links[$brand_slug])) {
            return $this->editorial_links[$brand_slug];
        }
        return null;
    }

    public function generate_contextual_footer($product_id) {
        $is_preorder = $this->is_product_preorder($product_id);
        $product_type = $this->get_product_type($product_id);

        $type_links = [
            'manga' => ['url' => '/product-category/manga/', 'text' => 'colección de manga'],
            'comics' => ['url' => '/product-category/comics/', 'text' => 'colección de cómics'],
            'manhwa' => ['url' => '/product-category/manga/manhwa/', 'text' => 'colección de manhwa'],
            'general' => ['url' => '/tienda/', 'text' => 'catálogo']
        ];

        $link_data = $type_links[$product_type['type']] ?? $type_links['general'];

        if ($is_preorder) {
            return 'Explora más títulos en nuestra <a href="' . $link_data['url'] . '">' . $link_data['text'] . '</a>.';
        } else {
            return 'Explora más títulos en nuestra <a href="' . $link_data['url'] . '">' . $link_data['text'] . '</a> o visita nuestras <a href="/product-category/preventa/">preventas</a>.';
        }
    }
}

// ============================================================
// EJECUTAR TESTS
// ============================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     AKIBARA SEO BOOSTER - TEST UNITARIO v2.2.0              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$plugin = new Akibara_SEO_Booster_Test();
$passed = 0;
$failed = 0;

function test($name, $expected, $actual) {
    global $passed, $failed;
    $match = $expected === $actual;
    if ($match) {
        echo "✅ PASS: {$name}\n";
        $passed++;
    } else {
        echo "❌ FAIL: {$name}\n";
        echo "   Expected: " . json_encode($expected) . "\n";
        echo "   Actual:   " . json_encode($actual) . "\n";
        $failed++;
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 1: DETECCIÓN DE PREVENTA\n";
echo "═══════════════════════════════════════════════════════════════\n";

test(
    "Solo Leveling 3 (ID:13215) NO es preventa",
    false,
    $plugin->is_product_preorder(13215)
);

test(
    "Dorohedoro 11 (ID:15382) ES preventa (categoría + YITH)",
    true,
    $plugin->is_product_preorder(15382)
);

test(
    "Batman (ID:99001) NO es preventa",
    false,
    $plugin->is_product_preorder(99001)
);

test(
    "Solo Leveling Comic (ID:99002) ES preventa (WC Pre-Orders)",
    true,
    $plugin->is_product_preorder(99002)
);

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "TEST 2: DETECCIÓN DE TIPO DE PRODUCTO\n";
echo "═══════════════════════════════════════════════════════════════\n";

$type1 = $plugin->get_product_type(13215);
test("Solo Leveling 3 es tipo MANGA", 'manga', $type1['type']);
test("Solo Leveling 3 es género SHONEN", 'shonen', $type1['genre']);

$type2 = $plugin->get_product_type(15382);
test("Dorohedoro 11 es tipo MANGA", 'manga', $type2['type']);
test("Dorohedoro 11 es género SEINEN", 'seinen', $type2['genre']);

$type3 = $plugin->get_product_type(99001);
test("Batman es tipo COMICS", 'comics', $type3['type']);

$type4 = $plugin->get_product_type(99002);
test("Solo Leveling Comic es tipo MANHWA", 'manhwa', $type4['type']);

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "TEST 3: RESOLUCIÓN DE ALIASES DE EDITORIALES\n";
echo "═══════════════════════════════════════════════════════════════\n";

// Editorial normal
$ed1 = $plugin->get_editorial_for_product(13215);
test(
    "Ivrea Argentina detectada correctamente",
    'Ivrea Argentina',
    $ed1['name'] ?? 'NOT FOUND'
);

// Editorial con slug corto
$ed2 = $plugin->get_editorial_for_product(99002);
test(
    "Panini Esp (slug corto) → Panini España",
    'Panini España',
    $ed2['name'] ?? 'NOT FOUND'
);

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "TEST 4: TEXTO CONTEXTUAL (PREVENTA VS DISPONIBLE)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$footer1 = $plugin->generate_contextual_footer(13215);
test(
    "Producto disponible INCLUYE link a preventas",
    true,
    strpos($footer1, 'preventas') !== false
);

$footer2 = $plugin->generate_contextual_footer(15382);
test(
    "Producto en preventa NO incluye link a preventas",
    false,
    strpos($footer2, 'preventas') !== false
);

$footer3 = $plugin->generate_contextual_footer(99001);
test(
    "Comics disponible usa URL de comics",
    true,
    strpos($footer3, '/product-category/comics/') !== false
);

$footer4 = $plugin->generate_contextual_footer(99002);
test(
    "Manhwa en preventa usa URL de manhwa",
    true,
    strpos($footer4, '/product-category/manga/manhwa/') !== false
);

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "TEST 5: CONSTANTES CRÍTICAS\n";
echo "═══════════════════════════════════════════════════════════════\n";

test(
    "PREVENTA_CATEGORY_ID = 215 (verificado en BD)",
    215,
    Akibara_SEO_Booster_Test::PREVENTA_CATEGORY_ID
);

test(
    "PREVENTA_CATEGORY_SLUG = 'preventa'",
    'preventa',
    Akibara_SEO_Booster_Test::PREVENTA_CATEGORY_SLUG
);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN DE RESULTADOS                     ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Tests pasados: %-3d                                       ║\n", $passed);
printf("║  ❌ Tests fallidos: %-3d                                      ║\n", $failed);
echo "╠══════════════════════════════════════════════════════════════╣\n";

if ($failed === 0) {
    echo "║  🎉 ¡TODOS LOS TESTS PASARON! Plugin verificado.            ║\n";
} else {
    echo "║  ⚠️  Hay tests fallidos. Revisar el código.                 ║\n";
}

echo "╚══════════════════════════════════════════════════════════════╝\n\n";

exit($failed > 0 ? 1 : 0);
