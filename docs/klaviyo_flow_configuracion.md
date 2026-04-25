# Klaviyo — Configuración Flow "Regalo comprado"

**Objetivo:** cuando alguien compra un producto marcando el modal "Lo quiero como regalo", Klaviyo envía automáticamente un email al destinatario con el código de canje e instrucciones.

**Pre-requisito:** la integración Shopify ↔ Klaviyo ya debe estar activa (lo está, ya que Klaviyo fue instalado).

---

## 1. Verificar Sender (From verificado)

Klaviyo no deja enviar desde cualquier dirección, el dominio debe estar verificado.

1. En Klaviyo → **Settings → Domains and senders**.
2. Confirmar que hay al menos un sender verificado (ej: `regalos@novavita.cl` o `contacto@novavita.cl`).
3. Si **no hay**: click **Add sender**, agregar el email, Klaviyo envía un correo de verificación con 3 DNS records (DKIM) para poner en Hostinger. Verificar.

Sin sender verificado, nada funciona.

---

## 2. Crear Email Template

1. Klaviyo → **Content → Email templates → Create template → Code**.
2. Nombre: `Regalo - Destinatario`.
3. En el editor seleccionar pestaña **HTML** (no Drag & Drop).
4. Pegar el contenido completo de:
   `/home/coder/clientes/novavita/docs/klaviyo_regalo_template.html`
5. En **Preview**, usar este JSON de prueba para ver el render (pegarlo en "Test data" o usar un evento real):
   ```json
   {
     "extra": {
       "name": "#TEST-001",
       "line_items": [{
         "id": 99999,
         "title": "Facial Glow",
         "properties": {
           "_es_regalo": "si",
           "Regalo para": "María Pérez",
           "De parte de": "Carolina González",
           "Email del destinatario": "maria@example.com",
           "Mensaje": "Feliz cumpleaños 🎂",
           "Fecha de envío": ""
         }
       }]
     }
   }
   ```
6. Guardar.

---

## 3. Crear Flow

1. Klaviyo → **Flows → Create flow → Create from scratch**.
2. Nombre: `Regalo - Email al destinatario`.
3. **Trigger:** Metric → **Placed Order** (el que viene con la integración Shopify).
4. **Trigger Filter** (muy importante, evita que se dispare con cualquier orden):
   - `Placed Order` → add filter →
   - Dimension: **Items** (o "Line items" según la versión)
   - Where: **Properties._es_regalo** equals `si`
   - Si la UI no muestra esa opción directamente, usar:
     - `Items` → `Properties` → key `_es_regalo` → value `si`

   **Alternativa** si la UI no permite filtrar por property:
   - Dejar el trigger como Placed Order sin filtro.
   - Agregar un **Conditional Split** después del trigger con:
     `event.extra.line_items[*].properties._es_regalo contains "si"` → Yes branch envía email, No branch termina flow.

5. **Action → Email**:
   - Select template: `Regalo - Destinatario`
   - Subject line:
     ```
     🎁 Tienes un regalo de {% for item in event.extra.line_items %}{% if item.properties._es_regalo == 'si' %}{{ item.properties['De parte de'] }}{% break %}{% endif %}{% endfor %} en Novavita
     ```
   - Preview text: `Completa los datos y coordinemos tu cita en Novavita.`
   - From label: `Novavita`
   - From email: el sender verificado del paso 1.

6. **Dynamic send-to email** (campo clave para que el email llegue al destinatario, no al comprador):
   - Dentro de la action Email buscar la opción **"Send to alternate email address"** o **"Dynamic recipient"** (el nombre varía según la versión; suele estar en Email settings → Advanced).
   - Pegar este valor:
     ```
     {% for item in event.extra.line_items %}{% if item.properties._es_regalo == 'si' %}{{ item.properties['Email del destinatario'] }}{% break %}{% endif %}{% endfor %}
     ```

   **Si tu plan de Klaviyo no tiene Dynamic recipient directo**, alternativa:
   - Agregar action previa **Update Profile** con un custom property `email_destinatario_regalo` igual al valor anterior.
   - Y en la action Email usar ese custom property como sender_to.
   - Nota: esto sigue enviando al customer registrado. Si no funciona, ver sección 7 (workaround con Transactional API).

7. **Smart Sending**: desactivar (cada regalo es único, no queremos skip por "ya recibió email").
8. Guardar y **Live** (botón arriba a la derecha).

---

## 4. Probar end-to-end (sin comprar)

**Opción recomendada: Bogus Gateway** (checkout gratis pero real).

1. Shopify Admin → **Settings → Payments → Manage** en el proveedor actual.
2. En "Supported payment methods" scroll abajo → activar **"(for testing) Bogus Gateway"**.
3. En el sitio (tema `new theme giftcard button` preview o ya publicado):
   - Abrir un producto → click **🎁 Lo quiero como regalo**
   - Completar datos (email destinatario = tu propio email para testing)
   - Checkout
   - Tarjeta: número `1`, CVV `111`, exp `12/27`, nombre cualquiera
4. Completar la compra.
5. Esperar 1-2 minutos → Klaviyo → Flow → deberías ver el evento disparado en "Analytics → Trigger events".
6. Llega email al destinatario (tu propio email) con el HTML renderizado.

**Después de testear:** desactivar Bogus Gateway antes de publicar a producción.

---

## 5. Troubleshooting

- **No se dispara el flow:** revisar Klaviyo → Profiles → buscar email del comprador → pestaña "Events" → verificar que aparece "Placed Order" con el property `_es_regalo=si` en el payload. Si no aparece, la integración Shopify-Klaviyo puede estar con delay (hasta 15 min) o no sincronizada.
- **Se dispara pero no envía:** revisar "Flow analytics → skipped" y el motivo. Lo más común: recipient email vacío (property no bien pasada) o smart sending activo.
- **Email llega al comprador en vez del destinatario:** el "Dynamic send-to" no quedó configurado. Volver al paso 3.6.
- **Render roto:** falta verificar sender (paso 1) o template tiene syntax error. Klaviyo preview mostrará el error exacto.

---

## 6. Variables disponibles (referencia rápida)

Dentro del template y del subject, entre `{% ... %}` y `{{ ... }}`:

| Variable | Valor |
|----------|-------|
| `event.extra.name` | Número de orden (ej: `#1542`) |
| `event.extra.line_items` | Array de ítems de la orden |
| `item.title` | Nombre del producto |
| `item.id` | ID del line item |
| `item.properties._es_regalo` | `"si"` si es regalo |
| `item.properties['Regalo para']` | Nombre destinatario |
| `item.properties['Email del destinatario']` | Email destinatario |
| `item.properties['De parte de']` | Nombre de quien regala |
| `item.properties['Mensaje']` | Mensaje del comprador (puede estar vacío) |
| `item.properties['Fecha de envío']` | Fecha opcional |

---

## 7. Workaround si Dynamic recipient NO funciona en el plan actual

Plan B usando Klaviyo Events API + Shopify Flow:

1. En Shopify Admin → **Apps → Flow → Create workflow**.
2. Trigger: **Order paid**.
3. Condition: al menos un line_item con property `_es_regalo` = `si`.
4. Action: **Send HTTP request** a:
   - URL: `https://a.klaviyo.com/api/events/`
   - Method: POST
   - Headers:
     ```
     Authorization: Klaviyo-API-Key <CLAVE_PRIVADA>
     Content-Type: application/json
     revision: 2024-10-15
     ```
   - Body (JSON) iterando los items regalo:
     ```json
     {
       "data": {
         "type": "event",
         "attributes": {
           "properties": {
             "order_name": "{{order.name}}",
             "destinatario_nombre": "{{line_item.properties.Regalo para}}",
             "de_parte_de": "{{line_item.properties.De parte de}}",
             "producto": "{{line_item.title}}",
             "mensaje": "{{line_item.properties.Mensaje}}",
             "codigo_canje": "{{order.name}}-{{line_item.id}}"
           },
           "metric": { "data": { "type": "metric", "attributes": { "name": "Regalo Comprado" } } },
           "profile": { "data": { "type": "profile", "attributes": { "email": "{{line_item.properties.Email del destinatario}}" } } }
         }
       }
     }
     ```

5. En Klaviyo crear un Flow con trigger `Regalo Comprado` (custom metric) → Email (ya se envía al profile que se creó con el email del destinatario).

Este workaround asegura que el email del destinatario queda como recipient nativo (no hay que usar Dynamic send-to).

---

## 8. Resumen de pendientes para Mati

- [ ] Verificar sender en Klaviyo (paso 1)
- [ ] Crear template `Regalo - Destinatario` (paso 2)
- [ ] Crear Flow `Regalo - Email al destinatario` (paso 3)
- [ ] Activar Bogus Gateway y probar (paso 4)
- [ ] Desactivar Bogus Gateway + publicar tema `new theme giftcard button` a producción
