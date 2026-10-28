<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="card page-head">
    <div class="page-head-inner">
        <span class="eyebrow">
            <?php
            if ($this->is('category')) {
                _e('分类');
            } elseif ($this->is('tag')) {
                _e('标签');
            } elseif ($this->is('search')) {
                _e('搜索');
            } elseif ($this->is('author')) {
                _e('作者');
            } else {
                _e('归档');
            }
            ?>
        </span>
        <h1 class="page-title">
            <?php $this->archiveTitle(array(
                'category' => _t('%s'),
                'search' => _t('“%s” 的结果'),
                'tag' => _t('#%s'),
                'author' => _t('%s 的文章'),
            ), '', ''); ?>
        </h1>
        <p class="page-desc">
            <?php
            if ($this->is('search')) {
                _e('按关键词匹配标题与正文');
            } elseif ($this->is('tag')) {
                _e('同一枚标签下的所有记录');
            } else {
                _e('按时间倒序排列的全部记录');
            }
            ?>
        </p>
    </div>
</div>

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
