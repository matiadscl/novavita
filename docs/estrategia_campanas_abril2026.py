from fpdf import FPDF
import os

# Paths
LOGO_NOVAVITA = '/home/coder/clientes/novavita/assets/novavita-logo-black.png'
LOGO_FACAND = '/home/coder/facand/logofacand.png'
FONT_DIR = '/home/coder/clientes/novavita/assets/fonts'
OUTPUT = '/home/coder/clientes/novavita/docs/estrategia_campanas_abril2026.pdf'

# Brand colors
C_PRIMARY = (45, 45, 45)        # Dark charcoal
C_ACCENT = (139, 115, 85)       # Warm gold/bronze (spa feel)
C_LIGHT_BG = (248, 246, 243)    # Warm off-white
C_TABLE_HEAD = (45, 45, 45)     # Dark header
C_TABLE_HEAD_TXT = (255, 255, 255)
C_TABLE_ALT = (248, 246, 243)   # Alternating row
C_TEXT = (50, 50, 50)
C_SUBTEXT = (100, 100, 100)
C_LINE = (200, 195, 188)        # Warm gray line


class PDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return  # Cover page has custom header
        # Logos in header
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
        self.cell(0, 4, 'Estrategia de Campanas  |  Abril 2026', align='C')
        self.ln(4)
        self.set_draw_color(*C_LINE)
        self.line(14, self.get_y(), 196, self.get_y())
        self.ln(5)

    def footer(self):
        self.set_y(-12)
        self.set_font('Poppins', '', 6.5)
        self.set_text_color(*C_SUBTEXT)
        if self.page_no() == 1:
            self.cell(0, 4, 'Confidencial  |  Facand', align='C')
        else:
            self.cell(0, 4, f'Novavita  |  Facand  |  Pagina {self.page_no()}/{{nb}}', align='C')


pdf = PDF('P', 'mm', 'A4')
pdf.alias_nb_pages()
pdf.set_auto_page_break(auto=True, margin=18)
pdf.set_margins(16, 14, 16)

# Register Poppins fonts (Unicode support)
for style_name, fname in [('', 'Poppins-Regular.ttf'),
                           ('B', 'Poppins-Bold.ttf'),
                           ('I', 'Poppins-Light.ttf'),
                           ('BI', 'Poppins-SemiBold.ttf')]:
    fpath = os.path.join(FONT_DIR, fname)
    if os.path.exists(fpath):
        pdf.add_font('Poppins', style_name, fpath, uni=True)

# Use SemiBold as a separate family for medium weight
pdf.add_font('PoppinsSB', '', os.path.join(FONT_DIR, 'Poppins-SemiBold.ttf'), uni=True)
pdf.add_font('PoppinsMed', '', os.path.join(FONT_DIR, 'Poppins-Medium.ttf'), uni=True)


# ============================================================
# HELPERS
# ============================================================
def safe(t):
    """Keep text as-is since we use Unicode fonts."""
    return t


def h1(t):
    pdf.ln(3)
    pdf.set_font('Poppins', 'B', 13)
    pdf.set_text_color(*C_PRIMARY)
    pdf.cell(0, 7, safe(t))
    pdf.ln(3)
    # Accent underline
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
    x = pdf.get_x()
    pdf.set_x(indent)
    pdf.set_font('Poppins', '', 7.5)
    pdf.set_text_color(*C_TEXT)
    # Draw bullet dot
    y_before = pdf.get_y()
    pdf.set_font('Poppins', 'B', 7.5)
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
    # Light background box
    y_start = pdf.get_y()
    pdf.set_font('Poppins', 'I', 7)
    pdf.set_text_color(*C_SUBTEXT)
    # Calculate height first
    pdf.set_x(18)
    x = pdf.get_x()
    w = pdf.w - pdf.r_margin - x
    # Draw note with left accent bar
    pdf.set_draw_color(*C_ACCENT)
    pdf.set_line_width(0.5)
    pdf.line(16, y_start, 16, y_start + 4)  # Will extend below
    pdf.set_x(18)
    pdf.multi_cell(w, 3.5, safe(t))
    y_end = pdf.get_y()
    # Extend accent bar to actual height
    pdf.set_draw_color(*C_ACCENT)
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
    """Table row using multi_cell for text wrapping."""
    pdf.set_text_color(*C_TEXT)
    pdf.set_draw_color(*C_LINE)

    if alt:
        pdf.set_fill_color(*C_TABLE_ALT)
    else:
        pdf.set_fill_color(255, 255, 255)

    # Calculate max height needed
    x_start = pdf.get_x()
    y_start = pdf.get_y()
    max_h = 5.5  # minimum row height

    # Pre-calculate heights
    for i, val in enumerate(vals):
        pdf.set_font('Poppins', 'B' if (i == 0 and bold_first) else '', 6.8)
        w = widths[i] - 1  # padding
        # Get number of lines
        lines = pdf.multi_cell(w, 4, safe(val), dry_run=True, output='LINES')
        h = len(lines) * 4 + 1.5
        if h > max_h:
            max_h = h

    # Draw cells with calculated height
    for i, val in enumerate(vals):
        pdf.set_font('Poppins', 'B' if (i == 0 and bold_first) else '', 6.8)
        x = x_start + sum(widths[:i])
        pdf.set_xy(x, y_start)
        # Fill background
        pdf.rect(x, y_start, widths[i], max_h, style='F')
        # Bottom border
        pdf.set_draw_color(*C_LINE)
        pdf.line(x, y_start + max_h, x + widths[i], y_start + max_h)
        # Text
        pdf.set_xy(x + 0.5, y_start + 0.5)
        pdf.multi_cell(widths[i] - 1, 4, safe(val))

    pdf.set_xy(x_start, y_start + max_h)


def campaign_badge(priority, platform, dates):
    pdf.set_font('PoppinsMed', '', 7.5)
    pdf.set_text_color(*C_ACCENT)
    pdf.cell(0, 4, safe(f'{priority}  |  {platform}  |  {dates}'))
    pdf.ln(5)


# ============================================================
# COVER PAGE
# ============================================================
pdf.add_page()

# Cover background - subtle accent line at top
pdf.set_draw_color(*C_ACCENT)
pdf.set_line_width(1.5)
pdf.line(0, 0, 210, 0)
pdf.set_line_width(0.2)

# Novavita logo centered
try:
    pdf.image(LOGO_NOVAVITA, 55, 35, 100)
except Exception:
    pass

pdf.ln(55)

# Title block
pdf.set_font('Poppins', 'B', 24)
pdf.set_text_color(*C_PRIMARY)
pdf.cell(0, 12, safe('Estrategia de Campanas'), align='C')
pdf.ln(12)
pdf.set_font('PoppinsMed', '', 12)
pdf.set_text_color(*C_ACCENT)
pdf.cell(0, 6, safe('Google Ads + Meta Ads + Contenido'), align='C')
pdf.ln(10)

# Divider
pdf.set_draw_color(*C_ACCENT)
pdf.set_line_width(0.4)
pdf.line(70, pdf.get_y(), 140, pdf.get_y())
pdf.set_line_width(0.2)
pdf.ln(10)

# Details
pdf.set_font('Poppins', '', 9)
pdf.set_text_color(*C_SUBTEXT)
pdf.cell(0, 5, safe('Periodo: Abril - Julio 2026'), align='C')
pdf.ln(6)
pdf.cell(0, 5, safe('Preparado por Facand'), align='C')
pdf.ln(6)
pdf.cell(0, 5, safe('Curico, Chile'), align='C')
pdf.ln(20)

# Facand logo at bottom
try:
    pdf.image(LOGO_FACAND, 92, 200, 26)
except Exception:
    pass


# ============================================================
# 1. CONTEXTO Y DIAGNOSTICO
# ============================================================
pdf.add_page()
h1('1. CONTEXTO Y DIAGNOSTICO')

h2('1.1 Situacion actual')
p('Meta de ventas: $30M/mes. Ventas actuales: ~$9M/mes (marzo 2026). Historico: $40M/mes. Las ventas han bajado respecto al historico, lo que representa una oportunidad concreta para optimizar la presencia digital y recuperar el nivel de facturacion mediante una estrategia integrada de campanas.')

p('Presupuesto publicitario actual: $650.000/mes (Google $500K + Meta $150K). Google tiene 10x mejor CPA que Meta. Las campanas Search nuevas llevan menos de 2 semanas activas.')

h2('1.2 Ventajas competitivas de Novavita')
bullet('Soprano Titanium Special Edition - unico equipo en Curico con tecnologia SHR (3 profundidades, pelo no vuelve)')
bullet('Dermapen 4 australiano original (aguja $30.000 vs $1.000 del Doctor Pen generico)')
bullet('BioRepil - solo 5 clinicas en Chile lo tienen')
bullet('Yoga Facial - servicio de mayor rentabilidad ($58.000, margen altisimo)')
bullet('12+ anos de trayectoria, 4.98 estrellas en reviews')
bullet('Nelson como rostro de la marca: explica con autoridad y genera confianza')
bullet('Temporada facial: abril-julio es la epoca de mayor demanda en cuidado de piel')

h2('1.3 Amenazas identificadas')
bullet('Cela (cadena nacional, 43 sedes): axilas $39.990 vs $85.000 Novavita. Competencia directa en Curico.')
bullet('Belenus: precios agresivos, fuerte posicionamiento digital, prueba gratis 1a sesion.')
bullet('Mercado local pequeno: audiencias se saturan rapido (frecuencia 2.84 en 7 dias en test previo).')
bullet('Meta sin campana de conversion activa desde la redistribucion del 12 abril.')

# ============================================================
# 2. ESTRATEGIA POR CAMPANA
# ============================================================
pdf.add_page()
h1('2. ESTRATEGIA POR CAMPANA')

# --- CAMPANA 1 ---
h2('CAMPANA 1: Flash Sale Fin de Mes - Depilacion Laser')
campaign_badge('Prioridad: URGENTE', 'Meta + Google', '22-30 abril')

h3('Objetivo')
p('Generar ventas inmediatas para cerrar abril con resultados visibles. Depilacion laser es el servicio mas buscado y el que mueve mayor volumen.')

h3('Mecanica')
bullet('Codigo de descuento visible en banner del home con cuenta atras (vence 30 abril)')
bullet('El codigo otorga 1 sesion extra gratis en cualquier pack o zona individual de depilacion laser')
bullet('Aplica a hombre y mujer')
bullet('Banner rotativo en home de novavita.cl como pieza principal')

h3('Segmentacion')
bullet('Ubicacion: Curico + 50 km')
bullet('Edad: 22-50 mujeres (70% budget) + 22-40 hombres (30% budget)')
bullet('Intereses: depilacion laser, estetica, cuidado personal, Soprano')

h3('Material audiovisual necesario')
bold_bullet('Video 1 (Stories 9:16): ', 'Clienta real en sesion de depilacion (proceso rapido, sin dolor visible). Texto overlay: "Sesion extra GRATIS hasta el 30/04". CTA: Swipe up / link en bio.')
bold_bullet('Video 2 (Feed 1:1): ', 'Nelson mostrando el equipo Soprano Titanium, explicando en 15-20 seg por que es diferente. Texto overlay con codigo de descuento.')
bold_bullet('Imagen 3 (Stories): ', 'Banner con cuenta atras, codigo visible, logo Novavita. Fondo oscuro, texto claro.')
note('Nelson: grabar video del equipo en formato horizontal/lejos para poder recortar a 9:16 y 1:1. Minimo 4K.')

h3('Presupuesto sugerido')
w = [44, 30, 30, 74]
table_header(['Canal', 'Diario', 'Total 9 dias', 'Objetivo'], w)
table_row(['Meta - Stories IG', '$5.000', '$45.000', 'Trafico a pagina depilacion + conversion'], w, alt=False)
table_row(['Meta - Feed IG', '$3.000', '$27.000', 'Alcance + engagement con video Nelson'], w, alt=True)
table_row(['Google - Search', '$5.000', 'Ya activo', 'Capturar busquedas "depilacion laser Curico"'], w, alt=False)
table_row(['TOTAL EXTRA', '$8.000', '$72.000', ''], w, alt=True)

pdf.ln(2)
h3('KPIs esperados')
bullet('Impresiones: 50.000-80.000 en 9 dias')
bullet('Clics al sitio: 400-700')
bullet('Conversiones (compras + WhatsApp): 15-25')
bullet('ROAS objetivo: 5x minimo')

# --- CAMPANA 2 ---
pdf.add_page()
h2('CAMPANA 2: Temporada Facial - Ciclo Largo')
campaign_badge('Prioridad: ALTA', 'Meta + Google', '23 abril - 31 julio')

h3('Objetivo')
p('Posicionar a Novavita como referente en cuidado facial durante la temporada de menor radiacion (abril-julio). Este es el periodo donde la gente cuida mas su piel y busca tratamientos mas invasivos. Doble proposito: ventas directas de limpiezas faciales (ticket bajo, alto volumen) + introduccion a tratamientos de mayor valor (BioRepil, Dermapen, Exosomas).')

h3('Estructura de embudo')
bold_bullet('TOFU (awareness): ', 'Videos educativos de Nelson explicando tipos de piel, por que es temporada facial, diferencias entre limpiezas. Objetivo: seguidores y engagement.')
bold_bullet('MOFU (consideracion): ', 'Testimonios de clientas reales (ej: video Javiera con BioRepil). Retargeting a quienes vieron >50% del video TOFU. Objetivo: visitas al sitio.')
bold_bullet('BOFU (conversion): ', 'Oferta directa: Primera Consulta como puerta de entrada ($precio accesible). Retargeting a visitantes del sitio + carrito abandonado. Objetivo: compra.')

h3('Servicios a promocionar (por orden de prioridad)')
w2 = [42, 22, 48, 66]
table_header(['Servicio', 'Precio', 'Angulo', 'Material necesario'], w2)
table_row(['Limpieza Facial Profunda', '$---', 'Caballito de batalla', 'Video Nelson haciendo el procedimiento'], w2, alt=False)
table_row(['Primera Consulta', '$---', 'Puerta de entrada', 'Video educativo: "No sabes tu tipo de piel?"'], w2, alt=True)
table_row(['BioRepil', '$---', 'Solo 5 clinicas en Chile', 'Video Javiera (ya existe en IG) + nuevo'], w2, alt=False)
table_row(['Yoga Facial', '$58.000', 'MAS RENTABLE', 'Video mostrando relajacion, mandibula, cuello'], w2, alt=True)
table_row(['Dermapen 4', '$150.000', 'Premium, diferenciador', 'Reutilizar video IG + nuevo con Nelson'], w2, alt=False)
table_row(['Exosomas', '$---', 'Rejuvenecimiento moderno', 'Video de aplicacion + antes/despues'], w2, alt=True)
note('Precios especificos pendientes de confirmar con Nelson/Dani para publicar en anuncios.')

h3('Campana especifica: Yoga Facial para clientas recurrentes')
p('Seleccionar clientas que en los ultimos 3 meses hayan comprado limpiezas faciales (via Shopify/Klaviyo). Enviar email + WhatsApp con codigo de 30% de descuento en Yoga Facial (queda en $40.600). Enfoque del mensaje: "Premiamos tu constancia. Descubre el facial mas relajante de Novavita."')

h3('Material audiovisual necesario')
bold_bullet('Video A - Nelson educativo (YouTube + ficha producto): ', 'Nelson frente a camara explicando por que abril-julio es temporada facial. Que es una limpieza facial profunda. Para quien es. Duracion: 60-90 seg. Formato: grabar horizontal 4K, recortar a 9:16 (Stories) + 1:1 (Feed) + 16:9 (YouTube). Subir a YouTube como Short e insertar en ficha de producto.')
bold_bullet('Video B - Testimonio Javiera BioRepil: ', 'Ya existe en Instagram (quinto post reciente). Reutilizar con subtitulos para Meta Ads. Si tiene musica con copyright, reemplazar con audio de YouTube Library. Publicar como colaboracion pagada desde su perfil (ella acepta).')
bold_bullet('Video C - Yoga Facial en accion: ', 'Grabar sesion real de yoga facial mostrando la relajacion, el producto de jalea de mango, los movimientos. Sin audio de paciente (solo musica suave). Texto overlay: "Tension en el cuello? Dolor de mandibula? Esto es para ti." Ideal: la clienta del viernes 25 abril.')
bold_bullet('Video D - Dermapen World: ', 'Reutilizar video de IG donde Nelson recibe tratamiento con representante de la marca. Agregar subtitulos + nuevo texto overlay. Si ya esta muy visto en Curico, grabar version corta nueva de Nelson mostrando el dispositivo y explicando la diferencia de aguja ($30.000 vs $1.000).')
bold_bullet('Fotos antes/despues: ', 'Pedir a Nelson fotos de resultados reales (con consentimiento de clientas). Para Dermapen, BioRepil y limpiezas faciales. Son el contenido con mayor tasa de conversion en estetica.')

h3('Presupuesto sugerido (mensual)')
w3 = [44, 30, 30, 74]
table_header(['Capa', 'Diario', 'Mensual', 'Objetivo'], w3)
table_row(['TOFU - Video educativo', '$2.000', '$60.000', 'Awareness, seguidores, video views'], w3, alt=False)
table_row(['MOFU - Testimonios', '$2.000', '$60.000', 'Retargeting video viewers, trafico web'], w3, alt=True)
table_row(['BOFU - Oferta directa', '$3.000', '$90.000', 'Conversion: compras + WhatsApp'], w3, alt=False)
table_row(['Email/WhatsApp Yoga', '$0', '$0', 'Base propia, sin costo ads'], w3, alt=True)
table_row(['TOTAL', '$7.000', '$210.000/mes', '3 meses = $630.000'], w3, alt=False)

h3('KPIs esperados (por mes)')
bullet('Video views (>50%): 5.000-10.000')
bullet('Nuevos seguidores IG: 150-300')
bullet('Visitas al sitio (facial): 800-1.500')
bullet('Conversiones (compra + WhatsApp): 20-40')
bullet('Revenue esperado: $1.5M-$3M/mes solo de facial')

# --- CAMPANA 3 ---
pdf.add_page()
h2('CAMPANA 3: Dia de la Madre (especial, fecha limite)')
campaign_badge('Prioridad: ALTA', 'Meta', '25 abril - 11 mayo')

h3('Objetivo')
p('Capturar ventas de regalo para el Dia de la Madre (11 mayo). Los servicios de spa y facial son regalos ideales. Aprovechar la coleccion "Feliz dia Mama" ya creada y el feature "Lo quiero como regalo" pendiente de publicar.')

h3('Mecanica')
bullet('Coleccion curada: packs faciales + masajes + combos regalo ya armados')
bullet('Feature "Lo quiero como regalo" activado en el tema (vigencia 3 meses, transferible)')
bullet('Video principal: clienta del viernes 25 abril grabada por Nelson (collar de fideo, tono sutil)')
bullet('Urgencia: "Ultimos dias para regalar algo que de verdad importe"')

h3('Material audiovisual necesario')
bold_bullet('Video principal (viernes 25/04): ', 'Clienta real en la clinica, tono cercano y humoristico sutil. Formato: 4K horizontal para recortar. Nelson la graba. Entrega: editado en 1:1 y 9:16 el mismo viernes/sabado.')
bold_bullet('Carrusel de productos: ', 'Imagenes de la coleccion Feliz dia Mama con precios visibles. Ultimo slide: "Compra online, llega como regalo."')
bold_bullet('Story con cuenta atras: ', 'Sticker de cuenta atras hacia el 11 de mayo. Link a la coleccion.')

h3('Segmentacion')
bullet('Hombres 25-55 Curico + 50km (compradores de regalo)')
bullet('Mujeres 28-55 Curico + 50km (autorregalo o regalo entre amigas)')
bullet('Retargeting: visitantes sitio + interacciones IG ultimos 30 dias')

h3('Presupuesto sugerido')
w4 = [50, 30, 30, 68]
table_header(['Fase', 'Diario', 'Total', 'Objetivo'], w4)
table_row(['Pre-lanzamiento 25-30/04', '$3.000', '$18.000', 'Video awareness + urgencia temprana'], w4, alt=False)
table_row(['Push final 1-10/05', '$6.000', '$60.000', 'Conversion directa, retargeting'], w4, alt=True)
table_row(['TOTAL', '', '$78.000', ''], w4, alt=False)

h3('KPIs esperados')
bullet('Ventas atribuidas a campana: 15-25 regalos')
bullet('Ticket promedio esperado: $60.000-$95.000')
bullet('Revenue: $900K-$2.4M')

# --- CAMPANA 4 ---
pdf.ln(3)
h2('CAMPANA 4: Depilacion Hombre - Normalizacion')
campaign_badge('Prioridad: MEDIA', 'Meta + Google', 'Mayo - Junio')

h3('Objetivo')
p('Nelson menciono que quieren mas hombres. Ya tienen contenido en IG normalizando la depilacion masculina. Oportunidad: en Curico no hay competidor fuerte en depilacion hombre (Cela se enfoca en mujer, Belenus no tiene sede fisica). Es un nicho sin explotar localmente.')

h3('Angulo creativo')
p('No vender "depilacion". Vender comodidad, higiene, confianza. Abordar miedos directamente (dolor, zona intima, seguridad). Nelson en la reunion explico con naturalidad como funciona - ese tono es el que hay que replicar.')

h3('Material audiovisual necesario')
bold_bullet('Video A - FAQ hombres: ', 'Nelson respondiendo las 3 preguntas mas comunes: Duele? Es seguro en zona intima? Cuantas sesiones necesito? Tono directo, sin marketing. 30-45 seg.')
bold_bullet('Video B - Proceso real: ', 'Sesion de depilacion de espalda o pecho (zona "segura" visualmente). Mostrar lo rapido que es. Texto overlay: "50 segundos. Sin dolor. Sin pelo."')
bold_bullet('Testimonio hombre: ', 'Cliente hombre real contando su experiencia. No necesita mostrar zona intima, solo hablar frente a camara.')

h3('Presupuesto sugerido (mensual)')
w5 = [44, 30, 30, 74]
table_header(['Canal', 'Diario', 'Mensual', 'Objetivo'], w5)
table_row(['Meta - IG Stories/Reels', '$2.000', '$60.000', 'Awareness hombres 22-40 Curico'], w5, alt=False)
table_row(['Google - Search', '$2.000', '$60.000', '"depilacion hombre curico" + variantes'], w5, alt=True)
table_row(['TOTAL', '$4.000', '$120.000/mes', ''], w5, alt=False)

# --- CAMPANA 5 ---
pdf.ln(3)
h2('CAMPANA 5: Liquidacion Vitamina Fusion')
campaign_badge('Prioridad: BAJA', 'Meta', 'Mayo (1 semana)')

h3('Objetivo')
p('Liquidar stock de Vitamina Fusion. Hacer una "semana loca" con precio agresivo combinado con limpieza facial para que tenga sentido.')

h3('Mecanica')
bullet('Combo: Limpieza Facial Profunda + Vitamina Fusion a precio especial (definir con Nelson)')
bullet('Duracion: 1 semana, stock limitado')
bullet('Solo en Stories IG con cuenta atras')

h3('Presupuesto: $15.000-$20.000 total (3 dias de ads)')

# ============================================================
# 3. RESUMEN PRESUPUESTO
# ============================================================
pdf.add_page()
h1('3. RESUMEN DE PRESUPUESTO')

p('Presupuesto incremental sugerido sobre el gasto actual de $650K/mes (Google $500K + Meta $150K):')
pdf.ln(2)

w6 = [58, 28, 28, 28, 36]
table_header(['Campana', 'Abril', 'Mayo', 'Junio', 'Julio'], w6)
table_row(['1. Flash Sale Depilacion', '$72.000', '-', '-', '-'], w6, alt=False)
table_row(['2. Temporada Facial', '$50.000', '$210.000', '$210.000', '$210.000'], w6, alt=True)
table_row(['3. Dia de la Madre', '-', '$78.000', '-', '-'], w6, alt=False)
table_row(['4. Depilacion Hombre', '-', '$120.000', '$120.000', '-'], w6, alt=True)
table_row(['5. Liquid. Vit. Fusion', '-', '$20.000', '-', '-'], w6, alt=False)
table_row(['TOTAL ADICIONAL', '$122.000', '$428.000', '$330.000', '$210.000'], w6, bold_first=True, alt=True)
table_row(['TOTAL GENERAL/MES', '$772.000', '$1.078.000', '$980.000', '$860.000'], w6, bold_first=True, alt=False)

pdf.ln(3)
note('El presupuesto se ajusta mensualmente segun resultados. Si una campana no rinde en 2 semanas, se pausa y reasigna. La meta es llegar a $30M/mes en ventas con un presupuesto total de ~$1M/mes (ROAS 30x sobre Google, 8x sobre Meta).')

# ============================================================
# 4. CALENDARIO DE CONTENIDO
# ============================================================
h1('4. CALENDARIO DE CONTENIDO REQUERIDO')

h2('Semana 22-27 abril (URGENTE)')
bullet('Nelson: Grabar video Soprano Titanium para Flash Sale (15-20 seg, formato 4K horizontal)')
bullet('Nelson: Grabar video clienta viernes 25 (Dia de la Madre)')
bullet('Nelson: Organizar carpeta Drive con material por servicio (subcarpetas)')
bullet('Equipo web: Implementar banner rotativo en home + cuenta atras con codigo')
bullet('Mati: Configurar campana Flash Sale en Meta + verificar geo PMax en Google')

h2('Semana 28 abril - 4 mayo')
bullet('Nelson: Grabar video educativo "Por que es temporada facial" (60-90 seg)')
bullet('Nelson: Grabar Yoga Facial con clienta (viernes si es posible)')
bullet('Subir videos a YouTube Shorts + insertar en fichas de producto')
bullet('Mati: Lanzar BOFU Dia de la Madre con video de la clienta del 25')
bullet('Mati: Enviar email Yoga Facial 30% a clientas recurrentes (Klaviyo)')

h2('Semana 5-11 mayo')
bullet('Push final Dia de la Madre (presupuesto duplicado)')
bullet('Evaluar resultados Flash Sale y decidir si extender')
bullet('Nelson: Grabar FAQ depilacion hombre (3 preguntas, 30-45 seg)')

h2('Semana 12-18 mayo')
bullet('Lanzar campana Depilacion Hombre')
bullet('Ejecutar Semana Fusion (liquidacion)')
bullet('Evaluar metricas primer mes de Temporada Facial')
bullet('Nelson: Grabar video BioRepil nuevo + video Dermapen actualizado')

h2('Junio - Julio')
bullet('Mantener Temporada Facial (ajustar creativos cada 2 semanas)')
bullet('Mantener Depilacion Hombre si rinde')
bullet('Incorporar Dermapen y Exosomas como creativos BOFU')
bullet('Grabar nuevos testimonios cada 2 semanas (pedir a clientas satisfechas)')

# ============================================================
# 5. INVENTARIO DE MATERIAL AUDIOVISUAL
# ============================================================
pdf.add_page()
h1('5. INVENTARIO DE MATERIAL AUDIOVISUAL')

h2('5.1 Material existente (reutilizable)')
w7 = [55, 55, 68]
table_header(['Material', 'Ubicacion', 'Uso sugerido'], w7)
table_row(['Video Dermapen (Nelson + rep. marca)', 'Instagram Novavita', 'Reutilizar con subtitulos nuevos'], w7, alt=False)
table_row(['Video Javiera BioRepil', 'IG (5to post reciente)', 'Colaboracion pagada + Meta Ads'], w7, alt=True)
table_row(['Video clienta Santiago', 'IG (junto a Javiera)', 'Testimonio para retargeting'], w7, alt=False)
table_row(['Ping pong Dermapen', 'IG (debajo del anterior)', 'Contenido educativo, YouTube'], w7, alt=True)
table_row(['Videos test A/B Dia Mama', 'assets/ads_meta/', '3 variantes listas (A/B/C)'], w7, alt=False)
table_row(['ZIP videos Dani (1.7GB)', 'assets/ (sin descomprimir)', 'Revisar y categorizar'], w7, alt=True)

h2('5.2 Material por grabar (Nelson)')
w8 = [48, 22, 50, 58]
table_header(['Video', 'Duracion', 'Formato', 'Fecha limite'], w8)
table_row(['Soprano Titanium Flash Sale', '15-20 seg', '4K horizontal, recortar', '23 abril'], w8, alt=False)
table_row(['Clienta Dia Madre', '30-60 seg', '4K horizontal, recortar', '25 abril'], w8, alt=True)
table_row(['Nelson: temporada facial', '60-90 seg', '4K horiz, YouTube+IG+fichas', '30 abril'], w8, alt=False)
table_row(['Yoga Facial sesion real', '30-45 seg', '4K horizontal', '2 mayo'], w8, alt=True)
table_row(['FAQ depilacion hombre', '30-45 seg', 'Nelson frente a camara', '9 mayo'], w8, alt=False)
table_row(['BioRepil nuevo', '30-60 seg', 'Proceso + resultado', '16 mayo'], w8, alt=True)
table_row(['Dermapen actualizado', '30-45 seg', 'Nelson + dispositivo', '16 mayo'], w8, alt=False)
table_row(['Testimonio hombre', '20-30 seg', 'Cliente frente a camara', 'mayo'], w8, alt=True)
table_row(['Fotos antes/despues', 'N/A', 'Alta resolucion', 'Continuo'], w8, alt=False)

note('Regla de grabacion: SIEMPRE grabar en 4K horizontal desde lejos para poder recortar a 9:16 (Stories), 1:1 (Feed), 16:9 (YouTube) sin perder calidad. Usar lineas de guia en la camara para centrar al sujeto.')

# ============================================================
# 6. GOOGLE ADS
# ============================================================
h1('6. GOOGLE ADS - OPTIMIZACION EN CURSO')

h2('6.1 Campanas activas')
w9 = [52, 28, 28, 70]
table_header(['Campana', 'Budget/dia', 'Estado', 'Accion'], w9)
table_row(['Search Brand', '$2.500', 'Activa', 'Mantener, captura marca'], w9, alt=False)
table_row(['Search Servicios Curico', '$5.000', 'Activa', 'Monitorear CPA 2 semanas'], w9, alt=True)
table_row(['PMax', '$9.200', 'Activa', 'URGENTE: verificar geo Santiago'], w9, alt=False)

h2('6.2 Acciones pendientes Google')
bullet('Revisar reporte de ubicaciones de PMax: si hay impresiones fuera de Curico, agregar exclusion de region')
bullet('Activar remarketing cuando audiencias GA4 tengan +100 usuarios (Carrito Abandonado, Engagement Alto)')
bullet('Customer Match: subir lista de emails para excluir compradores y crear lookalike')
bullet('Agregar keywords de campana facial: "limpieza facial curico", "facial cerca de mi", "dermapen curico"')
bullet('Crear campana Search especifica para depilacion hombre cuando se lance (mayo)')

# ============================================================
# 7. META ADS
# ============================================================
pdf.add_page()
h1('7. META ADS - REESTRUCTURACION')

h2('7.1 Estado actual')
p('Tras la redistribucion del 12 abril, Meta quedo sin campana de conversion activa. Las 3 campanas activas son de trafico (TOFU). El test A/B de Dia de la Madre deberia tener ganador al 23 abril.')

h2('7.2 Estructura propuesta')
w10 = [34, 32, 40, 72]
table_header(['Campana', 'Objetivo', 'Audiencia', 'Creativos'], w10)
table_row(['TOFU Facial', 'Video views', 'Broad mujeres 25-55', 'Nelson educativo'], w10, alt=False)
table_row(['TOFU Depilacion', 'Video views', 'Broad mujer+hombre', 'Soprano + proceso'], w10, alt=True)
table_row(['MOFU Retargeting', 'Trafico web', 'Video viewers 50%+', 'Testimonios + ofertas'], w10, alt=False)
table_row(['BOFU Conversion', 'Compras', 'Web visitors + cart', 'Precio + urgencia + CTA'], w10, alt=True)
table_row(['Dia Madre (temporal)', 'Compras', 'Hombres + mujeres', 'Video clienta 25/04'], w10, alt=False)

h2('7.3 Reglas operativas Meta')
bullet('NUNCA correr mas de 4-5 campanas simultaneas (audiencia Curico es chica)')
bullet('Frecuencia maxima: 2.5 en 7 dias. Si supera, pausar o ampliar audiencia.')
bullet('Rotar creativos cada 2 semanas para evitar fatiga')
bullet('Excluir SIEMPRE: compradores ultimos 30 dias, seguidores actuales (en TOFU)')
bullet('Presupuesto minimo por ad set: $2.000/dia para salir de learning phase')

# ============================================================
# 8. RESULTADOS ESPERADOS
# ============================================================
h1('8. RESULTADOS ESPERADOS POR MES')

w11 = [36, 34, 34, 34, 40]
table_header(['Metrica', 'Abril', 'Mayo', 'Junio', 'Julio'], w11)
table_row(['Inversion total', '$772K', '$1.078K', '$980K', '$860K'], w11, alt=False)
table_row(['Ventas web (est.)', '$3-5M', '$6-10M', '$10-15M', '$12-18M'], w11, alt=True)
table_row(['Ventas WhatsApp (est.)', '$2-3M', '$4-6M', '$5-8M', '$6-8M'], w11, alt=False)
table_row(['TOTAL ventas (est.)', '$5-8M', '$10-16M', '$15-23M', '$18-26M'], w11, alt=True)
table_row(['Seguidores IG nuevos', '100-200', '200-400', '300-500', '300-500'], w11, alt=False)
table_row(['ROAS estimado', '8-10x', '10-15x', '15-20x', '20-30x'], w11, alt=True)

pdf.ln(3)
note('IMPORTANTE: Estos numeros son estimaciones basadas en los datos historicos de Novavita (ROAS 8.5x con $1.24M/mes) y benchmarks del sector estetica en Chile. Los resultados reales dependen de la calidad del contenido, la velocidad de ejecucion y factores externos. Se ajustan mensualmente con datos reales. Nunca comprometemos cifras concretas.')

p('Meta de $30M/mes: estimamos alcanzarla entre junio-julio si se ejecuta el plan completo con el material audiovisual en los plazos indicados y el presupuesto se mantiene.')

# ============================================================
# 9. PROXIMOS PASOS
# ============================================================
h1('9. PROXIMOS PASOS INMEDIATOS')

h2('Hoy (22 abril)')
bullet('Mati: Configurar campana Flash Sale depilacion en Meta')
bullet('Nelson: Hacer banner Flash Sale + codigo de descuento + cuenta atras')
bullet('Equipo web: Implementar slider rotativo en home')

h2('23 abril')
bullet('Evaluar ganador test A/B Dia de la Madre (dia 7 del test)')
bullet('Nelson: Grabar video Soprano Titanium para Flash Sale')

h2('25 abril')
bullet('Nelson: Grabar video clienta Dia de la Madre')
bullet('Mati: Lanzar campana retargeting Dia de la Madre con video ganador del test')

h2('28-30 abril')
bullet('Evaluar resultados Flash Sale. Si funciona, extender hasta 10 mayo.')
bullet('Nelson: Grabar video educativo temporada facial')
bullet('Mati: Lanzar embudo Temporada Facial (TOFU)')

h2('Reuniones')
bullet('Semanal de 40 minutos hasta estabilizar (acordado con Nelson)')
bullet('Canal principal: grupo WhatsApp para cambios rapidos entre reuniones')

# ============================================================
# CLOSING
# ============================================================
pdf.ln(8)
pdf.set_draw_color(*C_ACCENT)
pdf.set_line_width(0.4)
pdf.line(60, pdf.get_y(), 150, pdf.get_y())
pdf.set_line_width(0.2)
pdf.ln(6)
pdf.set_font('Poppins', 'I', 8)
pdf.set_text_color(*C_SUBTEXT)
pdf.cell(0, 4, safe('Documento preparado por Facand para Novavita Clinica & Spa'), align='C')
pdf.ln(5)
pdf.cell(0, 4, safe('Basado en reunion estrategica del 21 de abril 2026 con Nelson'), align='C')
pdf.ln(5)
pdf.cell(0, 4, safe('Contacto: Matias Hidalgo | Facand'), align='C')

pdf.output(OUTPUT)
print('PDF generado OK en:', OUTPUT)
