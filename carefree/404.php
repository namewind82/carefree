<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="card error-page">
    <p class="error-code">404</p>
    <h1 class="error-title"><?php _e('这条小路没有尽头'); ?></h1>
    <p class="error-desc"><?php _e('你要找的页面可能被移动、删除了，或者从来没有存在过。'); ?></p>
    <div class="error-actions">
        <a class="btn btn-primary" href="<?php $this->options->siteUrl(); ?>"><?php _e('回到首页'); ?></a>
        <button type="button" class="btn" id="error-search"><?php sleek_icon('search'); ?><?php _e('搜索一下'); ?></button>
    </div>
</div>

<?php $this->need('footer.php'); ?>
