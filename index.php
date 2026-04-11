<?php
require_once __DIR__ . '/includes/functions.php';
send_security_headers();
require_auth();

$tab = filter_input(INPUT_GET, 'tab', FILTER_DEFAULT) ?? 'campanas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Novavita Clínica & Spa</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Novavita</h2>
                <span class="sidebar-subtitle">Clínica & Spa</span>
                <span class="sidebar-badge">Meta Ads API</span>
            </div>
            <nav class="sidebar-nav">
                <a href="?tab=campanas" class="nav-item <?php echo $tab === 'campanas' ? 'active' : ''; ?>">
                    <span class="nav-icon">&#x1f4ca;</span> Análisis de Campañas
                </a>
                <a href="?tab=estrategia" class="nav-item <?php echo $tab === 'estrategia' ? 'active' : ''; ?>">
                    <span class="nav-icon">&#x1f3af;</span> Estrategia
                </a>
                <a href="?tab=duenos" class="nav-item <?php echo $tab === 'duenos' ? 'active' : ''; ?>">
                    <span class="nav-icon">&#x1f4cb;</span> Para los Due&ntilde;os
                </a>
            </nav>
            <div class="sidebar-footer">
                <button onclick="clearCache()" class="nav-item" style="border:none;background:none;cursor:pointer;width:100%;text-align:left;color:var(--text-muted);font-size:0.9rem;">
                    <span class="nav-icon">&#x21bb;</span> Actualizar datos
                </button>
                <a href="logout.php" class="nav-item logout">
                    <span class="nav-icon">&#x2190;</span> Cerrar sesión
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div id="loading" class="loading-overlay"><div class="spinner"></div><p>Cargando datos desde Meta Ads...</p></div>

            <?php if ($tab === 'campanas'): ?>
            <!-- ==================== TAB: ANÁLISIS DE CAMPAÑAS ==================== -->
            <div class="page-header">
                <h1>Análisis de Campañas</h1>
                <div class="header-controls">
                    <select id="platformFilter" onchange="onFiltersChange()">
                        <option value="all">Meta + Google</option>
                        <option value="meta">Solo Meta Ads</option>
                        <option value="google">Solo Google Ads</option>
                    </select>
                    <select id="statusFilter" onchange="onFiltersChange()">
                        <option value="all">Todas</option>
                        <option value="ACTIVE">Activas</option>
                        <option value="PAUSED">Inactivas/Pausadas</option>
                    </select>
                    <select id="dateFilter" onchange="onDateFilterChange()">
                        <option value="all">Todo el periodo</option>
                        <option value="current">Mes en curso</option>
                        <option value="last">Último mes</option>
                        <option value="custom">Personalizado</option>
                    </select>
                    <div id="customDates" style="display:none;">
                        <input type="date" id="dateFrom" value="2026-01-01">
                        <input type="date" id="dateTo" value="<?php echo date('Y-m-d'); ?>">
                        <button onclick="loadWithDates()" class="btn-filter">Aplicar</button>
                    </div>
                    <span id="periodBadge" class="badge badge-period"></span>
                </div>
            </div>

            <div id="kpiGrid" class="kpi-grid"></div>

            <!-- Sub-sección: Embudo de Conversión -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">1</span>
                    <div>
                        <h2>Embudo de Conversión</h2>
                        <p class="section-desc">Flujo completo desde impresión hasta compra con tasas de conversión por nivel</p>
                    </div>
                </div>
                <div id="funnelContainer" class="funnel-wrapper"></div>
            </div>

            <!-- Sub-sección: Oportunidades por nivel del embudo -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">2</span>
                    <div>
                        <h2>Oportunidades de Mejora</h2>
                        <p class="section-desc">Acciones concretas para mejorar cada nivel del embudo según los datos reales</p>
                    </div>
                </div>
                <div id="funnelOpportunities" class="findings-list"></div>
            </div>

            <!-- Sub-sección: Rendimiento por Campaña -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">3</span>
                    <div>
                        <h2>Rendimiento por Campaña</h2>
                        <p class="section-desc">Click en una campaña para ver detalle de ad sets y anuncios</p>
                    </div>
                </div>
                <div id="campaignTable" class="table-responsive"></div>

                <div id="campaignDetail" style="display:none;margin-top:1.5rem;">
                    <h3 id="detailTitle" style="color:var(--text-primary);margin-bottom:0.75rem;">Detalle</h3>
                    <div id="adsetTable" class="table-responsive"></div>
                    <h4 style="margin-top:1.25rem;color:var(--text-secondary);margin-bottom:0.5rem;">Anuncios</h4>
                    <div id="adTable" class="table-responsive"></div>
                </div>
            </div>

            <!-- Sub-sección: Desglose Mensual -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">4</span>
                    <div>
                        <h2>Desglose Mensual</h2>
                        <p class="section-desc">Evolución de rendimiento mes a mes</p>
                    </div>
                    <div class="header-controls" style="margin-left:auto;">
                        <select id="monthFilter" onchange="renderMonthDetail()">
                            <option value="all">Todos los meses</option>
                        </select>
                    </div>
                </div>

                <div id="monthDetail" style="margin-bottom:1.5rem;">
                    <h3 id="monthDetailTitle" style="color:var(--text-secondary);margin-bottom:0.75rem;">Campañas del mes</h3>
                    <div id="monthCampaignTable" class="table-responsive"></div>
                </div>

                <h3 style="color:var(--text-secondary);margin-bottom:0.75rem;">Resumen Comparativo</h3>
                <div id="monthTable" class="table-responsive"></div>
            </div>

            <!-- Sub-sección: Google Ads -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number gads-number">G</span>
                    <div>
                        <h2>Google Ads</h2>
                        <p class="section-desc">Datos en tiempo real de campañas de Google Ads</p>
                    </div>
                </div>
                <div id="googleAdsContent"><p style="color:var(--text-muted)">Cargando Google Ads...</p></div>
            </div>

            <!-- Sub-sección: Shopify -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number shopify-number">S</span>
                    <div>
                        <h2>Shopify — Ventas Reales</h2>
                        <p class="section-desc">Pedidos y revenue directos desde la tienda de Novavita</p>
                    </div>
                </div>
                <div id="shopifyContent"><p style="color:var(--text-muted)">Cargando Shopify...</p></div>
            </div>

            <!-- Sub-sección: Diagnóstico -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">7</span>
                    <div>
                        <h2>Diagnóstico Automático</h2>
                        <p class="section-desc">Alertas generadas automáticamente a partir de los datos</p>
                    </div>
                    <div class="header-controls" style="margin-left:auto;">
                        <select id="diagFilter" onchange="renderDiagnostics()">
                            <option value="all">Todas las plataformas</option>
                            <option value="meta">Solo Meta</option>
                            <option value="google">Solo Google</option>
                            <option value="cross">Cross-platform</option>
                        </select>
                    </div>
                </div>
                <div id="diagnostics" class="diagnostic-grid"></div>
            </div>

            <?php elseif ($tab === 'estrategia'): ?>
            <!-- ==================== TAB: ESTRATEGIA ==================== -->
            <div class="page-header">
                <h1>Estrategia de Posicionamiento</h1>
                <span class="badge badge-period">Basada en análisis de datos, web e Instagram</span>
            </div>

            <div class="strategy-alert">
                <h3>Día de la Madre — Domingo 10 de Mayo 2026</h3>
                <p>Quedan <strong id="diasMadre"></strong> días. Es la fecha comercial más importante para estética y bienestar. Se debe activar campaña al menos 2-3 semanas antes (desde el 18 de abril).</p>
            </div>

            <!-- Estructura de campaña propuesta -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">1</span>
                    <div>
                        <h2>Estructura de Campañas Propuesta</h2>
                        <p class="section-desc">Embudo de 3 etapas: Tráfico (IG Stories) > Retargeting seguidores > Conversión</p>
                    </div>
                </div>

                <div class="strategy-funnel">
                    <!-- TOFU -->
                    <div class="strategy-stage strategy-stage-tofu">
                        <div class="strategy-stage-header">
                            <span class="strategy-stage-tag">TOFU — Tráfico</span>
                            <span class="strategy-stage-placement">Placement: Solo Instagram Stories</span>
                        </div>
                        <h3>Campaña de Tráfico a Instagram</h3>
                        <p class="strategy-stage-goal"><strong>Objetivo:</strong> Atraer nuevos seguidores con el menor costo por clic posible (~$10 CLP/clic según datos históricos).</p>
                        <div class="strategy-stage-config">
                            <div class="config-item">
                                <span class="config-label">Objetivo Meta</span>
                                <span class="config-value">Tráfico > Perfil de Instagram</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Placement</span>
                                <span class="config-value">Solo Instagram Stories (manual)</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Segmentación</span>
                                <span class="config-value">Curicó + 30km, Mujeres 25-54, intereses: spa, belleza, cuidado personal, depilación</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Presupuesto sugerido</span>
                                <span class="config-value">$3.000 - $5.000 CLP/día</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Duración</span>
                                <span class="config-value">Permanente (siempre activa como tope del embudo)</span>
                            </div>
                        </div>
                        <div class="strategy-stage-creative">
                            <h4>Material audiovisual necesario:</h4>
                            <ul>
                                <li><strong>Video vertical 9:16 (5-15 seg):</strong> Tour rápido de la clínica — mostrar instalaciones, equipamiento Soprano Titanium, ambiente relajado. Texto overlay: "Conoce Novavita" + flecha de swipe up.</li>
                                <li><strong>Video vertical 9:16 (8-12 seg):</strong> Testimonio rápido de clienta real — formato selfie/casual, hablando de su experiencia. Subtitulado. Similar a los anuncios "Karen" que son los de mejor CTR (3.09%).</li>
                                <li><strong>Video vertical 9:16 (5-10 seg):</strong> Before/after sutil de un tratamiento (sin prometer resultados médicos). Texto: "Resultados reales".</li>
                            </ul>
                        </div>
                    </div>

                    <div class="strategy-arrow">Los que se convierten en seguidores pasan a...</div>

                    <!-- MOFU -->
                    <div class="strategy-stage strategy-stage-mofu">
                        <div class="strategy-stage-header">
                            <span class="strategy-stage-tag">MOFU — Retargeting</span>
                            <span class="strategy-stage-placement">Audiencia: Seguidores IG + Interactuados</span>
                        </div>
                        <h3>Campaña de Conversión (Retargeting)</h3>
                        <p class="strategy-stage-goal"><strong>Objetivo:</strong> Convertir seguidores e interactuados en compradores web o leads de WhatsApp.</p>
                        <div class="strategy-stage-config">
                            <div class="config-item">
                                <span class="config-label">Objetivo Meta</span>
                                <span class="config-value">Ventas (Purchase) + Conversaciones WA</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Audiencias</span>
                                <span class="config-value">Custom: seguidores IG 90d + interactuados IG 30d + visitantes web 30d</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Estructura</span>
                                <span class="config-value">ABO (no CBO) — para controlar presupuesto por ad set</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Presupuesto sugerido</span>
                                <span class="config-value">$5.000 - $8.000 CLP/día por ad set</span>
                            </div>
                        </div>
                        <div class="strategy-stage-creative">
                            <h4>Material audiovisual necesario:</h4>
                            <ul>
                                <li><strong>Video 1:1 o 9:16 (15-30 seg):</strong> Daniela (dueña) hablando directo a cámara: "Si nos sigues, ya sabes lo que hacemos — ahora te invito a probarlo con un descuento especial para seguidoras". Formato personal, no producido.</li>
                                <li><strong>Carrusel de fotos (3-5 imágenes):</strong> Antes/después de tratamientos reales + precio del pack + CTA "Compra online". Para campañas de conversión web.</li>
                                <li><strong>Video 9:16 (10-15 seg):</strong> Clienta real enviando audio/mensaje recomendando Novavita. Overlay: "¿Quieres saber más? Escríbenos por WhatsApp". Para la versión de conversación.</li>
                                <li><strong>Imagen estática con oferta:</strong> Pack Día de la Madre — Gift Card con diseño visual atractivo + precio + countdown. Para la campaña temporal.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="strategy-arrow">Remarketing agresivo última semana antes del evento</div>

                    <!-- BOFU -->
                    <div class="strategy-stage strategy-stage-bofu">
                        <div class="strategy-stage-header">
                            <span class="strategy-stage-tag">BOFU — Cierre</span>
                            <span class="strategy-stage-placement">Audiencia: ATC + Checkout abandonado + WA contactados</span>
                        </div>
                        <h3>Remarketing de Cierre</h3>
                        <p class="strategy-stage-goal"><strong>Objetivo:</strong> Recuperar carritos abandonados y cerrar ventas en fechas clave.</p>
                        <div class="strategy-stage-config">
                            <div class="config-item">
                                <span class="config-label">Objetivo Meta</span>
                                <span class="config-value">Ventas (Purchase)</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Audiencias</span>
                                <span class="config-value">Add to Cart 14d + Initiate Checkout 14d sin compra</span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">Presupuesto sugerido</span>
                                <span class="config-value">$3.000 - $5.000 CLP/día (escalar en fechas clave)</span>
                            </div>
                        </div>
                        <div class="strategy-stage-creative">
                            <h4>Material audiovisual necesario:</h4>
                            <ul>
                                <li><strong>Catálogo dinámico Shopify:</strong> Configurar Meta Catalogue con productos de Shopify para mostrar exactamente lo que dejaron en el carrito.</li>
                                <li><strong>Video 1:1 (8-12 seg):</strong> Mensaje de urgencia — "Tu carrito te espera, los cupos son limitados". Tono amable, no agresivo.</li>
                                <li><strong>Imagen con countdown:</strong> "Últimas horas — Día de la Madre" con el producto/pack específico.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material audiovisual completo -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">2</span>
                    <div>
                        <h2>Checklist de Material Audiovisual</h2>
                        <p class="section-desc">Todo el contenido que necesitamos producir, ordenado por prioridad</p>
                    </div>
                </div>

                <div class="checklist-grid">
                    <div class="checklist-card checklist-urgent">
                        <span class="checklist-priority">Prioridad Alta</span>
                        <h4>1. Video testimonio tipo "Karen" (9:16)</h4>
                        <p>Los anuncios "Cuello Karen" y "Facial Karen" tienen el mejor rendimiento (CTR 3.09%, 27 compras). Necesitamos <strong>2-3 testimonios nuevos</strong> de clientas reales hablando a cámara sobre su experiencia con depilación láser y/o faciales.</p>
                        <div class="checklist-specs">
                            <span>Formato: Vertical 9:16</span>
                            <span>Duración: 15-30 seg</span>
                            <span>Estilo: Casual/selfie</span>
                            <span>Subtítulos: Sí</span>
                        </div>
                    </div>

                    <div class="checklist-card checklist-urgent">
                        <span class="checklist-priority">Prioridad Alta</span>
                        <h4>2. Tour de clínica para Stories (9:16)</h4>
                        <p>Para la campaña de tráfico en Stories. Video corto mostrando instalaciones, equipamiento y ambiente. Debe transmitir profesionalismo y confianza. Texto overlay con el nombre y ubicación.</p>
                        <div class="checklist-specs">
                            <span>Formato: Vertical 9:16</span>
                            <span>Duración: 5-15 seg</span>
                            <span>Estilo: Dinámico/rápido</span>
                            <span>Texto overlay: Sí</span>
                        </div>
                    </div>

                    <div class="checklist-card checklist-urgent">
                        <span class="checklist-priority">Prioridad Alta</span>
                        <h4>3. Video de Daniela hablando directo a cámara</h4>
                        <p>Para retargeting de seguidoras. Daniela invitando personalmente a probar los servicios con un incentivo. Genera conexión personal — clave para negocio local en Curicó.</p>
                        <div class="checklist-specs">
                            <span>Formato: 1:1 o 9:16</span>
                            <span>Duración: 15-30 seg</span>
                            <span>Estilo: Personal/directo</span>
                            <span>CTA: Oferta especial</span>
                        </div>
                    </div>

                    <div class="checklist-card checklist-medium">
                        <span class="checklist-priority">Prioridad Media</span>
                        <h4>4. Diseño Gift Card Día de la Madre</h4>
                        <p>Imagen estática con diseño visual premium para promocionar Gift Cards como regalo. Varios montos ($50K, $100K, $150K). Con logo de Novavita y copy emocional.</p>
                        <div class="checklist-specs">
                            <span>Formato: 1:1 + 9:16</span>
                            <span>Tipo: Imagen estática</span>
                            <span>Variantes: 3 montos</span>
                        </div>
                    </div>

                    <div class="checklist-card checklist-medium">
                        <span class="checklist-priority">Prioridad Media</span>
                        <h4>5. Carrusel antes/después</h4>
                        <p>3-5 imágenes de resultados reales de tratamientos (depilación láser, faciales). Con precio del pack y botón de compra. Para campañas de conversión en feed.</p>
                        <div class="checklist-specs">
                            <span>Formato: 1:1 carrusel</span>
                            <span>Imágenes: 3-5</span>
                            <span>Incluye: Precio + CTA</span>
                        </div>
                    </div>

                    <div class="checklist-card checklist-medium">
                        <span class="checklist-priority">Prioridad Media</span>
                        <h4>6. Video audiomensaje de clienta real</h4>
                        <p>Grabación de pantalla de un audiomensaje de WhatsApp de una clienta recomendando Novavita. Con overlay "¿Quieres saber más? Escríbenos". Para campañas de conversación WA.</p>
                        <div class="checklist-specs">
                            <span>Formato: 9:16</span>
                            <span>Duración: 10-15 seg</span>
                            <span>Estilo: Screen recording</span>
                        </div>
                    </div>

                    <div class="checklist-card">
                        <span class="checklist-priority">Complementario</span>
                        <h4>7. Catálogo dinámico Shopify-Meta</h4>
                        <p>No es un video sino una configuración técnica. Conectar el catálogo de productos de Shopify con Meta Commerce Manager para habilitar anuncios dinámicos de retargeting (mostrar el producto exacto que abandonaron en el carrito).</p>
                        <div class="checklist-specs">
                            <span>Tipo: Configuración técnica</span>
                            <span>Requiere: Meta Commerce Manager</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis web e IG -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">3</span>
                    <div>
                        <h2>Análisis de Canales</h2>
                        <p class="section-desc">Observaciones sobre novavita.cl e Instagram @novavita.cl</p>
                    </div>
                </div>
                <div class="diagnostic-grid">
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Shopify — Tech Stack</h3>
                        <p>Tema Fabric v3.5.1. Integraciones activas: <strong>Google Analytics, Facebook Pixel, Klaviyo, Loox, Mercado Pago</strong>. El Pixel está instalado pero debe verificarse que dispare eventos de conversión correctamente post-migración.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Shopify — Sin landing de temporada</h3>
                        <p>No existe una colección o landing page de "Regalos Día de la Madre". Se necesita crear una con: Gift Cards, Packs populares y Vouchers de Spa, con countdown al 10 de mayo.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Instagram — Contenido que funciona</h3>
                        <p>Los testimonios de clientas reales ("Karen") superan ampliamente a los catálogos genéricos y publicaciones boosteadas. CTR de 3.09% vs 1.23% promedio. La estrategia de contenido debe priorizar <strong>social proof y testimonios</strong>.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Instagram — Boosts ineficientes</h3>
                        <p>Las 4 publicaciones boosteadas gastaron $150K+ con CPC de $19-$43 y sin compras. Boostear desde la app de IG es ineficiente. Todo el presupuesto debe ir a campañas creadas desde Ads Manager con objetivos claros.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Catálogo de servicios</h3>
                        <p>Categorías: Depilación láser (mujer/hombre), Faciales, Spa/Masajes, Scizer, Dermocosmética, Gift Cards. <strong>Depilación láser</strong> es el servicio estrella tanto en demanda como en rendimiento publicitario.</p>
                    </div>
                </div>
            </div>

            <!-- Distribución de presupuesto -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">4</span>
                    <div>
                        <h2>Distribución de Presupuesto Recomendada</h2>
                        <p class="section-desc">Basada en el rendimiento real de cada canal</p>
                    </div>
                </div>

                <div class="owner-card owner-card-green" style="margin-bottom:1.25rem;">
                    <h3>Presupuesto total recomendado: $1.240.000 CLP/mes</h3>
                    <p>Basado en el promedio de inversión de los últimos 3 meses (Meta ~$1.115.000 + Google ~$125.000). Se mantiene el monto total pero se <strong>redistribuye</strong> hacia los canales más rentables. Google Ads logró 181 conversiones a $2.180 c/u vs Meta a ~$21.600 c/u. Revenue en Shopify: $21.3M. ROAS actual: ~5.6x. Objetivo a 6 meses: ROAS 8x+.</p>
                </div>

                <div class="diagnostic-grid">
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Google Ads — $500.000/mes (40%)</h3>
                        <p>Cuadruplicar inversión en Google (de $125K a $500K). Performance Max es el canal más rentable. Cada peso invertido aquí genera más retorno que en cualquier otro canal.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Meta — Tráfico IG Stories — $185.000/mes (15%)</h3>
                        <p>Campaña siempre activa en Stories, solo Curicó +30km, mujeres 25-54. CPC esperado ~$10. Alimenta el tope del embudo con seguidores nuevos.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Meta — Retargeting/Conversiones — $370.000/mes (30%)</h3>
                        <p>Campañas ABO para seguidores, visitantes web e interactuados. Incluye WhatsApp. Escala en fechas clave (Día de la Madre, Black Friday, Navidad).</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Meta — Remarketing BOFU — $185.000/mes (15%)</h3>
                        <p>Recuperación de carritos abandonados + checkout sin completar. Catálogo dinámico Shopify + emails Klaviyo. Escala la semana previa a fechas comerciales.</p>
                    </div>
                </div>

                <div class="owner-card owner-card-yellow" style="margin-top:1.25rem;">
                    <h3>Plan de optimización: 6 meses (Abril - Septiembre 2026)</h3>
                    <p>El presupuesto se mantiene en $1.240.000/mes. Cada mes se evalúan los resultados y se redistribuye hacia lo que funciona mejor. Si en 3 meses Google sigue siendo 10x más eficiente, se le asigna más presupuesto. Si un canal no rinde, se reduce. El objetivo es pasar de ROAS 5.6x a 8x+ para octubre 2026.</p>
                </div>
            </div>

            <!-- Calendario comercial -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">5</span>
                    <div>
                        <h2>Calendario Comercial 2026</h2>
                        <p class="section-desc">Fechas clave para activar campañas estacionales</p>
                    </div>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-item calendar-urgent">
                        <span class="calendar-date">10 May</span>
                        <h4>Día de la Madre</h4>
                        <p>Mayor oportunidad del año. <strong>Activar desde 18 de abril.</strong> Gift cards + packs de tratamientos como regalo. Escalar presupuesto la semana del 4-9 mayo.</p>
                    </div>
                    <div class="calendar-item">
                        <span class="calendar-date">20 Jun</span>
                        <h4>Día del Padre</h4>
                        <p>Potenciar depilación láser hombre y masajes. Menor demanda pero segmento masculino en crecimiento.</p>
                    </div>
                    <div class="calendar-item">
                        <span class="calendar-date">Jul</span>
                        <h4>Vacaciones de Invierno</h4>
                        <p>Época baja. Promociones agresivas en faciales y packs de preparación verano.</p>
                    </div>
                    <div class="calendar-item">
                        <span class="calendar-date">Sep-Oct</span>
                        <h4>Primavera / Pre-verano</h4>
                        <p>Pico de demanda en depilación láser. Iniciar TOFU en septiembre para alimentar retargeting.</p>
                    </div>
                    <div class="calendar-item">
                        <span class="calendar-date">Nov</span>
                        <h4>Black Friday / Cyber</h4>
                        <p>Packs con descuento. Estructura de embudo completo con audiencias calentadas.</p>
                    </div>
                    <div class="calendar-item">
                        <span class="calendar-date">Dic</span>
                        <h4>Navidad / Fin de año</h4>
                        <p>Gift cards como regalo. Promociones de última hora.</p>
                    </div>
                </div>
            </div>
            <?php elseif ($tab === 'duenos'): ?>
            <!-- ==================== TAB: PARA LOS DUEÑOS ==================== -->
            <div class="page-header">
                <h1>Resumen para Dani y Nelson</h1>
                <span class="badge badge-period">Informe ejecutivo — Abril 2026</span>
            </div>

            <div class="owner-welcome">
                <h2>Hola Dani y Nelson,</h2>
                <p>Este resumen les muestra en palabras simples cómo están las ventas y la publicidad de Novavita, cuánto se está invirtiendo, qué resultados se están obteniendo, y <strong>qué necesitamos de ustedes</strong> para mejorar.</p>
            </div>

            <!-- Números clave -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">1</span>
                    <div>
                        <h2>Los números de Novavita</h2>
                        <p class="section-desc">Datos reales de ventas y publicidad (enero - abril 2026)</p>
                    </div>
                </div>

                <div class="owner-cards">
                    <div class="owner-card owner-card-green">
                        <h3>Ventas en la web: $21.283.266 CLP</h3>
                        <p>Se han realizado <strong>127 pedidos</strong> en Shopify con un ticket promedio de <strong>$168.915</strong>. Los productos más vendidos son los packs de depilación láser. De cada $1 que se invierte en publicidad, vuelven <strong>$5,6 en ventas</strong> — eso es un buen retorno.</p>
                    </div>

                    <div class="owner-card owner-card-green">
                        <h3>Google Ads: el canal más rentable</h3>
                        <p>La campaña de Google generó <strong>181 conversiones</strong> invirtiendo solo <strong>$395 mil</strong>. Cada conversión costó apenas <strong>$2.180</strong>. Cuando alguien busca "depilación láser Curicó" en Google y les aparece Novavita, la probabilidad de que compre es alta porque <strong>ya está buscando</strong> el servicio.</p>
                    </div>

                    <div class="owner-card owner-card-yellow">
                        <h3>Meta Ads (Instagram/Facebook): necesita optimización</h3>
                        <p>Se invirtieron <strong>$3.5 millones</strong> en Meta entre enero y abril. Se obtuvieron ventas, pero el costo por venta es <strong>10 veces más caro</strong> que en Google. La campaña principal mostraba los mismos anuncios a las mismas personas más de 20 veces. Vamos a reestructurar esto completamente.</p>
                    </div>

                    <div class="owner-card">
                        <h3>WhatsApp: el mejor canal para cerrar ventas</h3>
                        <p>Los anuncios que invitan a escribir por WhatsApp generan interesados a un costo <strong>mucho menor</strong> que intentar que compren directo en la web. Para un negocio local como Novavita, muchas clientas prefieren preguntar antes de comprar un tratamiento. Vamos a potenciar este canal.</p>
                    </div>
                </div>
            </div>

            <!-- Presupuesto -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">2</span>
                    <div>
                        <h2>Presupuesto de Marketing</h2>
                        <p class="section-desc">Cuánto se está invirtiendo y cómo lo vamos a redistribuir</p>
                    </div>
                </div>

                <div class="owner-card owner-card-purple" style="margin-bottom:1.25rem;">
                    <h3>Inversión actual: ~$1.240.000 CLP/mes</h3>
                    <p>En los últimos 3 meses se ha invertido un promedio de <strong>$1.240.000 al mes</strong> en publicidad: $1.115.000 en Meta (Instagram/Facebook) y $125.000 en Google. Proponemos <strong>mantener este monto</strong> pero redistribuirlo de forma más inteligente.</p>
                </div>

                <div class="owner-steps">
                    <div class="owner-step">
                        <div class="owner-step-number">Distribución propuesta</div>
                        <h3>Mismo presupuesto, mejor resultado</h3>
                        <p>En vez de poner casi todo en Meta (que es más caro), vamos a repartir así:</p>
                        <ul style="margin:0.75rem 0 0 1.2rem;color:var(--text-secondary);font-size:0.9rem;line-height:2;">
                            <li><strong>Google Ads: $500.000/mes (40%)</strong> — Es el canal más rentable. Cada $1 invertido en Google genera más ventas que en Meta. Vamos a cuadruplicar la inversión aquí.</li>
                            <li><strong>Meta - Atraer gente nueva (Stories): $185.000/mes (15%)</strong> — Anuncios en Historias de Instagram para mujeres de Curicó. Costo bajo por clic (~$10).</li>
                            <li><strong>Meta - Convertir en clientas: $370.000/mes (30%)</strong> — Anuncios de ofertas a quienes ya los siguen o visitaron la web. Incluye WhatsApp.</li>
                            <li><strong>Meta - Recuperar carritos: $185.000/mes (15%)</strong> — Recordar a quienes agregaron algo al carrito pero no compraron.</li>
                        </ul>
                    </div>
                </div>

                <div class="owner-card owner-card-yellow" style="margin-top:1.25rem;">
                    <h3>Plan de optimización: 6 meses</h3>
                    <p>El objetivo es <strong>mantener la inversión de $1.240.000/mes y aumentar las ventas</strong> optimizando progresivamente. Mes a mes vamos a analizar qué campañas funcionan mejor y redistribuir el presupuesto hacia lo que genera más ingresos. En 6 meses (para octubre 2026), el objetivo es duplicar el retorno por cada peso invertido.</p>
                    <ul style="margin:0.75rem 0 0 1.2rem;color:var(--text-secondary);font-size:0.88rem;line-height:1.8;">
                        <li><strong>Mes 1-2 (Abr-May):</strong> Reestructurar campañas, crear material nuevo, lanzar campaña Día de la Madre</li>
                        <li><strong>Mes 3-4 (Jun-Jul):</strong> Analizar datos, eliminar lo que no funciona, escalar lo que sí. Día del Padre</li>
                        <li><strong>Mes 5-6 (Ago-Sep):</strong> Campaña pre-verano de depilación láser, evaluar si se puede aumentar o reducir presupuesto según resultados</li>
                    </ul>
                    <p style="margin-top:0.75rem;">Cada mes les vamos a mostrar en este dashboard si la inversión se está recuperando. Si un canal no rinde, le quitamos presupuesto y lo movemos al que sí funciona.</p>
                </div>
            </div>

            <!-- Qué vamos a hacer -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">3</span>
                    <div>
                        <h2>¿Qué vamos a hacer?</h2>
                        <p class="section-desc">Plan de acción en 3 pasos</p>
                    </div>
                </div>

                <div class="owner-steps">
                    <div class="owner-step">
                        <div class="owner-step-number">Paso 1</div>
                        <h3>Atraer gente nueva que no conoce Novavita</h3>
                        <p>Vamos a crear anuncios que aparezcan en las <strong>Historias de Instagram</strong> de mujeres de 25 a 54 años que vivan cerca de Curicó y que les interese belleza, spa o cuidado personal. Serán videos cortos de Nelson mostrando la clínica y testimonios de clientas reales. El objetivo es que conozcan Novavita y empiecen a seguirlos.</p>
                    </div>

                    <div class="owner-step">
                        <div class="owner-step-number">Paso 2</div>
                        <h3>Convertir seguidoras en clientas</h3>
                        <p>A quienes ya los siguen o visitaron la web, les mostramos <strong>ofertas concretas</strong>: packs de depilación, faciales, gift cards. Llevan a la web o a WhatsApp. Para <strong>Día de la Madre</strong>, crearemos packs de regalo especiales.</p>
                    </div>

                    <div class="owner-step">
                        <div class="owner-step-number">Paso 3</div>
                        <h3>Recuperar ventas perdidas</h3>
                        <p>Hoy, más del <strong>60% de las personas que agregan algo al carrito no terminan de comprar</strong>. Vamos a enviarles emails automáticos y anuncios recordándoles su carrito. También vamos a aumentar la inversión en Google, que es donde más se vende.</p>
                    </div>
                </div>
            </div>

            <!-- Nelson como rostro -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">4</span>
                    <div>
                        <h2>Nelson: el rostro de Novavita</h2>
                        <p class="section-desc">La humanización de la marca es clave para diferenciarse</p>
                    </div>
                </div>

                <div class="owner-card owner-card-purple">
                    <h3>¿Por qué Nelson debería aparecer en los anuncios?</h3>
                    <p>Los datos muestran que los anuncios con <strong>personas reales</strong> (como los testimonios de "Karen") tienen el <strong>doble de efectividad</strong> que los anuncios genéricos. La gente en Curicó quiere saber <strong>quién</strong> les va a hacer el tratamiento.</p>
                    <p style="margin-top:0.75rem;">Nelson, como cosmetólogo, puede:</p>
                    <ul style="margin-top:0.5rem;margin-left:1.2rem;color:var(--text-secondary);font-size:0.88rem;line-height:1.8;">
                        <li>Explicar en 15 segundos cómo funciona el Soprano Titanium</li>
                        <li>Mostrar un tratamiento en curso (con permiso de la clienta)</li>
                        <li>Responder preguntas frecuentes: "¿Duele?", "¿Cuántas sesiones necesito?"</li>
                        <li>Hacer videos de "un día en Novavita" para Stories</li>
                    </ul>
                    <p style="margin-top:0.75rem;"><strong>No necesita ser producido:</strong> un video grabado con celular, con buena luz y hablando naturalmente funciona mejor que un video "profesional" que se sienta como publicidad.</p>
                </div>
            </div>

            <!-- Lo que necesitamos de ustedes -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number owner-action-number">!</span>
                    <div>
                        <h2>Lo que necesitamos de ustedes</h2>
                        <p class="section-desc">Sin estos elementos no podemos ejecutar la estrategia</p>
                    </div>
                </div>

                <div class="owner-todo">
                    <div class="owner-todo-item owner-todo-urgent">
                        <div class="owner-todo-check">1</div>
                        <div>
                            <h4>Videos de Nelson explicando tratamientos</h4>
                            <p>Necesitamos <strong>3 videos cortos</strong> (15-30 segundos) de Nelson hablando a cámara. No necesita guion perfecto, solo que sea natural. Temas sugeridos:</p>
                            <ul>
                                <li>"Hola, soy Nelson de Novavita. Les voy a mostrar cómo funciona el Soprano Titanium..."</li>
                                <li>"¿Sabían que la depilación láser es permanente? Les explico..."</li>
                                <li>"Este es el tratamiento facial más pedido en nuestra clínica..."</li>
                            </ul>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item owner-todo-urgent">
                        <div class="owner-todo-check">2</div>
                        <div>
                            <h4>Testimonios de 2-3 clientas reales</h4>
                            <p>Pedirle a clientas satisfechas que graben un <strong>video selfie de 10-20 segundos</strong> contando su experiencia. Formato: mirando a cámara, con buena luz, audio claro. Puede ser en la misma clínica después del tratamiento.</p>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item owner-todo-urgent">
                        <div class="owner-todo-check">3</div>
                        <div>
                            <h4>Video del tour de la clínica</h4>
                            <p>Un video de <strong>5-15 segundos</strong> mostrando las instalaciones: recepción, salas de tratamiento, equipamiento. Grabado con celular moviéndose por el espacio. Será el anuncio principal para Stories.</p>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item owner-todo-urgent">
                        <div class="owner-todo-check">4</div>
                        <div>
                            <h4>Definir packs de Día de la Madre</h4>
                            <p>Necesitamos <strong>2-3 packs especiales</strong> para regalar con precio fijo. Ejemplo: "Pack Relax Mamá" ($89.990 — Limpieza facial + Masaje), "Pack Belleza Total" ($149.990 — 3 sesiones faciales), Gift Card de monto libre.</p>
                            <span class="owner-todo-deadline">Necesario antes del 15 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item">
                        <div class="owner-todo-check">5</div>
                        <div>
                            <h4>Crear sección "Regalos Día de la Madre" en Shopify</h4>
                            <p>En la web se necesita una <strong>colección especial</strong> con los packs de regalo. <em>Nosotros podemos ayudar con esto si nos dan acceso al admin de Shopify.</em></p>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item">
                        <div class="owner-todo-check">6</div>
                        <div>
                            <h4>Fotos de antes/después</h4>
                            <p>Fotos de <strong>resultados de tratamientos</strong> (con permiso de las clientas). Idealmente depilación láser y faciales. Sirven para crear carruseles de anuncios.</p>
                            <span class="owner-todo-deadline">Cuando estén disponibles</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos pasos -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">5</span>
                    <div>
                        <h2>Próximos Pasos</h2>
                        <p class="section-desc">Cronograma de las próximas semanas</p>
                    </div>
                </div>

                <div class="owner-timeline">
                    <div class="timeline-item timeline-now">
                        <span class="timeline-date">11 - 15 Abr</span>
                        <h4>Esta semana</h4>
                        <p>Ustedes: graban los videos y definen los packs. Nosotros: reestructuramos las campañas, aumentamos Google Ads y preparamos las audiencias en Meta.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">16 - 18 Abr</span>
                        <h4>Preparación</h4>
                        <p>Creamos los anuncios con el material entregado, configuramos campañas y emails automáticos de carritos abandonados.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">18 Abr - 4 May</span>
                        <h4>Campaña Día de la Madre activa</h4>
                        <p>Se activan las 3 etapas. Monitoreamos y optimizamos diariamente.</p>
                    </div>
                    <div class="timeline-item timeline-hot">
                        <span class="timeline-date">5 - 10 May</span>
                        <h4>Semana del Día de la Madre</h4>
                        <p>Aumentamos presupuesto. Anuncios de urgencia. Es donde se cierra la mayor cantidad de ventas.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">15 May</span>
                        <h4>Primer informe de resultados</h4>
                        <p>Les presentamos resultados de la campaña de Día de la Madre y definimos los ajustes para el mes siguiente.</p>
                    </div>
                </div>
            </div>

            <div class="owner-footer">
                <p>Este dashboard se actualiza en tiempo real con los datos de Meta Ads, Google Ads y Shopify. Pueden entrar en cualquier momento para ver cómo van las campañas y las ventas. Cualquier duda, nos escriben.</p>
                <p style="margin-top:0.5rem;color:var(--accent);font-weight:600;">— Equipo Facand</p>
            </div>

            <?php endif; ?>
        </main>
    </div>
    <!-- Mobile bottom navigation -->
    <nav class="mobile-nav">
        <a href="?tab=campanas" class="<?php echo $tab === 'campanas' ? 'active' : ''; ?>">
            <span class="mnav-icon">&#x1f4ca;</span>Campañas
        </a>
        <a href="?tab=estrategia" class="<?php echo $tab === 'estrategia' ? 'active' : ''; ?>">
            <span class="mnav-icon">&#x1f3af;</span>Estrategia
        </a>
        <a href="?tab=duenos" class="<?php echo $tab === 'duenos' ? 'active' : ''; ?>">
            <span class="mnav-icon">&#x1f4cb;</span>Due&ntilde;os
        </a>
        <a href="logout.php">
            <span class="mnav-icon">&#x2190;</span>Salir
        </a>
    </nav>
    <script src="js/main.js?v=3"></script>
</body>
</html>
<!-- deploy test -->
