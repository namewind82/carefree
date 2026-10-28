<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<article class="card page-card">
    <header class="post-head">
        <div class="post-tags-top"><span class="eyebrow"><?php _e('独立页面'); ?></span></div>
        <h1 class="post-title"><?php $this->title(); ?></h1>
        <div class="post-meta">
            <span class="meta-item"><?php sleek_icon('calendar'); ?><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y/m/d'); ?></time></span>
            <?php if ($this->commentsNum): ?>
                <span class="meta-item"><?php sleek_icon('comment'); ?><a href="#comments"><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></a></span>
            <?php endif; ?>
        </div>
    </header>

    <div class="article">
        <?php $this->content(); ?>
    </div>
</article>

<?php if ($this->allow('comment')): ?>
    <div class="card comments-card" id="comments">
        <?php $this->need('comments.php'); ?>
    </div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
