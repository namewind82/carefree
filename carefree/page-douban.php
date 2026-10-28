<?php
/**
 * 豆瓣电影与书籍
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$douban = null;
$doubanError = '';

if (class_exists('\TypechoPlugin\SleekDouban\Plugin')) {
    try {
        $douban = \TypechoPlugin\SleekDouban\Plugin::pageData();
    } catch (\Throwable $e) {
        $doubanError = '豆瓣数据加载失败，请稍后再试。';
    }
} else {
    $doubanError = '豆瓣插件尚未启用。';
}

$doubanMovies = $douban ? $douban['movies'] : array();
$doubanBooks = $douban ? $douban['books'] : array();
$doubanUser = $douban ? (string) $douban['userId'] : '';
$doubanUserName = $douban && !empty($douban['userName']) ? (string) $douban['userName'] : $doubanUser;
$doubanHasUser = $doubanUser !== '';
$doubanMovieSource = $doubanHasUser ? '我的电影' : '热门电影';
$doubanBookSource = $doubanHasUser ? '我的书籍' : '新书速递';
$doubanProfile = $doubanUser !== '' ? 'https://www.douban.com/people/' . rawurlencode($doubanUser) . '/' : 'https://www.douban.com/';
$doubanIsAdmin = isset($this->user) && $this->user->pass('administrator', true);
$doubanRefreshUrl = $this->permalink . (strpos($this->permalink, '?') === false ? '?' : '&') . 'douban_refresh=1';
?>

<?php if (class_exists('\TypechoPlugin\SleekDouban\Plugin')): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(\TypechoPlugin\SleekDouban\Plugin::assetUrl('douban.css'), ENT_QUOTES, 'UTF-8'); ?>?v=1.0.7">
<?php endif; ?>

<div class="douban-page">
    <section class="douban-hero">
        <div>
            <span class="douban-kicker">DOUBAN</span>
            <h1>豆瓣</h1>
            <p>电影与书籍，记录看过的故事和读过的世界。</p>
        </div>

        <div class="douban-hero-side">
            <div class="douban-counts">
                <span><strong><?php echo count($doubanBooks); ?></strong> 本书</span>
                <span><strong><?php echo count($doubanMovies); ?></strong> 部电影</span>
            </div>
            <?php if ($doubanIsAdmin): ?>
                <a class="douban-refresh" href="<?php echo htmlspecialchars($doubanRefreshUrl, ENT_QUOTES, 'UTF-8'); ?>" title="<?php _e('刷新豆瓣数据'); ?>">刷新</a>
            <?php endif; ?>
            <a class="douban-profile" href="<?php echo htmlspecialchars($doubanProfile, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                <?php echo $doubanUser !== '' ? '豆瓣主页' : '豆瓣'; ?>
                <?php sleek_icon('arrow-right'); ?>
            </a>
        </div>
    </section>

    <?php if ($doubanHasUser && empty($doubanMovies) && empty($doubanBooks)): ?>
        <div class="douban-bound">
            <div>
                <strong>已绑定豆瓣账号<?php echo $doubanUserName !== '' ? '：' . htmlspecialchars($doubanUserName, ENT_QUOTES, 'UTF-8') : ''; ?></strong>
                <p>该账号目前没有公开的「看过 / 读过」记录，所以这里暂时没有个人电影和书籍。</p>
            </div>
            <a href="<?php echo htmlspecialchars($doubanProfile, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">去豆瓣主页<?php sleek_icon('arrow-right'); ?></a>
        </div>
    <?php endif; ?>

    <?php if ($doubanError !== ''): ?>
        <div class="douban-empty"><?php echo htmlspecialchars($doubanError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php else: ?>
        <div class="douban-tabs" role="tablist">
            <button type="button" class="douban-tab is-active" data-douban-tab="books" role="tab" aria-selected="true">
                书籍 <em><?php echo count($doubanBooks); ?></em>
            </button>
            <button type="button" class="douban-tab" data-douban-tab="movies" role="tab" aria-selected="false">
                电影 <em><?php echo count($doubanMovies); ?></em>
            </button>
        </div>

        <section class="douban-panel" data-douban-panel="movies" hidden>
            <header class="douban-panel-head">
                <h2><?php echo $doubanMovieSource; ?></h2>
                <span><?php echo $doubanUser !== '' && $doubanMovieSource === '我的电影' ? '来自豆瓣收藏' : '豆瓣热门推荐'; ?></span>
            </header>

            <?php if (empty($doubanMovies)): ?>
                <div class="douban-empty"><?php echo $doubanHasUser ? '豆瓣没有公开的观影记录。' : '暂无电影内容。'; ?></div>
            <?php else: ?>
                <div class="douban-grid">
                    <?php foreach ($doubanMovies as $item): ?>
                        <a class="douban-card" href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                            <span class="douban-cover">
                                <?php if (!empty($item['cover'])): ?>
                                    <img src="<?php echo htmlspecialchars(\TypechoPlugin\SleekDouban\Plugin::imageUrl($item['cover']), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" onerror="this.remove()">
                                <?php endif; ?>
                                <?php if (!empty($item['rating'])): ?>
                                    <em class="douban-rate"><?php echo htmlspecialchars($item['rating'], ENT_QUOTES, 'UTF-8'); ?></em>
                                <?php endif; ?>
                            </span>
                            <span class="douban-card-body">
                                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="douban-panel" data-douban-panel="books">
            <header class="douban-panel-head">
                <h2><?php echo $doubanBookSource; ?></h2>
                <span><?php echo $doubanUser !== '' && $doubanBookSource === '我的书籍' ? '来自豆瓣收藏' : '豆瓣新书速递'; ?></span>
            </header>

            <?php if (empty($doubanBooks)): ?>
                <div class="douban-empty"><?php echo $doubanHasUser ? '豆瓣没有公开的读书记录。' : '暂无书籍内容。'; ?></div>
            <?php else: ?>
                <div class="douban-grid">
                    <?php foreach ($doubanBooks as $item): ?>
                        <a class="douban-card" href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                            <span class="douban-cover">
                                <?php if (!empty($item['cover'])): ?>
                                    <img src="<?php echo htmlspecialchars(\TypechoPlugin\SleekDouban\Plugin::imageUrl($item['cover']), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" onerror="this.remove()">
                                <?php endif; ?>
                                <?php if (!empty($item['rating'])): ?>
                                    <em class="douban-rate"><?php echo htmlspecialchars($item['rating'], ENT_QUOTES, 'UTF-8'); ?></em>
                                <?php endif; ?>
                            </span>
                            <span class="douban-card-body">
                                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($doubanUser === ''): ?>
            <p class="douban-tip">在插件设置中填写豆瓣 ID 后，这里会显示你的电影和书籍收藏。</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    var tabs = document.querySelectorAll('[data-douban-tab]');
    var panels = document.querySelectorAll('[data-douban-panel]');

    Array.prototype.forEach.call(tabs, function (tab) {
        tab.addEventListener('click', function () {
            var name = tab.getAttribute('data-douban-tab');

            Array.prototype.forEach.call(tabs, function (item) {
                var active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            Array.prototype.forEach.call(panels, function (panel) {
                panel.hidden = panel.getAttribute('data-douban-panel') !== name;
            });
        });
    });
})();
</script>

<?php $this->need('footer.php'); ?>
