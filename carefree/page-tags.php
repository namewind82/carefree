<?php
/**
 * 标签页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$sleekTags = sleek_tags();
$sleekTagRefs = 0;
$sleekTagMax = 0;

foreach ($sleekTags as $sleekTag) {
    $sleekTagRefs += $sleekTag['count'];
    $sleekTagMax = max($sleekTagMax, $sleekTag['count']);
}

$sleekPostTotal = count(sleek_all_posts());
?>

<section class="tags-hero">
    <div class="tags-hero-copy">
        <span class="tags-kicker"><?php _e('TAGS'); ?></span>
        <h1 class="tags-title"><?php $this->title(); ?></h1>
        <p class="tags-sub">
            <?php if (!empty($sleekTags)): ?>
                <?php printf(_t('共 %d 个标签，串联 %d 篇文章'), count($sleekTags), $sleekPostTotal); ?>
            <?php else: ?>
                <?php _e('还没有标签。'); ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="tags-stats">
        <div class="tags-stat">
            <strong><?php echo count($sleekTags); ?></strong>
            <span><?php _e('个标签'); ?></span>
        </div>
        <div class="tags-stat">
            <strong><?php echo $sleekTagRefs; ?></strong>
            <span><?php _e('次引用'); ?></span>
        </div>
        <div class="tags-stat">
            <strong><?php echo $sleekPostTotal; ?></strong>
            <span><?php _e('篇文章'); ?></span>
        </div>
    </div>
</section>

<?php if ($this->content): ?>
    <section class="tags-intro">
        <?php $this->content(); ?>
    </section>
<?php endif; ?>

<?php if (empty($sleekTags)): ?>
    <div class="tags-empty">
        <span class="empty-icon"><?php sleek_icon('tag'); ?></span>
        <h2><?php _e('还没有标签'); ?></h2>
        <p><?php _e('给文章添加标签后，这里会自动生成标签云。'); ?></p>
    </div>
<?php else: ?>
    <section class="tags-card">
        <header class="tags-head">
            <div>
                <span class="tags-kicker"><?php _e('ALL TAGS'); ?></span>
                <h2><?php _e('全部标签'); ?></h2>
            </div>
            <span class="tags-hint"><?php _e('点击标签查看对应文章'); ?></span>
        </header>

        <div class="tag-cloud">
            <?php foreach ($sleekTags as $sleekTag): ?>
                <?php
                $sleekLevel = 1;
                if ($sleekTagMax > 2 && $sleekTag['count'] >= max(2, (int) ceil($sleekTagMax * 0.66))) {
                    $sleekLevel = 3;
                } elseif ($sleekTag['count'] > 1) {
                    $sleekLevel = 2;
                }
                ?>
                <a class="tag-pill level-<?php echo (int) $sleekLevel; ?>"
                   href="<?php echo htmlspecialchars($sleekTag['permalink'], ENT_QUOTES, 'UTF-8'); ?>"
                   title="<?php echo htmlspecialchars($sleekTag['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="tag-hash">#</span>
                    <span class="tag-name"><?php echo htmlspecialchars($sleekTag['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="tag-count"><?php echo (int) $sleekTag['count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="tags-footnote">
        <span><?php sleek_icon('tag'); ?></span>
        <p><?php _e('标签按使用频率排列，字号越大代表文章越多。'); ?></p>
    </div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
