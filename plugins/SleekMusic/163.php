<?php
/**
 * 网易云音乐播放跳转
 *
 * 网易云 outer URL 会 302 到 http CDN，HTTPS 页面会拦截。
 * 这里在服务端解析出真实 CDN 地址，再 302 到对应的 https 地址。
 */

$id = isset($_GET['id']) ? preg_replace('/\D+/', '', (string) $_GET['id']) : '';

if ($id === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'bad id';
    exit;
}

$target = 'https://music.163.com/song/media/outer/url?id=' . $id . '.mp3';
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36';
$final = '';

if (function_exists('curl_init')) {
    $ch = curl_init($target);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_RANGE => '0-0',
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_HTTPHEADER => array(
            'Referer: https://music.163.com/',
            'Accept: audio/mpeg,*/*',
        ),
    ));
    curl_exec($ch);
    $final = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 400) {
        $final = '';
    }
}

if ($final === '') {
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 10,
            'follow_location' => 1,
            'max_redirects' => 5,
            'user_agent' => $userAgent,
            'header' => "Referer: https://music.163.com/\r\nAccept: audio/mpeg,*/*\r\n",
        ),
    ));
    $body = @file_get_contents($target, false, $context);
    $headers = isset($http_response_header) ? $http_response_header : array();

    foreach ($headers as $header) {
        if (stripos($header, 'Location:') === 0) {
            $final = trim(substr($header, 9));
        }
    }

    if ($final === '' && is_string($body) && $body !== '') {
        http_response_code(502);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'audio unavailable';
        exit;
    }
}

if ($final === '') {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'audio unavailable';
    exit;
}

if (stripos($final, 'http://') === 0) {
    $final = 'https://' . substr($final, 7);
}

$host = strtolower((string) parse_url($final, PHP_URL_HOST));

if (!preg_match('/(?:^|\.)music\.126\.net$|(?:^|\.)music\.163\.com$|(?:^|\.)126\.net$/', $host)) {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'invalid audio host';
    exit;
}

header('Cache-Control: no-store, max-age=0');
header('Location: ' . $final, true, 302);
exit;
