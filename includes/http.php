<?php
/**
 * Funciones HTTP universales — usa curl si está disponible, file_get_contents como fallback
 */

/**
 * GET request
 * @param string $url
 * @param array $headers Headers adicionales
 * @param int $timeout
 * @return string|false
 */
function http_get(string $url, array $headers = [], int $timeout = 30): string|false {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: false;
    }

    $header_str = implode("\r\n", $headers);
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'ignore_errors' => true, 'header' => $header_str]]);
    return @file_get_contents($url, false, $ctx);
}

/**
 * POST request
 * @param string $url
 * @param string $body
 * @param array $headers
 * @param int $timeout
 * @return string|false
 */
function http_post(string $url, string $body, array $headers = [], int $timeout = 30): string|false {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
        ]);
        if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: false;
    }

    $header_str = implode("\r\n", $headers);
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => $header_str,
        'content' => $body,
        'timeout' => $timeout,
        'ignore_errors' => true,
    ]]);
    return @file_get_contents($url, false, $ctx);
}
