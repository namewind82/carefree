<?php
/**
 * 歌词接口：优先按网易云歌曲 ID 获取，普通 MP3 按歌名搜索。
 */

$id = isset($_GET['id']) ? preg_replace('/\D+/', '', (string) $_GET['id']) : '';
$title = isset($_GET['title']) ? trim((string) $_GET['title']) : '';
$artist = isset($_GET['artist']) ? trim((string) $_GET['artist']) : '';

if ($id === '' && $title === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => false, 'message' => 'missing id or title'));
    exit;
}

$title = mb_substr($title, 0, 120, 'UTF-8');
$artist = mb_substr($artist, 0, 120, 'UTF-8');
$cacheDir = __DIR__ . '/cache';
$cacheKey = $id !== '' ? 'id-' . $id : 'q-' . md5(mb_strtolower($title . '|' . $artist, 'UTF-8'));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

if (is_file($cacheFile) && filemtime($cacheFile) > time() - 604800) {
    $cached = json_decode((string) file_get_contents($cacheFile), true);

    if (is_array($cached) && isset($cached['lrc'])) {
        echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

function sleek_music_http_json($url)
{
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => array('Referer: https://music.163.com/'),
        ));
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($status >= 200 && $status < 400 && is_string($body)) {
            $data = json_decode($body, true);

            if (is_array($data)) {
                return $data;
            }
        }
    }

    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 10,
            'follow_location' => 1,
            'user_agent' => $userAgent,
            'header' => "Referer: https://music.163.com/\r\n",
        ),
    ));
    $body = @file_get_contents($url, false, $context);

    if (is_string($body)) {
        $data = json_decode($body, true);

        if (is_array($data)) {
            return $data;
        }
    }

    return null;
}

function sleek_music_lyric_by_id($id)
{
    $data = sleek_music_http_json('https://music.163.com/api/song/lyric?id=' . $id . '&lv=1&kv=1&tv=-1');

    if (!is_array($data)) {
        return '';
    }

    return isset($data['lrc']['lyric']) ? (string) $data['lrc']['lyric'] : '';
}

function sleek_music_normalize($text)
{
    $text = mb_strtolower(trim((string) $text), 'UTF-8');
    $text = preg_replace('/[\s\p{P}]+/u', '', $text);

    return $text;
}

$result = array('ok' => false, 'lrc' => '', 'song' => '');

if ($id !== '') {
    $lrc = sleek_music_lyric_by_id($id);
    $result = array(
        'ok' => $lrc !== '',
        'lrc' => $lrc,
        'song' => '',
        'id' => $id,
    );
} else {
    $knownIds = array(
        '爱上张无忌洞箫版' => 166321,
        '情怨' => 2659002752,
        '一直很安静' => 1968781675,
        '逍遥仙' => 3394838484,
        '金刚萨埵心咒' => 3379696673,
    );
    $titleKey = sleek_music_normalize($title);
    $knownId = isset($knownIds[$titleKey]) ? $knownIds[$titleKey] : 0;

    if ($knownId > 0) {
        $lrc = sleek_music_lyric_by_id($knownId);
        $result = array(
            'ok' => $lrc !== '',
            'lrc' => $lrc,
            'song' => $title,
            'id' => $knownId,
        );
    } else {
    $query = trim($title . ' ' . ($artist !== '' && $artist !== '未知艺术家' ? $artist : ''));
    $search = sleek_music_http_json('https://music.163.com/api/search/get/web?s=' . rawurlencode($query) . '&type=1&limit=8');
    $songs = isset($search['result']['songs']) ? $search['result']['songs'] : array();
    $target = sleek_music_normalize($title);
    $chosen = null;

    if (is_array($songs)) {
        foreach ($songs as $song) {
            $name = isset($song['name']) ? sleek_music_normalize($song['name']) : '';

            if ($name === '') {
                continue;
            }

            if ($name === $target || strpos($name, $target) !== false || strpos($target, $name) !== false) {
                $chosen = $song;
                break;
            }
        }

        if (!$chosen && !empty($songs[0])) {
            $chosen = $songs[0];
        }
    }

    if ($chosen && !empty($chosen['id'])) {
        $lrc = sleek_music_lyric_by_id((int) $chosen['id']);
        $result = array(
            'ok' => $lrc !== '',
            'lrc' => $lrc,
            'song' => isset($chosen['name']) ? (string) $chosen['name'] : '',
            'id' => (int) $chosen['id'],
        );
    }
    }
}

if ($result['ok'] && $result['lrc'] !== '') {
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    if (is_dir($cacheDir)) {
        @file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
