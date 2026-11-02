<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
    </main>

    <footer class="site-footer">
        <div class="footer-card">
            <p class="footer-copy">
                &copy;
                <?php
                $sleekYear = (int) date('Y');
                $sleekStart = (int) sleek_option('startYear', '');
                if ($sleekStart > 1970 && $sleekStart < $sleekYear) {
                    echo $sleekStart . '–' . $sleekYear;
                } else {
                    echo $sleekYear;
                }
                ?>
                <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
            </p>
            <?php $sleekFooterText = trim((string) sleek_option('footerText', '')); ?>
            <?php if ($sleekFooterText !== ''): ?>
                <p class="footer-extra"><?php echo $sleekFooterText; ?></p>
            <?php endif; ?>
            <p class="footer-meta">
                <a href="<?php $this->options->feedUrl(); ?>" title="RSS"><?php sleek_icon('rss'); ?> RSS</a>
                <span class="dot">·</span>
                Powered by <a href="https://typecho.org" target="_blank" rel="noopener nofollow">Typecho</a>
                <span class="dot">·</span>
                Theme <span class="theme-name">Carefree</span>
            </p>
        </div>
    </footer>
</div><!-- .page-shell -->

<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-label="<?php _e('图片预览'); ?>">
    <button type="button" class="lb-btn lb-close" id="lb-close" aria-label="<?php _e('关闭'); ?>"><?php sleek_icon('close'); ?></button>
    <button type="button" class="lb-btn lb-prev" id="lb-prev" aria-label="<?php _e('上一张'); ?>"><?php sleek_icon('chevron-left'); ?></button>
    <figure class="lb-figure">
        <img id="lb-image" src="" alt="">
        <figcaption id="lb-caption"></figcaption>
    </figure>
    <button type="button" class="lb-btn lb-next" id="lb-next" aria-label="<?php _e('下一张'); ?>"><?php sleek_icon('chevron-right'); ?></button>
</div>

<div class="overlay" id="search-overlay" aria-hidden="true">
    <form class="search-panel" method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
        <div class="search-row">
            <?php sleek_icon('search', 'icon search-icon'); ?>
            <input type="search" name="s" id="search-input" placeholder="<?php _e('搜索文章、标签…'); ?>" autocomplete="off" required>
            <button type="submit" class="search-submit" aria-label="<?php _e('搜索'); ?>"><?php sleek_icon('arrow-right'); ?></button>
        </div>
        <p class="search-hint"><kbd>Enter</kbd> <?php _e('搜索'); ?> <span>·</span> <kbd>Esc</kbd> <?php _e('关闭'); ?></p>
    </form>
</div>

<a class="forever-wormhole" href="https://www.foreverblog.cn/go.html" target="_blank" rel="noopener nofollow" title="<?php _e('虫洞 · 随机访问十年之约成员博客'); ?>" aria-label="<?php _e('虫洞 · 随机访问十年之约成员博客'); ?>">
    <img src="<?php echo sleek_theme_url('assets/img/foreverblog-wormhole-dark.webp'); ?>" alt="<?php _e('十年之约虫洞'); ?>" width="82" height="32" decoding="async">
</a>

<div class="floating-actions" aria-label="<?php _e('页面工具'); ?>">
    <button type="button" class="to-top" id="to-top" aria-label="<?php _e('回到顶部'); ?>" title="<?php _e('回到顶部'); ?>">
        <?php sleek_icon('arrow-up'); ?>
    </button>
    <button type="button" class="footer-theme floating-theme" id="footer-theme" aria-label="<?php _e('切换配色'); ?>" title="<?php _e('切换配色'); ?>">
        <?php sleek_icon('moon', 'icon icon-moon'); ?>
        <?php sleek_icon('sun', 'icon icon-sun'); ?>
    </button>
</div>

<script src="<?php echo sleek_theme_url('assets/js/main.js'); ?>?v=3.3.15"></script>
<script src="<?php echo sleek_theme_url('assets/js/pjax.js'); ?>?v=3.3.15"></script>
<?php $this->footer(); ?>
</body>
</html>
