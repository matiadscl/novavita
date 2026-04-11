<?php
/**
 * API interna del dashboard — devuelve JSON con datos de Meta Ads
 * Endpoints: ?action=campaigns | ?action=insights&since=...&until=... | ?action=campaign_detail&id=...
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/meta_api.php';
require_once __DIR__ . '/includes/google_api.php';
require_once __DIR__ . '/includes/shopify_api.php';
send_security_headers();
require_auth();

header('Content-Type: application/json; charset=utf-8');

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?? '';

switch ($action) {

    case 'campaigns':
        $campaigns = fetch_campaigns();
        echo json_encode(['ok' => true, 'data' => $campaigns], JSON_UNESCAPED_UNICODE);
        break;

    case 'insights':
        $since = filter_input(INPUT_GET, 'since', FILTER_DEFAULT) ?? '2026-01-01';
        $until = filter_input(INPUT_GET, 'until', FILTER_DEFAULT) ?? date('Y-m-d');

        // Validar formato fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $since) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $until)) {
            echo json_encode(['ok' => false, 'error' => 'Formato de fecha inválido']);
            break;
        }

        $insights = fetch_campaign_insights($since, $until);

        // Agrupar por campaña y por mes
        $by_campaign = [];
        $by_month = [];

        foreach ($insights as $row) {
            $cid = $row['campaign_id'];
            $mk = month_key($row['date_start']);
            $ml = month_label($row['date_start']);

            $purchases = get_action_value($row['actions'] ?? null, 'purchase');
            $link_clicks = get_action_value($row['actions'] ?? null, 'link_click');
            $landing_views = get_action_value($row['actions'] ?? null, 'landing_page_view');
            $add_to_cart = get_action_value($row['actions'] ?? null, 'add_to_cart');
            $checkouts = get_action_value($row['actions'] ?? null, 'initiate_checkout');
            $messaging = get_action_value($row['actions'] ?? null, 'onsite_conversion.messaging_conversation_started_7d');
            $video_views = get_action_value($row['actions'] ?? null, 'video_view');
            $cost_purchase = get_cost_per_action($row['cost_per_action_type'] ?? null, 'purchase');

            $parsed = [
                'campaign_name' => $row['campaign_name'],
                'campaign_id' => $cid,
                'month_key' => $mk,
                'month_label' => $ml,
                'date_start' => $row['date_start'],
                'date_stop' => $row['date_stop'],
                'spend' => (int)($row['spend'] ?? 0),
                'impressions' => (int)($row['impressions'] ?? 0),
                'reach' => (int)($row['reach'] ?? 0),
                'clicks' => (int)($row['clicks'] ?? 0),
                'link_clicks' => $link_clicks,
                'cpc' => round((float)($row['cpc'] ?? 0), 2),
                'cpm' => round((float)($row['cpm'] ?? 0), 2),
                'ctr' => round((float)($row['ctr'] ?? 0), 2),
                'frequency' => round((float)($row['frequency'] ?? 0), 2),
                'purchases' => $purchases,
                'cost_per_purchase' => $cost_purchase,
                'landing_page_views' => $landing_views,
                'add_to_cart' => $add_to_cart,
                'checkouts' => $checkouts,
                'messaging_started' => $messaging,
                'video_views' => $video_views,
            ];

            $by_campaign[$cid]['name'] = $row['campaign_name'];
            $by_campaign[$cid]['months'][$mk] = $parsed;

            if (!isset($by_month[$mk])) {
                $by_month[$mk] = ['label' => $ml, 'spend' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'link_clicks' => 0, 'purchases' => 0, 'messaging' => 0, 'add_to_cart' => 0, 'checkouts' => 0, 'landing_page_views' => 0, 'campaigns' => []];
            }
            $by_month[$mk]['spend'] += $parsed['spend'];
            $by_month[$mk]['impressions'] += $parsed['impressions'];
            $by_month[$mk]['reach'] += $parsed['reach'];
            $by_month[$mk]['clicks'] += $parsed['clicks'];
            $by_month[$mk]['link_clicks'] += $parsed['link_clicks'];
            $by_month[$mk]['purchases'] += $purchases;
            $by_month[$mk]['messaging'] += $messaging;
            $by_month[$mk]['add_to_cart'] += $add_to_cart;
            $by_month[$mk]['checkouts'] += $checkouts;
            $by_month[$mk]['landing_page_views'] += $landing_views;
            $by_month[$mk]['campaigns'][] = $parsed;
        }

        // Calcular métricas derivadas por mes
        foreach ($by_month as &$m) {
            $m['cpm'] = $m['impressions'] > 0 ? round(($m['spend'] / $m['impressions']) * 1000) : 0;
            $m['ctr'] = $m['impressions'] > 0 ? round(($m['link_clicks'] / $m['impressions']) * 100, 2) : 0;
            $m['cpc'] = $m['link_clicks'] > 0 ? round($m['spend'] / $m['link_clicks']) : 0;
            $m['cost_per_purchase'] = $m['purchases'] > 0 ? round($m['spend'] / $m['purchases']) : 0;
        }

        ksort($by_month);

        // Campañas con info de estado
        $camp_list = fetch_campaigns();
        $camp_map = [];
        foreach ($camp_list as $cl) {
            $camp_map[$cl['id']] = $cl;
        }

        // Enriquecer by_campaign con estado
        foreach ($by_campaign as $cid => &$cd) {
            $cd['effective_status'] = $camp_map[$cid]['effective_status'] ?? 'UNKNOWN';
            $cd['objective'] = isset($camp_map[$cid]['objective']) ? readable_objective($camp_map[$cid]['objective']) : '-';
            $cd['daily_budget'] = $camp_map[$cid]['daily_budget'] ?? null;
            $cd['lifetime_budget'] = $camp_map[$cid]['lifetime_budget'] ?? null;

            // Totales de la campaña
            $cd['total_spend'] = 0;
            $cd['total_impressions'] = 0;
            $cd['total_reach'] = 0;
            $cd['total_clicks'] = 0;
            $cd['total_link_clicks'] = 0;
            $cd['total_purchases'] = 0;
            $cd['total_messaging'] = 0;
            $cd['total_landing_views'] = 0;
            $cd['total_add_to_cart'] = 0;
            $cd['total_checkouts'] = 0;
            foreach ($cd['months'] as $md) {
                $cd['total_spend'] += $md['spend'];
                $cd['total_impressions'] += $md['impressions'];
                $cd['total_reach'] += $md['reach'];
                $cd['total_clicks'] += $md['clicks'];
                $cd['total_link_clicks'] += $md['link_clicks'];
                $cd['total_purchases'] += $md['purchases'];
                $cd['total_messaging'] += $md['messaging_started'];
                $cd['total_landing_views'] += $md['landing_page_views'];
                $cd['total_add_to_cart'] += $md['add_to_cart'];
                $cd['total_checkouts'] += $md['checkouts'];
            }
            $cd['total_cpm'] = $cd['total_impressions'] > 0 ? round(($cd['total_spend'] / $cd['total_impressions']) * 1000) : 0;
            $cd['total_ctr'] = $cd['total_impressions'] > 0 ? round(($cd['total_link_clicks'] / $cd['total_impressions']) * 100, 2) : 0;
            $cd['total_cost_purchase'] = $cd['total_purchases'] > 0 ? round($cd['total_spend'] / $cd['total_purchases']) : 0;
        }

        echo json_encode([
            'ok' => true,
            'by_month' => $by_month,
            'by_campaign' => $by_campaign,
            'period' => ['since' => $since, 'until' => $until],
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'campaign_detail':
        $id = filter_input(INPUT_GET, 'id', FILTER_DEFAULT) ?? '';
        $since = filter_input(INPUT_GET, 'since', FILTER_DEFAULT) ?? '2026-01-01';
        $until = filter_input(INPUT_GET, 'until', FILTER_DEFAULT) ?? date('Y-m-d');

        if (!$id || !preg_match('/^\d+$/', $id)) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            break;
        }

        $adsets = fetch_adset_insights($id, $since, $until);
        $ads = fetch_ad_insights($id, $since, $until);

        // Parsear
        $parsed_adsets = [];
        foreach ($adsets as $a) {
            $parsed_adsets[] = [
                'name' => $a['adset_name'],
                'spend' => (int)($a['spend'] ?? 0),
                'impressions' => (int)($a['impressions'] ?? 0),
                'reach' => (int)($a['reach'] ?? 0),
                'link_clicks' => get_action_value($a['actions'] ?? null, 'link_click'),
                'purchases' => get_action_value($a['actions'] ?? null, 'purchase'),
                'cost_per_purchase' => get_cost_per_action($a['cost_per_action_type'] ?? null, 'purchase'),
                'messaging' => get_action_value($a['actions'] ?? null, 'onsite_conversion.messaging_conversation_started_7d'),
                'cpc' => round((float)($a['cpc'] ?? 0), 2),
                'cpm' => round((float)($a['cpm'] ?? 0), 2),
                'ctr' => round((float)($a['ctr'] ?? 0), 2),
                'frequency' => round((float)($a['frequency'] ?? 0), 2),
            ];
        }

        $parsed_ads = [];
        foreach ($ads as $a) {
            $parsed_ads[] = [
                'name' => $a['ad_name'],
                'adset' => $a['adset_name'] ?? '-',
                'spend' => (int)($a['spend'] ?? 0),
                'impressions' => (int)($a['impressions'] ?? 0),
                'reach' => (int)($a['reach'] ?? 0),
                'link_clicks' => get_action_value($a['actions'] ?? null, 'link_click'),
                'purchases' => get_action_value($a['actions'] ?? null, 'purchase'),
                'cost_per_purchase' => get_cost_per_action($a['cost_per_action_type'] ?? null, 'purchase'),
                'cpm' => round((float)($a['cpm'] ?? 0), 2),
                'ctr' => round((float)($a['ctr'] ?? 0), 2),
                'frequency' => round((float)($a['frequency'] ?? 0), 2),
            ];
        }

        echo json_encode(['ok' => true, 'adsets' => $parsed_adsets, 'ads' => $parsed_ads], JSON_UNESCAPED_UNICODE);
        break;

    case 'shopify':
        $since = filter_input(INPUT_GET, 'since', FILTER_DEFAULT) ?? '2026-01-01';
        $data = fetch_shopify_orders($since);
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        break;

    case 'google_insights':
        $since = filter_input(INPUT_GET, 'since', FILTER_DEFAULT) ?? '2026-01-01';
        $until = filter_input(INPUT_GET, 'until', FILTER_DEFAULT) ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $since) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $until)) {
            echo json_encode(['ok' => false, 'error' => 'Formato de fecha inválido']);
            break;
        }
        $gads = fetch_gads_insights($since, $until);
        echo json_encode(['ok' => true, 'data' => $gads, 'period' => ['since' => $since, 'until' => $until]], JSON_UNESCAPED_UNICODE);
        break;

    case 'clear_cache':
        if (is_dir(CACHE_DIR)) {
            $files = glob(CACHE_DIR . '/*.json');
            foreach ($files as $f) unlink($f);
        }
        echo json_encode(['ok' => true, 'message' => 'Caché limpiado']);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
}
