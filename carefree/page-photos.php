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

$sleekContent = (string) $this->content;
$sleekImages = sleek_images($sleekContent, 300);
$sleekClean = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $sleekContent);
$sleekClean = preg_replace('/<img[^>]*>/i', '', (string) $sleekClean);
$sleekIntro = trim(strip_tags((string) $sleekClean));
?>

<div class="card page-head">
    <div class="page-head-inner">
        <span class="eyebrow"><?php _e('相册'); ?></span>
        <h1 class="page-title"><?php $this->title(); ?></h1>
        <p class="page-desc">
            <?php if (count($sleekImages) > 0): ?>
                <?php printf(_t('%d 张照片'), count($sleekImages)); ?>
                <span class="dot">·</span><?php _e('点击查看大图'); ?>
            <?php else: ?>
                <?php _e('把图片插入页面正文即可自动生成瀑布流'); ?>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($sleekImages)): ?>
    <?php if ($sleekIntro !== ''): ?>
        <div class="card article page-intro">
            <?php echo $sleekClean; ?>
        </div>
    <?php endif; ?>

    <div class="photo-grid" id="photo-grid">
        <?php foreach ($sleekImages as $sleekImage): ?>
            <button type="button" class="photo-item" data-src="<?php echo htmlspecialchars($sleekImage, ENT_QUOTES, 'UTF-8'); ?>" data-alt="<?php echo htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8'); ?>">
                <img src="<?php echo htmlspecialchars($sleekImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
            </button>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php if ($sleekContent !== ''): ?>
        <div class="card article page-intro">
            <?php echo $sleekContent; ?>
        </div>
    <?php endif; ?>

    <div class="card empty-state">
        <span class="empty-icon"><?php sleek_icon('photo'); ?></span>
        <h2><?php _e('相册还是空的'); ?></h2>
        <p><?php _e('在页面正文里插入图片，它们会以瀑布流形式展示在这里。'); ?></p>
    </div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
