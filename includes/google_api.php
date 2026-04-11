<?php
/**
 * Wrapper para Google Ads API REST
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/http.php';

/**
 * Obtiene un access token usando el refresh token
 */
function gads_get_access_token(): ?string {
    if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
    $cache_file = CACHE_DIR . '/gads_token.json';
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['token']) && $data['expires'] > time()) return $data['token'];
    }

    $body = http_build_query([
        'client_id' => GADS_CLIENT_ID,
        'client_secret' => GADS_CLIENT_SECRET,
        'refresh_token' => GADS_REFRESH_TOKEN,
        'grant_type' => 'refresh_token',
    ]);

    $response = http_post('https://oauth2.googleapis.com/token', $body, ['Content-Type: application/x-www-form-urlencoded']);
    if (!$response) return null;

    $data = json_decode($response, true);
    $token = $data['access_token'] ?? null;
    if (!$token) return null;

    @file_put_contents($cache_file, json_encode([
        'token' => $token,
        'expires' => time() + ($data['expires_in'] ?? 3500) - 60,
    ]));
    return $token;
}

/**
 * Ejecuta una query GAQL contra Google Ads API
 */
function gads_query(string $query): array {
    $token = gads_get_access_token();
    if (!$token) return [];

    $url = 'https://googleads.googleapis.com/' . GADS_API_VERSION . '/customers/' . GADS_CUSTOMER_ID . '/googleAds:searchStream';

    $response = http_post($url, json_encode(['query' => $query]), [
        'Authorization: Bearer ' . $token,
        'developer-token: ' . GADS_DEVELOPER_TOKEN,
        'login-customer-id: ' . GADS_LOGIN_CUSTOMER_ID,
        'Content-Type: application/json',
    ]);

    if (!$response) return [];
    $data = json_decode($response, true);
    if (!$data || !is_array($data)) return [];

    $results = [];
    foreach ($data as $chunk) {
        if (isset($chunk['results'])) {
            foreach ($chunk['results'] as $row) $results[] = $row;
        }
    }
    return $results;
}

/**
 * Obtiene insights de campañas de Google Ads con desglose mensual
 */
function fetch_gads_insights(string $since, string $until): array {
    $cache_key = "gads_insights_{$since}_{$until}";
    return cached_fetch($cache_key, function() use ($since, $until) {
        $query = "SELECT campaign.name, campaign.id, campaign.status, campaign.advertising_channel_type, "
            . "segments.month, "
            . "metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, "
            . "metrics.conversions_value, metrics.ctr, metrics.average_cpc, metrics.cost_per_conversion, "
            . "metrics.interactions "
            . "FROM campaign "
            . "WHERE segments.date BETWEEN '{$since}' AND '{$until}' "
            . "ORDER BY metrics.cost_micros DESC";

        $results = gads_query($query);
        $campaigns = [];
        $by_month = [];

        foreach ($results as $row) {
            $c = $row['campaign'] ?? [];
            $m = $row['metrics'] ?? [];
            $seg = $row['segments'] ?? [];

            $cid = $c['id'] ?? '0';
            $month_str = $seg['month'] ?? '';
            $mk = $month_str ? substr($month_str, 0, 7) : 'unknown';

            $cost = round(((int)($m['costMicros'] ?? 0)) / 1000000);
            $impressions = (int)($m['impressions'] ?? 0);
            $clicks = (int)($m['clicks'] ?? 0);
            $conversions = round((float)($m['conversions'] ?? 0));
            $conv_value = round((float)($m['conversionsValue'] ?? 0));
            $ctr = round((float)($m['ctr'] ?? 0) * 100, 2);
            $avg_cpc = round(((int)($m['averageCpc'] ?? 0)) / 1000000);
            $cost_conv = round(((int)($m['costPerConversion'] ?? 0)) / 1000000);

            $parsed = [
                'campaign_name' => $c['name'] ?? '-',
                'campaign_id' => $cid,
                'status' => $c['status'] ?? 'UNKNOWN',
                'channel' => $c['advertisingChannelType'] ?? '-',
                'month_key' => $mk,
                'spend' => $cost,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'conversions_value' => $conv_value,
                'ctr' => $ctr,
                'cpc' => $avg_cpc,
                'cost_per_conversion' => $cost_conv,
            ];

            if (!isset($campaigns[$cid])) {
                $campaigns[$cid] = [
                    'name' => $c['name'] ?? '-',
                    'status' => $c['status'] ?? 'UNKNOWN',
                    'channel' => $c['advertisingChannelType'] ?? '-',
                    'total_spend' => 0, 'total_impressions' => 0, 'total_clicks' => 0,
                    'total_conversions' => 0, 'total_conv_value' => 0, 'months' => [],
                ];
            }
            $campaigns[$cid]['total_spend'] += $cost;
            $campaigns[$cid]['total_impressions'] += $impressions;
            $campaigns[$cid]['total_clicks'] += $clicks;
            $campaigns[$cid]['total_conversions'] += $conversions;
            $campaigns[$cid]['total_conv_value'] += $conv_value;
            $campaigns[$cid]['months'][$mk] = $parsed;

            if (!isset($by_month[$mk])) {
                $by_month[$mk] = ['spend' => 0, 'impressions' => 0, 'clicks' => 0, 'conversions' => 0, 'conv_value' => 0];
            }
            $by_month[$mk]['spend'] += $cost;
            $by_month[$mk]['impressions'] += $impressions;
            $by_month[$mk]['clicks'] += $clicks;
            $by_month[$mk]['conversions'] += $conversions;
            $by_month[$mk]['conv_value'] += $conv_value;
        }

        foreach ($campaigns as &$ca) {
            $ca['total_ctr'] = $ca['total_impressions'] > 0 ? round($ca['total_clicks'] / $ca['total_impressions'] * 100, 2) : 0;
            $ca['total_cpc'] = $ca['total_clicks'] > 0 ? round($ca['total_spend'] / $ca['total_clicks']) : 0;
            $ca['total_cost_conv'] = $ca['total_conversions'] > 0 ? round($ca['total_spend'] / $ca['total_conversions']) : 0;
        }

        foreach ($by_month as &$bm) {
            $bm['ctr'] = $bm['impressions'] > 0 ? round($bm['clicks'] / $bm['impressions'] * 100, 2) : 0;
            $bm['cpc'] = $bm['clicks'] > 0 ? round($bm['spend'] / $bm['clicks']) : 0;
            $bm['cost_conv'] = $bm['conversions'] > 0 ? round($bm['spend'] / $bm['conversions']) : 0;
        }

        ksort($by_month);
        return ['campaigns' => $campaigns, 'by_month' => $by_month];
    });
}
