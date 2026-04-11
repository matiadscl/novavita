<?php
/**
 * Wrapper para Shopify Admin API
 * Consulta pedidos y productos
 */

require_once __DIR__ . '/config.php';

/**
 * Ejecuta GET a la Shopify Admin API
 * @param string $endpoint Ej: 'orders.json?status=any'
 * @return array|null
 */
function shopify_get(string $endpoint): ?array {
    $url = 'https://' . SHOPIFY_STORE . '/admin/api/' . SHOPIFY_API_VERSION . '/' . $endpoint;
    $ctx = stream_context_create([
        'http' => [
            'header' => 'X-Shopify-Access-Token: ' . SHOPIFY_TOKEN,
            'timeout' => 30,
            'ignore_errors' => true,
        ]
    ]);
    $response = @file_get_contents($url, false, $ctx);
    if (!$response) return null;
    return json_decode($response, true);
}

/**
 * Obtiene resumen de ventas de Shopify con desglose mensual
 * @param string $since Fecha inicio YYYY-MM-DD
 * @return array Datos parseados
 */
function fetch_shopify_orders(string $since): array {
    $cache_key = "shopify_orders_{$since}";

    return cached_fetch($cache_key, function() use ($since) {
        $all_orders = [];
        $page_info = null;
        $limit = 250;

        // First request
        $endpoint = "orders.json?status=any&created_at_min={$since}T00:00:00-03:00&limit={$limit}";
        $result = shopify_get($endpoint);
        if (!$result || !isset($result['orders'])) return ['orders' => [], 'summary' => [], 'by_month' => []];

        $all_orders = array_merge($all_orders, $result['orders']);

        // Paginate via Link header (simplified — for most cases 250 is enough)
        // Parse orders
        $summary = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'paid_orders' => 0,
            'cancelled' => 0,
            'refunded' => 0,
            'avg_ticket' => 0,
            'products_sold' => [],
        ];

        $by_month = [];
        $product_counts = [];

        foreach ($all_orders as $o) {
            $price = (float)($o['total_price'] ?? 0);
            $month = substr($o['created_at'] ?? '', 0, 7);
            $status = $o['financial_status'] ?? '';
            $cancelled = !empty($o['cancelled_at']);

            $summary['total_orders']++;
            $summary['total_revenue'] += $price;

            if ($status === 'paid' || $status === 'partially_refunded') $summary['paid_orders']++;
            if ($cancelled) $summary['cancelled']++;
            if ($status === 'refunded') $summary['refunded']++;

            // By month
            if (!isset($by_month[$month])) {
                $by_month[$month] = ['orders' => 0, 'revenue' => 0, 'paid' => 0, 'cancelled' => 0, 'items' => 0];
            }
            $by_month[$month]['orders']++;
            $by_month[$month]['revenue'] += $price;
            if ($status === 'paid') $by_month[$month]['paid']++;
            if ($cancelled) $by_month[$month]['cancelled']++;

            // Products
            foreach ($o['line_items'] ?? [] as $item) {
                $name = $item['title'] ?? 'Desconocido';
                $qty = (int)($item['quantity'] ?? 1);
                $by_month[$month]['items'] += $qty;

                if (!isset($product_counts[$name])) {
                    $product_counts[$name] = ['qty' => 0, 'revenue' => 0];
                }
                $product_counts[$name]['qty'] += $qty;
                $product_counts[$name]['revenue'] += (float)($item['price'] ?? 0) * $qty;
            }
        }

        $summary['avg_ticket'] = $summary['total_orders'] > 0 ? round($summary['total_revenue'] / $summary['total_orders']) : 0;

        // Top products
        arsort($product_counts);
        $summary['top_products'] = array_slice($product_counts, 0, 10, true);

        // Monthly derived
        foreach ($by_month as &$m) {
            $m['avg_ticket'] = $m['orders'] > 0 ? round($m['revenue'] / $m['orders']) : 0;
        }

        ksort($by_month);

        return ['summary' => $summary, 'by_month' => $by_month];
    });
}
