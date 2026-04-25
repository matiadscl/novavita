# Plan de lanzamiento — Test A/B Stories IG · Día de la Madre
**Autora:** Vale
**Fecha:** 2026-04-16
**Cliente:** Novavita
**Estado:** Videos editados · listo para subir a Meta

---

## Objetivo
Construir audiencia custom de interesados via tráfico al perfil IG (Novavita), luego retargetear con oferta Día de la Madre en Capa 2.

## Configuración de campaña (ABO)

**Nombre:** NV — Día Mamá · TOFU Follow · Test A/B Scripts
**Objetivo:** Tráfico (al perfil IG)
**Presupuesto:** ABO — $1.000/día por ad set × 3 ad sets = **$3.000/día**
**Duración test:** 7 días → **$21.000 total**
**Placement:** SOLO Stories Instagram (manual placement)
**Optimización:** Clics en el enlace (o visita al perfil si disponible)

### Targeting (mismo en los 3 ad sets)
- **Ubicación:** Curicó + 50 km
- **Edad:** 28-55 mujeres (principal), expandir después
- **Intereses:** Spa, estética, belleza, cuidado de la piel, bienestar
- **Exclusiones obligatorias:**
  - Seguidores actuales de @novavita
  - Lista de compradores Shopify (customer match)

### 3 Ad Sets — Una variante por set
| Ad Set | Ángulo | Script encabezado | Video |
|---|---|---|---|
| 1. Transformación | Pain point + social proof | "¿Tu rutina de piel ya no te da resultados?" | `v07_stories_A_transformacion.mp4` |
| 2. Día de la Madre | Ocasión + precio accesible | "¿Ya pensaste en el regalo de mamá?" | `v07_stories_B_dia_mama.mp4` |
| 3. Autocuidado | Emoción personal + social proof | "Tú también mereces una pausa." | `v07_stories_C_autocuidado.mp4` |

## Criterios de lectura

**Día 3:** revisar si alguna variante no tiene impresiones. Si una tiene 3x más clics que las otras, ya está ganando.
**Día 5:** descartar la peor (pausar ad set con menor Profile Visit Rate).
**Día 7:** elegir ganador. Métricas prioritarias (en este orden):
1. **Profile Visits / Impression** (principal)
2. Seguidores ganados (si se puede atribuir)
3. CPM y CTR como apoyo

## Siguiente paso (Capa 2 — después del test)

Una vez identificado el ganador, levantar:
- **Campaña retargeting** (objetivo Tráfico a `/collections/feliz-dia-mama`)
- **Audiencia custom:** Interacción IG últimos 60 días + Vistas ≥50% del video del ganador
- **Creativos:** V07 Timexpert + V04 Hidrodermoabrasión + V10 aspiracional
- **Presupuesto:** $70.000/mes hasta el 10 de mayo

---

## Archivos listos para subir

```
/home/coder/clientes/novavita/assets/ads_meta/dia_mama_test_ab/
├── v07_base_14s_1080x1920.mp4              (6.0 MB — base sin overlays)
├── v07_stories_A_transformacion.mp4        (5.9 MB — LISTO)
├── v07_stories_B_dia_mama.mp4              (5.9 MB — LISTO)
├── v07_stories_C_autocuidado.mp4           (5.9 MB — LISTO)
└── txt/  (archivos de texto usados en overlays)
```

**Especificaciones técnicas:**
- 1080 × 1920 (9:16 Stories IG nativo)
- 14 segundos
- H.264, 30fps, AAC audio
- ~6 MB — por debajo del límite de 4 GB de Meta
- Cumple con los requisitos de Meta Business Suite

## Estructura del video (idéntica en las 3 variantes)
- **0-3s:** rostro mujer en primer plano (hook emocional)
- **3-9s:** aplicación Timexpert con cintillo "CAPUCCINI RADIANCE C+" visible (branding)
- **9-12s:** masaje facial íntimo
- **12-14s:** logo Novavita en pantalla (cierre branded)

**Overlays cronológicamente:**
- 0-14s: Encabezado (arriba, siempre)
- 3.5-11s: Cuerpo (centro)
- 9-14s: CTA (abajo, acento marrón)

---

## Checklist pre-lanzamiento (Matías)

- [ ] Verificar que Pixel Meta + API Conversiones está activo en novavita.cl
- [ ] Crear audiencia custom "Excluir compradores Shopify" (subir customer list)
- [ ] Crear audiencia custom "Excluir seguidores actuales @novavita"
- [ ] Subir los 3 MP4 a Meta Business Suite
- [ ] Configurar campaña ABO con 3 ad sets idénticos (solo cambia creativo)
- [ ] Link del CTA: perfil de @novavita (https://www.instagram.com/novavita / o link bio)
- [ ] Activar campaña y esperar 72h antes de tocar

---

## Pendiente crítico

**Si el Pixel Meta + API Conversiones no está verificado aún** (estaba pendiente en la ficha), la Capa 2 no podrá optimizar por conversiones. Eso no bloquea el Test A/B de Capa 1 (que va por Tráfico), pero lo urge para la segunda fase.

— Vale
