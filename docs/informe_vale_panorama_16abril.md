# Informe Ejecutivo Novavita — Panorama General
**Autora:** Vale (Facand) · **Fecha:** 16 de abril 2026 · **Cliente:** Novavita (Curico)

---

## 1. Diagnostico rapido

### ¿Por que no hay conversiones nuevas?

Porque las campanas nuevas tienen **menos de 24 horas activas**. Es fisicamente imposible tener conversiones aun.

**Contexto de las campanas activas hoy:**

| Campana | Estado | Creada | Spend 7d | Objetivo |
|---------|--------|--------|----------|----------|
| DiaMama_TOFU_TestScripts | ACTIVE | 16/04 (hoy) | $1.913 | Trafico (perfil IG) |
| TOFU_Abr2026 | ACTIVE | 16/04 (hoy) | $1.530 | Trafico |
| DiaMama_TOFU_TestScripts_Abr2026 | ACTIVE | 16/04 (hoy) | $186 | Trafico |

Las tres fueron creadas hoy. Es dia 0. No hay base para evaluar conversiones.

**Mientras tanto, el Testeo ABO (que SI generaba compras) estuvo activo hasta hace poco:**
- Ultimos 7 dias: $62.092 gastados, 3 compras (CPA $20.697), 22 add to cart, 16 checkouts
- Ultimos 30 dias: $291.094 gastados, 27 compras (CPA $10.781), 169 add to cart, 77 checkouts

El Testeo ABO fue pausado como parte de la redistribucion de presupuesto (Meta bajo de $1.1M a $150K, Google subio a $500K). Esa decision fue correcta dado que Google tiene 10x mejor CPA. Pero significa que Meta quedo sin campana de conversion activa — las nuevas campanas son todas de **trafico**, no de ventas.

### ¿El trafico de Santiago es problema?

Probablemente viene de **PMax de Google Ads**. Las campanas Search (Brand y Servicios Curico) estan geotargeteadas a Curico con "solo presencia", lo cual es correcto. Pero PMax tiene targeting mas amplio por naturaleza y puede mostrar anuncios fuera de Curico, especialmente a traves de Display y YouTube.

**Diagnostico:**
- Las campanas Meta NO son el problema de geo — todas apuntan a Curico + 50km.
- Search Brand puede captar busquedas "novavita" desde Santiago (gente que busca la marca sin estar en Curico), pero eso NO es un gasto significativo en un presupuesto de $2.500/dia.
- **PMax ($9.200/dia = 55% del presupuesto Google) es el sospechoso principal.** A $275K/mes, si una porcion relevante se va a Santiago, es plata perdida.
- Trafico organico/directo desde Santiago tambien puede inflar el numero en GA4 sin ser gasto publicitario.

**Accion requerida:** Revisar en Google Ads el reporte de ubicaciones de PMax (Campanas > PMax > Ubicaciones > Detalles geograficos). Si confirma impresiones fuera de Curico, agregar exclusion de regiones o cambiar a targeting "presencia" solamente.

### ¿Las campanas estan bien configuradas?

**Meta — lo que esta bien:**
- DiaMama_TOFU_TestScripts: estructura ABO correcta, 3 scripts (A/B/C), targeting Curico mujeres, Stories IG. Budget $2.000/dia por ad set. Coherente con el plan de test A/B.
- TOFU_Abr2026: 2 ad sets (Faciales + Depilacion laser), complementa la estrategia.

**Meta — lo que hay que vigilar:**
- Hay 3 campanas activas + una cuarta (Campana de Trafico a Instagram, vieja, con $1.000/dia) que **tambien esta activa** y gasto $2.296 esta semana. Son $2.296 en una campana vieja que probablemente no deberia estar corriendo.
- El Testeo ABO Faciales (ya pausado) tenia frecuencia 2.84 en los ultimos 7 dias con solo 3.755 de reach — audiencia saturandose rapido.

**Google — segun auditoria 11/04:**
- Conversion principal limpia (solo Google Shopping App Purchase). Correcto.
- Search Brand y Servicios correctamente configuradas. Sin IA Max, sin Display. Bien.
- PMax necesita revision de geo (ver arriba).

---

## 2. Senales de alerta

### Preocupante

| Senal | Dato | Por que importa |
|-------|------|-----------------|
| PMax mostrando fuera de Curico | >50% trafico GA4 de Santiago | $275K/mes potencialmente mal geotargeteados |
| Campana vieja activa | "Campana de Trafico a Instagram" gastando $2.296/sem | Fuga de presupuesto sin control |
| Faciales saturado (antes de pausar) | Frecuencia 2.84, reach solo 3.755 | Audiencia chica se agota rapido en Curico |
| Meta sin campana de conversion | Todas las activas son TRAFFIC | No hay campana optimizando por compra actualmente |

### Normal para dia 1

| Senal | Dato | Por que es normal |
|-------|------|-------------------|
| 0 conversiones nuevas campanas | DiaMama tiene $1.913 de spend | Menos de 24h activa, aprendizaje |
| CTR bajo en Stories | 0.51% a 1.12% | Stories tiene CTR naturalmente bajo; el objetivo es profile visit, no clic al sitio |
| CPM alto ($1.000-$1.300) | Campanas recien lanzadas | Meta cobra mas durante learning phase, baja en 3-5 dias |
| Bajo reach (192-559 por ad set) | Primeras horas | Se normaliza en 48-72h |

---

## 3. Acciones inmediatas (proximas 48h)

### Urgente (hoy)

1. **Pausar "Campana de Trafico a Instagram"** (120240350028480435). Es una campana vieja de enero 2026 que sigue activa gastando $330/dia sin proposito estrategico actual.

2. **Revisar geo de PMax en Google Ads.** Ir a la campana PMax > Ubicaciones > "Ver detalles de ubicacion" y verificar si hay impresiones en Santiago/RM. Si las hay, agregar exclusion de region o restringir a Curico + 50km.

### Manana (17 abril)

3. **Verificar que DiaMama_TOFU_TestScripts_Abr2026** (la tercera campana, $186 de spend) no sea un duplicado accidental de DiaMama_TOFU_TestScripts. Si lo es, pausarla para no fragmentar presupuesto.

4. **Confirmar exclusiones de audiencia** en TOFU_Abr2026 antes de escalar. Mati estaba configurandolas — verificar que esten aplicadas (excluir compradores, excluir seguidores).

### No tocar (dejar correr)

- DiaMama_TOFU_TestScripts: NO pausar ni editar nada por 72h minimo. El test A/B necesita datos.
- TOFU_Abr2026: dejar correr, es complementaria.

---

## 4. Plan de lectura

### Cuando esperar primeros resultados

| Fecha | Que mirar | Que esperar |
|-------|-----------|-------------|
| **19 abril (dia 3)** | DiaMama test A/B: impresiones, video views, profile visits por ad set | Identificar si algun script no entrega (0 impresiones). Si un script tiene 3x mas clics, ya gana. |
| **21 abril (dia 5)** | CTR, CPM estabilizado, profile visit rate | Pausar el peor script. CPM deberia bajar de $1.300 a ~$800-900. |
| **23 abril (dia 7)** | Ganador del test A/B, reach acumulado, frecuencia | Elegir ganador. Frecuencia deberia estar bajo 2.0. Si supera 2.5, la audiencia es muy chica. |
| **25 abril** | Google Ads: CPA de Search Brand y Servicios (2 semanas de datos) | Primeras conversiones atribuibles. CPA Search deberia estar bajo $5.000. |
| **30 abril** | Evaluacion general: Google vs Meta, geo resuelto | Decidir si reactivar una campana Meta de conversion (SALES) con el script ganador. |

### Metricas clave por canal

**Meta (campanas activas):**
- Profile Visit Rate (visitas al perfil / impresiones) — es la metrica principal del test
- Frecuencia — si sube de 2.5 antes del dia 7, la audiencia es muy chica
- CPM — deberia estabilizarse bajo $1.000 en 3-5 dias

**Google Ads:**
- CPA por campana (Search Brand vs Servicios vs PMax)
- % de impresiones fuera de Curico en PMax
- Clics en WhatsApp (evento GTM recien configurado)

---

## 5. Resumen de situacion

La cuenta esta en transicion. Se hizo una reestructuracion importante el 11-12 de abril (redistribucion 90% Meta / 10% Google → 23% Meta / 77% Google, limpieza de conversiones rotas, instalacion GA4 + GTM). Las campanas nuevas de Meta tienen horas de vida. Es **demasiado pronto para evaluar rendimiento**.

Los dos temas reales que resolver ahora:
1. **PMax y el trafico de Santiago** — verificar y corregir geo targeting.
2. **Meta no tiene campana de conversion** — cuando el test A/B termine (23 abril), hay que lanzar una campana SALES con el ganador.

Todo lo demas esta dentro de lo esperable para dia 1 de campanas nuevas.

---

Vale
