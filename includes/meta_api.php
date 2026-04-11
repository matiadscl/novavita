<?php
/**
 * Wrapper para la API de Meta Marketing
 * Consulta campañas, ad sets, anuncios e insights
 */

require_once __DIR__ . '/config.php';

/**
 * Ejecuta una llamada GET a la Graph API de Meta
 * @param string $endpoint Endpoint (sin base URL)
 * @param array $params Parámetros query
 * @return array|null Respuesta decodificada o null si falla
 */
function meta_api_get(string $endpoint, array $params = []): ?array {
    $params['access_token'] = META_ACCESS_TOKEN;
    $url = 'https://graph.facebook.com/' . META_API_VERSION . '/' . $endpoint . '?' . http_build_query($params);

    $ctx = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) return null;

    return json_decode($response, true);
}

/**
 * Fetch a full URL (for pagination)
 * @param string $url
 * @return array|null
 */
function meta_api_fetch_url(string $url): ?array {
    $ctx = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) return null;
    return json_decode($response, true);
}

/**
 * Lee desde caché o ejecuta la llamada API
 * @param string $cache_key Clave de caché
 * @param callable $fetcher Función que retorna los datos
 * @return array
 */
function cached_fetch(string $cache_key, callable $fetcher): array {
    if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

    $cache_file = CACHE_DIR . '/' . md5($cache_key) . '.json';

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TTL) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data !== null) return $data;
    }

    $data = $fetcher();
    file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_UNICODE));
    return $data;
}

/**
 * Obtiene todas las campañas de la cuenta
 * @return array Lista de campañas
 */
function fetch_campaigns(): array {
    return cached_fetch('campaigns_list', function() {
        $all = [];
        $params = [
            'fields' => 'name,status,objective,daily_budget,lifetime_budget,start_time,stop_time,effective_status',
            'limit' => 100,
        ];

        $result = meta_api_get(META_AD_ACCOUNT_ID . '/campaigns', $params);
        if (!$result || !isset($result['data'])) return [];

        foreach ($result['data'] as $c) {
            $all[] = $c;
        }

        // Paginar si hay más
        while (isset($result['paging']['next'])) {
            $result = meta_api_fetch_url($result['paging']['next']);
            if (!$result || !isset($result['data'])) break;
            foreach ($result['data'] as $c) {
                $all[] = $c;
            }
        }

        return $all;
    });
}

/**
 * Obtiene insights a nivel de campaña con desglose mensual
 * @param string $since Fecha inicio YYYY-MM-DD
 * @param string $until Fecha fin YYYY-MM-DD
 * @return array Insights por campaña y mes
 */
function fetch_campaign_insights(string $since, string $until): array {
    $cache_key = "insights_campaign_monthly_{$since}_{$until}";
    return cached_fetch($cache_key, function() use ($since, $until) {
        $all = [];
        $params = [
            'fields' => 'campaign_name,campaign_id,impressions,reach,clicks,cpc,cpm,ctr,spend,actions,cost_per_action_type,frequency',
            'time_range' => json_encode(['since' => $since, 'until' => $until]),
            'time_increment' => 'monthly',
            'level' => 'campaign',
            'limit' => 500,
        ];

        $result = meta_api_get(META_AD_ACCOUNT_ID . '/insights', $params);
        if (!$result || !isset($result['data'])) return [];

        foreach ($result['data'] as $row) {
            $all[] = $row;
        }

        while (isset($result['paging']['next'])) {
            $result = meta_api_fetch_url($result['paging']['next']);
            if (!$result || !isset($result['data'])) break;
            foreach ($result['data'] as $row) {
                $all[] = $row;
            }
        }

        return $all;
    });
}

/**
 * Obtiene insights a nivel de ad set para una campaña
 * @param string $campaign_id ID de la campaña
 * @param string $since Fecha inicio
 * @param string $until Fecha fin
 * @return array
 */
function fetch_adset_insights(string $campaign_id, string $since, string $until): array {
    $cache_key = "insights_adset_{$campaign_id}_{$since}_{$until}";
    return cached_fetch($cache_key, function() use ($campaign_id, $since, $until) {
        $params = [
            'fields' => 'adset_name,adset_id,impressions,reach,clicks,cpc,cpm,ctr,spend,actions,cost_per_action_type,frequency',
            'time_range' => json_encode(['since' => $since, 'until' => $until]),
            'level' => 'adset',
            'limit' => 200,
            'filtering' => json_encode([['field' => 'campaign.id', 'operator' => 'EQUAL', 'value' => $campaign_id]]),
        ];

        $result = meta_api_get(META_AD_ACCOUNT_ID . '/insights', $params);
        if (!$result || !isset($result['data'])) return [];
        return $result['data'];
    });
}

/**
 * Obtiene insights a nivel de anuncio para una campaña
 * @param string $campaign_id ID de la campaña
 * @param string $since Fecha inicio
 * @param string $until Fecha fin
 * @return array
 */
function fetch_ad_insights(string $campaign_id, string $since, string $until): array {
    $cache_key = "insights_ad_{$campaign_id}_{$since}_{$until}";
    return cached_fetch($cache_key, function() use ($campaign_id, $since, $until) {
        $params = [
            'fields' => 'ad_name,ad_id,adset_name,impressions,reach,clicks,cpc,cpm,ctr,spend,actions,cost_per_action_type,frequency',
            'time_range' => json_encode(['since' => $since, 'until' => $until]),
            'level' => 'ad',
            'limit' => 200,
            'filtering' => json_encode([['field' => 'campaign.id', 'operator' => 'EQUAL', 'value' => $campaign_id]]),
        ];

        $result = meta_api_get(META_AD_ACCOUNT_ID . '/insights', $params);
        if (!$result || !isset($result['data'])) return [];
        return $result['data'];
    });
}

/**
 * Extrae el valor de una acción específica del array de actions
 * @param array|null $actions Array de acciones
 * @param string $type Tipo de acción (ej: 'purchase', 'link_click')
 * @return int
 */
function get_action_value(?array $actions, string $type): int {
    if (!$actions) return 0;
    foreach ($actions as $a) {
        if ($a['action_type'] === $type) return (int)$a['value'];
    }
    return 0;
}

/**
 * Extrae el costo por acción específica
 * @param array|null $costs Array de costos por acción
 * @param string $type Tipo de acción
 * @return float
 */
function get_cost_per_action(?array $costs, string $type): float {
    if (!$costs) return 0;
    foreach ($costs as $c) {
        if ($c['action_type'] === $type) return round((float)$c['value'], 2);
    }
    return 0;
}

/**
 * Mapea el objetivo de Meta al nombre legible
 * @param string $objective
 * @return string
 */
function readable_objective(string $objective): string {
    $map = [
        'OUTCOME_SALES' => 'Ventas',
        'OUTCOME_TRAFFIC' => 'Tráfico',
        'OUTCOME_LEADS' => 'Leads',
        'OUTCOME_ENGAGEMENT' => 'Interacción',
        'OUTCOME_AWARENESS' => 'Reconocimiento',
        'LINK_CLICKS' => 'Clics al enlace',
        'MESSAGES' => 'Mensajes',
    ];
    return $map[$objective] ?? $objective;
}

/**
 * Formatea CLP
 * @param float $amount
 * @return string
 */
function format_clp(float $amount): string {
    return '$' . number_format($amount, 0, ',', '.');
}

/**
 * Nombre corto del mes en español
 * @param string $date_start YYYY-MM-DD
 * @return string
 */
function month_label(string $date_start): string {
    $m = (int)substr($date_start, 5, 2);
    $months = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $y = substr($date_start, 0, 4);
    return ($months[$m] ?? '?') . ' ' . $y;
}

/**
 * Retorna el mes key (YYYY-MM) de un date_start
 * @param string $date_start
 * @return string
 */
function month_key(string $date_start): string {
    return substr($date_start, 0, 7);
}
