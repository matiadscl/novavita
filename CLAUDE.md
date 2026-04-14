# NovaVita - Spa y Depilaciones

## Cliente
SpA de masajes y depilaciones en Curico. Dueños: Daniela (Dani) y Nelson.

## Servicios Facand
Dashboard de marketing, Google Ads, Meta Ads, integracion Shopify, reportes PDF.

## APIs y tracking

| Recurso | Valor |
|---------|-------|
| GA4 | `G-DCZ0V2TELX` |
| GTM | `GTM-K6ZXX5LT` |
| Google Ads | `AW-17404864667` |
| Shopify | `novavita.cl` |
| Dashboard | `https://mediumturquoise-rabbit-484065.hostingersite.com/` |
| Login | `novavita / novavita2026` |

## Presupuesto campañas
- **Total:** $650k/mes (Google $500k + Meta $150k)
- Google es **10x mas eficiente** en CPA que Meta.

## Estructura de carpetas

```
novavita/
├── dashboard/                  # PHP+JS — Hostinger auto-deploy
│   ├── includes/
│   │   ├── config.php
│   │   ├── api_guard.php       # Proteccion de APIs
│   │   ├── meta_api.php
│   │   ├── credentials.php
│   │   └── http.php
│   ├── downloads/              # APIs de descarga
│   ├── assets/
│   ├── css/
│   ├── js/
│   ├── cache/
│   ├── login.php
│   ├── index.php
│   └── shopify_callback.php
├── scripts/
│   ├── generate_pdfs.py
│   └── generate_resumen_v2.py
├── downloads/                  # APIs auxiliares
├── docs/
├── assets/
└── sesiones/
```

## Deploy
- Repo GitHub: `matiadscl/novavita`
- Auto-deploy via webhook Hostinger al hacer push.

## Equipo y permisos

| Persona | Acceso |
|---------|--------|
| mati | Todo |
| fabi | Marketing, datos (con restriccion) |
| nico | Dashboard dev |

## Reglas

1. **CRITICO: NUNCA exponer trabajo previo de Fabi en informes a dueños.** No mencionar tracking roto, errores de configuracion anterior, ni nada que evidencie problemas previos.
2. Las APIs estan protegidas con `api_guard.php` — no bypasear.
3. Credenciales en `credentials.php` — nunca commitear valores en claro.
4. Al modificar el dashboard, hacer push a GitHub para trigger auto-deploy.
