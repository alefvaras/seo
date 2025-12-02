-- ============================================
-- AKIBARA SEO BOOSTER - SQL QUERIES
-- Consultas útiles para diagnóstico y mejora de SEO
-- ============================================

-- ============================================
-- 1. DIAGNÓSTICO DE PRODUCTOS
-- ============================================

-- Ver todos los productos con su editorial
SELECT
    p.ID as product_id,
    p.post_title as producto,
    t.name as editorial,
    t.slug as editorial_slug
FROM wp_posts p
INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms t ON tt.term_id = t.term_id
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND tt.taxonomy = 'product_brand'
ORDER BY t.name, p.post_title;

-- Contar productos por editorial
SELECT
    t.name as editorial,
    t.slug as slug,
    COUNT(*) as total_productos
FROM wp_posts p
INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms t ON tt.term_id = t.term_id
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND tt.taxonomy = 'product_brand'
GROUP BY t.term_id
ORDER BY total_productos DESC;

-- ============================================
-- 2. ANÁLISIS DE CONTENIDO SEO
-- ============================================

-- Productos con descripción corta (menos de 600 palabras aproximado)
-- Nota: MySQL no tiene función nativa de conteo de palabras, usamos LENGTH
SELECT
    p.ID,
    p.post_title,
    LENGTH(p.post_content) as caracteres,
    LENGTH(p.post_content) - LENGTH(REPLACE(p.post_content, ' ', '')) + 1 as palabras_aprox
FROM wp_posts p
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND (LENGTH(p.post_content) - LENGTH(REPLACE(p.post_content, ' ', '')) + 1) < 600
ORDER BY palabras_aprox ASC
LIMIT 100;

-- Productos sin título SEO de Rank Math
SELECT
    p.ID,
    p.post_title,
    pm.meta_value as rank_math_title
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_title'
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND (pm.meta_value IS NULL OR pm.meta_value = '')
LIMIT 100;

-- Productos sin descripción SEO de Rank Math
SELECT
    p.ID,
    p.post_title,
    pm.meta_value as rank_math_description
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_description'
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND (pm.meta_value IS NULL OR pm.meta_value = '')
LIMIT 100;

-- ============================================
-- 3. ANÁLISIS DE ENLACES EXTERNOS
-- ============================================

-- Productos que YA tienen enlaces externos en el contenido
SELECT
    p.ID,
    p.post_title,
    CASE
        WHEN p.post_content LIKE '%href=%' THEN 'Sí tiene enlaces'
        ELSE 'Sin enlaces'
    END as tiene_enlaces
FROM wp_posts p
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND p.post_content LIKE '%href=%';

-- Productos procesados por Akibara SEO Booster
SELECT
    p.ID,
    p.post_title,
    pm.meta_value as datos_enlace
FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'product'
AND pm.meta_key = '_akibara_external_link_added';

-- ============================================
-- 4. LISTAR TODAS LAS EDITORIALES DISPONIBLES
-- ============================================

SELECT
    t.term_id,
    t.name as editorial,
    t.slug,
    tt.count as productos
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
WHERE tt.taxonomy = 'product_brand'
ORDER BY t.name;

-- ============================================
-- 5. BÚSQUEDA DE PRODUCTOS ESPECÍFICOS
-- ============================================

-- Buscar productos de Ivrea España
SELECT
    p.ID,
    p.post_title,
    p.post_status
FROM wp_posts p
INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms t ON tt.term_id = t.term_id
WHERE p.post_type = 'product'
AND tt.taxonomy = 'product_brand'
AND t.slug LIKE '%ivrea%espana%'
LIMIT 50;

-- ============================================
-- 6. ESTADÍSTICAS GENERALES
-- ============================================

-- Resumen general de SEO
SELECT
    'Total Productos' as metrica,
    COUNT(*) as valor
FROM wp_posts
WHERE post_type = 'product' AND post_status = 'publish'

UNION ALL

SELECT
    'Con Título SEO' as metrica,
    COUNT(*) as valor
FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND pm.meta_key = 'rank_math_title'
AND pm.meta_value != ''

UNION ALL

SELECT
    'Con Descripción SEO' as metrica,
    COUNT(*) as valor
FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND pm.meta_key = 'rank_math_description'
AND pm.meta_value != ''

UNION ALL

SELECT
    'Con Editorial Asignada' as metrica,
    COUNT(DISTINCT p.ID) as valor
FROM wp_posts p
INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
WHERE p.post_type = 'product'
AND p.post_status = 'publish'
AND tt.taxonomy = 'product_brand';

-- ============================================
-- 7. ACTUALIZACIÓN MANUAL (SI ES NECESARIO)
-- ============================================

-- EJEMPLO: Agregar enlace externo manualmente a un producto específico
-- CUIDADO: Reemplazar {PRODUCT_ID} con el ID real del producto
/*
UPDATE wp_posts
SET post_content = CONCAT(
    post_content,
    '\n\n<p class="editorial-link">📚 Conoce más sobre esta editorial: <a href="https://ivrea.es" target="_blank" rel="noopener">Ivrea España (Editorial Oficial)</a></p>'
)
WHERE ID = {PRODUCT_ID};
*/

-- EJEMPLO: Agregar power word al título SEO de Rank Math
/*
UPDATE wp_postmeta
SET meta_value = CONCAT('Comprar ', meta_value)
WHERE meta_key = 'rank_math_title'
AND post_id = {PRODUCT_ID}
AND meta_value NOT LIKE 'Comprar%';
*/

-- ============================================
-- 8. ELIMINAR TEXTO "EXPLORA MÁS TÍTULOS..."
-- ============================================

-- Ver productos que tienen el texto (para verificar antes de eliminar)
SELECT
    ID,
    post_title,
    post_content
FROM wp_posts
WHERE post_type = 'product'
AND post_status = 'publish'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ELIMINAR: Variante completa con preventas (párrafo con clase)
UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
    post_content,
    '<p[^>]*>Explora más títulos en nuestra <a[^>]*>colección de manga</a> o visita nuestras <a[^>]*>preventas</a>\\.</p>',
    ''
)
WHERE post_type = 'product'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ELIMINAR: Variante solo con colección de manga
UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
    post_content,
    '<p[^>]*>Explora más títulos en nuestra <a[^>]*>colección de manga</a>\\.</p>',
    ''
)
WHERE post_type = 'product'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ELIMINAR: Variante con colección de cómics
UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
    post_content,
    '<p[^>]*>Explora más títulos en nuestra <a[^>]*>colección de cómics</a>[^<]*</p>',
    ''
)
WHERE post_type = 'product'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ELIMINAR: Variante con catálogo
UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
    post_content,
    '<p[^>]*>Explora más títulos en nuestra <a[^>]*>catálogo</a>[^<]*</p>',
    ''
)
WHERE post_type = 'product'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ELIMINAR: Cualquier variante restante (más genérico)
UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
    post_content,
    '<p[^>]*class=["\']?akibara-contextual-footer["\']?[^>]*>[^<]*Explora más títulos[^<]*</p>',
    ''
)
WHERE post_type = 'product'
AND post_content LIKE '%Explora más títulos%';

-- Verificar después de la limpieza (debería dar 0 resultados)
SELECT COUNT(*) as productos_con_texto_restante
FROM wp_posts
WHERE post_type = 'product'
AND post_status = 'publish'
AND post_content LIKE '%Explora más títulos en nuestra%';

-- ============================================
-- 9. LIMPIEZA Y ROLLBACK
-- ============================================

-- Ver productos modificados por el plugin (para posible rollback)
SELECT
    p.ID,
    p.post_title,
    pm1.meta_value as enlace_agregado,
    pm2.meta_value as descripcion_expandida
FROM wp_posts p
LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_akibara_external_link_added'
LEFT JOIN wp_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_akibara_description_expanded'
WHERE p.post_type = 'product'
AND (pm1.meta_value IS NOT NULL OR pm2.meta_value IS NOT NULL);

-- ROLLBACK: Eliminar marcadores del plugin (NO elimina el contenido agregado)
/*
DELETE FROM wp_postmeta WHERE meta_key = '_akibara_external_link_added';
DELETE FROM wp_postmeta WHERE meta_key = '_akibara_description_expanded';
*/
