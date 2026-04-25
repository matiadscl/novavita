# Novavita — Configuración del flujo "Regalar un producto"

**Fecha:** 2026-04-16
**Ámbito:** tema "Copia de Fabric" (141742669929) — staging, NO producción
**Alcance:** botón "Lo quiero como regalo" visible en todos los productos excepto Gift Cards nativas (que mantienen su propio flujo).

## 1. ¿Cómo lo ve el cliente?

En la página de cualquier producto aparece, **sobre el botón "Agregar al carrito"**:

> 🎁 **Lo quiero como regalo**

Al hacer click se abre un modal con:

- Nombre del destinatario *(obligatorio)*
- Email del destinatario *(obligatorio)*
- Tu nombre, quien regala *(obligatorio)*
- Mensaje *(opcional, máx. 300 caracteres)*
- Fecha de envío *(opcional — por defecto se envía al confirmarse el pago)*

Al hacer click en **"🎁 Agregar regalo al carrito"** el producto se agrega al carrito con estos datos pegados como *line item properties* visibles:

- `Regalo para` → María Pérez
- `Email del destinatario` → maria@example.com
- `De parte de` → Carolina González
- `Mensaje` → "Feliz cumpleaños 🎂"
- `Fecha de envío` → 2026-05-10
- `_es_regalo` → `si` *(oculto — flag interno)*

Estos campos aparecen en **carrito, checkout, email de confirmación** al comprador y en la **orden dentro del admin**.

## 2. Entrega del código al destinatario

Shopify no envía nada al destinatario de forma automática para productos normales (solo lo hace con el tipo *gift_card* nativo). Hay **3 caminos**, de más simple a más robusto. Recomiendo **arrancar con A** y migrar a B o C una vez validado el flujo.

---

### Opción A — Manual con aviso interno (día 1, sin dev)

**Configuración en Shopify Admin → Apps → Flow:**

1. **Create workflow** → trigger **Order created**.
2. **Condition:** `for each line item → Line item properties → _es_regalo equals si` (marcar "Include if any line item matches").
3. **Action: Send internal email**
   - To: `contacto@novavita.cl` (y opcional, Mati)
   - Subject: `🎁 Nueva orden de regalo — #{{order.name}}`
   - Body: pegar `plantilla_email_interno.html` (sección 4 de este doc).
4. Save + Turn on.

**Flujo diario:**
Dani/Nelson reciben el correo interno, copian la plantilla cliente (sección 5), reemplazan los datos y la envían manualmente al destinatario desde Gmail/Outlook con asunto "🎁 Tienes un regalo de [de_parte_de] en Novavita".

- **Pro:** cero dev, se activa hoy.
- **Contra:** humano en el loop, no escala si aumentan los pedidos.

---

### Opción B — Shopify Email + Flow "Send HTTP request" (recomendado mediano plazo)

Requiere un endpoint HTTP propio. Se hace con un pequeño script en el VPS de Facand:

1. **Endpoint:** `POST https://<vps>/novavita/regalo_email` (a implementar en el VPS de Facand, ~30 líneas Python + SMTP Gmail o SendGrid).
2. En Flow: trigger `Order created` + condición igual que arriba → **Action "Send HTTP request"** con body JSON:
   ```json
   {
     "order_name": "{{order.name}}",
     "destinatario_email": "{{line_item.properties.Email del destinatario}}",
     "destinatario_nombre": "{{line_item.properties.Regalo para}}",
     "de_parte_de": "{{line_item.properties.De parte de}}",
     "mensaje": "{{line_item.properties.Mensaje}}",
     "fecha_envio": "{{line_item.properties.Fecha de envío}}",
     "producto": "{{line_item.title}}",
     "codigo_canje": "{{order.name}}-{{line_item.id}}"
   }
   ```
3. El script parsea, envía el email al destinatario (plantilla sección 5) y, si hay `fecha_envio`, guarda la tarea en un scheduler para enviarlo ese día.

- **Pro:** automático, dinámico, sin apps de terceros.
- **Contra:** requiere el endpoint (~2h de dev, reutiliza infra Facand).

---

### Opción C — Shopify Email campañas manuales

Usar la app gratuita **Shopify Email** creando una campaña segmentada por orden con line item property. Tiene menos flexibilidad que Flow y no cubre bien el caso de email a destinatario (no a cliente). **No la recomiendo** para este caso.

## 3. Operativo: canje en clínica

La validación de **vigencia (3 meses)** y **transferibilidad** queda del lado operativo:

1. Destinatario escribe por WhatsApp (+56 9 9611 4390) con el código `#{{order.name}}-{{line_item.id}}`.
2. Recepción busca la orden en Shopify Admin.
3. Valida:
   - Fecha de compra ≤ 3 meses → OK. Si > 3 meses → vencido.
   - Si ya se usó parcialmente (ej: 2/6 sesiones en HealthAtom) → NO transferible.
4. Agenda la sesión en HealthAtom manualmente, anotando en observaciones "Regalo orden #{{...}}, comprador: {{de_parte_de}}".

Recomendación: crear tag `regalo` + tag `vence_YYYYMMDD` en la orden desde Flow para filtrar fácilmente las órdenes de regalo próximas a vencer.

## 4. Plantilla email interno (Opción A)

```
Subject: 🎁 Nueva orden de regalo — #{{order.name}}

Hola equipo,

Se acaba de generar una orden que incluye un producto para regalar.

DATOS DE LA ORDEN
-----------------
Orden: {{order.name}}
Fecha: {{order.created_at}}
Comprador: {{order.customer.first_name}} {{order.customer.last_name}} ({{order.email}})

PRODUCTO REGALADO
-----------------
{% for li in order.line_items %}
  {% if li.properties._es_regalo == 'si' %}
  - Producto: {{li.title}}
  - Cantidad: {{li.quantity}}
  - Destinatario: {{li.properties['Regalo para']}}
  - Email destinatario: {{li.properties['Email del destinatario']}}
  - De parte de: {{li.properties['De parte de']}}
  - Mensaje: {{li.properties['Mensaje']}}
  - Fecha de envío solicitada: {{li.properties['Fecha de envío'] | default: 'Al confirmarse el pago'}}
  - Código de canje: {{order.name}}-{{li.id}}
  - Vence: {{order.created_at | date: '%Y-%m-%d' | plus_months: 3}}
  {% endif %}
{% endfor %}

ACCIÓN REQUERIDA
----------------
Copiar la plantilla "email al destinatario" (archivo regalo_configuracion_flow.md, sección 5),
reemplazar los datos y enviar desde contacto@novavita.cl al destinatario en la fecha solicitada.
```

## 5. Plantilla email al destinatario (Opción A manual, o B automática)

```
Subject: 🎁 Tienes un regalo de [DE_PARTE_DE] en Novavita

Hola [NOMBRE_DESTINATARIO],

[DE_PARTE_DE] te regaló un [NOMBRE_PRODUCTO] en Novavita.

[MENSAJE_OPCIONAL — solo incluir si viene con contenido]

Para canjearlo, escríbenos por WhatsApp al +56 9 9611 4390 con este código:

    Código de canje: [ORDER_NAME]-[LINE_ITEM_ID]

y coordinamos tu cita en nuestro local de Curicó.

Términos:
• Vigencia: 3 meses desde la fecha de compra (vence el [FECHA_VENCIMIENTO]).
• Transferible solo como producto completo. Si ya utilizaste una sesión, el saldo
  queda a tu nombre y no se puede transferir.
• Reagendamiento gratuito avisando con 24h de anticipación.

Nos vemos pronto,
Equipo Novavita
novavita.cl
```

## 6. Activación en producción

El botón está vivo en el tema **"Copia de Fabric"** (ID 141742669929). Flujo sugerido:

1. **Probar en staging**: previsualizar el tema copia, hacer un pedido de prueba con el botón, verificar que los line item properties aparezcan en la orden del admin.
2. **Configurar Flow Opción A** con email a `contacto@novavita.cl`.
3. **Publicar el tema**: desde Shopify Admin → Online Store → Themes → "Copia de Fabric" → Actions → Publish.
4. **Monitorear primera semana**: revisar carritos abandonados en modo regalo, validar UX con clientes reales.
5. Iterar si corresponde a Opción B.

## 7. Archivos modificados

- `snippets/gift-toggle.liquid` (nuevo, 9.2KB)
- `blocks/buy-buttons.liquid` (+3 líneas — render del snippet con guard `unless product.gift_card?`)

Backup de los archivos originales en `/home/coder/clientes/novavita/backups/` (nombre con fecha).

## 8. Rollback

Si hay que revertir sin republicar:

```bash
cd /home/coder/clientes/novavita/theme_copia_fabric
# Restaurar buy-buttons.liquid original (sin el render de gift-toggle)
# y eliminar snippets/gift-toggle.liquid del tema
python3 rollback.py
```

(Script de rollback pendiente de crear; mientras tanto, se puede hacer manual desde Shopify Admin → Themes → Edit code → eliminar snippet y revertir buy-buttons.liquid).
