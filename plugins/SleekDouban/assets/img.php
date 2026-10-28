<?php
/**
 * Douban image proxy
 *
 * Douban's image CDN rejects requests whose Referer is not a Douban domain.
 * This endpoint fetches only doubanio.com images with a Douban referer and
 * caches them locally.
 */

$url = isset($_GET['url']) ? (string) $_GET['url'] : '';
$parts = parse_url($url);
$host = isset($parts['host']) ? strtolower($parts['host']) : '';
$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';

if (!in_array($scheme, array('http', 'https'), true)
    || !preg_match('/(^|\.)doubanio\.com$/', $host)
    || empty($parts['path'])) {
    http_response_code(400);
    exit;
}

$path = $parts['path'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'), true)) {
    $ext = 'jpg';
}

$cacheDir = __DIR__ . '/../cache/img';

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/' . md5($url) . '.' . $ext;
$contentTypes = array(
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'gif' => 'image/gif',
);

if (!is_file($cacheFile) || filesize($cacheFile) < 500) {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36';
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_ENCODING => '',
        CURLOPT_USERAGENT => $ua,
        CURLOPT_REFERER => 'https://movie.douban.com/',
        CURLOPT_HTTPHEADER => array('Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8'),
    ));
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($status !== 200 || !is_string($body) || strlen($body) < 500) {
        http_response_code(404);
        exit;
    }

    @file_put_contents($cacheFile, $body, LOCK_EX);
}

$type = isset($contentTypes[$ext]) ? $contentTypes[$ext] : 'image/jpeg';
header('Content-Type: ' . $type);
header('Cache-Control: public, max-age=604800, immutable');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($cacheFile));
readfile($cacheFile);
