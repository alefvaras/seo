# Akibara SEO Booster

Plugin de WordPress para optimizar productos de WooCommerce y alcanzar **100/100 en Rank Math SEO**.

## Problemas que resuelve

| Error Rank Math | Solución | Puntos |
|-----------------|----------|--------|
| ❌ No se han encontrado enlaces externos | ✅ Agrega enlace dofollow a editorial oficial | +5 |
| ❌ 0 enlaces salientes dofollow | ✅ El enlace a editorial es dofollow | +5 |
| ❌ Título sin power word | ✅ Agrega "Comprar", "Nuevo", etc. | +5 |
| ❌ Contenido menor a 600 palabras | ✅ Expande descripción con plantillas SEO | +10 |

## Instalación

1. Subir `akibara-seo-booster.php` a `wp-content/plugins/`
2. Activar el plugin en WordPress → Plugins
3. Ir a **Herramientas → Akibara SEO Booster**
4. Ejecutar las optimizaciones

## Editoriales Soportadas

| Editorial | Sitio Oficial |
|-----------|---------------|
| Ivrea España | https://ivrea.es |
| Ivrea Argentina | https://www.ivrea.com.ar |
| Panini España | https://www.panini.es |
| Panini Argentina | https://www.paninicomics.com.ar |
| Arechi Manga | https://www.arechimanga.com |
| Planeta Cómic | https://www.planetacomic.com |
| Norma Editorial | https://www.normaeditorial.com |
| Milky Way Ediciones | https://www.milkywayediciones.com |
| ECC Ediciones | https://www.ecccomics.com |
| Distrito Manga | https://www.distritomanga.com |
| Ediciones Babylon | https://edicionesbabylon.es |
| OOSO Comics | https://oosocomics.com |
| Kitsune Books | https://www.kitsunemanga.com |
| Satori Ediciones | https://satoriediciones.com |

## Uso

### Vista Previa
Antes de aplicar cambios, usa el botón **Vista Previa** para ver qué productos serían modificados.

### Opciones Disponibles

1. **Enlaces Externos**: Agrega un enlace dofollow al sitio oficial de la editorial
2. **Power Words**: Agrega palabras como "Comprar", "Nuevo", "Oferta" al título SEO
3. **Expandir Descripción**: Añade contenido SEO-friendly a productos con menos de 600 palabras

### Filtros
- Filtrar por editorial específica
- Limitar cantidad de productos a procesar (1-500)

## Archivos

- `akibara-seo-booster.php` - Plugin principal de WordPress
- `sql-queries-seo.sql` - Consultas SQL útiles para diagnóstico

## Notas Técnicas

- El plugin marca los productos procesados con meta keys:
  - `_akibara_external_link_added` - Indica que se agregó enlace externo
  - `_akibara_description_expanded` - Indica que se expandió la descripción

- Los enlaces agregados son **dofollow** (sin rel="nofollow")
- Compatible con WooCommerce 5.0+
- Requiere Rank Math SEO para funcionalidad completa

## Agregar Nueva Editorial

Editar el array `$editorial_links` en el plugin:

```php
'nueva-editorial' => [
    'url' => 'https://www.nuevaeditorial.com',
    'name' => 'Nueva Editorial',
    'anchor_text' => 'Nueva Editorial (Editorial Oficial)'
],
```

## Soporte

Para agregar más editoriales o personalizar el plugin, contactar al equipo de desarrollo.
