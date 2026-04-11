/**
 * Novavita Dashboard — Frontend JS
 */

let DATA = { by_month: {}, by_campaign: {} };
let GDATA = { campaigns: {}, by_month: {} };
let SDATA = { summary: {}, by_month: {} };

let CURRENT_SINCE = '2025-08-01';
let CURRENT_UNTIL = new Date().toISOString().slice(0, 10);

document.addEventListener('DOMContentLoaded', () => {
    const diasMadreEl = document.getElementById('diasMadre');
    if (diasMadreEl) {
        const diff = Math.ceil((new Date('2026-05-10') - new Date()) / 86400000);
        diasMadreEl.textContent = diff;
    }

    const tab = new URLSearchParams(window.location.search).get('tab') || 'campanas';
    if (tab === 'campanas') {
        loadAllData();
    } else {
        showLoading(false);
        animateCards();
    }
});

// ==================== DATA LOADING ====================

async function loadAllData() {
    showLoading(true);
    try {
        const [metaRes, googleRes, shopifyRes] = await Promise.all([
            fetch(`api.php?action=insights&since=${CURRENT_SINCE}&until=${CURRENT_UNTIL}`).then(r => r.json()),
            fetch(`api.php?action=google_insights&since=${CURRENT_SINCE}&until=${CURRENT_UNTIL}`).then(r => r.json()),
            fetch(`api.php?action=shopify&since=${CURRENT_SINCE}`).then(r => r.json()),
        ]);

        if (metaRes.ok) DATA = metaRes;
        if (googleRes.ok) GDATA = googleRes.data || {};
        if (shopifyRes.ok) SDATA = shopifyRes.data || {};

        const badge = document.getElementById('periodBadge');
        if (badge) badge.textContent = `${CURRENT_SINCE} — ${CURRENT_UNTIL}`;

        renderAll();
    } catch (err) {
        console.error(err);
        const kpi = document.getElementById('kpiGrid');
        if (kpi) kpi.innerHTML = `<div class="alert alert-error">Error: ${err.message}</div>`;
    }
    showLoading(false);
}

function renderAll() {
    renderCampaigns();
    renderFunnel();
    renderFunnelOpportunities();
    renderMonthly();
    renderGoogleAds();
    renderShopify();
    renderDiagnostics();
    animateCards();
}

function onFiltersChange() {
    renderCampaigns();
    renderFunnel();
    renderFunnelOpportunities();
    renderDiagnostics();
}

function onDateFilterChange() {
    const sel = document.getElementById('dateFilter').value;
    const custom = document.getElementById('customDates');

    if (sel === 'custom') {
        custom.style.display = 'flex';
        return;
    }
    custom.style.display = 'none';

    const now = new Date();
    if (sel === 'current') {
        CURRENT_SINCE = now.toISOString().slice(0, 7) + '-01';
        CURRENT_UNTIL = now.toISOString().slice(0, 10);
    } else if (sel === 'last') {
        const last = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastEnd = new Date(now.getFullYear(), now.getMonth(), 0);
        CURRENT_SINCE = last.toISOString().slice(0, 10);
        CURRENT_UNTIL = lastEnd.toISOString().slice(0, 10);
    } else {
        CURRENT_SINCE = '2025-08-01';
        CURRENT_UNTIL = now.toISOString().slice(0, 10);
    }
    loadAllData();
}

function loadWithDates() {
    const from = document.getElementById('dateFrom').value;
    const to = document.getElementById('dateTo').value;
    if (from && to) {
        CURRENT_SINCE = from;
        CURRENT_UNTIL = to;
        loadAllData();
    }
}

function showLoading(show) {
    const el = document.getElementById('loading');
    if (el) el.style.display = show ? 'flex' : 'none';
}

function clp(n) { return '$' + Math.round(n).toLocaleString('es-CL'); }
function num(n) { return Math.round(n).toLocaleString('es-CL'); }
function getPlatform() { return document.getElementById('platformFilter')?.value || 'all'; }
function getStatus() { return document.getElementById('statusFilter')?.value || 'all'; }

// ==================== CAMPAÑAS ====================

function getFilteredMetaCampaigns() {
    const status = getStatus();
    let campaigns = Object.entries(DATA.by_campaign || {}).map(([id, c]) => ({ id, ...c, _platform: 'meta' }));
    if (status === 'ACTIVE') campaigns = campaigns.filter(c => c.effective_status === 'ACTIVE');
    if (status === 'PAUSED') campaigns = campaigns.filter(c => c.effective_status !== 'ACTIVE');
    return campaigns;
}

function getFilteredGoogleCampaigns() {
    const status = getStatus();
    let campaigns = Object.entries(GDATA.campaigns || {}).map(([id, c]) => ({ id, ...c, _platform: 'google' }));
    if (status === 'ACTIVE') campaigns = campaigns.filter(c => c.status === 'ENABLED');
    if (status === 'PAUSED') campaigns = campaigns.filter(c => c.status !== 'ENABLED');
    return campaigns;
}

function renderCampaigns() {
    const platform = getPlatform();
    const metaCamps = (platform === 'all' || platform === 'meta') ? getFilteredMetaCampaigns() : [];
    const googleCamps = (platform === 'all' || platform === 'google') ? getFilteredGoogleCampaigns() : [];

    // KPIs - Meta
    const tm = metaCamps.reduce((a, c) => {
        a.spend += c.total_spend; a.imp += c.total_impressions; a.reach += c.total_reach;
        a.clk += c.total_link_clicks; a.pur += c.total_purchases; a.msg += c.total_messaging;
        return a;
    }, { spend: 0, imp: 0, reach: 0, clk: 0, pur: 0, msg: 0 });

    // KPIs - Google
    const tg = googleCamps.reduce((a, c) => {
        a.spend += c.total_spend; a.imp += c.total_impressions; a.clk += c.total_clicks; a.conv += c.total_conversions;
        return a;
    }, { spend: 0, imp: 0, clk: 0, conv: 0 });

    const totalSpend = tm.spend + tg.spend;
    const totalImp = tm.imp + tg.imp;
    const totalClk = tm.clk + tg.clk;
    const totalConv = tm.pur + tg.conv;
    const totalCampaigns = metaCamps.length + googleCamps.length;

    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi-card"><span class="kpi-label">Inversión Total</span><span class="kpi-value">${clp(totalSpend)}</span><span class="kpi-detail">${totalCampaigns} campañas</span></div>
        <div class="kpi-card"><span class="kpi-label">Impresiones</span><span class="kpi-value">${num(totalImp)}</span><span class="kpi-detail">CPM: ${totalImp > 0 ? clp(totalSpend / totalImp * 1000) : '-'}</span></div>
        <div class="kpi-card"><span class="kpi-label">Clics</span><span class="kpi-value">${num(totalClk)}</span><span class="kpi-detail">CPC: ${totalClk > 0 ? clp(totalSpend / totalClk) : '-'} | CTR: ${totalImp > 0 ? (totalClk/totalImp*100).toFixed(2)+'%' : '-'}</span></div>
        <div class="kpi-card kpi-highlight"><span class="kpi-label">Conversiones</span><span class="kpi-value">${num(totalConv)}</span><span class="kpi-detail">CPA: ${totalConv > 0 ? clp(totalSpend / totalConv) : '-'}</span></div>
        <div class="kpi-card"><span class="kpi-label">Conv. WA (Meta)</span><span class="kpi-value">${num(tm.msg)}</span><span class="kpi-detail">Leads WhatsApp</span></div>
        <div class="kpi-card"><span class="kpi-label">Alcance (Meta)</span><span class="kpi-value">${num(tm.reach)}</span><span class="kpi-detail">Personas únicas</span></div>`;

    // Campaign table - Meta
    let rows = '';

    if (platform === 'all' || platform === 'meta') {
        metaCamps.sort((a, b) => b.total_spend - a.total_spend).forEach(c => {
            const sc = c.effective_status === 'ACTIVE' ? 'success' : 'inactive';
            const sl = c.effective_status === 'ACTIVE' ? 'Activa' : 'Pausada';
            rows += `<tr class="campaign-row" onclick="loadCampaignDetail('${c.id}','${c.name.replace(/'/g,"\\'")}')">
                <td><span class="platform-tag platform-meta">Meta</span></td>
                <td class="cell-name">${esc(c.name)}</td>
                <td><span class="badge badge-${sc}">${sl}</span></td>
                <td class="cell-small">${c.objective}</td>
                <td class="cell-number cell-bold">${num(c.total_purchases)}</td>
                <td class="cell-number">${c.total_purchases > 0 ? clp(c.total_spend / c.total_purchases) : '-'}</td>
                <td class="cell-number">${clp(c.total_spend)}</td>
                <td class="cell-number">${num(c.total_impressions)}</td>
                <td class="cell-number">${num(c.total_link_clicks)}</td>
                <td class="cell-number">${c.total_ctr}%</td>
                <td class="cell-number">${num(c.total_messaging)}</td></tr>`;
        });
    }

    if (platform === 'all' || platform === 'google') {
        googleCamps.sort((a, b) => b.total_spend - a.total_spend).forEach(c => {
            const sc = c.status === 'ENABLED' ? 'success' : 'inactive';
            const sl = c.status === 'ENABLED' ? 'Activa' : 'Pausada';
            rows += `<tr>
                <td><span class="platform-tag platform-google">Google</span></td>
                <td class="cell-name">${esc(c.name)}</td>
                <td><span class="badge badge-${sc}">${sl}</span></td>
                <td class="cell-small">${c.channel || '-'}</td>
                <td class="cell-number cell-bold">${num(c.total_conversions)}</td>
                <td class="cell-number">${c.total_cost_conv > 0 ? clp(c.total_cost_conv) : '-'}</td>
                <td class="cell-number">${clp(c.total_spend)}</td>
                <td class="cell-number">${num(c.total_impressions)}</td>
                <td class="cell-number">${num(c.total_clicks)}</td>
                <td class="cell-number">${c.total_ctr}%</td>
                <td class="cell-number">-</td></tr>`;
        });
    }

    document.getElementById('campaignTable').innerHTML = `<table class="data-table">
        <thead><tr><th>Plat.</th><th>Campaña</th><th>Estado</th><th>Objetivo</th><th>Conv.</th><th>CPA</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th><th>WA</th></tr></thead>
        <tbody>${rows}</tbody></table>`;
}

async function loadCampaignDetail(id, name) {
    const detail = document.getElementById('campaignDetail');
    detail.style.display = 'block';
    document.getElementById('detailTitle').textContent = `Detalle: ${name}`;
    document.getElementById('adsetTable').innerHTML = '<p style="color:var(--text-muted);padding:1rem;">Cargando...</p>';
    document.getElementById('adTable').innerHTML = '';

    try {
        const res = await fetch(`api.php?action=campaign_detail&id=${id}&since=${CURRENT_SINCE}&until=${CURRENT_UNTIL}`);
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
            document.getElementById('adsetTable').innerHTML = `<table class="data-table"><thead><tr><th>Ad Set</th><th>Conv.</th><th>CPA</th><th>Inversión</th><th>Imp.</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>Frec.</th><th>WA</th></tr></thead><tbody>${r}</tbody></table>`;
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
            document.getElementById('adTable').innerHTML = `<table class="data-table data-table-compact"><thead><tr><th>Anuncio</th><th>Ad Set</th><th>Conv.</th><th>CPA</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th></tr></thead><tbody>${r}</tbody></table>`;
        }
        detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        document.getElementById('adsetTable').innerHTML = `<div class="alert alert-error">Error: ${err.message}</div>`;
    }
}

// ==================== EMBUDO ====================

function svgFunnel(stages) {
    if (!stages || !stages.length) return '';
    const W = 500, SH = 60, GAP = 3, MAXW = W - 40, MINW = 80;
    const n = stages.length, step = (MAXW - MINW) / (n - 1 || 1);
    const H = n * SH + (n - 1) * GAP;
    let o = `<svg viewBox="0 0 ${W} ${H}" width="100%" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto">`;
    o += `<defs><filter id="sh"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".18"/></filter></defs>`;
    stages.forEach((s, i) => {
        const tW = MAXW - i * step, bW = Math.max(MINW, MAXW - (i + 1) * step);
        const tX = (W - tW) / 2, bX = (W - bW) / 2, y = i * (SH + GAP);
        o += `<polygon points="${tX},${y} ${tX+tW},${y} ${bX+bW},${y+SH} ${bX},${y+SH}" fill="${s.col}" filter="url(#sh)"/>`;
        const cy = y + SH / 2;
        o += `<text x="${W/2}" y="${cy-8}" text-anchor="middle" fill="white" font-size="10" font-weight="700" font-family="-apple-system,Segoe UI,Arial">${s.l}</text>`;
        o += `<text x="${W/2}" y="${cy+9}" text-anchor="middle" fill="rgba(255,255,255,.95)" font-size="14" font-weight="800" font-family="-apple-system,Segoe UI,Arial">${s.f}</text>`;
        if (s.pct !== null && i > 0) {
            o += `<text x="${(W+tW)/2+8}" y="${y+15}" fill="#9aa0b0" font-size="9" font-family="-apple-system,Segoe UI,Arial" font-weight="600">${s.pct}</text>`;
        }
    });
    return o + '</svg>';
}

function getFunnelData() {
    const platform = getPlatform();
    const f = { imp: 0, clk: 0, land: 0, atc: 0, co: 0, pur: 0, spend: 0, revenue: 0 };

    if (platform === 'all' || platform === 'meta') {
        getFilteredMetaCampaigns().forEach(c => {
            f.imp += c.total_impressions || 0; f.clk += c.total_link_clicks || 0;
            f.land += c.total_landing_views || 0; f.atc += c.total_add_to_cart || 0;
            f.co += c.total_checkouts || 0; f.pur += c.total_purchases || 0;
            f.spend += c.total_spend || 0;
        });
    }

    if (platform === 'all' || platform === 'google') {
        getFilteredGoogleCampaigns().forEach(c => {
            f.imp += c.total_impressions || 0; f.clk += c.total_clicks || 0;
            f.pur += c.total_conversions || 0; f.spend += c.total_spend || 0;
            f.revenue += c.total_conv_value || 0;
        });
    }

    // Add Shopify revenue
    const shopRevenue = SDATA.summary?.total_revenue || 0;
    if (shopRevenue > f.revenue) f.revenue = shopRevenue;

    return f;
}

function renderFunnel() {
    const el = document.getElementById('funnelContainer');
    if (!el) return;
    const f = getFunnelData();
    const platform = getPlatform();
    const r = (a, b) => b > 0 ? (a / b * 100).toFixed(1) + '%' : '-';

    let stages;
    if (platform === 'google') {
        stages = [
            { l: 'Impresiones', f: num(f.imp), col: '#4fc3f7', pct: null },
            { l: 'Clics', f: num(f.clk), col: '#2196f3', pct: r(f.clk, f.imp) },
            { l: 'Conversiones', f: num(f.pur), col: '#10b981', pct: r(f.pur, f.clk) },
            { l: 'Ingresos', f: clp(f.revenue), col: '#6f42c1', pct: null },
        ];
    } else {
        stages = [
            { l: 'Impresiones', f: num(f.imp), col: '#4fc3f7', pct: null },
            { l: 'Clics en enlace', f: num(f.clk), col: '#2196f3', pct: r(f.clk, f.imp) },
            { l: 'Vistas de página', f: num(f.land), col: '#1976d2', pct: r(f.land, f.clk) },
            { l: 'Agregar al carrito', f: num(f.atc), col: '#f59e0b', pct: r(f.atc, f.land) },
            { l: 'Iniciar checkout', f: num(f.co), col: '#fd7e14', pct: r(f.co, f.atc) },
            { l: 'Conversiones', f: num(f.pur), col: '#10b981', pct: r(f.pur, f.co) },
            { l: 'Ingresos', f: clp(f.revenue), col: '#6f42c1', pct: null },
        ];
    }

    const overall = f.imp > 0 ? (f.pur / f.imp * 100).toFixed(3) : '0';
    const roas = f.spend > 0 ? (f.revenue / f.spend).toFixed(2) : '0';
    const platformLabel = platform === 'google' ? 'Google Ads' : platform === 'meta' ? 'Meta Ads' : 'Meta + Google';

    el.innerHTML = `<div class="funnel-layout">
        <div class="funnel-svg"><div style="text-align:center;margin-bottom:0.75rem;"><span class="platform-tag platform-${platform === 'google' ? 'google' : platform === 'meta' ? 'meta' : 'all'}">${platformLabel}</span></div>${svgFunnel(stages)}</div>
        <div class="funnel-stats">
            <div class="funnel-stat"><span class="funnel-stat-val">${overall}%</span><span class="funnel-stat-lbl">Conversión Global</span><span class="funnel-stat-desc">Impresión a Conversión</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${r(f.clk,f.imp)}</span><span class="funnel-stat-lbl">CTR</span><span class="funnel-stat-desc">Imp. a Clic</span></div>
            ${platform !== 'google' ? `<div class="funnel-stat"><span class="funnel-stat-val">${r(f.atc,f.land)}</span><span class="funnel-stat-lbl">Landing a Carrito</span><span class="funnel-stat-desc">Interés de compra</span></div>` : ''}
            ${platform !== 'google' ? `<div class="funnel-stat"><span class="funnel-stat-val">${r(f.pur,f.co)}</span><span class="funnel-stat-lbl">Checkout a Compra</span><span class="funnel-stat-desc">Cierre</span></div>` : ''}
            <div class="funnel-stat"><span class="funnel-stat-val">${f.pur > 0 ? clp(f.spend/f.pur) : '-'}</span><span class="funnel-stat-lbl">CPA</span><span class="funnel-stat-desc">Costo por conversión</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${clp(f.revenue)}</span><span class="funnel-stat-lbl">Ingresos</span><span class="funnel-stat-desc">Revenue Shopify</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${roas}x</span><span class="funnel-stat-lbl">ROAS</span><span class="funnel-stat-desc">Retorno sobre inversión publicitaria</span></div>
            <div class="funnel-stat"><span class="funnel-stat-val">${clp(f.spend)}</span><span class="funnel-stat-lbl">Inversión</span><span class="funnel-stat-desc">Periodo seleccionado</span></div>
        </div></div>`;
}

// ==================== OPORTUNIDADES ====================

function renderFunnelOpportunities() {
    const el = document.getElementById('funnelOpportunities');
    if (!el) return;
    const f = getFunnelData();
    const platform = getPlatform();
    const opps = [];

    const ctr = f.imp > 0 ? f.clk / f.imp * 100 : 0;
    if (ctr < 2) {
        opps.push({ type: 'critical', level: 'Impresiones > Clics', rate: ctr.toFixed(2) + '%', title: 'CTR bajo — los anuncios no generan suficiente interés',
            text: 'El CTR global es menor al 2%. Se necesitan <strong>nuevos formatos de video corto</strong> (testimonios tipo "Karen") y <strong>segmentación más precisa</strong>.' });
    }

    if (platform !== 'google') {
        const clkToLand = f.clk > 0 ? f.land / f.clk * 100 : 0;
        if (clkToLand < 50 && f.clk > 100) {
            opps.push({ type: 'warning', level: 'Clics > Vistas de página', rate: clkToLand.toFixed(1) + '%', title: 'Alta pérdida entre clic y landing page',
                text: `Solo el ${clkToLand.toFixed(1)}% de los clics llegan a la página. Verificar <strong>velocidad de carga</strong> de Shopify y que las URLs de destino sean correctas.` });
        }

        const landToAtc = f.land > 0 ? f.atc / f.land * 100 : 0;
        if (landToAtc < 25 && f.land > 100) {
            opps.push({ type: 'warning', level: 'Vistas > Agregar al carrito', rate: landToAtc.toFixed(1) + '%', title: 'Baja intención de compra en la web',
                text: `Mejorar <strong>páginas de producto</strong>: reseñas visibles, fotos antes/después, precios claros, CTA prominente. Considerar WhatsApp como alternativa.` });
        }

        const atcToCo = f.atc > 0 ? f.co / f.atc * 100 : 0;
        if (atcToCo < 40 && f.atc > 20) {
            opps.push({ type: 'warning', level: 'Carrito > Checkout', rate: atcToCo.toFixed(1) + '%', title: 'Abandono de carrito significativo',
                text: `${(100-atcToCo).toFixed(0)}% abandona después de agregar al carrito. Implementar <strong>emails de carrito abandonado</strong> con Klaviyo y retargeting BOFU.` });
        }

        const coToPur = f.co > 0 ? f.pur / f.co * 100 : 0;
        if (coToPur < 30 && f.co > 10) {
            opps.push({ type: 'critical', level: 'Checkout > Compra', rate: coToPur.toFixed(1) + '%', title: 'Alta fricción en proceso de pago',
                text: `Solo ${coToPur.toFixed(1)}% completa la compra. Verificar proceso de Mercado Pago, simplificar checkout al mínimo de pasos.` });
        }
    }

    opps.push({ type: 'info', level: 'Canal alternativo', rate: '', title: 'WhatsApp como canal de conversión paralelo',
        text: 'Para un negocio local en Curicó, WhatsApp permite cerrar ventas con mayor tasa de conversión que la web.' });

    el.innerHTML = opps.map((o, i) => `<div class="finding-item finding-${o.type}">
        <span class="finding-number">${i + 1}</span>
        <div>
            <div class="finding-level"><span class="finding-level-tag">${o.level}</span>${o.rate ? `<span class="finding-level-rate">${o.rate}</span>` : ''}</div>
            <h4>${o.title}</h4><p>${o.text}</p>
        </div></div>`).join('');
}

// ==================== DESGLOSE MENSUAL ====================

function renderMonthly() {
    const bm = DATA.by_month || {};
    const months = Object.entries(bm).sort(([a], [b]) => a.localeCompare(b));
    const filter = document.getElementById('monthFilter');
    if (!filter) return;

    if (filter.options.length <= 1) {
        months.forEach(([key, m]) => {
            const opt = document.createElement('option');
            opt.value = key; opt.textContent = m.label;
            filter.appendChild(opt);
        });
    }

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
        <th>Mes</th><th>Inversión</th><th>Imp.</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>CPM</th><th>Compras</th><th>CPA</th><th>WA</th><th>ATC</th><th>Checkout</th>
    </tr></thead><tbody>${rows}</tbody></table>`;
    renderMonthDetail();
}

function renderMonthDetail() {
    const key = document.getElementById('monthFilter')?.value || 'all';
    const container = document.getElementById('monthCampaignTable');
    const title = document.getElementById('monthDetailTitle');

    if (key === 'all') {
        title.textContent = 'Selecciona un mes para ver detalle';
        container.innerHTML = '<p style="color:var(--text-muted);padding:1rem;">Haz clic en un mes o selecciónalo del desplegable.</p>';
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
        <th>Campaña</th><th>Inversión</th><th>Imp.</th><th>Alcance</th><th>Clics</th><th>CTR</th><th>Frec.</th><th>Compras</th><th>CPA</th><th>WA</th><th>ATC</th><th>Videos</th>
    </tr></thead><tbody>${rows}</tbody></table>`;
}

// ==================== GOOGLE ADS ====================

function renderGoogleAds() {
    const el = document.getElementById('googleAdsContent');
    if (!el) return;
    const camps = GDATA.campaigns || {};
    const bm = GDATA.by_month || {};
    const campList = Object.values(camps);

    if (campList.length === 0) { el.innerHTML = '<p style="color:var(--text-muted)">Sin datos de Google Ads.</p>'; return; }

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

    let rows = campList.sort((a,b) => b.total_spend - a.total_spend).map(c => {
        const sc = c.status === 'ENABLED' ? 'success' : 'inactive';
        const sl = c.status === 'ENABLED' ? 'Activa' : 'Pausada';
        return `<tr><td class="cell-name">${esc(c.name)}</td><td><span class="badge badge-${sc}">${sl}</span></td><td class="cell-small">${c.channel}</td><td class="cell-number cell-bold">${num(c.total_conversions)}</td><td class="cell-number">${c.total_cost_conv > 0 ? clp(c.total_cost_conv) : '-'}</td><td class="cell-number">${clp(c.total_spend)}</td><td class="cell-number">${num(c.total_impressions)}</td><td class="cell-number">${num(c.total_clicks)}</td><td class="cell-number">${c.total_ctr}%</td><td class="cell-number">${clp(c.total_cpc)}</td></tr>`;
    }).join('');

    html += `<div class="table-responsive"><table class="data-table"><thead><tr><th>Campaña</th><th>Estado</th><th>Tipo</th><th>Conv.</th><th>CPA</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th><th>CPC</th></tr></thead><tbody>${rows}</tbody></table></div>`;

    const monthKeys = Object.keys(bm).sort();
    if (monthKeys.length > 0) {
        const mLabels = {1:'Ene',2:'Feb',3:'Mar',4:'Abr',5:'May',6:'Jun',7:'Jul',8:'Ago',9:'Sep',10:'Oct',11:'Nov',12:'Dic'};
        let mRows = monthKeys.map(mk => {
            const m = bm[mk]; const mNum = parseInt(mk.split('-')[1]);
            return `<tr><td><strong>${(mLabels[mNum]||mk)+' '+mk.split('-')[0]}</strong></td><td class="cell-number">${clp(m.spend)}</td><td class="cell-number">${num(m.impressions)}</td><td class="cell-number">${num(m.clicks)}</td><td class="cell-number">${m.ctr}%</td><td class="cell-number cell-bold">${num(m.conversions)}</td><td class="cell-number">${m.cost_conv > 0 ? clp(m.cost_conv) : '-'}</td></tr>`;
        }).join('');
        html += `<h4 style="margin-top:1.25rem;color:var(--text-secondary);margin-bottom:0.5rem;">Desglose Mensual Google Ads</h4><div class="table-responsive"><table class="data-table data-table-compact"><thead><tr><th>Mes</th><th>Inversión</th><th>Imp.</th><th>Clics</th><th>CTR</th><th>Conv.</th><th>CPA</th></tr></thead><tbody>${mRows}</tbody></table></div>`;
    }
    el.innerHTML = html;
}

// ==================== SHOPIFY ====================

function renderShopify() {
    const el = document.getElementById('shopifyContent');
    if (!el) return;
    const s = SDATA.summary || {};
    const bm = SDATA.by_month || {};

    if (!s.total_orders) { el.innerHTML = '<p style="color:var(--text-muted)">Sin datos de Shopify.</p>'; return; }

    let html = `<div class="kpi-grid" style="margin-bottom:1.5rem;">
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Pedidos</span><span class="kpi-value">${num(s.total_orders)}</span><span class="kpi-detail">${s.paid_orders} pagados, ${s.cancelled} cancelados</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Revenue Total</span><span class="kpi-value">${clp(s.total_revenue)}</span><span class="kpi-detail">Ventas brutas</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Ticket Promedio</span><span class="kpi-value">${clp(s.avg_ticket)}</span><span class="kpi-detail">Por pedido</span></div>
        <div class="kpi-card kpi-shopify"><span class="kpi-label">Tasa de pago</span><span class="kpi-value">${s.total_orders > 0 ? (s.paid_orders/s.total_orders*100).toFixed(0) : 0}%</span><span class="kpi-detail">${s.paid_orders} de ${s.total_orders}</span></div>
    </div>`;

    const monthKeys = Object.keys(bm).sort();
    if (monthKeys.length > 0) {
        const mLabels = {1:'Ene',2:'Feb',3:'Mar',4:'Abr',5:'May',6:'Jun',7:'Jul',8:'Ago',9:'Sep',10:'Oct',11:'Nov',12:'Dic'};
        let mRows = monthKeys.map(mk => {
            const m = bm[mk]; const mNum = parseInt(mk.split('-')[1]);
            return `<tr><td><strong>${(mLabels[mNum]||mk)+' '+mk.split('-')[0]}</strong></td><td class="cell-number cell-bold">${m.orders}</td><td class="cell-number">${clp(m.revenue)}</td><td class="cell-number">${clp(m.avg_ticket)}</td><td class="cell-number">${m.paid}</td><td class="cell-number">${m.cancelled}</td><td class="cell-number">${m.items}</td></tr>`;
        }).join('');
        html += `<h4 style="color:var(--text-secondary);margin-bottom:0.5rem;">Ventas por Mes</h4><div class="table-responsive"><table class="data-table data-table-compact"><thead><tr><th>Mes</th><th>Pedidos</th><th>Revenue</th><th>Ticket</th><th>Pagados</th><th>Cancel.</th><th>Items</th></tr></thead><tbody>${mRows}</tbody></table></div>`;
    }

    const top = s.top_products || {};
    const topEntries = Object.entries(top);
    if (topEntries.length > 0) {
        let pRows = topEntries.map(([name, data]) => `<tr><td class="cell-name">${esc(name)}</td><td class="cell-number cell-bold">${data.qty}</td><td class="cell-number">${clp(data.revenue)}</td></tr>`).join('');
        html += `<h4 style="margin-top:1.25rem;color:var(--text-secondary);margin-bottom:0.5rem;">Top 10 Productos</h4><div class="table-responsive"><table class="data-table data-table-compact"><thead><tr><th>Producto</th><th>Uds.</th><th>Revenue</th></tr></thead><tbody>${pRows}</tbody></table></div>`;
    }
    el.innerHTML = html;
}

// ==================== DIAGNÓSTICO ====================

function renderDiagnostics() {
    const bc = DATA.by_campaign || {};
    const gc = GDATA.campaigns || {};
    const diagFilter = document.getElementById('diagFilter')?.value || 'all';
    const allDiags = [];

    // Meta diagnostics
    Object.values(bc).forEach(c => {
        Object.values(c.months || {}).forEach(m => {
            if (m.frequency > 10) {
                allDiags.push({ platform: 'meta', type: 'danger', title: `[Meta] Fatiga: ${c.name}`, text: `Frecuencia <strong>${m.frequency}x</strong> en ${m.month_label}. Audiencia sobre-saturada.` });
            }
        });
        if (c.total_purchases > 0 && c.total_cost_purchase > 15000) {
            allDiags.push({ platform: 'meta', type: 'warning', title: `[Meta] CPA alto: ${c.name}`, text: `Costo/compra: <strong>${clp(c.total_cost_purchase)}</strong>. Objetivo: < $10.000.` });
        }
        if (c.total_spend > 50000 && c.total_purchases === 0 && c.objective === 'Ventas') {
            allDiags.push({ platform: 'meta', type: 'danger', title: `[Meta] Sin conversiones: ${c.name}`, text: `<strong>${clp(c.total_spend)}</strong> sin compras. Verificar tracking.` });
        }
        if (c.effective_status !== 'ACTIVE' && c.total_spend > 100000) {
            allDiags.push({ platform: 'meta', type: 'info', title: `[Meta] Pausada: ${c.name}`, text: `Inversión acumulada <strong>${clp(c.total_spend)}</strong>. Evaluar reactivación.` });
        }
    });

    // WhatsApp efficiency
    let metaSpendSales = 0, metaPur = 0, metaSpendLeads = 0, metaMsg = 0;
    Object.values(bc).forEach(c => {
        if (c.objective === 'Ventas' && c.total_purchases > 0) { metaSpendSales += c.total_spend; metaPur += c.total_purchases; }
        if (c.total_messaging > 0) { metaSpendLeads += c.total_spend; metaMsg += c.total_messaging; }
    });
    if (metaMsg > 0 && metaPur > 0) {
        allDiags.push({ platform: 'meta', type: 'info', title: '[Meta] WhatsApp más eficiente', text: `WA: <strong>${clp(Math.round(metaSpendLeads/metaMsg))}</strong>/conv vs Web: <strong>${clp(Math.round(metaSpendSales/metaPur))}</strong>/compra.` });
    }

    // Google diagnostics
    Object.values(gc).forEach(c => {
        if (c.total_conversions > 0 && c.total_cost_conv < 3000) {
            allDiags.push({ platform: 'google', type: 'info', title: `[Google] Excelente: ${c.name}`, text: `CPA <strong>${clp(c.total_cost_conv)}</strong> con ${c.total_conversions} conversiones. Considerar aumentar presupuesto.` });
        } else if (c.total_conversions > 0 && c.total_cost_conv > 10000) {
            allDiags.push({ platform: 'google', type: 'warning', title: `[Google] CPA alto: ${c.name}`, text: `CPA <strong>${clp(c.total_cost_conv)}</strong>. Revisar keywords y segmentación.` });
        }
        const months = Object.values(c.months || {}).sort((a, b) => (a.month_key || '').localeCompare(b.month_key || ''));
        if (months.length >= 3) {
            const last = months[months.length - 1], prev = months[months.length - 2];
            if (last.conversions < prev.conversions * 0.5 && prev.conversions > 5) {
                allDiags.push({ platform: 'google', type: 'warning', title: `[Google] Caída: ${c.name}`, text: `Conversiones: <strong>${prev.conversions}</strong> a <strong>${last.conversions}</strong> último mes.` });
            }
        }
    });

    // Cross-platform
    let totalMetaSpend = 0, totalMetaPur = 0, totalGSpend = 0, totalGConv = 0;
    Object.values(bc).forEach(c => { totalMetaSpend += c.total_spend; totalMetaPur += c.total_purchases; });
    Object.values(gc).forEach(c => { totalGSpend += c.total_spend; totalGConv += c.total_conversions; });
    if (totalMetaPur > 0 && totalGConv > 0) {
        const mCPA = Math.round(totalMetaSpend / totalMetaPur), gCPA = Math.round(totalGSpend / totalGConv);
        if (gCPA < mCPA * 0.5) {
            allDiags.push({ platform: 'cross', type: 'info', title: '[Distribución] Google más eficiente', text: `CPA Google: <strong>${clp(gCPA)}</strong> vs Meta: <strong>${clp(mCPA)}</strong>. Reasignar presupuesto.` });
        }
    }

    // Filter
    const filtered = diagFilter === 'all' ? allDiags : allDiags.filter(d => d.platform === diagFilter);

    const el = document.getElementById('diagnostics');
    if (!el) return;
    el.innerHTML = filtered.length ? filtered.map(d => `<div class="diagnostic-card diagnostic-${d.type}"><h3>${d.title}</h3><p>${d.text}</p></div>`).join('') : '<p style="color:var(--text-muted)">Sin alertas para este filtro.</p>';
}

// ==================== UTILIDADES ====================

async function clearCache() {
    try {
        const res = await fetch('api.php?action=clear_cache');
        const json = await res.json();
        if (json.ok) location.reload();
    } catch (e) { alert('Error al limpiar caché'); }
}

function esc(str) { const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }

function animateCards() {
    document.querySelectorAll('.kpi-card,.diagnostic-card,.finding-item,.calendar-item,.checklist-card,.strategy-stage').forEach((c, i) => {
        c.style.opacity = '0'; c.style.transform = 'translateY(10px)';
        c.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(() => { c.style.opacity = '1'; c.style.transform = 'translateY(0)'; }, 40 * i);
    });
}
