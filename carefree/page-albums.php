<?php
/**
 * 相册页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$sleekImages = sleek_album_images();
$sleekPostCount = 0;
$sleekPostSeen = array();

foreach ($sleekImages as $sleekImage) {
    if (!isset($sleekPostSeen[$sleekImage['url']])) {
        $sleekPostSeen[$sleekImage['url']] = true;
        $sleekPostCount++;
    }
}
?>

<section class="album-hero">
    <div class="album-hero-copy">
        <span class="album-kicker"><?php _e('GALLERY'); ?></span>
        <h1 class="album-hero-title"><?php $this->title(); ?></h1>
        <p class="album-hero-sub">
            <?php if (!empty($sleekImages)): ?>
                <?php printf(_t('共 %d 张图片 · 来自 %d 篇文章'), count($sleekImages), $sleekPostCount); ?>
            <?php else: ?>
                <?php _e('还没有图片。'); ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="album-stats">
        <div class="album-stat">
            <strong><?php echo count($sleekImages); ?></strong>
            <span><?php _e('张图片'); ?></span>
        </div>
        <div class="album-stat">
            <strong><?php echo $sleekPostCount; ?></strong>
            <span><?php _e('篇文章'); ?></span>
        </div>
    </div>
</section>

<?php if ($this->content): ?>
    <section class="album-intro">
        <?php $this->content(); ?>
    </section>
<?php endif; ?>

<?php if (empty($sleekImages)): ?>
    <div class="album-empty">
        <span class="empty-icon"><?php sleek_icon('image'); ?></span>
        <h2><?php _e('还没有图片'); ?></h2>
        <p><?php _e('发布带图片的文章后，这里会自动聚合所有文章图片。'); ?></p>
    </div>
<?php else: ?>
    <div class="album-grid">
        <?php foreach ($sleekImages as $sleekImage): ?>
            <a class="album-item" href="<?php echo htmlspecialchars($sleekImage['url'], ENT_QUOTES, 'UTF-8'); ?>"
               title="<?php echo htmlspecialchars($sleekImage['title'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="album-frame">
                    <img src="<?php echo htmlspecialchars($sleekImage['src'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($sleekImage['title'], ENT_QUOTES, 'UTF-8'); ?>"
                         loading="lazy" decoding="async"
                         onerror="this.closest('.album-item').remove()">
                </span>
                <span class="album-caption">
                    <span class="album-caption-title"><?php echo htmlspecialchars($sleekImage['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="album-caption-meta">
                        <time datetime="<?php echo date('c', $sleekImage['created']); ?>"><?php echo date('Y-m-d', $sleekImage['created']); ?></time>
                        <span><?php _e('查看文章'); ?><?php sleek_icon('arrow-right'); ?></span>
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
