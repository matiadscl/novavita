# Auditoría y Optimización Google Ads — Novavita
**Fecha**: 11-12 de abril 2026
**Realizado por**: Matías (con asistencia de Claude)
**Para**: Fabi / Equipo Facand

---

## 1. Contexto inicial

- **Cliente**: Novavita — SpA, masajes, depilaciones en Curicó
- **Contactos**: Daniela (co-dueña), Nelson (co-dueño, cosmetólogo)
- **Plataforma**: Shopify (novavita.cl / x23gng-2u.myshopify.com)
- **Presupuesto actual**: ~$1.240.000 CLP/mes total
  - Meta: ~$1.115.000/mes (90%)
  - Google: ~$125.000/mes (10%)
- **Google Ads**: 1 campaña Performance Max general, CPA ~$2.180
- **Hallazgo clave**: Google es 10x más eficiente en CPA que Meta

---

## 2. Diagnóstico de conversiones en Google Ads

### Estado encontrado (antes de cambios)

| Conversión | Fuente | Datos | Estado | Rol |
|-----------|--------|-------|--------|-----|
| Google Shopping App Purchase | App Shopify | 7 conv / $2.3M | Funciona | Principal |
| Google Shopping App Add To Cart | App Shopify | 34 eventos / $4.7M | Funciona | Secundaria |
| Compra (manual) | Tag manual Jul 2025 | 0 | Rota | Principal |
| Mensaje WhatsApp (manual) | Tag manual Jul 2025 | 0 | Rota | Principal |
| Google Shopping App Search | App Shopify | 0 | Inactiva | Secundaria |
| Google Shopping App Add Payment Info | App Shopify | 0 | Inactiva | Secundaria |
| Android installs | Google Play | 0 | No aplica | Secundaria |

### Problemas encontrados

1. **Dos acciones de "Compra" como Principal** — la manual con 0 conversiones metía ruido al algoritmo
2. **"Mensaje WhatsApp" como Principal con 0 conversiones** — nunca funcionó el tag
3. **Todo lo manual (creado Jul 2025, probablemente por CM anterior) estaba roto**
4. **Solo 7 compras reales registradas** — las campañas optimizaban con datos mínimos
5. **No hay enlace directo de WhatsApp en el sitio** — el botón usa un widget (app "chwhatsapp") que no genera `<a href>` estándar

### Cambios realizados

- **"Compra" (manual)**: cambiada de Principal a **Secundaria**
- **"Mensaje WhatsApp"**: cambiada de Principal a **Secundaria**

### Estado actual (después de cambios)

Solo queda **1 acción de conversión Principal**: "Google Shopping App Purchase" (7 conv, funciona correctamente)

#### Detalle de "Google Shopping App Purchase" (la buena)
- Modelo de atribución: Basado en datos
- Ventana post-clic: 30 días
- Vista interesada: 3 días
- Post-impresión: 1 día
- Conversiones avanzadas: Habilitadas
- Recuento: Todas las conversiones
- Valor: Dinámico desde Shopify

---

## 3. Diagnóstico del Tag de Google

- **Tag de Google Ads**: AW-17404864667 (creado 29/07/2025)
- **Tag GT**: GT-KDZT25BT
- **Estado**: Instalado en el sitio, "Requiere atención" (problemas menores de dominios)
- **No había GA4 conectado**

---

## 4. Acciones completadas (11-12 abril 2026)

### Fase 1: GA4 conectado a Shopify ✅
- Propiedad GA4 creada: **"Novavita - Web"**
- ID de medición: **G-DCZ0V2TELX**
- Stream URL: www.novavita.cl
- Medición mejorada: Activada (scroll, clics, video, búsqueda interna)
- Conectada a Shopify vía app "Google & YouTube"
- ads.novavitaclinica@gmail.com agregado como Editor en GA4

### Fase 2: GA4 vinculado con Google Ads ✅
- Vinculación creada: GA4 ↔ Google Ads (377-443-5191 / AW-17404864667)
- Publicidad personalizada: Habilitada
- Etiquetado automático: Habilitado
- Acceso a funciones Analytics desde Google Ads: Habilitado

### Fase 3: Google Tag Manager instalado ✅
- Contenedor creado: **GTM-K6ZXX5LT**
- Instalado en theme.liquid de Shopify (head + body)
- Verificado con Preview: conectado y funcionando

### Fase 4: Eventos personalizados en GTM ✅
Todos verificados y funcionando en Preview:

| Evento | Activador | Etiqueta | Estado |
|--------|-----------|----------|--------|
| Scroll depth (25/50/75/100%) | Scroll 50% (profundidad desplazamiento vertical) | GA4 - Scroll Depth | ✅ Funcionando |
| Tiempo en página (30s/60s/90s/120s) | Timer 30s (temporizador, 30000ms, límite 4) | GA4 - Time on Page | ✅ Funcionando |
| Clic en WhatsApp | Evento WhatsApp DataLayer (custom event: whatsapp_click) | GA4 - Clic WhatsApp | ✅ Funcionando |
| Vista de colección/servicio | Vista Colecciones (page view en URLs con /collections/) | GA4 - Vista Colección | ✅ Funcionando |

**Nota técnica sobre WhatsApp**: El botón de WhatsApp usa un widget (app "chwhatsapp", ID: `chwhatsapp-btn`, clase: `whatsappbutton`) que no genera enlaces `<a href>` estándar. Se resolvió con una etiqueta HTML personalizada ("Custom - WhatsApp Listener") que inyecta un event listener y pushea al dataLayer.

**Versión publicada en GTM**: v4 - WhatsApp custom listener

---

## 5. Estructura del sitio novavita.cl

### Navegación / Categorías
- Packs Soprano (depilación láser)
- Packs Mujer → /collections/depilacion-laser-mujer-packs
- Packs Hombre → /collections/depilacion-packs-laser-hombre-curico
- Todos → /collections/packs

### Apps instaladas en Shopify
- Google & YouTube (Merchant Center + Ads + GA4)
- Loox (reviews)
- CartHike
- BOGOS (promociones)
- Facebook Pixel
- Widget WhatsApp (chwhatsapp)

### Cobertura de tags
- Todas las páginas etiquetadas excepto: /products/masaje-cervical-hombros-y-espalda-copia (probable producto duplicado/borrador)

---

## 6. Tags instalados en novavita.cl

| Tag | ID | Fuente |
|-----|-----|--------|
| Google Ads | AW-17404864667 | App Shopify |
| GA4 | G-DCZ0V2TELX | App Shopify |
| GT (general) | GT-TXXPRJ5B | App Shopify |
| GTM | GTM-K6ZXX5LT | Manual (theme.liquid) |
| Facebook Pixel | (presente) | Shopify |

---

## 7. Plan pendiente

### Fase 5: Audiencias personalizadas en Google Ads (PENDIENTE)
- Visitantes por servicio (según URL de colección)
- Carrito abandonado (add_to_cart sin purchase)
- Engagement alto (+60s o +2 páginas)
- Compradores (para exclusión y upsell)
- Customer Match (emails de Shopify)
- Clics en WhatsApp (nueva audiencia posible gracias al evento configurado)

### Fase 6: Reestructurar campañas Google Ads ✅

#### Decisión de presupuesto
- **Presupuesto total reducido**: de $1.240.000 a $650.000/mes
- **Meta**: bajado de $1.115.000 a **$150.000/mes** (campañas pausadas, solo se mantienen las esenciales)
- **Google**: subido de $125.000 a **$500.000/mes**
- **Razón**: Google tiene 10x mejor CPA que Meta

#### Campañas Google Ads activas

| Campaña | Tipo | Presupuesto diario | Mensual aprox. | Estado |
|---------|------|-------------------|----------------|--------|
| Search - Brand Novavita | Búsqueda | $2.500 | ~$75.000 | Nueva - activa |
| Search - Servicios Curicó | Búsqueda | $5.000 | ~$150.000 | Nueva - activa |
| Máximo rendimiento general | PMax | $9.200 | ~$275.000 | Existente - presupuesto ajustado (era $5.000) |
| **Total Google** | | **$16.700** | **~$500.000** | |

#### Detalle de campaña Brand
- Keywords: novavita, novavita, novavita curicó, novavita clinica, novavita spa, novavita clinica spa, novavita chile, novavita depilacion
- Ubicación: Curicó, solo presencia
- Sin IA Max, sin redes de Display ni socios de búsqueda
- Conversiones: Google Shopping App Purchase

#### Detalle de campaña Servicios
- Keywords: depilacion laser curico, spa curico, clinica estetica curico, masajes curico, tratamiento facial curico, soprano titanium curico, etc. (15 keywords locales)
- Ubicación: Curicó, solo presencia
- Sin IA Max, sin redes de Display ni socios de búsqueda
- Conversiones: Google Shopping App Purchase

---

## 8. Próximos pasos (cuando haya datos)

### Corto plazo (1-2 semanas)
- Monitorear CPA de las nuevas campañas Search
- Verificar que GA4 esté recibiendo datos correctamente
- Verificar que los eventos de GTM aparezcan en GA4 (Informes → Eventos)
- Revisar si "click_whatsapp" acumula datos para decidir si agregarlo como conversión principal

### Mediano plazo (2-4 semanas)
- Cuando las audiencias tengan +100 usuarios: crear campaña de **Remarketing Display**
- Cuando las audiencias tengan +1.000 usuarios: activar **Customer Acquisition** en PMax
- Evaluar segmentar PMax por servicio (Depilación vs Faciales/Spa)
- Subir lista de Customer Match (emails de Shopify) para crear lookalikes

### Pendiente de confirmar con Fabi/Dani
- ¿Los leads de WhatsApp llegan por el botón del sitio, por anuncios de Meta, o ambos?
- ¿Cuánto tráfico mensual tiene novavita.cl? (necesario para validar viabilidad de audiencias — mínimo ~1.000 usuarios/mes)
- ¿Se debe agregar WhatsApp como conversión principal en Google Ads una vez que acumule datos?
- Evaluar rendimiento de Meta con $150K: ¿mantener, subir o eliminar?

---

## 10. Ajuste de Meta Ads (12 abril 2026)

### Situación encontrada
- Solo 1 campaña activa: **"Testeo ABO"** (objetivo: ventas)
- 2 adsets: "Depilación láser" y "Faciales"
- Todas las demás campañas ya estaban pausadas (24 campañas históricas)

### Rendimiento últimos 30 días (13 mar - 11 abr 2026)

| Adset | Gasto | Compras | CPA | Add to Cart | Checkouts |
|-------|-------|---------|-----|-------------|-----------|
| Depilación láser | $209.051 | 16 | $13.066 | 83 | 43 |
| Faciales | $80.260 | 11 | $7.296 | 75 | 33 |
| **Total** | **$289.387** | **27** | **$10.718** | **158** | **76** |

### Decisión
- **Faciales tiene casi la mitad del CPA** ($7.296 vs $13.066) con mejor ratio de conversión
- Google Search se encargará de capturar la demanda de depilación láser en Curicó

### Cambios realizados
- **Faciales**: activado con presupuesto de **$5.000/día** (~$150.000/mes)
- **Depilación láser**: pausado
- Campaña "Testeo ABO": se mantiene activa como contenedor

### Distribución final de presupuesto

| Canal | Campaña | Presupuesto diario | Mensual aprox. |
|-------|---------|-------------------|----------------|
| Google | Search - Brand Novavita | $2.500 | ~$75.000 |
| Google | Search - Servicios Curicó | $5.000 | ~$150.000 |
| Google | Máximo rendimiento general (PMax) | $9.200 | ~$275.000 |
| Meta | Testeo ABO → Faciales | $5.000 | ~$150.000 |
| **Total** | | **$21.700** | **~$650.000** |

---

## 9. Accesos configurados

| Plataforma | Cuenta | Estado |
|-----------|--------|--------|
| Google Ads | ads.novavitaclinica@gmail.com | Activo |
| GA4 | Propiedad creada por Mati, acceso Editor a ads.novavitaclinica | Activo |
| Shopify Admin | Acceso de staff | Activo |
| GTM | GTM-K6ZXX5LT, creado por Mati | Activo |
| Meta Ads | API conectada al dashboard | Activo |

---

## 11. Pendiente próxima sesión

- Agregar pestaña "Gestiones" al dashboard de Novavita para registrar avances de cada sesión de trabajo
