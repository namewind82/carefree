<?php
/**
 * 留言 / 说说页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<div class="card page-head">
    <div class="page-head-inner">
        <span class="eyebrow"><?php _e('留言板'); ?></span>
        <h1 class="page-title"><?php $this->title(); ?></h1>
        <p class="page-desc"><?php _e('说说近况，或者只是打个招呼。'); ?></p>
    </div>
</div>

<?php if ($this->content): ?>
    <div class="card article page-intro">
        <?php $this->content(); ?>
    </div>
<?php endif; ?>

<div class="card comments-card messages-page" id="comments">
    <?php $this->need('comments.php'); ?>
</div>

<?php $this->need('footer.php'); ?>
