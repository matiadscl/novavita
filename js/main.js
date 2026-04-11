/**
 * Novavita Dashboard — Frontend JS
 * Consume api.php y renderiza datos dinámicamente
 */

let DATA = { by_month: {}, by_campaign: {} };
let GDATA = { campaigns: {}, by_month: {} };
let SDATA = { summary: {}, by_month: {} };
const SINCE = '2025-08-01';
const UNTIL = new Date().toISOString().slice(0, 10);

document.addEventListener('DOMContentLoaded', () => {
    const diasMadreEl = document.getElementById('diasMadre');
    if (diasMadreEl) {
        const diff = Math.ceil((new Date('2026-05-10') - new Date()) / 86400000);
        diasMadreEl.textContent = diff;
    }

    const tab = new URLSearchParams(window.location.search).get('tab') || 'campanas';
    if (tab === 'campanas') {
        loadInsights();
    } else {
        showLoading(false);
        animateCards();
    }

    // Highlight urgent todos on owners tab
    if (tab === 'duenos') {
        document.querySelectorAll('.owner-todo-urgent').forEach(el => {
            el.style.animation = 'none';
        });
    }
});

async function loadInsights() {
    showLoading(true);
    try {
        const res = await fetch(`api.php?action=insights&since=${SINCE}&until=${UNTIL}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Error de API');
        DATA = json;

        const badge = document.getElementById('periodBadge');
        if (badge) badge.textContent = `${json.period.since} — ${json.period.until}`;

        // Load Google Ads and Shopify in parallel
        fetch(`api.php?action=google_insights&since=${SINCE}&until=${UNTIL}`)
            .then(r => r.json())
            .then(g => { if (g.ok) GDATA = g.data; renderGoogleAds(); })
            .catch(() => {});

        fetch(`api.php?action=shopify&since=${SINCE}`)
            .then(r => r.json())
            .then(s => { if (s.ok) SDATA = s.data; renderShopify(); })
            .catch(() => {});

        renderCampaigns();
        renderFunnel();
        renderFunnelOpportunities();
        renderMonthly();
        renderDiagnostics();
    } catch (err) {
        console.error(err);
        const kpi = document.getElementById('kpiGrid');
        if (kpi) kpi.innerHTML = `<div class="alert alert-error">Error: ${err.message}</div>`;
    }
    showLoading(false);
}

function showLoading(show) {
    const el = document.getElementById('loading');
    if (el) el.style.display = show ? 'flex' : 'none';
}

function clp(n) { return '$' + Math.round(n).toLocaleString('es-CL'); }
function num(n) { return Math.round(n).toLocaleString('es-CL'); }

// ==================== CAMPAÑAS ====================

function renderCampaigns() {
    const filter = document.getElementById('statusFilter')?.value || 'all';
    const bc = DATA.by_campaign || {};

    let campaigns = Object.entries(bc).map(([id, c]) => ({ id, ...c }));
    if (filter === 'ACTIVE') campaigns = campaigns.filter(c => c.effective_status === 'ACTIVE');
    if (filter === 'PAUSED') campaigns = campaigns.filter(c => c.effective_status !== 'ACTIVE');
    campaigns.sort((a, b) => b.total_spend - a.total_spend);

    const t = campaigns.reduce((a, c) => {
        a.spend += c.total_spend; a.imp += c.total_impressions; a.reach += c.total_reach;
        a.clk += c.total_link_clicks; a.pur += c.total_purchases; a.msg += c.total_messaging;
        return a;
    }, { spend: 0, imp: 0, reach: 0, clk: 0, pur: 0, msg: 0 });

    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi-card"><span class="kpi-label">Inversión Total</span><span class="kpi-value">${clp(t.spend)}</span><span class="kpi-detail">${campaigns.length} campañas</span></div>
        <div class="kpi-card"><span class="kpi-label">Impresiones</span><span class="kpi-value">${num(t.imp)}</span><span class="kpi-detail">CPM: ${t.imp > 0 ? clp(t.spend / t.imp * 1000) : '-'}</span></div>
        <div class="kpi-card"><span class="kpi-label">Clics</span><span class="kpi-value">${num(t.clk)}</span><span class="kpi-detail">CPC: ${t.clk > 0 ? clp(t.spend / t.clk) : '-'} | CTR: ${t.imp > 0 ? (t.clk/t.imp*100).toFixed(2)+'%' : '-'}</span></div>
        <div class="kpi-card kpi-highlight"><span class="kpi-label">Compras</span><span class="kpi-value">${num(t.pur)}</span><span class="kpi-detail">CPA: ${t.pur > 0 ? clp(t.spend / t.pur) : '-'}</span></div>
        <div class="kpi-card"><span class="kpi-label">Conversaciones WA</span><span class="kpi-value">${num(t.msg)}</span><span class="kpi-detail">Leads WhatsApp</span></div>
        <div class="kpi-card"><span class="kpi-label">Alcance</span><span class="kpi-value">${num(t.reach)}</span><span class="kpi-detail">Personas únicas</span></div>`;

    let rows = campaigns.map(c => {
        const sc = c.effective_status === 'ACTIVE' ? 'success' : 'inactive';
        const sl = c.effective_status === 'ACTIVE' ? 'Activa' : 'Pausada';
        return `<tr class="campaign-row" onclick="loadCampaignDetail('${c.id}','${c.name.replace(/'/g,"\\'")}')">
            <td class="cell-name">${esc(c.name)}</td>
            <td><span class="badge badge-${sc}">${sl}</span></td>
            <td class="cell-small">${c.objective}</td>
            <td class="cell-number cell-bold">${num(c.total_purchases)}</td>
            <td class="cell-number">${c.total_purchases > 0 ? clp(c.total_spend / c.total_purchases) : '-'}</td>
            <td class="cell-number">${clp(c.total_spend)}</td>
            <td class="cell-number">${num(c.total_impressions)}</td>
            <td class="cell-number">${num(c.total_link_clicks)}</td>
            <td class="cell-number">${c.total_ctr}%</td>
            <td class="cell-number">${clp(c.total_cpm)}</td>
            <td class="cell-number">${num(c.total_messaging)}</td></tr>`;
    }).join('');

    document.getElementById('campaignTable').innerHTML = `<table class="data-table">
        <thead><tr><th>Campaña</th><th>Estado</th><th>Objetivo</th><th>Compras</th><th>CPA</th><th>Inversión</th><th>Impresiones</th><th>Clics</th><th>CTR</th><th>CPM</th><th>WA</th></tr></thead>
        <tbody>${rows}</tbody></table>`;

    renderFunnel();
    animateCards();
}

async function loadCampaignDetail(id, name) {
    const detail = document.getElementById('campaignDetail');
    detail.style.display = 'block';
    document.getElementById('detailTitle').textContent = `Detalle: ${name}`;
    document.getElementById('adsetTable').innerHTML = '<p style="color:var(--text-muted);padding:1rem;">Cargando...</p>';
    document.getElementById('adTable').innerHTML = '';

    try {
        const res = await fetch(`api.php?action=campaign_detail&id=${id}&since=${SINCE}&until=${UNTIL}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.error);

        if (json.adsets.length > 0) {
            const r = json.adsets.sort((a,b) => b.spend - a.spend).map(a => `<tr>
                <td class="cell-name">${esc(a.name)}</td>
                <td class="cell-number cell-bold">${a.purchases}</td>
                <td class="cell-number">${a.cost_per_purchase > 0 ? clp(a.cost_per_purchase) : '-'}</td>
                <td class="cell-number">${clp(a.spend)}</td>
                <td class="cell-number">${num(a.impressions)}</td>
                <td class="cell-number">${num(a.reach)}</td>
                <td class="cell-number">${num(a.link_clicks)}</td>
                <td class="cell-number">${a.ctr}%</td>
                <td class="cell-number">${a.frequency}x</td>
                <td class="cell-number">${a.messaging}</td></tr>`).join('');
            document.getElementById('adsetTable').innerHTML = `<table class="data-table"><thead><tr><th>Ad Set</th><th>Compras</th><th>CPA</th><th>Inversión</th><th>Impresiones</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>Frec.</th><th>WA</th></tr></thead><tbody>${r}</tbody></table>`;
        } else {
            document.getElementById('adsetTable').innerHTML = '<p style="color:var(--text-muted);padding:1rem;">Sin ad sets</p>';
        }

        if (json.ads.length > 0) {
            const r = json.ads.sort((a,b) => b.spend - a.spend).map(a => `<tr>
                <td class="cell-name">${esc(a.name)}</td>
                <td class="cell-small">${esc(a.adset)}</td>
                <td class="cell-number cell-bold">${a.purchases}</td>
                <td class="cell-number">${a.cost_per_purchase > 0 ? clp(a.cost_per_purchase) : '-'}</td>
                <td class="cell-number">${clp(a.spend)}</td>
                <td class="cell-number">${num(a.impressions)}</td>
                <td class="cell-number">${num(a.link_clicks)}</td>
                <td class="cell-number">${a.ctr}%</td></tr>`).join('');
            document.getElementById('adTable').innerHTML = `<table class="data-table data-table-compact"><thead><tr><th>Anuncio</th><th>Ad Set</th><th>Compras</th><th>CPA</th><th>Inversión</th><th>Impresiones</th><th>Clics</th><th>CTR</th></tr></thead><tbody>${r}</tbody></table>`;
        }
        detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        document.getElementById('adsetTable').innerHTML = `<div class="alert alert-error">Error: ${err.message}</div>`;
    }
}

// ==================== EMBUDO ====================

function svgFunnel(stages) {
    if (!stages || !stages.length) return '';
    const W = 650, SH = 68, GAP = 4, MAXW = W - 60, MINW = 100;
    const n = stages.length, step = (MAXW - MINW) / (n - 1 || 1);
    const H = n * SH + (n - 1) * GAP;
    let o = `<svg viewBox="0 0 ${W} ${H}" width="100%" xmlns="http://www.w3.org/2000/svg" style="max-width:${W}px;display:block;margin:0 auto">`;
    o += `<defs><filter id="sh"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".18"/></filter></defs>`;

    stages.forEach((s, i) => {
        const tW = MAXW - i * step, bW = Math.max(MINW, MAXW - (i + 1) * step);
        const tX = (W - tW) / 2, bX = (W - bW) / 2, y = i * (SH + GAP);
        o += `<polygon points="${tX},${y} ${tX+tW},${y} ${bX+bW},${y+SH} ${bX},${y+SH}" fill="${s.col}" filter="url(#sh)"/>`;
        const cy = y + SH / 2;
        o += `<text x="${W/2}" y="${cy-10}" text-anchor="middle" fill="white" font-size="11" font-weight="700" font-family="-apple-system,Segoe UI,Arial">${s.l}</text>`;
        o += `<text x="${W/2}" y="${cy+10}" text-anchor="middle" fill="rgba(255,255,255,.95)" font-size="16" font-weight="800" font-family="-apple-system,Segoe UI,Arial">${s.f}</text>`;
        if (s.pct !== null && i > 0) {
            o += `<text x="${(W+tW)/2+12}" y="${y+18}" fill="#9aa0b0" font-size="11" font-family="-apple-system,Segoe UI,Arial" font-weight="600">${s.pct}</text>`;
        }
    });
    return o + '</svg>';
}

function getFunnelData() {
    const filter = document.getElementById('statusFilter')?.value || 'all';
    const bc = DATA.by_campaign || {};
    let campaigns = Object.values(bc);
    if (filter === 'ACTIVE') campaigns = campaigns.filter(c => c.effective_status === 'ACTIVE');
    if (filter === 'PAUSED') campaigns = campaigns.filter(c => c.effective_status !== 'ACTIVE');

    return campaigns.reduce((a, c) => {
        a.imp += c.total_impressions || 0; a.clk += c.total_link_clicks || 0;
        a.land += c.total_landing_views || 0; a.atc += c.total_add_to_cart || 0;
        a.co += c.total_checkouts || 0; a.pur += c.total_purchases || 0;
        a.spend += c.total_spend || 0;
        return a;
    }, { imp: 0, clk: 0, land: 0, atc: 0, co: 0, pur: 0, spend: 0 });
}

function renderFunnel() {
    const el = document.getElementById('funnelContainer');
    if (!el) return;
    const f = getFunnelData();
    const r = (a, b) => b > 0 ? (a / b * 100).toFixed(1) + '%' : '-';

    const stages = [
        { l: 'Impresiones', f: num(f.imp), col: '#4fc3f7', pct: null },
        { l: 'Clics en enlace', f: num(f.clk), col: '#2196f3', pct: r(f.clk, f.imp) },
        { l: 'Vistas de página', f: num(f.land), col: '#1976d2', pct: r(f.land, f.clk) },
        { l: 'Agregar al carrito', f: num(f.atc), col: '#f59e0b', pct: r(f.atc, f.land) },
        { l: 'Iniciar checkout', f: num(f.co), col: '#fd7e14', pct: r(f.co, f.atc) },
        { l: 'Compras', f: num(f.pur), col: '#10b981', pct: r(f.pur, f.co) },
    ];

    const overall = f.imp > 0 ? (f.pur / f.imp * 100).toFixed(3) : '0';

    el.innerHTML = `<div class="funnel-layout">
        <div class="funnel-svg">${svgFunnel(stages)}</div>
        <div class="funnel-stats">
            <div class="funnel-stat"><span class="funnel-stat-val">${overall}%</span><span class="funnel-stat-lbl">Conversión Global</span><span class="funnel-stat-desc">Impresión a Compra</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${r(f.clk,f.imp)}</span><span class="funnel-stat-lbl">CTR</span><span class="funnel-stat-desc">Imp. a Clic</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${r(f.atc,f.land)}</span><span class="funnel-stat-lbl">Landing a Carrito</span><span class="funnel-stat-desc">Interés de compra</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${r(f.pur,f.co)}</span><span class="funnel-stat-lbl">Checkout a Compra</span><span class="funnel-stat-desc">Cierre</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${f.pur > 0 ? clp(f.spend/f.pur) : '-'}</span><span class="funnel-stat-lbl">CPA</span><span class="funnel-stat-desc">Costo por compra</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${clp(f.spend)}</span><span class="funnel-stat-lbl">Inversión</span><span class="funnel-stat-desc">Periodo seleccionado</span></div>
        </div></div>`;
}

// ==================== OPORTUNIDADES DEL EMBUDO ====================

function renderFunnelOpportunities() {
    const el = document.getElementById('funnelOpportunities');
    if (!el) return;
    const f = getFunnelData();
    const opps = [];

    // CTR
    const ctr = f.imp > 0 ? f.clk / f.imp * 100 : 0;
    if (ctr < 2) {
        opps.push({ type: 'critical', level: 'Impresiones > Clics', rate: ctr.toFixed(2) + '%', title: 'CTR bajo — los anuncios no generan suficiente interés',
            text: 'El CTR global es menor al 2%. Las creatividades no están captando la atención. Se necesitan <strong>nuevos formatos de video corto</strong> (testimonios tipo "Karen") y <strong>segmentación más precisa</strong>. Los anuncios de texto estático y catálogos genéricos deben reemplazarse por contenido de valor.' });
    }

    // Click to Landing
    const clkToLand = f.clk > 0 ? f.land / f.clk * 100 : 0;
    if (clkToLand < 50) {
        opps.push({ type: 'warning', level: 'Clics > Vistas de página', rate: clkToLand.toFixed(1) + '%', title: 'Alta pérdida entre clic y landing page',
            text: `Solo el ${clkToLand.toFixed(1)}% de los clics llegan a ver la página. Esto indica <strong>velocidad de carga lenta</strong> en novavita.cl, <strong>URLs de destino incorrectas</strong>, o que el usuario abandona antes de cargar. Verificar velocidad de Shopify con PageSpeed Insights y que las URLs de los anuncios apunten a las páginas correctas.` });
    }

    // Landing to ATC
    const landToAtc = f.land > 0 ? f.atc / f.land * 100 : 0;
    if (landToAtc < 25) {
        opps.push({ type: 'warning', level: 'Vistas > Agregar al carrito', rate: landToAtc.toFixed(1) + '%', title: 'Baja intención de compra en la web',
            text: `El ${landToAtc.toFixed(1)}% de quienes ven la página agregan al carrito. Se necesita <strong>mejorar las páginas de producto</strong>: agregar reseñas visibles (Loox ya está instalado), fotos de antes/después, precios claros, y un <strong>CTA más prominente</strong>. También considerar campañas de WhatsApp como alternativa para cerrar la venta por chat.` });
    }

    // ATC to Checkout
    const atcToCo = f.atc > 0 ? f.co / f.atc * 100 : 0;
    if (atcToCo < 40) {
        opps.push({ type: 'warning', level: 'Carrito > Checkout', rate: atcToCo.toFixed(1) + '%', title: 'Abandono de carrito significativo',
            text: `${(100-atcToCo).toFixed(0)}% abandona después de agregar al carrito. Posibles causas: <strong>costos de envío inesperados</strong>, falta de métodos de pago, o proceso confuso. Implementar <strong>emails de carrito abandonado</strong> con Klaviyo (ya instalado) y crear campañas de retargeting BOFU dirigidas a esta audiencia.` });
    }

    // Checkout to Purchase
    const coToPur = f.co > 0 ? f.pur / f.co * 100 : 0;
    if (coToPur < 30) {
        opps.push({ type: 'critical', level: 'Checkout > Compra', rate: coToPur.toFixed(1) + '%', title: 'Alta fricción en el proceso de pago',
            text: `Solo ${coToPur.toFixed(1)}% completa la compra después de iniciar checkout. Esto es <strong>crítico</strong>. Verificar: ¿el proceso de pago de Mercado Pago es fluido? ¿Hay errores técnicos? ¿Los precios son claros? Simplificar el checkout al mínimo de pasos. Un checkout roto destruye todo el embudo.` });
    } else if (coToPur > 0) {
        opps.push({ type: 'info', level: 'Checkout > Compra', rate: coToPur.toFixed(1) + '%', title: 'Tasa de cierre aceptable',
            text: `${coToPur.toFixed(1)}% de conversión en checkout. Está en rango aceptable. Mantener y monitorear. Se puede mejorar con <strong>sellos de confianza</strong>, garantías visibles y múltiples opciones de pago.` });
    }

    // Oportunidad general: WhatsApp
    opps.push({ type: 'info', level: 'Canal alternativo', rate: '', title: 'WhatsApp como canal de conversión paralelo',
        text: 'Para un negocio local en Curicó, muchos clientes prefieren consultar por WhatsApp antes de comprar online. Los datos muestran que el costo por conversación WA es significativamente menor que el CPA web. <strong>Mantener siempre una campaña de conversaciones activa</strong> como complemento al embudo web.' });

    el.innerHTML = opps.map((o, i) => `<div class="finding-item finding-${o.type}">
        <span class="finding-number">${i + 1}</span>
        <div>
            <div class="finding-level"><span class="finding-level-tag">${o.level}</span>${o.rate ? `<span class="finding-level-rate">${o.rate}</span>` : ''}</div>
            <h4>${o.title}</h4>
            <p>${o.text}</p>
        </div>
    </div>`).join('');
}

// ==================== DESGLOSE MENSUAL ====================

function renderMonthly() {
    const bm = DATA.by_month || {};
    const months = Object.entries(bm).sort(([a], [b]) => a.localeCompare(b));

    const filter = document.getElementById('monthFilter');
    if (!filter) return;

    // Solo agregar opciones si no se han agregado
    if (filter.options.length <= 1) {
        months.forEach(([key, m]) => {
            const opt = document.createElement('option');
            opt.value = key; opt.textContent = m.label;
            filter.appendChild(opt);
        });
    }

    // Tabla comparativa
    let rows = months.map(([key, m]) => `<tr>
        <td class="cell-name" style="cursor:pointer" onclick="document.getElementById('monthFilter').value='${key}';renderMonthDetail()"><strong>${m.label}</strong></td>
        <td class="cell-number">${clp(m.spend)}</td>
        <td class="cell-number">${num(m.impressions)}</td>
        <td class="cell-number">${num(m.reach)}</td>
        <td class="cell-number">${num(m.link_clicks)}</td>
        <td class="cell-number">${m.ctr}%</td>
        <td class="cell-number">${clp(m.cpm)}</td>
        <td class="cell-number cell-bold">${m.purchases}</td>
        <td class="cell-number">${m.cost_per_purchase > 0 ? clp(m.cost_per_purchase) : '-'}</td>
        <td class="cell-number">${m.messaging}</td>
        <td class="cell-number">${m.add_to_cart}</td>
        <td class="cell-number">${m.checkouts}</td></tr>`).join('');

    document.getElementById('monthTable').innerHTML = `<table class="data-table"><thead><tr>
        <th>Mes</th><th>Inversión</th><th>Impresiones</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>CPM</th><th>Compras</th><th>CPA</th><th>WA</th><th>ATC</th><th>Checkout</th>
    </tr></thead><tbody>${rows}</tbody></table>`;

    renderMonthDetail();
}

function renderMonthDetail() {
    const key = document.getElementById('monthFilter')?.value || 'all';
    const container = document.getElementById('monthCampaignTable');
    const title = document.getElementById('monthDetailTitle');

    if (key === 'all') {
        title.textContent = 'Selecciona un mes para ver detalle';
        container.innerHTML = '<p style="color:var(--text-muted);padding:1rem;">Haz clic en un mes de la tabla o selecciónalo del desplegable.</p>';
        return;
    }

    const m = DATA.by_month?.[key];
    if (!m) { container.innerHTML = ''; return; }
    title.textContent = `Campañas — ${m.label}`;

    const rows = (m.campaigns || []).sort((a, b) => b.spend - a.spend).map(c => `<tr>
        <td class="cell-name">${esc(c.campaign_name)}</td>
        <td class="cell-number">${clp(c.spend)}</td>
        <td class="cell-number">${num(c.impressions)}</td>
        <td class="cell-number">${num(c.reach)}</td>
        <td class="cell-number">${num(c.link_clicks)}</td>
        <td class="cell-number">${c.ctr}%</td>
        <td class="cell-number">${c.frequency}x</td>
        <td class="cell-number cell-bold">${c.purchases}</td>
        <td class="cell-number">${c.cost_per_purchase > 0 ? clp(c.cost_per_purchase) : '-'}</td>
        <td class="cell-number">${c.messaging_started}</td>
        <td class="cell-number">${c.add_to_cart}</td>
        <td class="cell-number">${c.video_views}</td></tr>`).join('');

    container.innerHTML = `<table class="data-table"><thead><tr>
        <th>Campaña</th><th>Inversión</th><th>Impresiones</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>Frec.</th><th>Compras</th><th>CPA</th><th>WA</th><th>ATC</th><th>Videos</th>
    </tr></thead><tbody>${rows}</tbody></table>`;
}

// ==================== SHOPIFY ====================

function renderShopify() {
    const el = document.getElementById('shopifyContent');
    if (!el) return;

    const s = SDATA.summary || {};
    const bm = SDATA.by_month || {};

    if (!s.total_orders) {
        el.innerHTML = '<p style="color:var(--text-muted)">Sin datos de Shopify.</p>';
        return;
    }

    let html = `<div class="kpi-grid" style="margin-bottom:1.5rem;">
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Pedidos</span><span class="kpi-value">${num(s.total_orders)}</span><span class="kpi-detail">${s.paid_orders} pagados, ${s.cancelled} cancelados</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Revenue Total</span><span class="kpi-value">${clp(s.total_revenue)}</span><span class="kpi-detail">Ventas brutas en Shopify</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Ticket Promedio</span><span class="kpi-value">${clp(s.avg_ticket)}</span><span class="kpi-detail">Por pedido</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Tasa de pago</span><span class="kpi-value">${s.total_orders > 0 ? (s.paid_orders/s.total_orders*100).toFixed(0) : 0}%</span><span class="kpi-detail">${s.paid_orders} de ${s.total_orders} pagados</span></div>
    </div>`;

    // Monthly breakdown
    const monthKeys = Object.keys(bm).sort();
    if (monthKeys.length > 0) {
        const mLabels = {1:'Ene',2:'Feb',3:'Mar',4:'Abr',5:'May',6:'Jun',7:'Jul',8:'Ago',9:'Sep',10:'Oct',11:'Nov',12:'Dic'};
        let mRows = monthKeys.map(mk => {
            const m = bm[mk];
            const mNum = parseInt(mk.split('-')[1]);
            const label = (mLabels[mNum] || mk) + ' ' + mk.split('-')[0];
            return `<tr>
                <td><strong>${label}</strong></td>
                <td class="cell-number cell-bold">${m.orders}</td>
                <td class="cell-number">${clp(m.revenue)}</td>
                <td class="cell-number">${clp(m.avg_ticket)}</td>
                <td class="cell-number">${m.paid}</td>
                <td class="cell-number">${m.cancelled}</td>
                <td class="cell-number">${m.items}</td></tr>`;
        }).join('');

        html += `<h4 style="color:var(--text-secondary);margin-bottom:0.5rem;">Ventas por Mes</h4>
        <div class="table-responsive"><table class="data-table data-table-compact">
            <thead><tr><th>Mes</th><th>Pedidos</th><th>Revenue</th><th>Ticket Prom.</th><th>Pagados</th><th>Cancelados</th><th>Items</th></tr></thead>
            <tbody>${mRows}</tbody></table></div>`;
    }

    // Top products
    const top = s.top_products || {};
    const topEntries = Object.entries(top);
    if (topEntries.length > 0) {
        let pRows = topEntries.map(([name, data]) => `<tr>
            <td class="cell-name">${esc(name)}</td>
            <td class="cell-number cell-bold">${data.qty}</td>
            <td class="cell-number">${clp(data.revenue)}</td></tr>`).join('');

        html += `<h4 style="margin-top:1.25rem;color:var(--text-secondary);margin-bottom:0.5rem;">Top 10 Productos Vendidos</h4>
        <div class="table-responsive"><table class="data-table data-table-compact">
            <thead><tr><th>Producto</th><th>Unidades</th><th>Revenue</th></tr></thead>
            <tbody>${pRows}</tbody></table></div>`;
    }

    el.innerHTML = html;
}

// ==================== DIAGNÓSTICO ====================

function renderDiagnostics() {
    const bc = DATA.by_campaign || {};
    const gc = GDATA.campaigns || {};
    const diags = [];

    // --- Meta Ads diagnostics ---
    Object.values(bc).forEach(c => {
        const months = Object.values(c.months || {});
        months.forEach(m => {
            if (m.frequency > 10) {
                diags.push({ type: 'danger', title: `[Meta] Fatiga de audiencia: ${c.name}`, text: `Frecuencia de <strong>${m.frequency}x</strong> en ${m.month_label}. Audiencia sobre-saturada — las mismas personas ven el anuncio demasiadas veces.` });
            }
        });
        if (c.total_purchases > 0 && c.total_cost_purchase > 15000) {
            diags.push({ type: 'warning', title: `[Meta] CPA alto: ${c.name}`, text: `Costo por compra de <strong>${clp(c.total_cost_purchase)}</strong>. Objetivo recomendado: menor a $10.000 CLP.` });
        }
        if (c.total_spend > 50000 && c.total_purchases === 0 && c.objective === 'Ventas') {
            diags.push({ type: 'danger', title: `[Meta] Sin conversiones: ${c.name}`, text: `<strong>${clp(c.total_spend)}</strong> invertidos sin compras registradas. Verificar tracking o cambiar objetivo.` });
        }
        if (c.effective_status !== 'ACTIVE' && c.total_spend > 100000) {
            diags.push({ type: 'info', title: `[Meta] Campaña pausada con historial: ${c.name}`, text: `Inversión acumulada de <strong>${clp(c.total_spend)}</strong>. Evaluar si vale reactivar con mejores creatividades o reasignar presupuesto.` });
        }
    });

    // WhatsApp vs Web efficiency
    let metaSpendSales = 0, metaPurchases = 0, metaSpendLeads = 0, metaMsg = 0;
    Object.values(bc).forEach(c => {
        if (c.objective === 'Ventas' && c.total_purchases > 0) { metaSpendSales += c.total_spend; metaPurchases += c.total_purchases; }
        if (c.total_messaging > 0) { metaSpendLeads += c.total_spend; metaMsg += c.total_messaging; }
    });
    if (metaMsg > 0 && metaPurchases > 0) {
        const cpaSales = Math.round(metaSpendSales / metaPurchases);
        const cpaMsg = Math.round(metaSpendLeads / metaMsg);
        diags.push({ type: 'info', title: '[Meta] WhatsApp más eficiente que compra web', text: `Costo/conversación WA: <strong>${clp(cpaMsg)}</strong> vs costo/compra web: <strong>${clp(cpaSales)}</strong>. Para un negocio local, WhatsApp permite cerrar ventas con mayor tasa de conversión.` });
    }

    // --- Google Ads diagnostics ---
    Object.values(gc).forEach(c => {
        if (c.total_conversions > 0 && c.total_cost_conv > 0) {
            if (c.total_cost_conv < 3000) {
                diags.push({ type: 'info', title: `[Google] Excelente rendimiento: ${c.name}`, text: `CPA de <strong>${clp(c.total_cost_conv)}</strong> con ${c.total_conversions} conversiones. Google Ads está siendo el canal más eficiente. Considerar aumentar presupuesto.` });
            } else if (c.total_cost_conv > 10000) {
                diags.push({ type: 'warning', title: `[Google] CPA alto: ${c.name}`, text: `Costo por conversión de <strong>${clp(c.total_cost_conv)}</strong>. Revisar keywords y segmentación.` });
            }
        }

        // Tendencia decreciente
        const months = Object.values(c.months || {}).sort((a, b) => (a.month_key || '').localeCompare(b.month_key || ''));
        if (months.length >= 3) {
            const last = months[months.length - 1];
            const prev = months[months.length - 2];
            if (last.conversions < prev.conversions * 0.5 && prev.conversions > 5) {
                diags.push({ type: 'warning', title: `[Google] Caída de conversiones: ${c.name}`, text: `Las conversiones cayeron de <strong>${prev.conversions}</strong> a <strong>${last.conversions}</strong> en el último mes. Revisar presupuesto, pujas y calidad de landing pages.` });
            }
        }

        if (c.status === 'PAUSED' && c.total_spend > 50000) {
            diags.push({ type: 'warning', title: `[Google] Campaña pausada: ${c.name}`, text: `Campaña con <strong>${c.total_conversions} conversiones</strong> históricas está pausada. Evaluar reactivación.` });
        }
    });

    // --- Cross-platform comparison ---
    let totalMetaSpend = 0, totalMetaPurch = 0, totalGSpend = 0, totalGConv = 0;
    Object.values(bc).forEach(c => { totalMetaSpend += c.total_spend; totalMetaPurch += c.total_purchases; });
    Object.values(gc).forEach(c => { totalGSpend += c.total_spend; totalGConv += c.total_conversions; });

    if (totalMetaPurch > 0 && totalGConv > 0) {
        const metaCPA = Math.round(totalMetaSpend / totalMetaPurch);
        const gCPA = Math.round(totalGSpend / totalGConv);
        if (gCPA < metaCPA * 0.5) {
            diags.push({ type: 'info', title: '[Distribución] Google Ads más eficiente que Meta', text: `CPA Google: <strong>${clp(gCPA)}</strong> vs CPA Meta: <strong>${clp(metaCPA)}</strong>. Google tiene un CPA ${Math.round((1 - gCPA/metaCPA) * 100)}% menor. Considerar reasignar parte del presupuesto de Meta a Google Ads.` });
        }
    }

    const el = document.getElementById('diagnostics');
    if (!el) return;
    el.innerHTML = diags.length ? diags.map(d => `<div class="diagnostic-card diagnostic-${d.type}"><h3>${d.title}</h3><p>${d.text}</p></div>`).join('') : '<p style="color:var(--text-muted)">Sin alertas.</p>';
}

// ==================== GOOGLE ADS ====================

function renderGoogleAds() {
    const el = document.getElementById('googleAdsContent');
    if (!el) return;

    const camps = GDATA.campaigns || {};
    const bm = GDATA.by_month || {};
    const campList = Object.values(camps);

    if (campList.length === 0) {
        el.innerHTML = '<p style="color:var(--text-muted)">Sin datos de Google Ads en el periodo.</p>';
        return;
    }

    // KPIs
    const t = campList.reduce((a, c) => {
        a.spend += c.total_spend; a.imp += c.total_impressions; a.clk += c.total_clicks;
        a.conv += c.total_conversions; a.val += c.total_conv_value;
        return a;
    }, { spend: 0, imp: 0, clk: 0, conv: 0, val: 0 });

    let html = `<div class="kpi-grid" style="margin-bottom:1.5rem;">
        <div class="kpi-card kpi-google"><span class="kpi-label">Inversión Google</span><span class="kpi-value">${clp(t.spend)}</span><span class="kpi-detail">${campList.length} campañas</span></div>
        <div class="kpi-card kpi-google"><span class="kpi-label">Impresiones</span><span class="kpi-value">${num(t.imp)}</span><span class="kpi-detail">CTR: ${t.imp > 0 ? (t.clk/t.imp*100).toFixed(2)+'%' : '-'}</span></div>
        <div class="kpi-card kpi-google"><span class="kpi-label">Clics</span><span class="kpi-value">${num(t.clk)}</span><span class="kpi-detail">CPC: ${t.clk > 0 ? clp(t.spend/t.clk) : '-'}</span></div>
        <div class="kpi-card kpi-google"><span class="kpi-label">Conversiones</span><span class="kpi-value">${num(t.conv)}</span><span class="kpi-detail">CPA: ${t.conv > 0 ? clp(t.spend/t.conv) : '-'}</span></div>
    </div>`;

    // Campaign table
    let rows = campList.sort((a,b) => b.total_spend - a.total_spend).map(c => {
        const sc = c.status === 'ENABLED' ? 'success' : 'inactive';
        const sl = c.status === 'ENABLED' ? 'Activa' : 'Pausada';
        return `<tr>
            <td class="cell-name">${esc(c.name)}</td>
            <td><span class="badge badge-${sc}">${sl}</span></td>
            <td class="cell-small">${c.channel}</td>
            <td class="cell-number cell-bold">${num(c.total_conversions)}</td>
            <td class="cell-number">${c.total_cost_conv > 0 ? clp(c.total_cost_conv) : '-'}</td>
            <td class="cell-number">${clp(c.total_spend)}</td>
            <td class="cell-number">${num(c.total_impressions)}</td>
            <td class="cell-number">${num(c.total_clicks)}</td>
            <td class="cell-number">${c.total_ctr}%</td>
            <td class="cell-number">${clp(c.total_cpc)}</td></tr>`;
    }).join('');

    html += `<div class="table-responsive"><table class="data-table">
        <thead><tr><th>Campaña</th><th>Estado</th><th>Tipo</th><th>Conv.</th><th>CPA</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th><th>CPC</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;

    // Monthly breakdown
    const monthKeys = Object.keys(bm).sort();
    if (monthKeys.length > 0) {
        const mLabels = {1:'Ene',2:'Feb',3:'Mar',4:'Abr',5:'May',6:'Jun',7:'Jul',8:'Ago',9:'Sep',10:'Oct',11:'Nov',12:'Dic'};
        let mRows = monthKeys.map(mk => {
            const m = bm[mk];
            const mNum = parseInt(mk.split('-')[1]);
            const label = (mLabels[mNum] || mk) + ' ' + mk.split('-')[0];
            return `<tr>
                <td><strong>${label}</strong></td>
                <td class="cell-number">${clp(m.spend)}</td>
                <td class="cell-number">${num(m.impressions)}</td>
                <td class="cell-number">${num(m.clicks)}</td>
                <td class="cell-number">${m.ctr}%</td>
                <td class="cell-number cell-bold">${num(m.conversions)}</td>
                <td class="cell-number">${m.cost_conv > 0 ? clp(m.cost_conv) : '-'}</td></tr>`;
        }).join('');

        html += `<h4 style="margin-top:1.25rem;color:var(--text-secondary);margin-bottom:0.5rem;">Desglose Mensual Google Ads</h4>
        <div class="table-responsive"><table class="data-table data-table-compact">
            <thead><tr><th>Mes</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th><th>Conv.</th><th>CPA</th></tr></thead>
            <tbody>${mRows}</tbody></table></div>`;
    }

    el.innerHTML = html;
}

// ==================== UTILIDADES ====================

async function clearCache() {
    try {
        const res = await fetch('api.php?action=clear_cache');
        const json = await res.json();
        if (json.ok) location.reload();
    } catch (e) { alert('Error al limpiar caché'); }
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

function animateCards() {
    document.querySelectorAll('.kpi-card,.diagnostic-card,.finding-item,.calendar-item,.checklist-card,.strategy-stage').forEach((c, i) => {
        c.style.opacity = '0'; c.style.transform = 'translateY(10px)';
        c.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(() => { c.style.opacity = '1'; c.style.transform = 'translateY(0)'; }, 40 * i);
    });
}
