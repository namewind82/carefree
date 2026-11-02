<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php
$sleekArchiveKicker = 'ARCHIVE';
$sleekArchiveLabel = _t('归档');
$sleekArchiveIcon = 'archive';
$sleekArchiveDesc = _t('按时间倒序排列的全部记录');
$sleekArchiveUnit = _t('篇文章');

if ($this->is('category')) {
    $sleekArchiveKicker = 'CATEGORY';
    $sleekArchiveLabel = _t('分类');
    $sleekArchiveIcon = 'folder';
    $sleekArchiveDesc = _t('该分类下的全部文章');
} elseif ($this->is('tag')) {
    $sleekArchiveKicker = 'TAG';
    $sleekArchiveLabel = _t('标签');
    $sleekArchiveIcon = 'tag';
    $sleekArchiveDesc = _t('同一枚标签下的所有记录');
    $sleekArchiveUnit = _t('条记录');
} elseif ($this->is('search')) {
    $sleekArchiveKicker = 'SEARCH';
    $sleekArchiveLabel = _t('搜索');
    $sleekArchiveIcon = 'search';
    $sleekArchiveDesc = _t('按关键词匹配标题与正文');
    $sleekArchiveUnit = _t('条结果');
} elseif ($this->is('author')) {
    $sleekArchiveKicker = 'AUTHOR';
    $sleekArchiveLabel = _t('作者');
    $sleekArchiveIcon = 'user';
    $sleekArchiveDesc = _t('作者发布的全部文章');
}

$sleekArchiveTotal = method_exists($this, 'getTotal') ? (int) $this->getTotal() : 0;
?>

<section class="archive-hero taxonomy-hero">
    <div class="taxonomy-hero-main">
        <span class="taxonomy-badge" aria-hidden="true"><?php sleek_icon($sleekArchiveIcon); ?></span>
        <div class="archive-hero-copy">
            <nav class="taxonomy-crumb" aria-label="<?php _e('当前位置'); ?>">
                <a href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a>
                <span aria-hidden="true">/</span>
                <span><?php echo htmlspecialchars($sleekArchiveLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </nav>
            <span class="archive-kicker"><?php echo htmlspecialchars($sleekArchiveKicker, ENT_QUOTES, 'UTF-8'); ?></span>
            <h1 class="archive-hero-title">
                <?php $this->archiveTitle(array(
                    'category' => _t('%s'),
                    'search' => _t('“%s” 的结果'),
                    'tag' => _t('#%s'),
                    'author' => _t('%s 的文章'),
                ), '', ''); ?>
            </h1>
            <p class="archive-hero-sub"><?php echo htmlspecialchars($sleekArchiveDesc, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>
    <div class="archive-stats">
        <div class="archive-stat">
            <strong><?php echo $sleekArchiveTotal; ?></strong>
            <span><?php echo htmlspecialchars($sleekArchiveUnit, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</section>

<div class="feed" id="feed-list">
    <?php $sleekHasPost = false; ?>
    <?php while ($this->next()): ?>
        <?php $sleekHasPost = true; ?>
        <?php sleek_post_card($this); ?>
    <?php endwhile; ?>
</div>

<?php if (!$sleekHasPost): ?>
    <div class="card empty-state">
        <span class="empty-icon"><?php sleek_icon('search'); ?></span>
        <h2><?php _e('没有找到内容'); ?></h2>
        <p><?php _e('换一个关键词，或者回首页看看。'); ?></p>
        <a class="btn" href="<?php $this->options->siteUrl(); ?>"><?php _e('回到首页'); ?></a>
    </div>
<?php else: ?>
    <div class="pager-wrap">
        <div class="pager" id="pager"><?php $this->pageNav('上一页', '下一页'); ?></div>
        <div class="load-status" id="load-status" role="status" aria-live="polite" hidden>
            <span class="spinner"></span><?php _e('正在加载…'); ?>
        </div>
        <button type="button" class="load-retry" id="load-retry" hidden><?php _e('加载失败，点击重试'); ?></button>
        <p class="feed-end" id="feed-end" hidden><?php _e('已经到底啦 ~'); ?></p>
        <div id="feed-sentinel" class="feed-sentinel" aria-hidden="true"></div>
    </div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
