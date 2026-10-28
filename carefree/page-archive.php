<?php
/**
 * 归档页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$sleekPosts = sleek_all_posts();
$sleekGroups = array();

foreach ($sleekPosts as $sleekPost) {
    $sleekYear = date('Y', (int) $sleekPost['created']);
    if (!isset($sleekGroups[$sleekYear])) {
        $sleekGroups[$sleekYear] = array();
    }
    $sleekGroups[$sleekYear][] = $sleekPost;
}

$sleekYears = array_keys($sleekGroups);
$sleekNewest = !empty($sleekYears) ? reset($sleekYears) : '';
$sleekOldest = !empty($sleekYears) ? end($sleekYears) : '';
$sleekHeatmap = sleek_heatmap_data();
?>

<section class="archive-hero">
    <div class="archive-hero-copy">
        <span class="archive-kicker"><?php _e('ARCHIVE'); ?></span>
        <h1 class="archive-hero-title"><?php $this->title(); ?></h1>
        <p class="archive-hero-sub">
            <?php if (!empty($sleekPosts)): ?>
                <?php if ($sleekNewest === $sleekOldest): ?>
                    <?php printf(_t('共 %d 篇文章 · %s'), count($sleekPosts), $sleekNewest); ?>
                <?php else: ?>
                    <?php printf(_t('共 %d 篇文章 · %s—%s'), count($sleekPosts), $sleekOldest, $sleekNewest); ?>
                <?php endif; ?>
            <?php else: ?>
                <?php _e('还没有文章。'); ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="archive-stats">
        <div class="archive-stat">
            <strong><?php echo count($sleekPosts); ?></strong>
            <span><?php _e('篇文章'); ?></span>
        </div>
        <div class="archive-stat">
            <strong><?php echo count($sleekGroups); ?></strong>
            <span><?php _e('个年份'); ?></span>
        </div>
    </div>
</section>

<?php if (!empty($sleekYears)): ?>
    <section class="about-v2 archive-heat">
    <section class="about-card about-heat-card">
        <header class="about-heat-head">
            <div>
                <span class="about-kicker"><?php _e('ACTIVITY'); ?></span>
                <h2><?php _e('热力打卡'); ?></h2>
                <p><?php _e('近半年，每一次更新都留下痕迹。'); ?></p>
            </div>
            <div class="about-heat-total">
                <strong><?php echo (int) $sleekHeatmap['total']; ?></strong>
                <span><?php _e('次打卡'); ?></span>
            </div>
        </header>

        <div class="about-heat-scroll">
            <div class="about-heat-days" aria-hidden="true">
                <span></span>
                <span><?php _e('一'); ?></span>
                <span></span>
                <span><?php _e('三'); ?></span>
                <span></span>
                <span><?php _e('五'); ?></span>
                <span></span>
            </div>

            <div class="about-heat-body">
                <div class="about-heat-grid" role="img" aria-label="<?php _e('近半年文章更新热力图'); ?>">
                    <?php foreach ($sleekHeatmap['cells'] as $sleekHeatCell): ?>
                        <span class="about-heat-cell level-<?php echo (int) $sleekHeatCell['level']; ?><?php echo $sleekHeatCell['future'] ? ' is-future' : ''; ?><?php echo $sleekHeatCell['today'] ? ' is-today' : ''; ?>"
                              title="<?php echo htmlspecialchars($sleekHeatCell['label'], ENT_QUOTES, 'UTF-8'); ?>"
                              aria-label="<?php echo htmlspecialchars($sleekHeatCell['label'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <footer class="about-heat-foot">
            <span><?php echo $sleekHeatmap['start']->format('Y.m.d'); ?> — <?php echo $sleekHeatmap['today']->format('Y.m.d'); ?></span>
            <span class="about-heat-legend">
                <?php _e('少'); ?>
                <i class="level-0"></i>
                <i class="level-1"></i>
                <i class="level-2"></i>
                <i class="level-3"></i>
                <i class="level-4"></i>
                <?php _e('多'); ?>
            </span>
        </footer>
    </section>
</section>

<nav class="archive-years" aria-label="<?php _e('按年份跳转'); ?>">
        <?php foreach ($sleekYears as $sleekYear): ?>
            <a href="#archive-year-<?php echo htmlspecialchars($sleekYear, ENT_QUOTES, 'UTF-8'); ?>">
                <span><?php echo htmlspecialchars($sleekYear, ENT_QUOTES, 'UTF-8'); ?></span>
                <em><?php echo count($sleekGroups[$sleekYear]); ?></em>
            </a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php if ($this->content): ?>
    <section class="archive-intro">
        <?php $this->content(); ?>
    </section>
<?php endif; ?>

<?php if (empty($sleekGroups)): ?>
    <div class="archive-empty-state">
        <span class="empty-icon"><?php sleek_icon('archive'); ?></span>
        <h2><?php _e('还没有文章'); ?></h2>
        <p><?php _e('发布第一篇文章后，这里会自动生成时间线。'); ?></p>
    </div>
<?php else: ?>
    <section class="archive-timeline">
        <?php foreach ($sleekGroups as $sleekYear => $sleekYearPosts): ?>
            <section class="archive-year" id="archive-year-<?php echo htmlspecialchars($sleekYear, ENT_QUOTES, 'UTF-8'); ?>">
                <header class="archive-year-head">
                    <h2><?php echo htmlspecialchars($sleekYear, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <span class="archive-year-count"><?php echo count($sleekYearPosts); ?> <?php _e('篇'); ?></span>
                    <span class="archive-year-rule" aria-hidden="true"></span>
                </header>

                <ul class="archive-list">
                    <?php foreach ($sleekYearPosts as $sleekPost): ?>
                        <?php $sleekTimestamp = (int) $sleekPost['created']; ?>
                        <li class="archive-item">
                            <a href="<?php echo htmlspecialchars(sleek_post_url($sleekPost['cid'], $sleekPost['slug']), ENT_QUOTES, 'UTF-8'); ?>">
                                <time datetime="<?php echo date('c', $sleekTimestamp); ?>">
                                    <span class="archive-month"><?php echo date('n', $sleekTimestamp); ?><?php _e('月'); ?></span>
                                    <strong><?php echo date('d', $sleekTimestamp); ?></strong>
                                </time>
                                <span class="archive-dot" aria-hidden="true"></span>
                                <span class="archive-title"><?php echo htmlspecialchars($sleekPost['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="archive-arrow"><?php sleek_icon('arrow-right'); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
