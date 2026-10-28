<?php
require dirname(__DIR__, 3) . '/config.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(array('ok' => false, 'error' => 'Method not allowed'));
    exit;
}

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'Bad request'));
    exit;
}

try {
    $token = trim((string) ($_POST['token'] ?? ''));
    $db = \Typecho\Db::get();
    $tokenRow = $db->fetchRow(
        $db->select('value')->from('table.options')->where('name = ?', 'carefree_status_token')
    );
    $expectedToken = $tokenRow ? trim((string) $tokenRow['value']) : '';

    if ($expectedToken === '' || $token === '' || !hash_equals($expectedToken, $token)) {
        http_response_code(403);
        echo json_encode(array('ok' => false, 'error' => 'Forbidden'));
        exit;
    }

    $icon = trim((string) ($_POST['icon'] ?? ''));
    $text = trim(strip_tags((string) ($_POST['text'] ?? '')));
    $icon = preg_replace('/[^a-z0-9-]/', '', strtolower($icon));
    $text = preg_replace('/\s+/u', ' ', $text);
    $icon = mb_substr($icon, 0, 40, 'UTF-8');
    $text = mb_substr($text, 0, 16, 'UTF-8');

    if ($text === '') {
        $icon = '';
    } elseif ($icon === '') {
        $icon = 'leaf';
    }

    $value = json_encode(array(
        'icon' => $icon,
        'text' => $text,
        'updated' => time(),
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $row = $db->fetchRow(
        $db->select('name')->from('table.options')->where('name = ?', 'carefree_status')
    );

    if ($row) {
        $db->query(
            $db->update('table.options')
                ->rows(array('value' => $value))
                ->where('name = ?', 'carefree_status')
        );
    } else {
        $db->query(
            $db->insert('table.options')
                ->rows(array(
                    'name' => 'carefree_status',
                    'user' => 0,
                    'value' => $value,
                ))
        );
    }

    echo json_encode(array(
        'ok' => true,
        'status' => array(
            'icon' => $icon,
            'text' => $text,
        ),
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Server error'));
}
