<?php
/**
 * QQ空间说说风格的单列说说流，包含正文、图片、互动与评论。
 *
 * @package Carefree
 * @author Carefree
 * @version 3.3.15
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<div class="feed" id="feed-list">
    <?php $sleekHasPost = false; ?>
    <?php while ($this->next()): ?>
        <?php $sleekHasPost = true; ?>
        <?php sleek_post_card($this); ?>
    <?php endwhile; ?>
</div>

<?php if (!$sleekHasPost): ?>
    <div class="card empty-state">
        <span class="empty-icon"><?php sleek_icon('file'); ?></span>
        <h2><?php _e('还没有文章'); ?></h2>
        <p><?php _e('去后台写下第一篇随笔吧。'); ?></p>
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
