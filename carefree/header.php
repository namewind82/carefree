<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$sleekMode = sleek_color_mode();
$sleekThemeColor = sleek_theme_color();
$sleekPalette = sleek_theme_palette();
$sleekAccent = $sleekPalette['accent'];
$sleekNav = sleek_nav_items($this);
$sleekCss = trim((string) sleek_option('customCss', ''));
$sleekHeaderStyle = trim((string) sleek_option('headerStyle', 'classic'));
if (!in_array($sleekHeaderStyle, array('classic', 'wechat'), true)) {
    $sleekHeaderStyle = 'classic';
}
$sleekNavCurrentLabel = _t('导航');
foreach ($sleekNav as $sleekNavItem) {
    if (!empty($sleekNavItem['active'])) {
        $sleekNavCurrentLabel = $sleekNavItem['label'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN" class="no-js" data-color="<?php echo htmlspecialchars($sleekThemeColor, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="<?php echo htmlspecialchars($sleekPalette['bg'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="renderer" content="webkit">
    <title><?php $this->archiveTitle(array(
            'category' => _t('分类 %s 下的文章'),
            'search' => _t('包含关键字 %s 的文章'),
            'tag' => _t('标签 %s 下的文章'),
            'author' => _t('%s 发布的文章'),
        ), '', ' · '); ?><?php $this->options->title(); ?></title>
    <script>
        (function () {
            var mode;
            try {
                mode = localStorage.getItem('sleek-mode') || '<?php echo $sleekMode; ?>';
            } catch (e) {
                mode = '<?php echo $sleekMode; ?>';
            }
            var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = mode === 'dark' || (mode === 'auto' && systemDark);
            var root = document.documentElement;
            root.className = root.className.replace('no-js', 'js');
            root.setAttribute('data-mode', mode);
            if (isDark) {
                root.classList.add('dark');
                root.style.colorScheme = 'dark';
            }
        })();
    </script>
    <?php if ($this->is('index')): ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo sleek_theme_url('assets/css/style.css'); ?>?v=3.3.15">
    <style>
        :root {
            --accent: <?php echo $sleekPalette['accent']; ?>;
            --accent-strong: <?php echo $sleekPalette['strong']; ?>;
            --accent-soft: rgba(<?php echo $sleekPalette['rgb']; ?>, 0.14);
            --accent-rgb: <?php echo $sleekPalette['rgb']; ?>;
            --accent-soft-solid: <?php echo $sleekPalette['soft']; ?>;
        }
    </style>
    <?php if ($sleekCss !== ''): ?>
        <style><?php echo $sleekCss; ?></style>
    <?php endif; ?>
    <?php $this->header(); ?>
</head>
<body class="<?php
    if ($this->is('index')) {
        echo 'is-home';
    } elseif ($this->is('post')) {
        echo 'is-post';
    } elseif ($this->is('page')) {
        echo 'is-page';
    } else {
        echo 'is-archive';
    }
?>">

<svg class="svg-sprite" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
    <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 9.8 12 3l9 6.8V20a1 1 0 0 1-1 1h-5.5v-6.5h-5V21H4a1 1 0 0 1-1-1Z"/></symbol>
    <symbol id="i-archive" viewBox="0 0 24 24"><path d="M3 8h18M5 8v12h14V8M9 12h6M4 4h16v4H4z"/></symbol>
    <symbol id="i-chat" viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.6L3.5 21l1.9-5.1A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8.5 10.5h7M8.5 14h4.5"/></symbol>
    <symbol id="i-photo" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.4" cy="8.4" r="1.6"/><path d="m21 15.2-4.7-4.7L5 21"/></symbol>
    <symbol id="i-link" viewBox="0 0 24 24"><path d="M10.2 13.8a5 5 0 0 0 7.1 0l2.4-2.4a5 5 0 0 0-7.1-7.1l-1.4 1.4"/><path d="M13.8 10.2a5 5 0 0 0-7.1 0l-2.4 2.4a5 5 0 0 0 7.1 7.1l1.4-1.4"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><path d="M20 21v-1.8a4.2 4.2 0 0 0-4.2-4.2H8.2A4.2 4.2 0 0 0 4 19.2V21"/><circle cx="12" cy="7.5" r="4"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="7.3"/><path d="m21 21-4.4-4.4"/></symbol>
    <symbol id="i-moon" viewBox="0 0 24 24"><path d="M21 12.9A8.6 8.6 0 1 1 11.1 3a6.7 6.7 0 0 0 9.9 9.9Z"/></symbol>
    <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.2M12 19.8V22M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M2 12h2.2M19.8 12H22M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"/></symbol>
    <symbol id="i-eye" viewBox="0 0 24 24"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></symbol>
    <symbol id="i-comment" viewBox="0 0 24 24"><path d="M20 15.5a2.5 2.5 0 0 1-2.5 2.5H8l-4 3.5V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5Z"/></symbol>
    <symbol id="i-thumb" viewBox="0 0 24 24"><path d="M7 10v11H4.5A1.5 1.5 0 0 1 3 19.5v-8A1.5 1.5 0 0 1 4.5 10H7Z"/><path d="M7 10.5 11.6 2a2.6 2.6 0 0 1 2.5 3.3L13.3 9h4.4a2.8 2.8 0 0 1 2.7 3.5l-1.6 6A2.8 2.8 0 0 1 16.1 21H7"/></symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24"><path d="M4.5 12h14M12.5 5.5 19 12l-6.5 6.5"/></symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24"><path d="M19.5 12h-14M11.5 5.5 5 12l6.5 6.5"/></symbol>
    <symbol id="i-arrow-up" viewBox="0 0 24 24"><path d="M12 19.5v-14M5.5 11.5 12 5l6.5 6.5"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.7"/><path d="M12 7.3V12l3.2 2"/></symbol>
    <symbol id="i-folder" viewBox="0 0 24 24"><path d="M21.5 18.5a2 2 0 0 1-2 2h-15a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2h4.6l2 3h8.4a2 2 0 0 1 2 2Z"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.2"/></symbol>
        <symbol id="i-douban" viewBox="0 0 1024 1024" stroke="none"><path d="M392.709169 869.930856a13.927912 13.927912 0 0 1-11.830995-6.549795l-106.077592-169.647559c-4.081207-6.526245-2.097941-15.125858 4.428304-19.207065 6.526245-4.082231 15.124834-2.098965 19.207065 4.428304l106.077591 169.64756c4.081207 6.526245 2.097941 15.125858-4.428304 19.207064a13.875694 13.875694 0 0 1-7.376069 2.121491z" fill="#ffffff" stroke="none"/><path d="M122.851905 870.768394c-5.424544 0-10.581855-3.185307-12.835427-8.494153-3.008175-7.085286 0.296927-15.268178 7.381189-18.277377a34.885821 34.885821 0 0 1 13.703682-2.780872h763.416065c7.69757 0 13.937127 6.239557 13.937127 13.937127s-6.240581 13.937127-13.937127 13.937127H131.101349a7.14672 7.14672 0 0 0-2.809542 0.565185 13.871598 13.871598 0 0 1-5.439902 1.112963z" fill="#ffffff" stroke="none"/><path d="M628.553986 869.929832c-2.523877 0-5.077447-0.684979-7.375046-2.120467-6.527269-4.080183-8.511559-12.679796-4.430352-19.207064L722.794439 678.953717c4.079159-6.527269 12.679796-8.510535 19.207065-4.430352 6.527269 4.080183 8.511559 12.679796 4.430352 19.207065l-106.045851 169.64756a13.928936 13.928936 0 0 1-11.832019 6.551842zM746.091926 647.880845H279.560625c-54.474795 0-98.793671-44.304542-98.793671-98.76193v-169.64756c0-54.457389 44.318876-98.761931 98.793671-98.76193h466.531301c54.458413 0 98.761931 44.304542 98.76193 98.76193v169.64756c0 54.457389-44.304542 98.761931-98.76193 98.76193zM279.560625 308.584703c-39.104228 0-70.917369 31.79983-70.917369 70.885628v169.64756c0 39.086822 31.813141 70.886653 70.917369 70.886652h466.531301c39.086822 0 70.886653-31.800854 70.886652-70.886652v-169.64756c0-39.086822-31.800854-70.885629-70.886652-70.885628H279.560625zM852.106036 181.348521H173.51375c-7.69757 0-13.938151-6.240581-13.93815-13.938151s6.240581-13.938151 13.93815-13.938151h678.592286c7.698594 0 13.937127 6.240581 13.937127 13.938151s-6.238533 13.938151-13.937127 13.938151z" fill="#ffffff" stroke="none"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24"><path d="m18 6-12 12M6 6l12 12"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="i-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="m4.5 12.5 5 5 10-11"/></symbol>
    <symbol id="i-smile" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 14.2s1.4 2 4 2 4-2 4-2M9 9.5h.01M15 9.5h.01"/></symbol>
    <symbol id="i-send" viewBox="0 0 24 24"><path d="M21.5 2.5 11 13M21.5 2.5l-6.7 19-3.8-8.5-8.5-3.8Z"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24"><rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><path d="m3 6 9 7 9-7"/></symbol>
    <symbol id="i-external" viewBox="0 0 24 24"><path d="M18 13.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5.5"/><path d="M15 3h6v6M10.5 13.5 21 3"/></symbol>
    <symbol id="i-rss" viewBox="0 0 24 24"><path d="M4 11a9 9 0 0 1 9 9M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1.4" fill="currentColor" stroke="none"/></symbol>
    <symbol id="i-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8Z"/><path d="M14 3v5h5"/></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24"><rect x="4.5" y="10" width="15" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></symbol>
    <symbol id="i-chevron-left" viewBox="0 0 24 24"><path d="M15 5 8 12l7 7"/></symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
</svg>

<div class="bg-decor" aria-hidden="true">
    <span class="blob blob-a"></span>
    <span class="blob blob-b"></span>
</div>

<aside class="nav-drawer" id="nav-drawer" aria-label="<?php _e('移动端导航'); ?>" aria-hidden="true">
    <a class="drawer-brand" href="<?php $this->options->siteUrl(); ?>" title="<?php $this->options->title(); ?>">
        <img src="<?php echo htmlspecialchars(sleek_avatar_url(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php $this->options->title(); ?>" onerror="this.remove()">
    </a>
    <nav class="drawer-nav">
        <?php foreach ($sleekNav as $item): ?>
            <a class="drawer-link<?php echo $item['active'] ? ' current' : ''; ?>"
               href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
               title="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php sleek_icon($item['icon']); ?>
                <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="drawer-foot">
        <button type="button" class="drawer-btn" id="drawer-theme" aria-label="<?php _e('切换配色'); ?>">
            <?php sleek_icon('moon', 'icon icon-moon'); ?>
            <?php sleek_icon('sun', 'icon icon-sun'); ?>
        </button>
        <button type="button" class="drawer-btn" id="drawer-search" aria-label="<?php _e('搜索'); ?>">
            <?php sleek_icon('search'); ?>
        </button>
        <?php if ($this->user->hasLogin()): ?>
            <a class="drawer-btn" href="<?php $this->options->adminUrl(); ?>" aria-label="<?php _e('进入后台'); ?>" title="<?php _e('进入后台'); ?>">
                <?php sleek_icon('user'); ?>
            </a>
        <?php else: ?>
            <a class="drawer-btn" href="<?php $this->options->loginUrl(); ?>" aria-label="<?php _e('登录'); ?>" title="<?php _e('登录'); ?>">
                <?php sleek_icon('user'); ?>
            </a>
        <?php endif; ?>
    </div>
</aside>
<button type="button" class="drawer-mask" id="drawer-mask" tabindex="-1" aria-label="<?php _e('关闭菜单'); ?>"></button>

<?php
$heroImage = trim((string) sleek_option('headerImage', sleek_theme_url('assets/img/header-default.jpg')));
$heroName = (string) $this->options->title;
$heroBio = trim((string) sleek_option('headerSignature', ''));

if ($heroBio === '') {
    $heroBio = trim((string) $this->options->description);
}

if ($heroImage !== '') {
    $heroImage = sleek_abs_url($heroImage);
}

$sleekStatus = sleek_status();
$sleekCanEditStatus = false;

try {
    $sleekCanEditStatus = $this->user->hasLogin() && $this->user->pass('administrator', true);
} catch (\Throwable $e) {
    $sleekCanEditStatus = false;
}

$sleekStatusToken = $sleekCanEditStatus ? sleek_status_token() : '';

$sleekStatusPresets = array(
    array('leaf', '自在'),
    array('smile', '美滋滋'),
    array('bomb', '裂开'),
    array('fish', '求锦鲤'),
    array('sun', '等天晴'),
    array('battery-low', '疲惫'),
    array('hourglass', '发呆'),
    array('zap', '冲'),
    array('cloud-rain', 'emo'),
    array('brain', '胡思乱想'),
    array('sparkles', '元气满满'),
    array('bot', 'bot'),
    array('brick-wall', '搬砖'),
    array('book-open', '沉迷学习'),
    array('briefcase-business', '忙'),
    array('fish', '摸鱼'),
    array('plane', '出差'),
    array('house', '飞奔回家'),
    array('moon', '勿扰模式'),
    array('party-popper', '活动'),
    array('pencil', '自定义')
);
?>
<header class="site-hero" id="site-header" data-scrolled="0" data-header-style="<?php echo htmlspecialchars($sleekHeaderStyle, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="hero-head">
    <div class="hero-cover">
        <?php if ($heroImage !== ''): ?>
            <img class="hero-image" src="<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="eager" fetchpriority="high" onerror="this.onerror=null;this.style.opacity='0'">
        <?php endif; ?>
        <span class="hero-shade" aria-hidden="true"></span>

        <?php if ($this->user->hasLogin()): ?>
            <a class="hero-account" href="<?php $this->options->adminUrl(); ?>" aria-label="<?php _e('进入后台'); ?>" title="<?php _e('进入后台'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="12" cy="7.5" r="3.2" />
                    <path d="M5 20a7 7 0 0 1 14 0" />
                </svg>
            </a>
        <?php else: ?>
            <a class="hero-account" href="<?php $this->options->loginUrl(); ?>" aria-label="<?php _e('登录'); ?>" title="<?php _e('登录'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="12" cy="7.5" r="3.2" />
                    <path d="M5 20a7 7 0 0 1 14 0" />
                </svg>
            </a>
        <?php endif; ?>

    </div>

        <div class="hero-profile">
            <a class="hero-avatar" href="<?php $this->options->siteUrl(); ?>" title="<?php echo htmlspecialchars($heroName, ENT_QUOTES, 'UTF-8'); ?>">
                <img src="<?php echo htmlspecialchars(sleek_avatar_url(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($heroName, ENT_QUOTES, 'UTF-8'); ?>" width="54" height="54" onerror="this.remove()">
            </a>
            <div class="hero-info">
                <div class="hero-name-row">
                    <a class="hero-name" href="<?php $this->options->siteUrl(); ?>"><?php echo htmlspecialchars($heroName, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php if ($sleekStatus['text'] !== ''): ?>
                        <?php if ($sleekCanEditStatus): ?>
                            <button type="button" class="hero-status is-editable" data-status-open aria-haspopup="dialog" title="<?php _e('设置状态'); ?>">
                                <span class="hero-status-icon"><?php echo sleek_status_icon($sleekStatus['icon']); ?></span>
                                <span class="hero-status-text"><?php echo htmlspecialchars($sleekStatus['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="hero-status-caret" aria-hidden="true">⌄</span>
                            </button>
                        <?php else: ?>
                            <span class="hero-status">
                                <span class="hero-status-icon"><?php echo sleek_status_icon($sleekStatus['icon']); ?></span>
                                <span class="hero-status-text"><?php echo htmlspecialchars($sleekStatus['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        <?php endif; ?>
                    <?php elseif ($sleekCanEditStatus): ?>
                        <button type="button" class="hero-status is-empty is-editable" data-status-open aria-haspopup="dialog" title="<?php _e('设置状态'); ?>">
                            <span class="hero-status-plus">+</span>
                            <span>状态</span>
                        </button>
                    <?php endif; ?>
                </div>
                <?php if ($heroBio !== ''): ?>
                    <p class="hero-bio"><?php echo htmlspecialchars($heroBio, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="hero-nav">
        <div class="hero-nav-inner">
            <div class="wechat-nav-dropdown" data-wechat-nav>
                <button type="button" class="wechat-nav-toggle" aria-expanded="false" aria-controls="wechat-nav-panel">
                    <span class="wechat-nav-toggle-icon" aria-hidden="true"><?php sleek_icon('menu'); ?></span>
                    <span class="wechat-nav-toggle-label"><?php echo htmlspecialchars($sleekNavCurrentLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="wechat-nav-chevron" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                    </span>
                </button>
                <div class="wechat-nav-panel" id="wechat-nav-panel" hidden>
                    <?php foreach ($sleekNav as $item): ?>
                        <a class="wechat-nav-item<?php echo $item['active'] ? ' is-current' : ''; ?>"
                           href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                           <?php if ($item['active']): ?>aria-current="page"<?php endif; ?>>
                            <span class="wechat-nav-item-icon" aria-hidden="true"><?php sleek_icon($item['icon']); ?></span>
                            <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <nav class="header-nav" aria-label="<?php _e('主导航'); ?>">
                <?php foreach ($sleekNav as $item): ?>
                    <a class="nav-link<?php echo $item['active'] ? ' current' : ''; ?>"
                       href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                       title="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>"
                       aria-label="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>"
                       <?php if ($item['active']): ?>aria-current="page"<?php endif; ?>>
                        <span class="nav-link-icon" aria-hidden="true"><?php sleek_icon($item['icon']); ?></span>
                        <span class="nav-link-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="hero-actions">
                <button type="button" class="icon-btn" id="search-toggle" aria-label="<?php _e('搜索'); ?>" title="<?php _e('搜索'); ?>">
                    <?php sleek_icon('search'); ?>
                </button>
                <button type="button" class="menu-toggle" id="menu-toggle" aria-label="<?php _e('打开菜单'); ?>" aria-expanded="false">
                    <?php sleek_icon('menu'); ?>
                </button>
            </div>
        </div>
    </div>
</header>

<nav class="compact-nav" id="compact-nav" aria-label="<?php _e('快捷导航'); ?>" aria-hidden="true" inert>
    <div class="compact-nav-glass">
        <?php foreach ($sleekNav as $item): ?>
            <a class="compact-link<?php echo $item['active'] ? ' is-current' : ''; ?>"
               href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
               title="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>"
               tabindex="-1"
               <?php if ($item['active']): ?>aria-current="page"<?php endif; ?>>
                <span class="compact-icon" aria-hidden="true"><?php sleek_icon($item['icon']); ?></span>
                <span class="compact-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<?php if ($sleekCanEditStatus): ?>
<div class="status-modal" id="status-modal" hidden data-status-url="<?php echo htmlspecialchars(sleek_theme_url('status.php'), ENT_QUOTES, 'UTF-8'); ?>" data-status-token="<?php echo htmlspecialchars($sleekStatusToken, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="status-modal-backdrop" data-status-close></div>
    <section class="status-dialog" role="dialog" aria-modal="true" aria-labelledby="status-dialog-title">
        <header class="status-dialog-head">
            <span class="status-dialog-icon" aria-hidden="true"><?php echo sleek_status_icon('sparkles'); ?></span>
            <div class="status-dialog-copy">
                <h2 class="status-dialog-title" id="status-dialog-title"><?php _e('设个状态'); ?></h2>
                <p class="status-dialog-sub"><?php _e('展示在站点名称旁，访客可见'); ?></p>
            </div>
            <button type="button" class="status-dialog-close" data-status-close aria-label="<?php _e('关闭'); ?>">×</button>
        </header>

        <p class="status-section-label"><?php _e('快捷状态'); ?></p>
        <div class="status-grid">
            <?php foreach ($sleekStatusPresets as $preset): ?>
                <?php $selected = $sleekStatus['icon'] === $preset[0] && $sleekStatus['text'] === $preset[1]; ?>
                <button type="button" class="status-option<?php echo $selected ? ' is-active' : ''; ?>" data-status-icon="<?php echo htmlspecialchars($preset[0], ENT_QUOTES, 'UTF-8'); ?>" data-status-text="<?php echo htmlspecialchars($preset[1], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="status-option-icon"><?php echo sleek_status_icon($preset[0]); ?></span>
                    <span class="status-option-text"><?php echo htmlspecialchars($preset[1], ENT_QUOTES, 'UTF-8'); ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <p class="status-section-label"><?php _e('自定义'); ?></p>
        <form class="status-custom" data-status-form>
            <input type="text" name="statusText" maxlength="16" value="<?php echo htmlspecialchars($sleekStatus['text'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php _e('自定义状态'); ?>" autocomplete="off">
            <button type="submit"><?php _e('保存'); ?></button>
        </form>

        <div class="status-dialog-foot">
            <button type="button" class="status-clear" data-status-clear><?php _e('清除状态'); ?></button>
            <span class="status-error" data-status-error></span>
        </div>
    </section>
</div>
<?php endif; ?>

<div class="page-shell" id="page-shell">
    <main class="site-main" id="main">
