<?php
/**
 * Carefree 主题函数库
 *
 * @package Carefree
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

require_once __DIR__ . '/inc-heatmap.php';

/* -------------------------------------------------------------------------
 * 后台主题设置
 * ---------------------------------------------------------------------- */

/**
 * 兼容 Typecho 1.1 / 1.2 的表单元素类名
 */
function sleek_element_class($type)
{
    $namespaced = '\\Typecho\\Widget\\Helper\\Form\\Element\\' . $type;

    return class_exists($namespaced) ? $namespaced : 'Typecho_Widget_Helper_Form_Element_' . $type;
}

function themeConfig($form)
{
    $text = sleek_element_class('Text');
    $select = sleek_element_class('Select');
    $radio = sleek_element_class('Radio');
    $textarea = sleek_element_class('Textarea');

    $form->addInput(new $text(
        'headerImage',
        null,
        sleek_theme_url('assets/img/header-default.jpg'),
        _t('顶部封面图'),
        _t('填写图片地址，例如 https://example.com/cover.jpg；留空则使用深色渐变背景。建议 1600×400 以上。')
    ));

    $form->addInput(new $text(
        'headerSignature',
        null,
        '',
        _t('顶部签名'),
        _t('显示在站名下方。留空则使用站点描述。')
    ));

    $form->addInput(new $select(
        'cardAuthorMode',
        array(
            'user' => _t('登录用户'),
            'site' => _t('站点名称'),
        ),
        'user',
        _t('说说卡片作者'),
        _t('选择「登录用户」后，登录状态显示当前用户的昵称和头像；未登录访客仍显示站点名称和站点头像。')
    ));

    $form->addInput(new $text(
        'userAvatarUrl',
        null,
        '',
        _t('登录用户头像地址'),
        _t('登录后使用此头像；留空则自动使用登录邮箱的 Gravatar。')
    ));

    $form->addInput(new $text(
        'avatarUrl',
        null,
        '',
        _t('站点头像地址'),
        _t('未登录访客看到的头像，也用于评论博主头像；留空使用主题自带头像。')
    ));

    $form->addInput(new $text(
        'gravatarMirror',
        null,
        'https://cravatar.cn/avatar/',
        _t('评论头像服务'),
        _t('默认使用 Cravatar 镜像，国内访问更快；也可换成 Gravatar 或自建地址。')
    ));

    $form->addInput(new $select(
        'colorMode',
        array(
            'auto' => _t('跟随系统'),
            'light' => _t('浅色'),
            'dark' => _t('深色'),
        ),
        'auto',
        _t('默认配色'),
        _t('访客仍可通过右上角按钮手动切换，选择会被记住。')
    ));

    $form->addInput(new $select(
        'headerStyle',
        array(
            'classic' => _t('经典封面（默认）'),
            'wechat' => _t('微信样式'),
        ),
        'classic',
        _t('顶部样式'),
        _t('经典封面保留当前头像叠在封面上的样式；微信样式将头像与资料移到封面下方。')
    ));

    $form->addInput(new $radio(
        'themeColor',
        array(
            'default' => _t('澄蓝（默认）'),
            'pine' => _t('松绿'),
            'amber' => _t('琥珀'),
            'crimson' => _t('朱砂'),
            'indigo' => _t('黛蓝'),
            'simplecatch' => _t('简灰'),
        ),
        'default',
        _t('主题配色'),
        _t('默认使用当前澄蓝；另有松绿、琥珀、朱砂、青瓷四种低饱和配色。')
    ));

    $form->addInput(new $text(
        'excerptLength',
        null,
        '220',
        _t('摘要字数'),
        _t('首页与列表页的说说长度，单位为字。默认 220。')
    ));

    $form->addInput(new $select(
        'showCatBar',
        array(
            '1' => _t('显示'),
            '0' => _t('隐藏'),
        ),
        '1',
        _t('首页分类筛选'),
        _t('是否在首页显示分类标签栏。')
    ));

    $form->addInput(new $text(
        'startYear',
        null,
        '',
        _t('建站年份'),
        _t('页脚版权年份的起始值，例如 2018；留空则只显示当前年份。')
    ));

    $form->addInput(new $text(
        'footerText',
        null,
        '',
        _t('页脚附加信息'),
        _t('支持 HTML，例如备案号、公安备案等。')
    ));

    $form->addInput(new $textarea(
        'customCss',
        null,
        '',
        _t('自定义 CSS'),
        _t('会追加在主题样式之后，便于微调。')
    ));
}

/**
 * 给文章编辑器增加封面字段
 */
function themeFields($layout)
{
    $text = sleek_element_class('Text');
    $isPage = $layout instanceof \Widget\Contents\Page\Edit;
    $isPost = $layout instanceof \Widget\Contents\Post\Edit;

    // 文章：仅需要封面字段
    if ($isPost || (!$isPage && !$isPost)) {
        $layout->addItem(new $text(
            'cover',
            null,
            '',
            _t('封面图地址'),
            _t('可选。填写后优先用作列表缩略图，留空则自动取文章内第一张图片。')
        ));
    }
}

/* -------------------------------------------------------------------------
 * 基础工具
 * ---------------------------------------------------------------------- */

function sleek_options()
{
    return Helper::options();
}

/**
 * 读取主题设置，空值回退到默认值
 */
function sleek_option($key, $default = '')
{
    $options = sleek_options();
    $value = $options->{$key};

    return ($value === null || $value === '' || $value === false) ? $default : $value;
}

function sleek_theme_url($path = '')
{
    $base = rtrim(sleek_options()->themeUrl, '/');

    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function sleek_avatar_url()
{
    $url = trim((string) sleek_option('avatarUrl', ''));

    if ($url === '') {
        return sleek_theme_url('assets/img/avatar.svg');
    }

    $path = parse_url($url, PHP_URL_PATH);
    $host = parse_url($url, PHP_URL_HOST);
    $siteHost = parse_url((string) sleek_options()->siteUrl, PHP_URL_HOST);

    if ($path && ($host === null || $host === '' || $host === $siteHost)) {
        $file = __TYPECHO_ROOT_DIR__ . $path;

        if (is_file($file)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . filemtime($file);
        }
    }

    return $url;
}

/**
 * 当前登录用户；未登录或调用失败时返回 null
 */
function sleek_current_user()
{
    static $resolved = false;
    static $user = null;

    if ($resolved) {
        return $user;
    }

    $resolved = true;

    try {
        if (class_exists('\Widget\User')) {
            $user = \Widget\User::alloc();
        }
    } catch (Throwable $e) {
        $user = null;
    }

    return $user;
}

/**
 * 说说卡片与顶栏使用的身份：登录用户或站点
 */
function sleek_identity()
{
    static $identity = null;

    if ($identity !== null) {
        return $identity;
    }

    $options = sleek_options();
    $siteName = trim((string) $options->title);
    $siteDescription = trim((string) $options->description);
    $siteSignature = trim((string) sleek_option('headerSignature', ''));

    if ($siteSignature === '') {
        $siteSignature = $siteDescription;
    }

    $mode = trim((string) sleek_option('cardAuthorMode', 'user'));
    $user = sleek_current_user();

    if ($mode !== 'site' && $user && $user->hasLogin()) {
        $name = trim((string) $user->screenName);
        $mail = trim((string) $user->mail);
        $avatar = trim((string) sleek_option('userAvatarUrl', ''));
        $signature = trim((string) sleek_option('userSignature', ''));

        if ($avatar !== '') {
            $avatar = sleek_abs_url($avatar);
        } elseif ($mail !== '') {
            $avatar = sleek_comment_avatar_url((int) $user->uid, 0, $mail, 96);
        } else {
            $avatar = sleek_avatar_url();
        }

        if ($signature === '') {
            $signature = $siteDescription;
        }

        $identity = array(
            'name' => $name !== '' ? $name : $siteName,
            'avatar' => $avatar,
            'signature' => $signature,
            'isUser' => true,
        );

        return $identity;
    }

    $identity = array(
        'name' => $siteName,
        'avatar' => sleek_avatar_url(),
        'signature' => $siteSignature,
        'isUser' => false,
    );

    return $identity;
}

function sleek_theme_palettes()
{
    return array(
        'default' => array(
            'label' => _t('澄蓝（默认）'),
            'accent' => '#4a90e2',
            'strong' => '#3b7fd1',
            'soft' => '#eaf3ff',
            'rgb' => '74,144,226',
            'bg' => '#f5f6f7',
        ),
        'pine' => array(
            'label' => _t('松绿'),
            'accent' => '#6f8368',
            'strong' => '#4f6548',
            'soft' => '#edf2e8',
            'rgb' => '111,131,104',
            'bg' => '#f3f5f0',
        ),
        'amber' => array(
            'label' => _t('琥珀'),
            'accent' => '#a87d2b',
            'strong' => '#7d5a14',
            'soft' => '#f6efe0',
            'rgb' => '168,125,43',
            'bg' => '#f6f3ec',
        ),
        'crimson' => array(
            'label' => _t('朱砂'),
            'accent' => '#b93a32',
            'strong' => '#8f241e',
            'soft' => '#fbedeb',
            'rgb' => '185,58,50',
            'bg' => '#f6f4f1',
        ),
        'indigo' => array(
            'label' => _t('黛蓝'),
            'accent' => '#4f46e5',
            'strong' => '#3730a3',
            'soft' => '#eef0ff',
            'rgb' => '79,70,229',
            'bg' => '#f5f7fb',
        ),
        'simplecatch' => array(
            'label' => _t('简灰'),
            'accent' => '#333333',
            'strong' => '#1f1f1f',
            'soft' => '#ededed',
            'rgb' => '51,51,51',
            'bg' => '#f0f0f0',
        ),
    );
}

function sleek_theme_color()
{
    $color = trim((string) sleek_option('themeColor', 'default'));

    return array_key_exists($color, sleek_theme_palettes()) ? $color : 'default';
}

function sleek_theme_palette()
{
    $palettes = sleek_theme_palettes();

    return $palettes[sleek_theme_color()];
}

function sleek_accent()
{
    $palette = sleek_theme_palette();

    return $palette['accent'];
}

/**
 * 状态线性图标
 */
function sleek_status_icon($name)
{
    $icons = array(
        'leaf' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" /> <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />',
        'smile' => '<circle cx="12" cy="12" r="10" /> <path d="M8 14s1.5 2 4 2 4-2 4-2" /> <line x1="9" x2="9.01" y1="9" y2="9" /> <line x1="15" x2="15.01" y1="9" y2="9" />',
        'bomb' => '<circle cx="11" cy="13" r="9" /> <path d="M14.35 4.65 16.3 2.7a2.41 2.41 0 0 1 3.4 0l1.6 1.6a2.4 2.4 0 0 1 0 3.4l-1.95 1.95" /> <path d="m22 2-1.5 1.5" />',
        'fish' => '<path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-.94 3.47-3.44 6-7 6s-7.56-2.53-8.5-6Z" /> <path d="M18 12v.5" /> <path d="M16 17.93a9.77 9.77 0 0 1 0-11.86" /> <path d="M7 10.67C7 8 5.58 5.97 2.73 5.5c-1 1.5-1 5 .23 6.5-1.24 1.5-1.24 5-.23 6.5C5.58 18.03 7 16 7 13.33" /> <path d="M10.46 7.26C10.2 5.88 9.17 4.24 8 3h5.8a2 2 0 0 1 1.98 1.67l.23 1.4" /> <path d="m16.01 17.93-.23 1.4A2 2 0 0 1 13.8 21H9.5a5.96 5.96 0 0 0 1.49-3.98" />',
        'sun' => '<circle cx="12" cy="12" r="4" /> <path d="M12 2v2" /> <path d="M12 20v2" /> <path d="m4.93 4.93 1.41 1.41" /> <path d="m17.66 17.66 1.41 1.41" /> <path d="M2 12h2" /> <path d="M20 12h2" /> <path d="m6.34 17.66-1.41 1.41" /> <path d="m19.07 4.93-1.41 1.41" />',
        'battery-low' => '<rect width="16" height="10" x="2" y="7" rx="2" ry="2" /> <line x1="22" x2="22" y1="11" y2="13" /> <line x1="6" x2="6" y1="11" y2="13" />',
        'hourglass' => '<path d="M5 22h14" /> <path d="M5 2h14" /> <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" /> <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />',
        'zap' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />',
        'cloud-rain' => '<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" /> <path d="M16 14v6" /> <path d="M8 14v6" /> <path d="M12 16v6" />',
        'brain' => '<path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z" /> <path d="M12 5a3 3 0 1 1 5.997.125 4 4 0 0 1 2.526 5.77 4 4 0 0 1-.556 6.588A4 4 0 1 1 12 18Z" /> <path d="M15 13a4.5 4.5 0 0 1-3-4 4.5 4.5 0 0 1-3 4" /> <path d="M17.599 6.5a3 3 0 0 0 .399-1.375" /> <path d="M6.003 5.125A3 3 0 0 0 6.401 6.5" /> <path d="M3.477 10.896a4 4 0 0 1 .585-.396" /> <path d="M19.938 10.5a4 4 0 0 1 .585.396" /> <path d="M6 18a4 4 0 0 1-1.967-.516" /> <path d="M19.967 17.484A4 4 0 0 1 18 18" />',
        'sparkles' => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" /> <path d="M20 3v4" /> <path d="M22 5h-4" /> <path d="M4 17v2" /> <path d="M5 18H3" />',
        'bot' => '<path d="M12 8V4H8" /> <rect width="16" height="12" x="4" y="8" rx="2" /> <path d="M2 14h2" /> <path d="M20 14h2" /> <path d="M15 13v2" /> <path d="M9 13v2" />',
        'brick-wall' => '<rect width="18" height="18" x="3" y="3" rx="2" /> <path d="M12 9v6" /> <path d="M16 15v6" /> <path d="M16 3v6" /> <path d="M3 15h18" /> <path d="M3 9h18" /> <path d="M8 15v6" /> <path d="M8 3v6" />',
        'book-open' => '<path d="M12 7v14" /> <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z" />',
        'briefcase-business' => '<path d="M12 12h.01" /> <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" /> <path d="M22 13a18.15 18.15 0 0 1-20 0" /> <rect width="20" height="14" x="2" y="6" rx="2" />',
        'plane' => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z" />',
        'house' => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" /> <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />',
        'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />',
        'party-popper' => '<path d="M5.8 11.3 2 22l10.7-3.79" /> <path d="M4 3h.01" /> <path d="M22 8h.01" /> <path d="M15 2h.01" /> <path d="M22 20h.01" /> <path d="m22 2-2.24.75a2.9 2.9 0 0 0-1.96 3.12c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10" /> <path d="m22 13-.82-.33c-.86-.34-1.82.2-1.98 1.11c-.11.7-.72 1.22-1.43 1.22H17" /> <path d="m11 2 .33.82c.34.86-.2 1.82-1.11 1.98C9.52 4.9 9 5.52 9 6.23V7" /> <path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2Z" />',
        'pencil' => '<path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" /> <path d="m15 5 4 4" />',
    );

    $name = preg_replace('/[^a-z0-9-]/', '', strtolower((string) $name));

    if (!isset($icons[$name])) {
        $name = 'leaf';
    }

    return '<svg class="status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
}

/**
 * 当前站点状态
 */
function sleek_status()
{
    static $status = null;

    if ($status !== null) {
        return $status;
    }

    $status = array(
        'icon' => 'leaf',
        'text' => '自在',
        'updated' => 0,
    );

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('value')->from('table.options')->where('name = ?', 'carefree_status')
        );

        if ($row) {
            $data = json_decode((string) $row['value'], true);

            if (is_array($data)) {
                $text = trim((string) ($data['text'] ?? ''));
                $icon = trim((string) ($data['icon'] ?? ''));
                $legacyEmoji = trim((string) ($data['emoji'] ?? ''));
                $emojiMap = array(
                    '🌿' => 'leaf',
                    '🙂' => 'smile',
                    '🤯' => 'bomb',
                    '🐟' => 'fish',
                    '☀️' => 'sun',
                    '😮‍💨' => 'battery-low',
                    '😶' => 'hourglass',
                    '🏃' => 'zap',
                    '🌧️' => 'cloud-rain',
                    '💭' => 'brain',
                    '✨' => 'sparkles',
                    '🤖' => 'bot',
                    '🧱' => 'brick-wall',
                    '📚' => 'book-open',
                    '💼' => 'briefcase-business',
                    '✈️' => 'plane',
                    '🏠' => 'house',
                    '🌙' => 'moon',
                    '🎉' => 'party-popper',
                    '✍️' => 'pencil',
                );
                if ($icon === '' && $legacyEmoji !== '' && isset($emojiMap[$legacyEmoji])) {
                    $icon = $emojiMap[$legacyEmoji];
                }
                $status['text'] = $text;
                $status['icon'] = $text !== '' ? ($icon !== '' ? $icon : 'leaf') : '';
                $status['updated'] = (int) ($data['updated'] ?? 0);
            }
        }
    } catch (\Throwable $e) {
        // 使用默认状态
    }

    return $status;
}

/**
 * 状态编辑令牌（仅管理员页面输出）
 */
function sleek_status_token()
{
    static $token = null;

    if ($token !== null) {
        return $token;
    }

    $token = '';

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('value')->from('table.options')->where('name = ?', 'carefree_status_token')
        );

        if ($row) {
            $token = trim((string) $row['value']);
        }

        if ($token === '') {
            $token = bin2hex(random_bytes(16));

            if ($row) {
                $db->query(
                    $db->update('table.options')
                        ->rows(array('value' => $token))
                        ->where('name = ?', 'carefree_status_token')
                );
            } else {
                $db->query(
                    $db->insert('table.options')
                        ->rows(array(
                            'name' => 'carefree_status_token',
                            'user' => 0,
                            'value' => $token,
                        ))
                );
            }
        }
    } catch (\Throwable $e) {
        $token = '';
    }

    return $token;
}

function sleek_color_mode()
{
    $mode = sleek_option('colorMode', 'auto');

    return in_array($mode, array('auto', 'light', 'dark'), true) ? $mode : 'auto';
}

/**
 * 兼容新旧版本获取数据库对象
 */
function sleek_db()
{
    if (class_exists('\Typecho\Db')) {
        return \Typecho\Db::get();
    }

    return Typecho_Db::get();
}

/**
 * 生成任意路由地址
 */
function sleek_router_url($name, array $params)
{
    $options = sleek_options();

    if (class_exists('\Typecho\Router')) {
        return \Typecho\Router::url($name, $params, $options->index);
    }

    return Typecho_Router::url($name, $params, $options->index);
}

function sleek_post_url($cid, $slug)
{
    return sleek_router_url('post', array('cid' => $cid, 'slug' => $slug));
}

function sleek_abs_url($url)
{
    $url = trim((string) $url);

    if ($url === '' || strpos($url, 'data:') === 0) {
        return $url;
    }

    if (preg_match('#^(?:https?:)?//#i', $url)) {
        return $url;
    }

    return rtrim(sleek_options()->siteUrl, '/') . '/' . ltrim($url, '/');
}

function sleek_icon($name, $class = 'icon')
{
    echo '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true" focusable="false"><use href="#i-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"></use></svg>';
}

function sleek_nav_icon($slug, $title = '')
{
    $slug = strtolower((string) $slug);
    $map = array(
        'about' => 'user',
        'about-me' => 'user',
        'me' => 'user',
        'links' => 'link',
        'link' => 'link',
        'friends' => 'link',
        'friend' => 'link',
        'photos' => 'photo',
        'photo' => 'photo',
        'gallery' => 'photo',
        'messages' => 'chat',
        'message' => 'chat',
        'guestbook' => 'chat',
        'talk' => 'chat',
        'archive' => 'archive',
        'archives' => 'archive',
        'timeline' => 'archive',
        'circle' => 'circle',
        'tags' => 'tag',
        'tag' => 'tag',
        'douban' => 'douban',
        'movie' => 'douban',
        'movies' => 'douban',
        'book' => 'douban',
        'books' => 'douban',
    );

    if (isset($map[$slug])) {
        return $map[$slug];
    }

    $title = (string) $title;

    if (strpos($title, '豆瓣') !== false) {
        return 'douban';
    }
    if (strpos($title, '友') !== false || strpos($title, '链') !== false) {
        return 'link';
    }
    if (strpos($title, '相') !== false || strpos($title, '图') !== false) {
        return 'photo';
    }
    if (strpos($title, '留') !== false || strpos($title, '说') !== false) {
        return 'chat';
    }
    if (strpos($title, '档') !== false || strpos($title, '归') !== false) {
        return 'archive';
    }
    if (strpos($title, '关') !== false || strpos($title, '我') !== false) {
        return 'user';
    }

    return 'file';
}

/**
 * 全站标签
 */
function sleek_tags()
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $cache = array();

    try {
        $class = class_exists('\Widget\Metas\Tag\Cloud')
            ? '\Widget\Metas\Tag\Cloud'
            : 'Widget_Metas_Tag_Cloud';

        $class::alloc('sort=count&desc=1&limit=200')->to($tags);

        while ($tags->next()) {
            $name = trim((string) $tags->name);
            $slug = trim((string) $tags->slug);

            if ($name === '' || $slug === '' || (int) $tags->count <= 0) {
                continue;
            }

            $permalink = trim((string) $tags->permalink);

            if ($permalink === '') {
                $permalink = rtrim((string) sleek_options()->siteUrl, '/') . '/tag/' . rawurlencode($slug) . '/';
            }

            $cache[] = array(
                'name' => $name,
                'slug' => $slug,
                'count' => (int) $tags->count,
                'permalink' => $permalink,
            );
        }
    } catch (\Throwable $e) {
        $cache = array();
    }

    if (empty($cache)) {
        try {
            $db = sleek_db();
            $rows = $db->fetchAll(
                $db->select('name', 'slug', 'count')
                    ->from('table.metas')
                    ->where('type = ?', 'tag')
                    ->order('count', \Typecho\Db::SORT_DESC)
            );

            foreach ($rows as $row) {
                $name = trim((string) $row['name']);
                $slug = trim((string) $row['slug']);

                if ($name === '' || $slug === '' || (int) $row['count'] <= 0) {
                    continue;
                }

                $cache[] = array(
                    'name' => $name,
                    'slug' => $slug,
                    'count' => (int) $row['count'],
                    'permalink' => rtrim((string) sleek_options()->siteUrl, '/') . '/tag/' . rawurlencode($slug) . '/',
                );
            }
        } catch (\Throwable $e) {
            $cache = array();
        }
    }

    return $cache;
}

/**
 * Links 插件友链
 */
function sleek_links_items($limit = 0)
{
    static $cache = null;

    if ($cache !== null) {
        return $limit > 0 ? array_slice($cache, 0, $limit) : $cache;
    }

    $cache = array();

    try {
        $options = \Helper::options();

        if (!isset($options->plugins['activated']['Links'])) {
            return $cache;
        }

        $db = \Typecho\Db::get();
        $prefix = $db->getPrefix();
        $rows = $db->fetchAll(
            $db->select()
                ->from('table.links')
                ->where('state = ?', 1)
                ->order($prefix . 'links.order', \Typecho\Db::SORT_ASC)
        );

        foreach ($rows as $row) {
            $name = trim((string) $row['name']);
            $url = trim((string) $row['url']);

            if ($name === '' || $url === '') {
                continue;
            }

            $cache[] = array(
                'name' => $name,
                'url' => $url,
                'desc' => trim((string) $row['description']),
                'avatar' => trim((string) $row['image']),
                'sort' => trim((string) $row['sort']),
            );
        }
    } catch (\Throwable $e) {
        $cache = array();
    }

    return $limit > 0 ? array_slice($cache, 0, $limit) : $cache;
}

/**
 * 主导航项目（首页 + 所有独立页面）
 */
function sleek_nav_items($archive)
{
    $options = sleek_options();
    $items = array(
        array(
            'label' => _t('首页'),
            'url' => $options->siteUrl,
            'icon' => 'home',
            'active' => $archive->is('index'),
        ),
    );

    $class = class_exists('\Widget\Contents\Page\Rows')
        ? '\Widget\Contents\Page\Rows'
        : 'Widget_Contents_Page_List';

    $class::alloc()->to($pages);

    while ($pages->next()) {
        $items[] = array(
            'label' => $pages->title,
            'url' => $pages->permalink,
            'icon' => sleek_nav_icon($pages->slug, $pages->title),
            'active' => $archive->is('page', $pages->slug),
        );
    }

    return $items;
}

/**
 * 首页分类标签栏
 */
function sleek_category_bar($activeSlug = '')
{
    $class = class_exists('\Widget\Metas\Category\Rows')
        ? '\Widget\Metas\Category\Rows'
        : 'Widget_Metas_Category_List';

    $class::alloc()->to($categories);

    $items = array();
    while ($categories->next()) {
        $items[] = array(
            'name' => $categories->name,
            'slug' => $categories->slug,
            'permalink' => $categories->permalink,
            'count' => (int) $categories->count,
        );
    }

    if (empty($items)) {
        return;
    }

    echo '<div class="cat-bar" role="navigation" aria-label="分类">';
    echo '<a class="chip' . ($activeSlug === '' ? ' current' : '') . '" href="' . htmlspecialchars(sleek_options()->siteUrl, ENT_QUOTES, 'UTF-8') . '">全部</a>';

    foreach ($items as $item) {
        $chipClass = 'chip' . ($activeSlug === $item['slug'] ? ' current' : '');
        echo '<a class="' . $chipClass . '" href="' . htmlspecialchars($item['permalink'], ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '</a>';
    }

    echo '</div>';
}

/* -------------------------------------------------------------------------
 * 内容处理
 * ---------------------------------------------------------------------- */

function sleek_time_ago($timestamp)
{
    $diff = time() - (int) $timestamp;

    if ($diff < 60) {
        return _t('刚刚');
    }
    if ($diff < 3600) {
        return sprintf(_t('%d 分钟前'), floor($diff / 60));
    }
    if ($diff < 86400) {
        return sprintf(_t('%d 小时前'), floor($diff / 3600));
    }
    if ($diff < 2592000) {
        return sprintf(_t('%d 天前'), floor($diff / 86400));
    }
    if ($diff < 31536000) {
        return sprintf(_t('%d 个月前'), floor($diff / 2592000));
    }

    return sprintf(_t('%d 年前'), floor($diff / 31536000));
}

function sleek_reading_time($content)
{
    $text = trim(preg_replace('/\s+/u', '', strip_tags((string) $content)));
    $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);

    return max(1, (int) ceil($length / 400));
}

/**
 * 提取纯文本摘要
 */
function sleek_excerpt($post, $length = 120, $content = null, &$truncated = null)
{
    $truncated = false;
    $html = null === $content ? (string) $post->content : (string) $content;
    $html = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $html);
    $html = preg_replace('/<(p|div|br|li|h[1-6]|blockquote|pre|tr)[^>]*>/i', ' ', $html);
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);

    $length = (int) $length;
    if ($length <= 0) {
        $length = 120;
    }

    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        $text = mb_substr($text, 0, $length, 'UTF-8') . '…';
        $truncated = true;
    } elseif (strlen($text) > $length * 3) {
        $text = substr($text, 0, $length * 3) . '…';
        $truncated = true;
    }

    return $text;
}

/**
 * 提取文章中的视频
 */
function sleek_videos($html, $limit = 1)
{
    $videos = array();

    if (!preg_match_all('/<video\b([^>]*)>(.*?)<\/video>/is', (string) $html, $matches, PREG_SET_ORDER)) {
        return $videos;
    }

    foreach ($matches as $match) {
        $attrs = $match[1];
        $inner = $match[2];
        $src = '';
        $poster = '';

        if (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $attrs, $m)) {
            $src = $m[1];
        } elseif (preg_match('/<source\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $inner, $m)) {
            $src = $m[1];
        }

        if (preg_match('/\bposter\s*=\s*["\']([^"\']+)["\']/i', $attrs, $m)) {
            $poster = $m[1];
        }

        $src = sleek_abs_url(html_entity_decode(trim($src), ENT_QUOTES, 'UTF-8'));
        $poster = sleek_abs_url(html_entity_decode(trim($poster), ENT_QUOTES, 'UTF-8'));

        if ($src === '' || !preg_match('#^https?://#i', $src)) {
            continue;
        }

        $videos[] = array(
            'src' => $src,
            'poster' => $poster,
        );

        if (count($videos) >= (int) $limit) {
            break;
        }
    }

    return $videos;
}

/**
 * 首页短文：保留 Markdown 渲染后的正文结构
 */
function sleek_home_rich_html($post, $source = null)
{
    $html = null === $source ? (string) $post->content : (string) $source;

    if (trim($html) === '') {
        return '';
    }

    $html = preg_replace('/<!--SLEEK_MUSIC_START-->.*?<!--SLEEK_MUSIC_END-->/is', '', $html);
    $html = preg_replace('/\[music\b[^\]]*\]/i', '', $html);
    $html = preg_replace('/\[163\b[^\]]*\]/i', '', $html);
    $html = preg_replace('/!\[[^\]]*\]\([^)]*\)/i', '', $html);
    $html = preg_replace('/!\[[^\]]*\]\[[^\]]*\]/i', '', $html);
    $html = preg_replace('/<img\b[^>]*>/i', '', $html);
    $html = preg_replace('/<span\b[^>]*\bclass="[^"]*\bbili-player\b[^"]*"[^>]*>.*?<\/span>\s*<\/span>/is', '', $html);
    $html = preg_replace('/<(audio|video|iframe|figure)\b[^>]*>.*?<\/\1>/is', '', $html);
    $html = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $html);
    $html = preg_replace('/<!--more-->/i', '', $html);
    $html = trim($html);

    if ($html === '') {
        return '';
    }

    $text = trim(strip_tags($html));

    if ($text === '' && !preg_match('/<(ul|ol|blockquote|pre|table|hr)\b/i', $html)) {
        return '';
    }

    return $html;
}

/**
 * 提取文章中的图片地址
 */
function sleek_images($html, $limit = 9)
{
    $images = array();

    if (preg_match_all('/<img\b[^>]*>/i', (string) $html, $matches)) {
        foreach ($matches[0] as $tag) {
        $src = '';

        if (preg_match('/\bdata-(?:src|original|actual)\s*=\s*["\']([^"\']+)["\']/i', $tag, $m)) {
            $src = $m[1];
        } elseif (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $tag, $m)) {
            $src = $m[1];
        }

        $src = trim($src);

        if ($src === '' || stripos($src, 'data:') === 0) {
            continue;
        }

        if (preg_match('#(emoji|loading\.(?:svg|gif)|/avatar/|gravatar|cravatar)#i', $src)) {
            continue;
        }

        $images[] = sleek_abs_url($src);

            if (count($images) >= (int) $limit) {
                break;
            }
        }
    }

    foreach (sleek_markdown_images($html) as $src) {
        $src = trim((string) $src);

        if ($src === '' || stripos($src, 'data:') === 0) {
            continue;
        }

        if (preg_match('#(emoji|loading\.(?:svg|gif)|/avatar/|gravatar|cravatar)#i', $src)) {
            continue;
        }

        $images[] = sleek_abs_url($src);

        if (count($images) >= (int) $limit) {
            break;
        }
    }

    return array_slice(array_values(array_unique($images)), 0, (int) $limit);
}

/**
 * 封面图：自定义字段 cover 优先，其次正文第一张图
 */
function sleek_cover($post, $content = null)
{
    $cover = trim((string) $post->fields->cover);

    if ($cover !== '') {
        return sleek_abs_url($cover);
    }

    $images = sleek_images(null === $content ? $post->content : $content, 1);

    return empty($images) ? '' : $images[0];
}

/**
 * 评论头像：博主使用站点头像，访客使用镜像头像服务
 */
function sleek_gravatar($comments, $size = 38)
{
    $size = (int) $size;
    $local = sleek_theme_url('assets/img/avatar.svg');
    $name = (string) $comments->author;
    $src = $local;

    if (!empty($comments->authorId) && $comments->authorId == $comments->ownerId) {
        $src = sleek_identity()['avatar'];
    } else {
        $mail = strtolower(trim((string) $comments->mail));

        if ($mail !== '') {
            $mirror = trim((string) sleek_option('gravatarMirror', 'https://cravatar.cn/avatar/'));

            if ($mirror === '') {
                $mirror = 'https://cravatar.cn/avatar/';
            }

            $src = rtrim($mirror, '/') . '/' . md5($mail) . '?d=mp&s=' . ($size * 2);
        }
    }

    $fallback = htmlspecialchars($local, ENT_QUOTES, 'UTF-8');

    echo '<img class="avatar" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
        . ' alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'
        . ' width="' . $size . '" height="' . $size . '" loading="lazy" referrerpolicy="no-referrer"'
        . ' onerror="this.onerror=null;this.src=\'' . $fallback . '\';">';
}

/**
 * 评论头像地址（用于卡片上的最新回复）
 */
function sleek_comment_avatar_url($authorId, $ownerId, $mail, $size = 24)
{
    if (!empty($authorId) && $authorId == $ownerId) {
        return sleek_identity()['avatar'];
    }

    $mail = strtolower(trim((string) $mail));

    if ($mail === '') {
        return sleek_theme_url('assets/img/avatar.svg');
    }

    $mirror = trim((string) sleek_option('gravatarMirror', 'https://cravatar.cn/avatar/'));

    if ($mirror === '') {
        $mirror = 'https://cravatar.cn/avatar/';
    }

    return rtrim($mirror, '/') . '/' . md5($mail) . '?d=mp&s=' . ((int) $size * 2);
}

/**
 * 评论纯文本摘要，去掉 HTML / Markdown 图片链接
 */
function sleek_comment_excerpt($text, $length = 80)
{
    $text = (string) $text;
    $text = preg_replace('/<!--.*?-->/s', '', $text);
    $text = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $text);
    $text = preg_replace('/<br\b[^>]*>/i', ' ', $text);
    $text = preg_replace('/<\/(p|div|li|blockquote)>/i', ' ', $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/u', ' [图片] ', $text);
    $text = preg_replace('/\[([^\]]+)\]\([^)]*\)/u', '$1', $text);
    $text = preg_replace('/[*_~`]{1,3}/u', '', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);

    $len = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);

    if ($len > $length) {
        $text = function_exists('mb_substr')
            ? mb_substr($text, 0, $length, 'UTF-8')
            : substr($text, 0, $length);
        $text .= '…';
    }

    return $text;
}

/**
 * 每篇文章最新的一条已审核回复（parent > 0）
 */
function sleek_latest_reply($cid)
{
    static $cache = array();

    $cid = (int) $cid;

    if ($cid <= 0) {
        return null;
    }

    if (array_key_exists($cid, $cache)) {
        return $cache[$cid];
    }

    $cache[$cid] = null;

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('coid', 'author', 'authorId', 'ownerId', 'mail', 'parent', 'text', 'created')
                ->from('table.comments')
                ->where('cid = ?', $cid)
                ->where('parent > ?', 0)
                ->where('status = ?', 'approved')
                ->order('created', 'DESC')
                ->order('coid', 'DESC')
                ->limit(1)
        );
    } catch (Exception $e) {
        return null;
    }

    if (!$row) {
        return null;
    }

    $parentAuthor = '';

    if (!empty($row['parent'])) {
        try {
            $parent = $db->fetchRow(
                $db->select('author')->from('table.comments')
                    ->where('coid = ?', (int) $row['parent'])
                    ->limit(1)
            );

            if ($parent) {
                $parentAuthor = (string) $parent['author'];
            }
        } catch (Exception $e) {
            $parentAuthor = '';
        }
    }

    $cache[$cid] = array(
        'coid' => (int) $row['coid'],
        'author' => (string) $row['author'],
        'authorId' => (int) $row['authorId'],
        'ownerId' => (int) $row['ownerId'],
        'mail' => (string) $row['mail'],
        'parentAuthor' => $parentAuthor,
        'text' => sleek_comment_excerpt($row['text'], 80),
        'created' => (int) $row['created'],
    );

    return $cache[$cid];
}

/* -------------------------------------------------------------------------
 * 阅读量
 * ---------------------------------------------------------------------- */

function sleek_views($cid, $increment = false)
{
    $cid = (int) $cid;

    if ($cid <= 0) {
        return 0;
    }

    $cookie = 'sleek_viewed_' . $cid;
    $viewed = isset($_COOKIE[$cookie]);

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('int_value')->from('table.fields')
                ->where('cid = ?', $cid)
                ->where('name = ?', 'sleek_views')
        );

        $views = $row ? (int) $row['int_value'] : 0;

        if (!$increment || $viewed) {
            return $views;
        }

        $views++;

        if ($row) {
            $db->query(
                $db->update('table.fields')
                    ->rows(array('int_value' => $views))
                    ->where('cid = ?', $cid)
                    ->where('name = ?', 'sleek_views')
            );
        } else {
            $db->query(
                $db->insert('table.fields')->rows(array(
                    'cid' => $cid,
                    'name' => 'sleek_views',
                    'type' => 'int',
                    'str_value' => '',
                    'int_value' => 1,
                    'float_value' => 0,
                ))
            );
        }

        if (!headers_sent()) {
            setcookie($cookie, '1', time() + 3600, '/');
        }

        return $views;
    } catch (Throwable $e) {
        return 0;
    }
}

/* -------------------------------------------------------------------------
 * 点赞
 * ---------------------------------------------------------------------- */

function sleek_like_fingerprint($cid, $client = '')
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
    $salt = defined('AUTH_KEY') ? (string) AUTH_KEY : 'sleek-likes';
    $client = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $client);

    if ($client !== '') {
        $identity = 'client|' . $client;
    } else {
        $identity = 'ip|' . $ip . '|' . $ua;
    }

    return substr(hash('sha256', $cid . '|' . $identity . '|' . $salt), 0, 32);
}

function sleek_likes($cid, $mode = '', $client = '')
{
    $cid = (int) $cid;

    if ($cid <= 0) {
        return array('count' => 0, 'liked' => false);
    }

    $cookie = 'sleek_liked_' . $cid;
    $fingerprint = sleek_like_fingerprint($cid, $client);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('int_value', 'str_value')->from('table.fields')
                ->where('cid = ?', $cid)
                ->where('name = ?', 'sleek_likes')
        );

        $count = $row ? (int) $row['int_value'] : 0;
        $ledger = array();

        if ($row && !empty($row['str_value'])) {
            $decoded = json_decode((string) $row['str_value'], true);

            if (is_array($decoded)) {
                $ledger = $decoded;
            }
        }

        $knownLike = isset($ledger[$fingerprint]);
        $liked = isset($_COOKIE[$cookie]) || $knownLike;

        $makeLike = $mode === true || $mode === 'like';
        $makeUnlike = $mode === 'unlike';

        if ($makeLike && !$liked) {
            $count++;
            $ledger[$fingerprint] = time();
            $liked = true;
        } elseif ($makeLike && $liked && !$knownLike) {
            $ledger[$fingerprint] = time();
        } elseif ($makeUnlike && $liked) {
            $count = max(0, $count - 1);
            unset($ledger[$fingerprint]);
            $liked = false;
        }

        if ($makeLike || $makeUnlike) {
            if (count($ledger) > 500) {
                $ledger = array_slice($ledger, -500, null, true);
            }

            $encoded = json_encode($ledger, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($row) {
                $db->query(
                    $db->update('table.fields')
                        ->rows(array(
                            'int_value' => $count,
                            'str_value' => $encoded,
                        ))
                        ->where('cid = ?', $cid)
                        ->where('name = ?', 'sleek_likes')
                );
            } else {
                $db->query(
                    $db->insert('table.fields')->rows(array(
                        'cid' => $cid,
                        'name' => 'sleek_likes',
                        'type' => 'int',
                        'str_value' => $encoded,
                        'int_value' => $count,
                        'float_value' => 0,
                    ))
                );
            }

            if (!headers_sent()) {
                if ($liked) {
                    setcookie($cookie, '1', array(
                        'expires' => time() + 31536000,
                        'path' => '/',
                        'secure' => $secure,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ));
                } else {
                    setcookie($cookie, '', array(
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => $secure,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ));
                    unset($_COOKIE[$cookie]);
                }
            }
        }

        return array('count' => $count, 'liked' => $liked);
    } catch (Throwable $e) {
        return array('count' => 0, 'liked' => isset($_COOKIE[$cookie]));
    }
}

function sleek_like_button($cid, $class = '', $label = true)
{
    $cid = (int) $cid;
    $data = sleek_likes($cid);
    $classes = 'shuo-act like-btn';

    if ($class !== '') {
        $classes .= ' ' . $class;
    }

    if ($data['liked']) {
        $classes .= ' is-liked';
    }

    $url = rtrim((string) sleek_options()->siteUrl, '/') . '/?sleek_like=' . $cid;
    ?>
    <button type="button"
            class="<?php echo htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'); ?>"
            data-like-cid="<?php echo $cid; ?>"
            data-like-url="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
            aria-pressed="<?php echo $data['liked'] ? 'true' : 'false'; ?>"
            title="<?php echo $data['liked'] ? '取消点赞' : '点赞'; ?>">
        <?php sleek_icon('thumb'); ?>
        <span class="like-count"><?php echo (int) $data['count']; ?></span>
        <?php if ($label): ?><span class="shuo-act-label"><?php echo $data['liked'] ? '已赞' : '点赞'; ?></span><?php endif; ?>
    </button>
    <?php
}

function sleek_like_endpoint()
{
    if (!isset($_GET['sleek_like'])) {
        return;
    }

    $cid = (int) $_GET['sleek_like'];

    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    if ($cid <= 0) {
        http_response_code(400);
        echo json_encode(array('ok' => false, 'message' => 'Invalid post.'));
        exit;
    }

    try {
        $db = sleek_db();
        $exists = $db->fetchRow(
            $db->select('cid')->from('table.contents')
                ->where('cid = ?', $cid)
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->limit(1)
        );

        if (!$exists) {
            http_response_code(404);
            echo json_encode(array('ok' => false, 'message' => 'Post not found.'));
            exit;
        }

        $action = isset($_GET['action']) && $_GET['action'] === 'unlike' ? 'unlike' : 'like';
        $data = sleek_likes($cid, $action);
        echo json_encode(array(
            'ok' => true,
            'count' => (int) $data['count'],
            'liked' => (bool) $data['liked'],
        ));
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(array('ok' => false, 'message' => 'Like failed.'));
    }

    exit;
}

sleek_like_endpoint();

/**
 * 评论头像加载失败时的首字头像
 */
function sleek_initial_avatar($name)
{
    $name = trim((string) $name);
    $initial = $name !== ''
        ? (function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1))
        : '?';
    $hue = abs((int) crc32($name)) % 360;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60">'
        . '<rect width="60" height="60" rx="30" fill="hsl(' . $hue . ',52%,58%)"/>'
        . '<text x="30" y="31" text-anchor="middle" dominant-baseline="central" '
        . 'font-family="Arial,Helvetica,sans-serif" font-size="26" font-weight="600" fill="#fff">'
        . htmlspecialchars($initial, ENT_QUOTES | ENT_XML1, 'UTF-8')
        . '</text></svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

/**
 * 已审核评论总数
 */
function sleek_comment_total($cid)
{
    $cid = (int) $cid;

    if ($cid <= 0) {
        return 0;
    }

    try {
        $db = sleek_db();
        $row = $db->fetchRow(
            $db->select('COUNT(*) AS total')->from('table.comments')
                ->where('cid = ?', $cid)
                ->where('status = ?', 'approved')
        );

        return $row ? (int) $row['total'] : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * 首页说说下方直接展开的评论楼层
 */
function sleek_home_comments($post, $likes)
{
    $cid = (int) $post->cid;
    $ownerId = (int) $post->authorId;
    $canComment = $post->allow('comment');
    $commentUrl = (string) $post->commentUrl;
    $permalink = (string) $post->permalink;
    $options = Helper::options();
    $requireMail = (int) $options->commentsRequireMail;
    $requireUrl = (int) $options->commentsRequireUrl;
    $rows = array();

    try {
        $db = sleek_db();
        $rows = $db->fetchAll(
            $db->select('coid', 'author', 'authorId', 'ownerId', 'mail', 'url', 'text', 'created', 'parent')
                ->from('table.comments')
                ->where('cid = ?', $cid)
                ->where('status = ?', 'approved')
                ->order('created', 'ASC')
                ->order('coid', 'ASC')
        );
    } catch (Throwable $e) {
        $rows = array();
    }

    $count = count($rows);

    $children = array();
    $authors = array();

    foreach ($rows as $row) {
        $parent = (int) $row['parent'];
        $coid = (int) $row['coid'];
        $children[$parent][] = $row;
        $authors[$coid] = (string) $row['author'];
    }

    $renderComment = function ($row, $depth) use (&$renderComment, $children, $authors, $ownerId) {
        $coid = (int) $row['coid'];
        $parent = (int) $row['parent'];
        $author = (string) $row['author'];
        $text = sleek_comment_excerpt($row['text'], 300);
        $avatar = sleek_comment_avatar_url($row['authorId'], $row['ownerId'], $row['mail'], 30);
        $fallbackAvatar = sleek_initial_avatar($author);
        $replyName = $parent > 0 && isset($authors[$parent]) ? $authors[$parent] : '';
        $classes = 'qzone-comment' . ($depth > 0 ? ' is-reply' : '');
        ?>
        <div class="<?php echo $classes; ?>" id="home-comment-<?php echo $coid; ?>" data-coid="<?php echo $coid; ?>">
            <img class="qzone-avatar"
                 src="<?php echo htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="" width="<?php echo $depth > 0 ? 26 : 30; ?>" height="<?php echo $depth > 0 ? 26 : 30; ?>"
                 loading="lazy" referrerpolicy="no-referrer"
                 style="background-image:url('<?php echo htmlspecialchars($fallbackAvatar, ENT_QUOTES, 'UTF-8'); ?>');background-position:center;background-size:cover;background-repeat:no-repeat;"
                 onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($fallbackAvatar, ENT_QUOTES, 'UTF-8'); ?>'">
            <div class="qzone-body">
                <div class="qzone-head">
                    <span class="qzone-name"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if (!empty($row['authorId']) && $row['authorId'] == $ownerId): ?>
                        <span class="qzone-owner"><?php _e('博主'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="qzone-text">
                    <?php if ($replyName !== ''): ?>
                        <span class="qzone-reply-to"><?php _e('回复'); ?> <b><?php echo htmlspecialchars($replyName, ENT_QUOTES, 'UTF-8'); ?></b>：</span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($text !== '' ? $text : '（无文字内容）', ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="qzone-foot">
                    <time class="qzone-time" datetime="<?php echo date('c', (int) $row['created']); ?>"><?php echo date('n月j日 H:i', (int) $row['created']); ?></time>
                    <button type="button" class="qzone-reply"
                        data-reply-to="<?php echo $coid; ?>"
                        data-reply-name="<?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>"><?php _e('回复'); ?></button>
                </div>
            </div>
        </div>
        <?php
        if (isset($children[$coid])) {
            foreach ($children[$coid] as $child) {
                $renderComment($child, min($depth + 1, 2));
            }
        }
    };

    $topLevel = isset($children[0]) ? $children[0] : array();

    foreach ($rows as $row) {
        $parent = (int) $row['parent'];
        if ($parent > 0 && !isset($authors[$parent])) {
            $topLevel[] = $row;
        }
    }
    ?>
    <div class="home-comments qzone-comments<?php echo ($likes['count'] > 0 || !empty($topLevel)) ? '' : ' is-empty'; ?>"
         id="home-comments-<?php echo $cid; ?>"
         data-cid="<?php echo $cid; ?>"
         data-permalink="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>"
         data-comment-url="<?php echo htmlspecialchars($commentUrl, ENT_QUOTES, 'UTF-8'); ?>"
         data-require-mail="<?php echo $requireMail; ?>"
         data-require-url="<?php echo $requireUrl; ?>"
         data-default-avatar="<?php echo htmlspecialchars(sleek_theme_url('assets/img/avatar.svg'), ENT_QUOTES, 'UTF-8'); ?>"
         data-loaded="1"
         data-count="<?php echo $count; ?>"
         data-parent="0"
         aria-label="<?php _e('评论'); ?>">
        <div class="home-comments-inner">
            <div class="home-social<?php echo ($likes['count'] > 0 || !empty($topLevel)) ? ' has-activity' : ''; ?>">
            <div class="qzone-likes" data-like-summary-cid="<?php echo $cid; ?>"<?php echo $likes['count'] > 0 ? '' : ' hidden'; ?>>
                <?php sleek_icon('thumb'); ?>
                <span><?php _e('共'); ?> <b class="like-summary-count"><?php echo (int) $likes['count']; ?></b> <?php _e('人觉得很赞'); ?></span>
            </div>

            <?php if ($topLevel): ?>
                <div class="qzone-comment-list">
                    <?php foreach ($topLevel as $row): ?>
                        <?php $renderComment($row, 0); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>

            <?php if ($canComment): ?>
                <form class="home-comment-form qzone-form"
                      method="post"
                      action="<?php echo htmlspecialchars($commentUrl, ENT_QUOTES, 'UTF-8'); ?>"
                      data-cid="<?php echo $cid; ?>">
                    <div class="reply-hint" hidden>
                        <span class="reply-hint-text"></span>
                        <button type="button" class="reply-cancel"><?php _e('取消回复'); ?></button>
                    </div>
                    <div class="qzone-compose">
                        <input type="text" name="text" placeholder="<?php echo htmlspecialchars($count > 0 ? '我也说一句' : '还没有评论，来坐个沙发。', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                        <button type="button" class="qzone-emoji" data-emoji-toggle aria-label="<?php _e('表情'); ?>" aria-expanded="false"><?php sleek_icon('smile'); ?></button>
                        <button type="submit" class="qzone-send submit-btn" aria-label="<?php _e('发送评论'); ?>">
                            <?php sleek_icon('send'); ?>
                        </button>
                    </div>
                    <div class="qzone-fields">
                        <label class="qzone-field">
                            <span><?php _e('称呼'); ?></span>
                            <input type="text" name="author" placeholder="<?php _e('怎么称呼你'); ?>" autocomplete="nickname">
                        </label>
                        <label class="qzone-field">
                            <span><?php _e('邮箱'); ?><?php echo $requireMail ? ' *' : ''; ?></span>
                            <input type="email" name="mail" placeholder="name@example.com" autocomplete="email">
                        </label>
                        <label class="qzone-field">
                            <span><?php _e('网站'); ?><?php echo $requireUrl ? ' *' : ''; ?></span>
                            <input type="url" name="url" placeholder="https://" autocomplete="url">
                        </label>
                    </div>
                    <p class="comment-notice" hidden></p>
                </form>
            <?php else: ?>
                <p class="qzone-closed"><?php _e('评论已关闭。'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/* -------------------------------------------------------------------------
 * 列表卡片
 * ---------------------------------------------------------------------- */

function sleek_post_card($post)
{
    $rawContent = (string) $post->content;
    $musicHTML = '';
    $excerptSource = $rawContent;

    if (preg_match('/<!--\s*SLEEK_MUSIC_START\s*-->(.*?)<!--\s*SLEEK_MUSIC_END\s*-->/is', $rawContent, $musicMatch)) {
        $musicHTML = $musicMatch[1];
        $excerptSource = preg_replace('/<!--\s*SLEEK_MUSIC_START\s*-->.*?<!--\s*SLEEK_MUSIC_END\s*-->/is', '', $rawContent);
    } elseif (class_exists('\TypechoPlugin\SleekMusic\Plugin')) {
        $musicHTML = \TypechoPlugin\SleekMusic\Plugin::renderFromContent($rawContent);
        $excerptSource = \TypechoPlugin\SleekMusic\Plugin::stripShortcodes($rawContent);
    } else {
        $excerptSource = preg_replace('/\[music\b[^\]]*\]/i', '', $rawContent);
    }

    $excerptSource = preg_replace('/\[music\b[^\]]*\]/i', '', $excerptSource);

    if ($musicHTML === '' && class_exists('\TypechoPlugin\SleekMusic\Plugin')) {
        $sleekNeteaseId = \TypechoPlugin\SleekMusic\Plugin::neteaseId($excerptSource);

        if ($sleekNeteaseId !== '') {
            $musicHTML = \TypechoPlugin\SleekMusic\Plugin::neteasePlayer($sleekNeteaseId);
        }
    }

    $excerptLength = (int) sleek_option('excerptLength', 220);
    $excerptTruncated = false;
    $excerpt = sleek_excerpt($post, max(80, $excerptLength), $excerptSource, $excerptTruncated);

    $images = sleek_images($excerptSource, 50);
    $cover = sleek_cover($post, $excerptSource);

    if ($cover !== '' && !in_array($cover, $images, true)) {
        array_unshift($images, $cover);
    }

    $images = array_slice(array_values(array_unique($images)), 0, 50);
    $imageCount = count($images);
    $sleekVideos = sleek_videos($excerptSource, 1);
    $sleekVideo = !empty($sleekVideos) ? $sleekVideos[0] : null;
    $sleekBiliHTML = '';

    if (preg_match('/<span\b[^>]*class="[^"]*\bbili-player\b[^"]*"[^>]*>.*?<iframe\b.*?<\/iframe>.*?<\/span>\s*<\/span>/is', $rawContent, $sleekBiliMatch)) {
        $sleekBiliHTML = $sleekBiliMatch[0];
    }

    $permalink = $post->permalink;
    $likes = sleek_likes($post->cid);
    $comments = sleek_comment_total($post->cid);
    $canComment = $post->allow('comment');
    $commentUrl = $post->commentUrl;
    $sleekOptions = Helper::options();
    $sleekIdentity = sleek_identity();
    $authorName = $sleekIdentity['name'];
    $authorAvatar = $sleekIdentity['avatar'];
    $requireMail = (int) $sleekOptions->commentsRequireMail;
    $requireUrl = (int) $sleekOptions->commentsRequireUrl;
    $siteTitle = (string) $sleekOptions->title;
    $siteUrl = (string) $sleekOptions->siteUrl;
    $iso = date('c', (int) $post->created);
    $fullTime = $post->date->format('Y-m-d H:i');
    $dateText = $post->date->format('Y年n月j日');
    $category = !empty($post->categories) ? $post->categories[0] : null;
    $categoryName = $category ? (string) $category['name'] : $siteTitle;
    $categoryUrl = $category ? (string) $category['permalink'] : '';
    $postTitle = trim((string) $post->title);
    ?>
    <article class="feed-item shuo-item" id="post-<?php echo (int) $post->cid; ?>">
<?php if ($excerptTruncated): ?>
<span class="shuo-long-badge<?php echo sleek_is_sticky($post->cid) ? ' is-offset' : ''; ?>" title="<?php _e('长图文'); ?>">
<span><?php _e('图文'); ?></span>
</span>
<?php endif; ?>
<?php if (sleek_is_sticky($post->cid)): ?>
<span class="shuo-sticky-badge" role="img" aria-label="<?php _e('置顶'); ?>" title="<?php _e('置顶'); ?>">
            <svg class="sticky-stamp" viewBox="0 0 76 76" aria-hidden="true" focusable="false">
                <defs>
                    <path id="carefree-stamp-top" d="M 13 40 A 25 25 0 0 1 63 40" fill="none" />
                </defs>
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="38" cy="38" r="34" stroke-width="2.4" stroke-dasharray="1.8 2.2" />
                    <circle cx="38" cy="38" r="31.5" stroke-width="1.1" />
                    <circle cx="38" cy="38" r="27.5" stroke-width="0.7" opacity="0.65" />
                    <path d="M 14 31 H 62" stroke-width="1.4" />
                    <path d="M 14 47 H 62" stroke-width="1.4" />
                </g>
                <text fill="currentColor" class="sticky-stamp-arc">
                    <textPath href="#carefree-stamp-top" startOffset="50%" text-anchor="middle">PINNED POST</textPath>
                </text>
                <text x="38" y="45" text-anchor="middle" fill="currentColor" class="sticky-stamp-title">置顶</text>
            </svg>
        </span>
<?php endif; ?>
        <a class="shuo-avatar" href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'); ?>">
            <img src="<?php echo htmlspecialchars($authorAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'); ?>" width="48" height="48" loading="lazy" onerror="this.remove()">
        </a>
        <div class="shuo-main">
            <div class="shuo-head">
                <a class="shuo-author" href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="shuo-author-name"><?php echo htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <span class="shuo-author-badge"><?php _e('博主'); ?></span>

            </div>

            <?php if ($excerptTruncated): ?>
                <?php $linkThumb = $cover !== '' ? $cover : sleek_theme_url('assets/img/article-default.png'); ?>
                <a class="shuo-link-card" href="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php _e('继续阅读'); ?>：<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="shuo-link-thumb<?php echo $cover === '' ? ' is-default' : ''; ?>" aria-hidden="true">
                    <img src="<?php echo htmlspecialchars($linkThumb, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(sleek_theme_url('assets/img/article-default.png'), ENT_QUOTES, 'UTF-8'); ?>'">
                </span>
                    <span class="shuo-link-main">
                        <span class="shuo-link-excerpt"><?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="shuo-link-more"><?php _e('继续阅读'); ?><?php sleek_icon('arrow-right'); ?></span>
                    </span>
                </a>
            <?php endif; ?>

            <?php if (!$excerptTruncated): ?>
                <?php $sleekRichHtml = sleek_home_rich_html($post, $excerptSource); ?>
                <?php if ($sleekRichHtml !== ''): ?>
                    <div class="shuo-text is-rich"><?php echo $sleekRichHtml; ?></div>
                <?php elseif ($excerpt !== ''): ?>
                    <div class="shuo-text"><?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($musicHTML !== ''): ?>
                <div class="shuo-music"><?php echo $musicHTML; ?></div>
            <?php endif; ?>

            <?php if ($sleekBiliHTML !== ''): ?>
<div class="shuo-bili"><?php echo $sleekBiliHTML; ?></div>
<?php endif; ?>

<?php if ($sleekVideo !== null): ?>
                <div class="shuo-video">
                    <video controls playsinline preload="metadata"<?php echo $sleekVideo['poster'] !== '' ? ' poster="' . htmlspecialchars($sleekVideo['poster'], ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
                        <source src="<?php echo htmlspecialchars($sleekVideo['src'], ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                        <a href="<?php echo htmlspecialchars($sleekVideo['src'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php _e('打开视频'); ?></a>
                    </video>
                </div>
            <?php endif; ?>

            <?php if (!$excerptTruncated && $imageCount > 0): ?>
                <?php $photoVisible = $imageCount > 1 ? 3 : 1; $photoCarousel = $imageCount > 3; ?>
                <?php if ($photoCarousel): ?><div class="shuo-gallery"><?php endif; ?>
                <div class="shuo-photos count-<?php echo (int) $photoVisible; ?><?php echo $photoCarousel ? ' is-carousel' : ''; ?><?php echo $musicHTML !== '' ? ' has-player' : ''; ?>"<?php if ($photoCarousel): ?> data-photo-carousel tabindex="0" role="group" aria-label="<?php _e('图片，可左右滑动查看'); ?>"<?php endif; ?>>
                    <?php foreach ($images as $index => $src): ?>
                        <button type="button" class="shuo-photo gallery-item"
                                data-src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"
                                data-alt="<?php echo htmlspecialchars($post->title, ENT_QUOTES, 'UTF-8'); ?>">
                            <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($post->title, ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy" decoding="async">
                            <?php if ($index === 0): ?>
                                <span class="shuo-zoom"><?php _e('查看大图'); ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php if ($photoCarousel): ?>
                    <button type="button" class="shuo-gallery-arrow prev" data-gallery-prev aria-label="<?php _e('上一张图片'); ?>" title="<?php _e('上一张图片'); ?>" disabled><?php sleek_icon('chevron-left'); ?></button>
                    <button type="button" class="shuo-gallery-arrow next" data-gallery-next aria-label="<?php _e('下一张图片'); ?>" title="<?php _e('下一张图片'); ?>"><?php sleek_icon('chevron-right'); ?></button>
                    <span class="shuo-gallery-more" title="<?php printf(_t('共 %d 张图片'), $imageCount); ?>"><?php echo (int) $imageCount; ?> 张</span>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="shuo-meta">
                <?php if ($postTitle !== ''): ?>
                    <a class="shuo-title" href="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
                <time class="shuo-date" datetime="<?php echo htmlspecialchars($iso, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($fullTime, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8'); ?></time>
                <span class="shuo-source"><?php _e('来自'); ?>
                    <?php if ($categoryUrl !== ''): ?>
                        <a href="<?php echo htmlspecialchars($categoryUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </span>
                <div class="shuo-bar">
                    <a class="shuo-act stat-comments"
                       href="#home-comments-<?php echo (int) $post->cid; ?>"
                       title="<?php _e('评论'); ?>"
                       <?php if ($canComment): ?>
                       data-comment-toggle
                       data-cid="<?php echo (int) $post->cid; ?>"
                       data-permalink="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>"
                       data-comment-url="<?php echo htmlspecialchars($commentUrl, ENT_QUOTES, 'UTF-8'); ?>"
                       data-require-mail="<?php echo $requireMail; ?>"
                       data-require-url="<?php echo $requireUrl; ?>"
                       aria-expanded="true"
                       aria-controls="home-comments-<?php echo (int) $post->cid; ?>"
                       <?php endif; ?>>
                        <?php sleek_icon('comment'); ?><span class="stat-count"><?php echo $comments; ?></span><span class="shuo-act-label"><?php _e('评论'); ?></span>
                    </a>
                    <?php sleek_like_button($post->cid); ?>
                </div>
            </div>

            <?php sleek_home_comments($post, $likes); ?>
        </div>
    </article>
    <?php
}

/* -------------------------------------------------------------------------
 * 归档数据
 * ---------------------------------------------------------------------- */

function sleek_all_posts()
{
    $db = sleek_db();

    return $db->fetchAll(
        $db->select('cid', 'title', 'slug', 'created')->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->order('created', 'DESC')
    );
}

/**
 * 置顶文章 CID 列表
 */
function sleek_sticky_ids()
{
    static $ids = null;

    if ($ids !== null) {
        return $ids;
    }

    $ids = array();

    try {
        $db = sleek_db();
        $rows = $db->fetchAll(
            $db->select('cid')->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->where('order > ?', 0)
        );

        foreach ($rows as $row) {
            $ids[] = (int) $row['cid'];
        }
    } catch (\Throwable $e) {
        $ids = array();
    }

    return $ids;
}

function sleek_is_sticky($cid)
{
    return in_array((int) $cid, sleek_sticky_ids(), true);
}

/**
 * Markdown 图片地址
 */
function sleek_markdown_images($text)
{
    $text = (string) $text;
    $images = array();
    $defs = array();

    if (preg_match_all('/^[ \t]*\[([^\]]+)\]:[ \t]*<?([^\s>]+)>?/mi', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $key = strtolower(trim((string) $match[1]));
            $url = trim((string) $match[2]);

            if ($key !== '' && $url !== '') {
                $defs[$key] = $url;
            }
        }
    }

    if (preg_match_all('/!\[([^\]]*)\]\(\s*<?([^\s)>]+)>?(?:\s+[\'"][^\'"]*[\'"])?\s*\)/i', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $url = trim((string) $match[2]);

            if ($url !== '') {
                $images[] = $url;
            }
        }
    }

    if (preg_match_all('/!\[([^\]]*)\]\[([^\]]*)\]/i', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $key = strtolower(trim((string) $match[2]));

            if ($key === '') {
                $key = strtolower(trim((string) $match[1]));
            }

            if ($key !== '' && isset($defs[$key])) {
                $images[] = $defs[$key];
            }
        }
    }

    return $images;
}

/**
 * 所有文章中的图片（相册页）
 */
function sleek_album_images()
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $cache = array();
    $db = sleek_db();
    $rows = $db->fetchAll(
        $db->select('cid', 'title', 'slug', 'created', 'text')->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->order('created', 'DESC')
    );

    $seen = array();

    foreach ($rows as $row) {
        $text = (string) $row['text'];
        $images = array_merge(
            sleek_images($text, 50),
            sleek_markdown_images($text)
        );

        if (empty($images)) {
            continue;
        }

        $postUrl = sleek_post_url($row['cid'], $row['slug']);
        $postTitle = trim((string) $row['title']);

        foreach ($images as $image) {
            $image = trim((string) $image);

            if ($image === '' || stripos($image, 'data:') === 0) {
                continue;
            }

            $image = sleek_abs_url($image);

            if (!preg_match('#^(?:https?:)?//#i', $image)) {
                continue;
            }

            $key = md5($image);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $cache[] = array(
                'src' => $image,
                'title' => $postTitle,
                'url' => $postUrl,
                'created' => (int) $row['created'],
            );
        }
    }

    return $cache;
}
