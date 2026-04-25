# Guía de Lanzamiento Meta — Test A/B Stories IG Día de la Madre
**Autora:** Vale
**Objetivo:** documento ejecutable. Copia y pega cada campo en Meta Business Suite.

---

## 1. Configuración de Campaña

| Campo | Valor |
|---|---|
| **Nombre** | NV · Día Mamá · TOFU Follow · Test A/B Scripts |
| **Objetivo** | Tráfico |
| **Categoría especial** | Ninguna |
| **Presupuesto** | A nivel de ad set (ABO) — $1.000 CLP/día por ad set |
| **Fecha inicio** | [hoy, apenas la subas] |
| **Fecha fin** | 7 días después |

---

## 2. Ad Sets (3 × idéntica configuración, solo cambia el ad)

### Configuración base (aplica a los 3)

| Campo | Valor |
|---|---|
| **Optimización** | Clics en el enlace |
| **Destino** | Sitio web (el link llevará al perfil IG) |
| **Presupuesto diario** | $1.000 CLP |
| **Programación** | Todo el día |
| **Ubicación** | Curicó, Chile · Radio 50 km |
| **Edad** | 28 – 55 |
| **Género** | Mujeres |
| **Idioma** | Español |
| **Intereses** | Spa, Estética, Belleza, Cuidado de la piel, Bienestar, Instagram (categoría) |
| **Exclusiones custom** | 1. Seguidores @novavita (crear audiencia custom IG engagement — todos los que siguen la cuenta) · 2. Compradores Shopify (subir customer list CSV con emails) |
| **Placement** | Manual — **SOLO Stories Instagram** (desmarcar todo lo demás) |

### Ad Set 1 — Transformación
- Nombre: `NV-DiaMama-TOFU-A-Transformacion`
- Creative: `v07_stories_A_transformacion.mp4`

### Ad Set 2 — Día de la Madre
- Nombre: `NV-DiaMama-TOFU-B-DiaMama`
- Creative: `v07_stories_B_dia_mama.mp4`

### Ad Set 3 — Autocuidado
- Nombre: `NV-DiaMama-TOFU-C-Autocuidado`
- Creative: `v07_stories_C_autocuidado.mp4`

---

## 3. Copy de cada Anuncio (campo por campo, listo para pegar)

Meta para anuncios con objetivo Tráfico te pedirá: **Primary Text**, **Headline**, **Description**, **URL del destino** y **CTA Button**.

### Anuncio A — Transformación

| Campo Meta | Texto |
|---|---|
| **Identidad** | Instagram: @novavita |
| **Formato** | Video único |
| **Media** | `v07_stories_A_transformacion.mp4` |
| **Primary text** | Tecnología de clínica en Curicó: Dermapen, Timexpert y Germaine de Capuccini. Más de 200 mujeres confían en Novavita cada mes. 💆‍♀️ |
| **Headline** | Piel visible desde la primera sesión |
| **Description** | Síguenos y reserva tu hora |
| **CTA Button** | Más información |
| **Destination URL** | https://www.instagram.com/novavita/ |

### Anuncio B — Día de la Madre

| Campo Meta | Texto |
|---|---|
| **Identidad** | Instagram: @novavita |
| **Formato** | Video único |
| **Media** | `v07_stories_B_dia_mama.mp4` |
| **Primary text** | Un ritual de Vitamina C pura o una sesión de relajación profunda. Regalos desde $30.000 — Novavita Curicó. 🌸 |
| **Headline** | El regalo que mamá sí va a recordar |
| **Description** | Síguenos y mira los packs |
| **CTA Button** | Más información |
| **Destination URL** | https://www.instagram.com/novavita/ |

### Anuncio C — Autocuidado

| Campo Meta | Texto |
|---|---|
| **Identidad** | Instagram: @novavita |
| **Formato** | Video único |
| **Media** | `v07_stories_C_autocuidado.mp4` |
| **Primary text** | Cuidado facial profesional con equipos de nivel clínico. Una pausa consciente con tecnología Germaine de Capuccini y Dermapen 4. ✨ |
| **Headline** | Tu ritual de cuidado en Curicó |
| **Description** | Síguenos y reserva tu hora |
| **CTA Button** | Más información |
| **Destination URL** | https://www.instagram.com/novavita/ |

---

## 4. Paso a paso Meta Business Suite (~15 min)

1. **Ingresar a Meta Business Suite** → Administrador de anuncios → + Crear
2. **Objetivo:** Tráfico → Continuar
3. **Nombre de campaña:** copiar del punto 1
4. **Presupuesto de campaña:** **Desactivado** (usar presupuesto por ad set)
5. **Crear Ad Set 1 — Transformación**
   - Pegar todos los campos de la tabla "Configuración base"
   - Placement: Manual → desmarcar todo → solo ✅ Instagram Stories
   - Guardar
6. **Crear Anuncio 1**
   - Identidad IG: @novavita
   - Subir `v07_stories_A_transformacion.mp4`
   - Pegar Primary Text, Headline, Description, CTA, URL de tabla "Anuncio A"
   - Vista previa en Instagram Stories
   - Publicar
7. **Duplicar Ad Set** → rename a "B-DiaMama" → cambiar solo el creativo a `v07_stories_B_dia_mama.mp4` + copy del Anuncio B
8. **Duplicar Ad Set** → rename a "C-Autocuidado" → creativo + copy del Anuncio C
9. **Revisar los 3 ad sets** — presupuesto $1.000 CLP/día cada uno, mismo targeting
10. **Publicar campaña**

---

## 5. Plan de lectura (7 días)

| Día | Qué revisar | Acción |
|---|---|---|
| **Día 1-2** | Frecuencia, CPM, si se están aprobando los ads | Dejar correr sin tocar |
| **Día 3** | CTR y Profile Visits por ad set | Si uno tiene 3x más clics, nota — no pausar aún |
| **Día 5** | Profile Visit Rate (clics al perfil / impresiones) | Pausar la variante con peor rendimiento |
| **Día 7** | Lectura final | Elegir ganador. Preparar Capa 2 |

**Métrica principal:** Profile Visit Rate. Meta no reporta nativamente "Seguidores ganados por ad", por eso el proxy es visitas al perfil.

**Métrica secundaria:** CPM (< $3.000 CLP es bueno para Curicó) y CTR (> 1% es saludable).

---

## 6. Capa 2 — Retargeting (YA preparada)

**Audiencia:** Interacción IG 60 días + Video view ≥50% de la variante ganadora
**Objetivo:** Tráfico a `/collections/feliz-dia-mama`
**Placements:** Stories + Reels IG
**Presupuesto:** $2.000 CLP/día × 2 semanas = $56.000 total

**Creativos listos en** `/home/coder/clientes/novavita/assets/ads_meta/dia_mama_capa2/`:

| Creativo | Ángulo | Copy encabezado | CTA |
|---|---|---|---|
| `capa2_v04_hidrodermoabrasion.mp4` (6.1 MB) | Beneficio concreto con precio | "Limpieza profunda con hidrodermoabrasión" | "Reserva tu hora en novavita.cl" |
| `capa2_v10_safe_equipos.mp4` (8.8 MB) | Equipos/tecnología clínica | "Equipos de nivel clínico en Curicó" | "Mira los packs Día de la Madre" |

---

## ⚠️ Hallazgo crítico — V10 original

Al inspeccionar el reel V10 de Dani descubrí que **NO es enteramente contenido Novavita**. Mezcla material propio con **stock/contenido ajeno**:

**Segmentos Novavita ✓:**
- 8-15s: pantallas equipo Soprano Titanium + cabezal "The Max" (equipos propios)
- 20-27s: tratamiento en proceso (mujer con gafas de protección)
- 43-48s: máquina Soprano + logo Novavita

**Segmentos stock/ajeno ❌:**
- 0-6s: zapatos, café (lifestyle genérico)
- **16s: auto F1 rojo en pista**
- **32-38s: "Dr. Kelly" — mujer afro con bata bordada "Dr. Kelly"** (persona que NO trabaja en Novavita)
- 40s: mujer sonriendo en exterior (stock lifestyle)

**Implicaciones:**
1. **No usar V10 original como anuncio** — mostraría a Dr. Kelly (persona inexistente en Novavita) y eso puede ser engañoso o violar políticas de Meta.
2. **Verificar con Dani** si Novavita tiene derechos/licencia sobre el contenido stock.
3. Creé una **versión "safe"** (`capa2_v10_safe_equipos.mp4`) que usa solo los segmentos 100% Novavita. Esa sí se puede pautar.

**Recomendación:** pedir a Dani que clarifique el origen del material stock y, si es compra de stock, que envíe la licencia. Si son solo descargas libres, también ok — pero confirmar.

— Vale
