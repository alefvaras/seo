# Guia de Mejoras SEO para Akibara
## Puntuacion Actual: 68/100 | Objetivo: 90+/100

---

## ERRORES IDENTIFICADOS

### 1. SEO Basico (1 Error)
- **Problema:** Contenido con 575 palabras (minimo requerido: 600)
- **Solucion:** Expandir descripciones de productos

### 2. Legibilidad del Titulo (1 Error)
- **Problema:** Titulos sin "Power Words"
- **Solucion:** Agregar palabras de impacto

### 3. Legibilidad del Contenido (2 Errores)
- **Problema A:** Sin Table of Contents
- **Problema B:** Parrafos muy largos
- **Solucion:** Plugin TOC + dividir contenido

### 4. Adicional (3 Errores)
- **Problema A:** Sin enlaces externos
- **Problema B:** 0 enlaces dofollow
- **Problema C:** No usa Content AI
- **Solucion:** Agregar links externos relevantes

---

## SOLUCIONES DETALLADAS

### Mejorar Titulos SEO

**ANTES (sin power words):**
```
Compra Dan Da Dan 20 - Ivrea Argentina | Akibara
Compra Chainsaw Man 1 - Ivrea Argentina | Akibara
```

**DESPUES (con power words):**
```
Oferta! Dan Da Dan 20 Original - Ivrea Argentina | Stock Inmediato | Akibara
Chainsaw Man 1 - Edicion Oficial Ivrea | Envio Gratis | Akibara
Exclusivo: Frieren 14 - Ivrea Argentina | Ultimo Stock | Akibara
```

**Power Words recomendadas:**
- Oferta, Exclusivo, Nuevo, Original, Limitado
- Stock Inmediato, Envio Gratis, Ultimo
- Autentico, Oficial, Premium, Bestseller

**Configuracion en Rank Math:**
1. Ir a Rank Math > Titulos y Meta > Productos
2. Cambiar patron a:
```
%title% Original | %tax_product_brand% | Stock Disponible | Akibara
```

---

### Mejorar Meta Descriptions

**ANTES (repetitiva):**
```
Compra [producto] en Akibara. Manga original en espanol, stock disponible. Envios a todo Chile.
```

**DESPUES (unica y optimizada):**
```
[Nombre] Tomo [X] de [Autor]. Manga original [Editorial] en espanol. Stock inmediato - Envio a todo Chile - Pago seguro. Compra ahora!
```

**Configuracion en Rank Math:**
1. Ir a Rank Math > Titulos y Meta > Productos
2. Usar variables:
```
%title% de %tax_pa_autor%. %tax_product_brand% en espanol. Stock inmediato - Envio a Chile - Pago seguro. Compra en Akibara!
```

---

### Expandir Contenido de Productos (600+ palabras)

**Estructura recomendada para cada ficha de producto:**

```html
<h2>Sinopsis</h2>
[150-200 palabras sobre la historia del manga]

<h2>Sobre el Autor</h2>
[80-100 palabras sobre el mangaka]

<h2>Detalles del Producto</h2>
<ul>
  <li><strong>Editorial:</strong> Ivrea Argentina</li>
  <li><strong>Paginas:</strong> 192</li>
  <li><strong>Idioma:</strong> Espanol</li>
  <li><strong>Formato:</strong> Tankobon</li>
  <li><strong>ISBN:</strong> XXX-XXX-XXX</li>
</ul>

<h2>Por que elegir Akibara?</h2>
[100 palabras sobre beneficios de comprar en tu tienda]

<h2>Productos Relacionados</h2>
[Links internos a otros tomos de la serie]
```

---

### Agregar Enlaces Externos

**Ejemplo de enlaces a agregar en cada producto:**

```html
<!-- En la descripcion del producto -->
<p>
  Mas informacion sobre esta serie en
  <a href="https://myanimelist.net/manga/XXXXX" target="_blank" rel="noopener">MyAnimeList</a>
</p>

<!-- O en un widget/shortcode -->
<div class="external-links">
  <h4>Enlaces de Interes:</h4>
  <ul>
    <li><a href="https://myanimelist.net/manga/XXXXX" rel="noopener" target="_blank">Ver en MyAnimeList</a></li>
    <li><a href="https://es.wikipedia.org/wiki/XXXXX" rel="noopener" target="_blank">Wikipedia</a></li>
  </ul>
</div>
```

**Fuentes recomendadas para enlaces:**
- MyAnimeList (myanimelist.net)
- Wikipedia
- Pagina oficial de la editorial
- Redes del autor/mangaka

---

### Instalar Table of Contents

**Opcion 1: Rank Math TOC Block (recomendado)**
1. Editar producto en Gutenberg
2. Agregar bloque "Rank Math - Table of Contents"
3. Se genera automaticamente basado en H2/H3

**Opcion 2: Plugin Easy Table of Contents**
1. Instalar plugin
2. Activar para post type "product"
3. Configurar insercion automatica

---

### Dividir Parrafos Largos

**ANTES:**
```
Este manga cuenta la historia de un joven que descubre poderes sobrenaturales y debe enfrentarse a demonios mientras intenta vivir una vida normal como estudiante de secundaria, enfrentando diversos desafios y conociendo aliados que lo ayudaran en su camino.
```

**DESPUES:**
```
Este manga cuenta la historia de un joven que descubre poderes sobrenaturales.

Debe enfrentarse a demonios mientras intenta vivir una vida normal como estudiante de secundaria.

A lo largo de su camino, conocera aliados que lo ayudaran a superar diversos desafios.
```

**Reglas:**
- Maximo 3-4 lineas por parrafo
- Usar subtitulos H2, H3, H4
- Incluir listas con vinetas
- Agregar imagenes entre secciones

---

## CHECKLIST DE IMPLEMENTACION

### Prioridad Alta (Mayor impacto en SEO)
- [ ] Agregar power words a titulos SEO
- [ ] Expandir contenido a 600+ palabras
- [ ] Agregar al menos 2 enlaces externos por producto

### Prioridad Media
- [ ] Mejorar meta descriptions
- [ ] Instalar Table of Contents
- [ ] Dividir parrafos largos

### Prioridad Baja
- [ ] Optimizar imagenes (alt text)
- [ ] Agregar schema markup adicional
- [ ] Mejorar velocidad de carga

---

## IMPACTO ESTIMADO

| Mejora | Puntos Estimados |
|--------|------------------|
| Power Words en titulos | +5 |
| Contenido 600+ palabras | +8 |
| Enlaces externos | +5 |
| Table of Contents | +2 |
| Parrafos cortos | +3 |
| Meta descriptions unicas | +3 |
| **TOTAL** | **+26 puntos** |

**Puntuacion proyectada: 68 + 26 = 94/100**

---

## SCRIPT SQL PARA ACTUALIZAR TITULOS (Rank Math)

Si deseas actualizar titulos masivamente, ejecuta esto en phpMyAdmin:

```sql
-- Agregar "Original" a todos los titulos SEO de productos
UPDATE wp_postmeta pm
INNER JOIN wp_posts p ON pm.post_id = p.ID
SET pm.meta_value = CONCAT(
  REPLACE(pm.meta_value, ' | Akibara', ' Original | Akibara')
)
WHERE pm.meta_key = 'rank_math_title'
AND p.post_type = 'product'
AND pm.meta_value NOT LIKE '%Original%';
```

**IMPORTANTE:** Hacer backup antes de ejecutar cualquier SQL.

---

## AUTOMATIZACION CON RANK MATH

### Configurar Variables Dinamicas

En Rank Math > Titulos y Meta > Productos:

**Titulo SEO:**
```
%title% | %tax_product_brand% | Stock Disponible | Akibara
```

**Meta Description:**
```
Compra %title% de %tax_pa_autor%. Manga %tax_product_brand% original en espanol. Envio rapido a todo Chile. Stock disponible!
```

### Usar Content AI (si tienes Rank Math Pro)
1. Editar producto
2. Click en "Content AI" en el panel de Rank Math
3. Generar sugerencias de contenido
4. Expandir descripcion automaticamente

---

## CONTACTO Y SOPORTE

Para implementar estas mejoras, puedes:
1. Hacerlo manualmente producto por producto
2. Usar importacion masiva con WP All Import
3. Contratar un desarrollador para automatizar

Tiempo estimado de implementacion: 2-4 horas para configuracion base + 5-10 min por producto para contenido personalizado.
