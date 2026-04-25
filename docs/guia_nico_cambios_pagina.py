from fpdf import FPDF
import os

# Paths
LOGO_NOVAVITA = '/home/coder/clientes/novavita/assets/novavita-logo-black.png'
LOGO_FACAND = '/home/coder/facand/logofacand.png'
FONT_DIR = '/home/coder/clientes/novavita/assets/fonts'
OUTPUT = '/home/coder/clientes/novavita/docs/guia_nico_cambios_pagina.pdf'

# Brand colors
C_PRIMARY = (45, 45, 45)
C_ACCENT = (139, 115, 85)
C_LIGHT_BG = (248, 246, 243)
C_TABLE_HEAD = (45, 45, 45)
C_TABLE_HEAD_TXT = (255, 255, 255)
C_TABLE_ALT = (248, 246, 243)
C_TEXT = (50, 50, 50)
C_SUBTEXT = (100, 100, 100)
C_LINE = (200, 195, 188)
C_RED = (180, 60, 60)
C_GREEN = (60, 140, 80)


class PDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        try:
            self.image(LOGO_NOVAVITA, 14, 6, 32)
        except Exception:
            pass
        try:
            self.image(LOGO_FACAND, 176, 5, 10)
        except Exception:
            pass
        self.set_font('Poppins', '', 7)
        self.set_text_color(*C_SUBTEXT)
        self.set_y(10)
        self.cell(0, 4, 'Guía de Cambios — Página Novavita  |  Abril 2026', align='C')
        self.ln(4)
        self.set_draw_color(*C_LINE)
        self.line(14, self.get_y(), 196, self.get_y())
        self.ln(5)

    def footer(self):
        self.set_y(-12)
        self.set_font('Poppins', '', 6.5)
        self.set_text_color(*C_SUBTEXT)
        if self.page_no() == 1:
            self.cell(0, 4, 'Documento interno  |  Facand', align='C')
        else:
            self.cell(0, 4, f'Novavita  |  Facand  |  Página {self.page_no()}/{{nb}}', align='C')


pdf = PDF('P', 'mm', 'A4')
pdf.alias_nb_pages()
pdf.set_auto_page_break(auto=True, margin=18)
pdf.set_margins(16, 14, 16)

for style_name, fname in [('', 'Poppins-Regular.ttf'),
                           ('B', 'Poppins-Bold.ttf'),
                           ('I', 'Poppins-Light.ttf'),
                           ('BI', 'Poppins-SemiBold.ttf')]:
    fpath = os.path.join(FONT_DIR, fname)
    if os.path.exists(fpath):
        pdf.add_font('Poppins', style_name, fpath, uni=True)

pdf.add_font('PoppinsSB', '', os.path.join(FONT_DIR, 'Poppins-SemiBold.ttf'), uni=True)
pdf.add_font('PoppinsMed', '', os.path.join(FONT_DIR, 'Poppins-Medium.ttf'), uni=True)


def safe(t):
    return t

def h1(t):
    pdf.ln(3)
    pdf.set_font('Poppins', 'B', 13)
    pdf.set_text_color(*C_PRIMARY)
    pdf.cell(0, 7, safe(t))
    pdf.ln(3)
    pdf.set_draw_color(*C_ACCENT)
    pdf.set_line_width(0.6)
    pdf.line(16, pdf.get_y(), 80, pdf.get_y())
    pdf.set_line_width(0.2)
    pdf.ln(5)

def h2(t):
    pdf.set_font('PoppinsSB', '', 10)
    pdf.set_text_color(*C_PRIMARY)
    pdf.ln(2)
    pdf.cell(0, 5, safe(t))
    pdf.ln(6)

def h3(t):
    pdf.set_font('PoppinsMed', '', 8.5)
    pdf.set_text_color(60, 60, 60)
    pdf.cell(0, 4.5, safe(t))
    pdf.ln(5)

def p(t):
    pdf.set_font('Poppins', '', 8)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(0, 4.2, safe(t))
    pdf.ln(1.5)

def bullet(t, indent=20):
    pdf.set_x(indent)
    pdf.set_font('Poppins', 'B', 7.5)
    pdf.set_text_color(*C_TEXT)
    pdf.cell(4, 4, chr(8226), ln=0)
    pdf.set_font('Poppins', '', 7.5)
    pdf.multi_cell(0, 4, safe(t))
    pdf.ln(0.8)

def bold_bullet(bold, rest, indent=20):
    pdf.set_x(indent)
    pdf.set_font('Poppins', 'B', 7.5)
    pdf.set_text_color(*C_TEXT)
    pdf.cell(4, 4, chr(8226), ln=0)
    w = pdf.get_string_width(safe(bold))
    pdf.cell(w, 4, safe(bold))
    pdf.set_font('Poppins', '', 7.5)
    pdf.multi_cell(0, 4, safe(rest))
    pdf.ln(0.8)

def note(t):
    y_start = pdf.get_y()
    pdf.set_font('Poppins', 'I', 7)
    pdf.set_text_color(*C_SUBTEXT)
    pdf.set_x(18)
    w = pdf.w - pdf.r_margin - 18
    pdf.multi_cell(w, 3.5, safe(t))
    y_end = pdf.get_y()
    pdf.set_draw_color(*C_ACCENT)
    pdf.set_line_width(0.5)
    pdf.line(16, y_start, 16, y_end)
    pdf.set_line_width(0.2)
    pdf.ln(2)

def warning(t):
    y_start = pdf.get_y()
    pdf.set_font('Poppins', 'B', 7.5)
    pdf.set_text_color(*C_RED)
    pdf.set_x(18)
    w = pdf.w - pdf.r_margin - 18
    pdf.multi_cell(w, 3.8, safe(t))
    y_end = pdf.get_y()
    pdf.set_draw_color(*C_RED)
    pdf.set_line_width(0.5)
    pdf.line(16, y_start, 16, y_end)
    pdf.set_line_width(0.2)
    pdf.ln(2)

def ok_note(t):
    y_start = pdf.get_y()
    pdf.set_font('Poppins', '', 7.5)
    pdf.set_text_color(*C_GREEN)
    pdf.set_x(18)
    w = pdf.w - pdf.r_margin - 18
    pdf.multi_cell(w, 3.8, safe(t))
    y_end = pdf.get_y()
    pdf.set_draw_color(*C_GREEN)
    pdf.set_line_width(0.5)
    pdf.line(16, y_start, 16, y_end)
    pdf.set_line_width(0.2)
    pdf.ln(2)

def table_header(cols, widths):
    pdf.set_font('PoppinsSB', '', 7)
    pdf.set_fill_color(*C_TABLE_HEAD)
    pdf.set_text_color(*C_TABLE_HEAD_TXT)
    pdf.set_draw_color(*C_LINE)
    for i, col in enumerate(cols):
        pdf.cell(widths[i], 6, safe(col), border=0, fill=True, align='C')
    pdf.ln()

def table_row(vals, widths, bold_first=False, alt=False):
    pdf.set_text_color(*C_TEXT)
    pdf.set_draw_color(*C_LINE)
    if alt:
        pdf.set_fill_color(*C_TABLE_ALT)
    else:
        pdf.set_fill_color(255, 255, 255)
    x_start = pdf.get_x()
    y_start = pdf.get_y()
    max_h = 5.5
    for i, val in enumerate(vals):
        pdf.set_font('Poppins', 'B' if (i == 0 and bold_first) else '', 6.8)
        w = widths[i] - 1
        lines = pdf.multi_cell(w, 4, safe(val), dry_run=True, output='LINES')
        h = len(lines) * 4 + 1.5
        if h > max_h:
            max_h = h
    for i, val in enumerate(vals):
        pdf.set_font('Poppins', 'B' if (i == 0 and bold_first) else '', 6.8)
        x = x_start + sum(widths[:i])
        pdf.set_xy(x, y_start)
        pdf.rect(x, y_start, widths[i], max_h, style='F')
        pdf.set_draw_color(*C_LINE)
        pdf.line(x, y_start + max_h, x + widths[i], y_start + max_h)
        pdf.set_xy(x + 0.5, y_start + 0.5)
        pdf.multi_cell(widths[i] - 1, 4, safe(val))
    pdf.set_xy(x_start, y_start + max_h)

def step_number(num, title):
    pdf.set_font('Poppins', 'B', 9.5)
    pdf.set_text_color(*C_ACCENT)
    pdf.cell(8, 5, str(num))
    pdf.set_text_color(*C_PRIMARY)
    pdf.set_font('PoppinsSB', '', 9.5)
    pdf.cell(0, 5, safe(title))
    pdf.ln(6)


# ====================== PORTADA ======================
pdf.add_page()

pdf.set_draw_color(*C_ACCENT)
pdf.set_line_width(1.5)
pdf.line(0, 0, 210, 0)
pdf.set_line_width(0.2)

try:
    pdf.image(LOGO_NOVAVITA, 55, 35, 100)
except Exception:
    pass

pdf.ln(55)

pdf.set_font('Poppins', 'B', 22)
pdf.set_text_color(*C_PRIMARY)
pdf.cell(0, 10, safe('Guía de Cambios'), align='C')
pdf.ln(10)
pdf.set_font('PoppinsMed', '', 12)
pdf.set_text_color(*C_ACCENT)
pdf.cell(0, 6, safe('Reestructuración de Página Web'), align='C')
pdf.ln(8)

pdf.set_draw_color(*C_ACCENT)
pdf.set_line_width(0.5)
pdf.line(70, pdf.get_y(), 140, pdf.get_y())
pdf.set_line_width(0.2)
pdf.ln(8)

pdf.set_font('Poppins', '', 9)
pdf.set_text_color(*C_SUBTEXT)
pdf.cell(0, 5, safe('Para: Nico (desarrollo)'), align='C')
pdf.ln(5)
pdf.cell(0, 5, safe('Fecha: 22 abril 2026'), align='C')
pdf.ln(5)
pdf.cell(0, 5, safe('Basado en reunión con Nelson (Novavita) del 21 abril'), align='C')
pdf.ln(15)

try:
    pdf.image(LOGO_FACAND, 95, pdf.get_y(), 20)
except Exception:
    pass


# ====================== CONTEXTO ======================
pdf.add_page()

h1('Contexto')

p('Esta guía detalla los cambios que se deben realizar en la página de Novavita (novavita.cl) en Shopify. Los cambios se basan en la reunión del 21 de abril con Nelson (dueño y cosmetólogo de Novavita), donde se definió la estructura ideal de servicios.')

p('Todos los cambios deben hacerse en la COPIA del tema (no en el tema activo). Una vez revisados y aprobados por Nelson, se publica la copia.')

warning('REGLA CLAVE: Todo cambio debe quedar editable por Nelson desde el editor visual de Shopify. No hacer cambios que solo sean posibles editando código. Si Nelson no puede mover un bloque o cambiar un texto desde el editor, el cambio no sirve.')

note('Tema activo: Fabric (ID 141428293737). Tema de trabajo/copia: "Copia de Fabric" (ID 141742669929). SIEMPRE trabajar en la copia.')


# ====================== ESTRUCTURA ======================
h1('1. Estructura General de la Página')

h2('Menú de navegación (header)')

p('El menú actual tiene links rotos (404). Reestructurar así:')

w = [55, 55, 68]
table_header(['Item menú', 'Link', 'Notas'], w)
table_row(['Depilación Láser', '/pages/depilacion-laser', 'Mantener tal cual'], w)
table_row(['  > Mujer', '/pages/depilacion-laser-mujer-curico', 'Sub-item, mantener'], w, alt=True)
table_row(['  > Hombre', '/pages/depilacion-laser-hombres-curico', 'Sub-item, mantener'], w)
table_row(['Cuidado Facial', '/collections/cosmetologia-curico', 'NUEVO — reemplaza "Facial" roto'], w, alt=True)
table_row(['Spa y Masajes', '/collections/servicios-spa', 'Renombrar "Corporal" a esto'], w)
table_row(['Packs', '/collections/packs', 'Renombrar "Feliz día Mamá" a esto'], w, alt=True)
table_row(['Skin Center', '/collections/skin-center-dermocosmetica', 'Mantener'], w)
table_row(['Tarjetas de Regalo', '/products/gift-card', 'Mantener'], w, alt=True)

pdf.ln(2)
warning('ELIMINAR del menú: "Packs Mujer" (404), "Packs Hombre" (404), "Facial" viejo (/pages/copia-de-cuidado-facial = 404), sub-items Corporal (Endybody, Bodyter, Scizer = todos 404).')

p('Cómo hacerlo: Shopify Admin > Tienda online > Navegación > Menú principal. Eliminar items con 404, renombrar los indicados, agregar "Cuidado Facial" nuevo.')


# ====================== HOME ======================
pdf.add_page()
h1('2. Home Page')

h2('2.1 Slider/Carrusel de banners')

p('Actualmente el home tiene un solo banner estático. Necesitamos un slider rotativo con 3-4 banners que cambien cada 4-5 segundos.')

h3('Banners necesarios (en orden):')
bold_bullet('Banner 1 — Flash Sale: ', 'Depilación láser con código de descuento y cuenta atrás (vence 30 abril). Link: /collections/depilacion-laser-mujer ó la colección que Nelson defina. Nelson va a crear este banner.')
bold_bullet('Banner 2 — Temporada Facial: ', '"Es temporada de cuidar tu piel". Link: /collections/cosmetologia-curico. Imagen: usar foto de facial de los assets.')
bold_bullet('Banner 3 — Día de la Madre: ', 'Cuando Nelson suba el material del 25 abril. Link: /collections/feliz-dia-mama.')
bold_bullet('Banner 4 (opcional): ', 'Packs destacados o lo que Nelson quiera promocionar.')

h3('Implementación')
p('El tema Fabric tiene la sección "Slideshow" disponible. En el editor del tema: Home > Agregar sección > Slideshow. Configurar auto-play cada 4-5 segundos. Cada slide tiene: imagen, texto overlay (título + subtítulo) y botón con link.')

warning('Si el tema no tiene sección slideshow nativa, usar la sección "Image with text overlay" repetida con un app gratuita de slider, o crear la sección via código (última opción). Priorizar siempre que Nelson pueda editar desde el admin.')

h2('2.2 Cuenta atrás con código de descuento')

p('Nelson quiere una cuenta atrás visible cuando haya una promoción con código. Opciones:')

bullet('Opción A (preferida): Usar una app gratuita de countdown (ej: "Essential Countdown Timer Bar" o similar). Se configura desde Shopify Admin, no requiere código.')
bullet('Opción B: Agregar HTML/Liquid en una sección custom con countdown JS. Solo si no hay app adecuada.')

note('El código de descuento lo crea Nelson desde Shopify Admin > Descuentos. La cuenta atrás solo muestra la fecha de vencimiento y el código visualmente.')

h2('2.3 Accesos rápidos bajo el slider')

p('Debajo del slider, agregar botones/iconos de acceso rápido a las 3 secciones principales:')

bullet('Cuidado Facial > /collections/cosmetologia-curico')
bullet('Depilación Láser > /pages/depilacion-laser')
bullet('Spa y Masajes > /collections/servicios-spa')

p('Usar la sección "Multicolumn" del tema con 3 columnas, cada una con ícono/imagen + título + link. Nelson puede cambiar los íconos y textos desde el editor.')


# ====================== CUIDADO FACIAL ======================
pdf.add_page()
h1('3. Sección: Cuidado Facial')

p('Esta es la sección más compleja. Nelson fue muy claro en el orden y la lógica de los servicios. Hay un template custom ya creado (collection.cuidado-facial-v2) que puede servir de base.')

note('Template existente: collection.cuidado-facial-v2.json aplicado a la colección Tratamientos Faciales (ID 308675641449, handle: cosmetologia-curico). URL: novavita.cl/collections/cosmetologia-curico. Verificar su estado actual antes de modificar.')

h2('3.1 Orden de secciones (de arriba a abajo)')

step_number(1, 'Limpiezas Faciales — LO PRIMERO QUE SE VE')
p('Las 4 limpiezas faciales son el caballito de batalla. Deben aparecer destacadas arriba de todo:')
bullet('Limpieza Facial Profunda (hidrodermabrasión) — la más vendida')
bullet('Balancing Expert — para acné')
bullet('Sou Delicate — piel sensible / rosácea')
bullet('Pieles Maduras — pieles con arrugas')

ok_note('Usar sección product-list vinculada a la sub-colección "Limpiezas Faciales" (ID 308675346537, handle: facial). Mostrar las 4 como tarjetas destacadas.')

step_number(2, 'Primera Consulta — DESTACADO APARTE')
p('No va dentro de las 4 limpiezas. Es un producto especial para gente que nunca ha ido a la clínica, a precio más bajo. Debe aparecer como "producto destacado" debajo de las limpiezas, idealmente con una sección tipo "Featured product" con descripción al lado.')

note('Nelson lo describía como: "Aquí es donde todo comienza". Es la puerta de entrada para nuevos clientes. Puede ir al lado de un video explicativo si se tiene.')

step_number(3, 'Tratamientos Faciales — SECCIÓN INFERIOR')
p('Estos son tratamientos que se compran después de una limpieza facial. Están publicados para transparentar precios, pero la venta es principalmente presencial. Ordenar por tipo:')

w2 = [40, 40, 98]
table_header(['Categoría', 'Productos', 'Notas'], w2)
table_row(['Peelings', 'BioRepil', 'Solo 5 clínicas en Chile. DESTACAR.'], w2)
table_row(['Mesoterapia', 'Exosomas', 'Buen margen, potenciar.'], w2, alt=True)
table_row(['Microagujas', 'Dermapen World (4 productos)', 'Diferenciador vs Doctor Pen.'], w2)
table_row(['Antioxidantes', 'Timexpert Radiance C+', 'RENOMBRAR a "Tratamiento Antioxidante Radiance" o similar.'], w2, alt=True)
table_row(['Radiofrecuencia', 'Endimet facial', 'Publicar si no está publicado.'], w2)
table_row(['LIQUIDAR', 'Vitamina Fusión', 'Hacer promo agresiva y luego ELIMINAR.'], w2, alt=True)
table_row(['ELIMINAR', 'Peeling Enzimático', 'Descontinuado, ya no existe en Chile.'], w2)

pdf.ln(2)
warning('IMPORTANTE: El Peeling Enzimático ya no se vende — archivar el producto. Vitamina Fusión se va a liquidar con promo especial en mayo y luego también se archiva.')


# ====================== SPA Y MASAJES ======================
pdf.add_page()
h1('4. Sección: Spa y Masajes')

p('Nelson fue enfático: estos servicios NO son higiénicos (no son limpiezas). Son de relajación y spa. No mezclar con cuidado facial.')

h2('Productos que van en esta sección')

w3 = [50, 22, 106]
table_header(['Producto', 'Precio', 'Nota importante'], w3)
table_row(['Yoga Facial', '$58.000', 'El MÁS RENTABLE. Posicionar como SPA, no como facial higiénico. Enfoque: tensión cuello/mandíbula, relajación.'], w3, bold_first=True)
table_row(['Ronaland Lift', '$---', 'Masaje facial relajante, misma categoría que Yoga Facial.'], w3, alt=True)
table_row(['Limpieza Facial + Masaje', '$95.000', 'NO DEJAR PERMANENTE. Activar solo en fechas clave (Día de la Madre, Día de la Mujer, etc.). Nelson lo pide así.'], w3, bold_first=True)
table_row(['Masajes corporales', '$---', 'Los masajes de relajación que ya existen.'], w3, alt=True)

pdf.ln(2)
warning('El Yoga Facial y Ronaland Lift estaban antes dentro de "Cuidado Facial". Nelson los quiere en Spa porque NO son higiénicos — son masajes faciales de relajación. Moverlos a la colección servicios-spa.')

note('La Limpieza Facial + Masaje ($95.000) se activa/desactiva desde Shopify Admin (publicar/archivar producto) según lo que Nelson pida. No dejarla siempre visible.')


# ====================== DEPILACION ======================
h1('5. Sección: Depilación Láser')

h2('5.1 Página informativa')

p('Ya existe una página informativa sobre Soprano Titanium. Necesita mejoras:')

bullet('Agregar tabla comparativa: Soprano Titanium vs Diodo genérico vs Alexandrita vs IPL. Nelson va a pasar los datos.')
bullet('Agregar datos de Alma: 60+ patentes, 38 tecnologías, 18 premios mundiales.')
bullet('Cambiar cualquier texto que diga "indoloro" por "cómodo". Nelson fue claro: duele un poco, no decir que no duele.')
bullet('Agregar videos cuando estén listos (YouTube Shorts insertados).')

h2('5.2 Catálogo Mujer y Hombre')

p('La estructura de Nelson por trenes corporales es la correcta. El orden ya existe, solo hay que verificar:')

bold_bullet('Mujer: ', 'Packs primero > Rostro > Tren Superior > Tren Inferior. Cada sección con todos sus productos visibles.')
bold_bullet('Hombre: ', 'Misma estructura. NUNCA mezclar con catálogo mujer.')

h3('Filtros y navegación')
bullet('Opción ideal: filtros por sección (Packs, Rostro, Tren Superior, etc.) en la parte superior de la colección.')
bullet('Si no se puede con filtros nativos, usar los botones que ya existían (banner superior con links a sub-colecciones).')
bullet('Agregar botón flotante "volver arriba" para no tener que hacer scroll largo.')

warning('Nelson pide que SIEMPRE se vea el catálogo completo de una sección. Nada de "ver más" que oculte productos. Si entro a Rostro, tengo que ver TODO rostro.')


# ====================== FICHAS DE PRODUCTO ======================
pdf.add_page()
h1('6. Fichas de Producto (todas)')

p('Cambios que aplican a TODAS las fichas de producto:')

h2('6.1 Precio más grande')
p('El precio actual se pierde visualmente. Hacerlo más grande y prominente. Si requiere cambio de CSS en el tema, hacerlo en el bloque de precio del template de producto.')

h2('6.2 Cuotas sin interés')
p('"3 a 6 cuotas sin interés con Mercado Pago" debe aparecer visible debajo del precio en TODAS las fichas. Nelson ya lo movió arriba en algunos productos, pero hay que verificar que esté en todos.')

note('Nelson lo mueve manualmente en la descripción del producto. Idealmente crear un bloque o metafield que lo muestre automáticamente en todas las fichas sin que Nelson tenga que ponerlo producto por producto.')

h2('6.3 Videos insertados (YouTube Shorts)')
p('Cuando Nelson grabe los videos y los suba a YouTube como Shorts, insertar el video en la ficha del producto correspondiente. Shopify permite insertar video de YouTube en la galería del producto (no solo fotos).')

h3('Cómo insertar:')
bullet('Shopify Admin > Productos > [producto] > Multimedia > "Agregar desde URL"')
bullet('Pegar la URL del YouTube Short')
bullet('El video aparece en la galería junto a las fotos')

ok_note('Esto lo puede hacer Nelson solo. Pero si podemos agregar un bloque de "Video explicativo" debajo de la galería que se alimente de un metafield, es mejor UX.')


# ====================== YOUTUBE ======================
h1('7. Canal YouTube + Shorts')

p('Los videos de Instagram de Novavita se van a subir a YouTube como Shorts. Esto sirve para dos cosas:')

bullet('SEO: YouTube Shorts indexan en Google, más visibilidad.')
bullet('Fichas de producto: se insertan en Shopify desde la URL de YouTube.')

h2('Proceso')
step_number(1, 'Acceder al canal YouTube de Novavita (Nelson envía credenciales)')
step_number(2, 'Descargar videos de Instagram que no tengan música con copyright')
step_number(3, 'Si tienen música IG, reemplazar con audio de YouTube Audio Library')
step_number(4, 'Subir como Short (vertical, menos de 60 seg)')
step_number(5, 'Título descriptivo: "Limpieza Facial Profunda — Novavita Curicó"')
step_number(6, 'Insertar URL del Short en la ficha del producto en Shopify')

warning('Videos con música de Instagram van a tener strike de copyright en YouTube. SIEMPRE verificar antes de subir. Si tiene música, quitar y reemplazar.')


# ====================== TAREAS PRIORIZADAS ======================
pdf.add_page()
h1('8. Lista de Tareas Priorizada')

h2('URGENTE — Hacer hoy/mañana (22-23 abril)')

w4 = [8, 80, 90]
table_header(['#', 'Tarea', 'Detalle'], w4)
table_row(['1', 'Corregir menú de navegación', 'Eliminar links 404, reestructurar según tabla de sección 1'], w4, bold_first=True)
table_row(['2', 'Implementar slider en home', 'Agregar sección Slideshow con al menos 2 banners. Nelson sube el de Flash Sale.'], w4, alt=True, bold_first=True)
table_row(['3', 'Cuenta atrás con código', 'Instalar app countdown o crear barra superior con código de descuento visible.'], w4, bold_first=True)
table_row(['4', 'Accesos rápidos bajo slider', '3 botones: Facial, Depilación, Spa. Sección Multicolumn.'], w4, alt=True, bold_first=True)

h2('ESTA SEMANA (24-27 abril)')

table_header(['#', 'Tarea', 'Detalle'], w4)
table_row(['5', 'Reorganizar Cuidado Facial', 'Verificar template cuidado-facial-v2. Orden: 4 limpiezas > Primera Consulta > Tratamientos.'], w4, bold_first=True)
table_row(['6', 'Mover Yoga Facial y Ronaland a Spa', 'Sacarlos de Cuidado Facial, agregarlos a colección servicios-spa.'], w4, alt=True, bold_first=True)
table_row(['7', 'Archivar Peeling Enzimático', 'Producto descontinuado. Admin > Productos > Archivar.'], w4, bold_first=True)
table_row(['8', 'Renombrar Vitamina C', 'Cambiar "Time Expert Radiance C" a "Tratamiento Antioxidante Radiance" o similar.'], w4, alt=True, bold_first=True)
table_row(['9', 'Precio más grande en fichas', 'Ajustar CSS del bloque de precio en template de producto.'], w4, bold_first=True)

h2('PRÓXIMA SEMANA (28 abril - 4 mayo)')

table_header(['#', 'Tarea', 'Detalle'], w4)
table_row(['10', 'Subir videos a YouTube Shorts', 'Cuando Nelson dé acceso al canal. Empezar con videos sin copyright.'], w4, bold_first=True)
table_row(['11', 'Insertar videos en fichas', 'Agregar YouTube Shorts en la galería de cada producto correspondiente.'], w4, alt=True, bold_first=True)
table_row(['12', 'Verificar "cuotas sin interés"', 'Revisar que aparezca en TODAS las fichas, no solo en algunas.'], w4, bold_first=True)
table_row(['13', 'Mejoras depilación láser', 'Tabla comparativa, datos Alma, cambiar "indoloro" por "cómodo".'], w4, alt=True, bold_first=True)
table_row(['14', 'Botón flotante "volver arriba"', 'En páginas de catálogo largo (depilación mujer/hombre).'], w4, bold_first=True)

h2('CUANDO NELSON LO PIDA')

table_header(['#', 'Tarea', 'Detalle'], w4)
table_row(['15', 'Activar/desactivar Limpieza+Masaje', 'Solo publicar cuando Nelson lo autorice (fechas especiales).'], w4, bold_first=True)
table_row(['16', 'Liquidación Vitamina Fusión', 'Crear combo promo + publicar + archivar después. Mayo.'], w4, alt=True, bold_first=True)
table_row(['17', 'Publicar Endimet corporal', 'Crear ficha de servicio corporal cuando Nelson confirme datos.'], w4, bold_first=True)


# ====================== REGLAS ======================
pdf.add_page()
h1('9. Reglas Generales')

h2('Lo que SIEMPRE hay que hacer')
bullet('Trabajar en la COPIA del tema, nunca en el activo.')
bullet('Antes de cualquier cambio de código, hacer backup del archivo que vas a modificar.')
bullet('Probar en mobile primero (Nelson diseña pensando en teléfono).')
bullet('Verificar que Nelson puede editar el cambio desde el editor visual de Shopify.')
bullet('Avisar en el grupo de WhatsApp cuando hagas un cambio relevante.')

h2('Lo que NUNCA hay que hacer')
bullet('Mezclar catálogos: hombre/mujer, facial/spa, higiénico/tratamiento.')
bullet('Ocultar productos detrás de "ver más" — el catálogo completo siempre visible.')
bullet('Decir "indoloro" en ningún texto de depilación — siempre "cómodo".')
bullet('Eliminar productos sin archivarlos primero (siempre archivar, nunca borrar).')
bullet('Editar el tema activo directamente.')
bullet('Subir videos con música de Instagram a YouTube sin verificar copyright.')

h2('Accesos necesarios')
bullet('Shopify Admin (pedir a Mati)')
bullet('YouTube Novavita (pedir credenciales a Nelson)')
bullet('Carpeta Drive de Novavita (pedir link a Nelson)')

pdf.ln(6)
pdf.set_draw_color(*C_LINE)
pdf.line(50, pdf.get_y(), 160, pdf.get_y())
pdf.ln(5)
pdf.set_font('Poppins', 'I', 8)
pdf.set_text_color(*C_SUBTEXT)
pdf.cell(0, 4, safe('Cualquier duda, preguntar a Mati o Fabi antes de hacer cambios.'), align='C')
pdf.ln(4)
pdf.cell(0, 4, safe('Documento preparado por Facand — Abril 2026'), align='C')

pdf.output(OUTPUT)
print(f'PDF generado: {OUTPUT}')
