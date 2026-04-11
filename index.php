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
    <link rel="stylesheet" href="css/styles.css">
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
                    <h3>Google Ads es el canal más eficiente</h3>
                    <p>La campaña "Máximo rendimiento general" en Google logró <strong>181 conversiones con $395K invertidos</strong> (CPA ~$2.180). En Meta, el CPA de compras web es de ~$21.600. Google Ads es <strong>10x más eficiente</strong> en costo por conversión porque captura demanda existente (gente buscando "depilación láser Curicó"). Meta sirve para crear demanda nueva y retargeting.</p>
                </div>

                <div class="diagnostic-grid">
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Google Ads — 40% del presupuesto</h3>
                        <p>Aumentar inversión en Google. La campaña Performance Max está funcionando bien. Con más presupuesto, puede capturar más búsquedas. Si el presupuesto total es $500K/mes: <strong>$200K para Google</strong>.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-info">
                        <h3>Meta — Tráfico IG Stories — 15%</h3>
                        <p>Campaña siempre activa de bajo costo para alimentar el embudo con seguidores nuevos. Solo Stories, segmentación local. <strong>$75K/mes</strong>. CPC esperado: ~$10.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Meta — Retargeting/Conversiones — 30%</h3>
                        <p>Campañas ABO de conversión dirigidas a seguidores, visitantes web e interactuados. Incluye campaña de WhatsApp. <strong>$150K/mes</strong>. Este es el presupuesto que sube en fechas clave.</p>
                    </div>
                    <div class="diagnostic-card diagnostic-warning">
                        <h3>Meta — Remarketing BOFU — 15%</h3>
                        <p>Recuperación de carritos abandonados y checkout sin completar. Catálogo dinámico + urgencia. <strong>$75K/mes</strong>. Escalar en semanas previas a fechas comerciales.</p>
                    </div>
                </div>
            </div>

            <!-- Calendario comercial -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">4</span>
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
                <p>Este resumen les muestra en palabras simples qué está pasando con la publicidad digital de Novavita, qué vamos a hacer para mejorar las ventas, y <strong>qué necesitamos de ustedes</strong> para que la estrategia funcione.</p>
            </div>

            <!-- Situación actual -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">1</span>
                    <div>
                        <h2>¿Qué encontramos?</h2>
                        <p class="section-desc">Estado actual de la publicidad que se estaba manejando</p>
                    </div>
                </div>

                <div class="owner-cards">
                    <div class="owner-card owner-card-red">
                        <h3>La publicidad en Meta (Instagram/Facebook) no estaba generando ventas</h3>
                        <p>Se invirtieron <strong>$3.5 millones en Meta Ads</strong> entre enero y abril, pero la mayoría del presupuesto estaba en una campaña que mostraba los mismos anuncios a las mismas personas más de 20 veces. Eso es como poner el mismo cartel en la puerta de alguien 20 veces — después de la tercera vez, ya no lo miran.</p>
                    </div>

                    <div class="owner-card owner-card-green">
                        <h3>Google Ads sí está generando resultados</h3>
                        <p>La campaña de Google tiene <strong>181 conversiones</strong> con una inversión de $395 mil. Cuando alguien busca "depilación láser Curicó" en Google y les aparece Novavita, eso funciona bien porque esa persona <strong>ya está buscando</strong> lo que ustedes ofrecen.</p>
                    </div>

                    <div class="owner-card owner-card-yellow">
                        <h3>El cambio a Shopify trajo mejoras, pero también problemas</h3>
                        <p>La página se ve mejor y tiene más funcionalidades. Sin embargo, hay detalles técnicos que debemos revisar: un <strong>temporizador de oferta que expiró</strong> hace un mes (se ve mal), y necesitamos confirmar que cuando alguien compra en la web, Meta y Google registren esa venta correctamente.</p>
                    </div>

                    <div class="owner-card">
                        <h3>Los anuncios de WhatsApp son los más eficientes para ustedes</h3>
                        <p>Cuando un anuncio invita a escribir por WhatsApp, el costo por cada persona interesada es <strong>mucho menor</strong> que intentar que compren directo en la web. Esto tiene sentido: Novavita es un negocio local en Curicó, y la gente prefiere consultar antes de comprar un tratamiento.</p>
                    </div>
                </div>
            </div>

            <!-- Qué vamos a hacer -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">2</span>
                    <div>
                        <h2>¿Qué vamos a hacer?</h2>
                        <p class="section-desc">Plan de acción en 3 pasos</p>
                    </div>
                </div>

                <div class="owner-steps">
                    <div class="owner-step">
                        <div class="owner-step-number">Paso 1</div>
                        <h3>Atraer gente nueva que no conoce Novavita</h3>
                        <p>Vamos a crear anuncios que aparezcan en las <strong>Historias de Instagram</strong> de mujeres de 25 a 54 años que vivan cerca de Curicó y que les interese belleza, spa o cuidado personal. Estos anuncios serán videos cortos que muestren la clínica y testimonios de clientas reales. El objetivo es que conozcan Novavita y empiecen a seguirlos en Instagram.</p>
                        <span class="owner-step-cost">Inversión estimada: $3.000 - $5.000/día</span>
                    </div>

                    <div class="owner-step">
                        <div class="owner-step-number">Paso 2</div>
                        <h3>Convertir seguidoras en clientas</h3>
                        <p>A las personas que ya los siguen en Instagram o visitaron la página web, les vamos a mostrar <strong>anuncios con ofertas concretas</strong>: packs de depilación láser, tratamientos faciales, gift cards. Estos anuncios llevarán a la web de Novavita para comprar, o a WhatsApp para consultar. Para <strong>Día de la Madre</strong>, crearemos una campaña especial con packs de regalo.</p>
                        <span class="owner-step-cost">Inversión estimada: $5.000 - $8.000/día por servicio</span>
                    </div>

                    <div class="owner-step">
                        <div class="owner-step-number">Paso 3</div>
                        <h3>Recuperar a quienes no terminaron de comprar</h3>
                        <p>Si alguien entró a la web, puso un pack en el carrito pero no compró, le vamos a mostrar un anuncio recordándole que <strong>su carrito los está esperando</strong>. También vamos a activar emails automáticos (Klaviyo ya está instalado en Shopify) para estos casos.</p>
                        <span class="owner-step-cost">Inversión estimada: $3.000 - $5.000/día</span>
                    </div>
                </div>
            </div>

            <!-- Nelson como rostro -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">3</span>
                    <div>
                        <h2>Nelson: el rostro de Novavita</h2>
                        <p class="section-desc">La humanización de la marca es clave para diferenciarse</p>
                    </div>
                </div>

                <div class="owner-card owner-card-purple">
                    <h3>¿Por qué Nelson debería aparecer en los anuncios?</h3>
                    <p>Los datos muestran que los anuncios con <strong>personas reales</strong> (como los testimonios de "Karen") tienen el <strong>doble de efectividad</strong> que los anuncios genéricos con fotos de stock o catálogos. La gente en Curicó quiere saber <strong>quién</strong> les va a hacer el tratamiento.</p>
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
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril para Día de la Madre</span>
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
                            <p>Un video de <strong>5-15 segundos</strong> mostrando las instalaciones: recepción, salas de tratamiento, equipamiento. Puede ser grabado con celular moviéndose por el espacio. Este será el anuncio principal para Stories.</p>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item">
                        <div class="owner-todo-check">4</div>
                        <div>
                            <h4>Definir packs de Día de la Madre</h4>
                            <p>Necesitamos que definan <strong>2-3 packs especiales</strong> para regalar en Día de la Madre con precio fijo. Ejemplo: "Pack Relax Mamá" ($89.990 — Limpieza facial + Masaje), "Pack Belleza Total" ($149.990 — 3 sesiones faciales), Gift Card de monto libre.</p>
                            <span class="owner-todo-deadline">Necesario antes del 15 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item">
                        <div class="owner-todo-check">5</div>
                        <div>
                            <h4>Actualizar la web (Shopify)</h4>
                            <p>Necesitamos que en la web se cree una <strong>sección "Regalos Día de la Madre"</strong> con los packs definidos. También hay que quitar el countdown expirado (10 de marzo) que todavía aparece en la página principal. <em>Nosotros podemos ayudar con esto si nos dan acceso.</em></p>
                            <span class="owner-todo-deadline">Necesario antes del 18 de abril</span>
                        </div>
                    </div>

                    <div class="owner-todo-item">
                        <div class="owner-todo-check">6</div>
                        <div>
                            <h4>Fotos de antes/después</h4>
                            <p>Si tienen <strong>fotos de resultados de tratamientos</strong> (con permiso de las clientas), nos sirven para crear carruseles de anuncios. Idealmente de depilación láser y faciales que son los servicios más buscados.</p>
                            <span class="owner-todo-deadline">Cuando estén disponibles</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos pasos -->
            <div class="section-block">
                <div class="section-block-header">
                    <span class="section-block-number">4</span>
                    <div>
                        <h2>Próximos Pasos</h2>
                        <p class="section-desc">Cronograma de las próximas semanas</p>
                    </div>
                </div>

                <div class="owner-timeline">
                    <div class="timeline-item timeline-now">
                        <span class="timeline-date">11 - 15 Abr</span>
                        <h4>Esta semana</h4>
                        <p>Ustedes: graban los videos y definen los packs. Nosotros: corregimos los temas técnicos de la web y preparamos las audiencias en Meta.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">16 - 18 Abr</span>
                        <h4>Preparación</h4>
                        <p>Nosotros: creamos los anuncios con el material que nos entreguen, configuramos las campañas y los emails automáticos de carritos abandonados.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">18 Abr - 4 May</span>
                        <h4>Campaña activa</h4>
                        <p>Se activan las 3 etapas de publicidad. Monitoreamos diariamente y optimizamos en base a resultados.</p>
                    </div>
                    <div class="timeline-item timeline-hot">
                        <span class="timeline-date">5 - 10 May</span>
                        <h4>Semana del Día de la Madre</h4>
                        <p>Aumentamos presupuesto al máximo. Anuncios de urgencia: "Últimos días para reservar el regalo de mamá". Es donde se cierra la mayor cantidad de ventas.</p>
                    </div>
                </div>
            </div>

            <div class="owner-footer">
                <p>Este dashboard se actualiza en tiempo real con los datos de Meta Ads y Google Ads. Pueden entrar en cualquier momento para ver cómo van las campañas. Cualquier duda, nos escriben.</p>
                <p style="margin-top:0.5rem;color:var(--accent);font-weight:600;">— Equipo Facand</p>
            </div>

            <?php endif; ?>
        </main>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
<!-- deploy test -->
