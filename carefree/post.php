<?php
/**
 * 文章详情
 *
 * @package Carefree
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$sleekWords = sleek_reading_time($this->content);
$sleekTags = $this->tags;
$sleekAuthor = $this->author;
$sleekAuthorId = (int) $this->authorId;
$sleekAuthorName = trim((string) $sleekAuthor->screenName);
$sleekAuthorUrl = trim((string) $sleekAuthor->permalink);
$sleekAuthorAvatar = sleek_comment_avatar_url($sleekAuthorId, $sleekAuthorId, (string) $sleekAuthor->mail, 40);

if ($sleekAuthorName === '') {
    $sleekAuthorName = $this->options->title;
}

if ($sleekAuthorUrl === '') {
    $sleekAuthorUrl = $this->options->siteUrl;
}

$this->need('header.php');
?>

<article class="card post-card" id="post-<?php echo (int) $this->cid; ?>">
    <header class="post-head">
        <div class="post-byline">
            <a class="post-author" href="<?php echo htmlspecialchars($sleekAuthorUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="post-author-avatar">
                    <img src="<?php echo htmlspecialchars($sleekAuthorAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($sleekAuthorName, ENT_QUOTES, 'UTF-8'); ?>"
                         width="40" height="40" loading="lazy" onerror="this.remove()">
                </span>
                <span class="post-author-info">
                    <span class="post-author-name"><?php echo htmlspecialchars($sleekAuthorName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="post-author-role"><?php _e('作者'); ?></span>
                </span>
            </a>
        </div>

        <h1 class="post-title"><?php $this->title(); ?></h1>

        <div class="post-meta">
            <span class="meta-item"><?php sleek_icon('calendar'); ?><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y/m/d H:i'); ?></time></span>
            <?php if ($this->categories): ?>
                <span class="meta-item"><?php sleek_icon('folder'); ?><?php $this->category(' / '); ?></span>
            <?php endif; ?>
            <span class="meta-item meta-like"><?php sleek_like_button($this->cid, 'like-btn-meta', true); ?></span>
            <span class="meta-item"><?php sleek_icon('clock'); ?><?php _e('约'); ?> <?php echo $sleekWords; ?> <?php _e('分钟'); ?></span>
            <span class="meta-item"><?php sleek_icon('comment'); ?><a href="#comments"><?php _e('评论'); ?></a></span>
        </div>

        <?php if ($this->password): ?>
            <p class="post-locked"><?php sleek_icon('lock'); ?><?php _e('这是一篇加密文章，输入密码后可见。'); ?></p>
        <?php endif; ?>
    </header>

    <div class="article" id="article">
        <?php
        ob_start();
        $this->content();
        $sleekPostBody = (string) ob_get_clean();
        $sleekPostBody = preg_replace_callback(
            '/(?<!\[)\[music\b[^\]]*\]/i',
            function ($match) {
                if (class_exists('\TypechoPlugin\SleekMusic\Plugin')) {
                    return \TypechoPlugin\SleekMusic\Plugin::renderFromContent($match[0]);
                }

                return '';
            },
            $sleekPostBody
        );
        $sleekPostBody = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $sleekPostBody);
        echo $sleekPostBody;
        ?>
    </div>

    <?php if (!empty($sleekTags)): ?>
        <footer class="post-tags">
            <?php foreach ($sleekTags as $tag): ?>
                <a class="tag-chip" href="<?php echo htmlspecialchars($tag['permalink'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="hash">#</span><?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </footer>
    <?php endif; ?>
</article>

<nav class="card post-nav" aria-label="<?php _e('文章导航'); ?>">
    <div class="pn-item pn-prev">
        <?php
        $this->thePrev(
            '<span class="pn-dir">' . _t('上一篇') . '</span><span class="pn-title">%s</span>',
            '<span class="pn-dir">' . _t('上一篇') . '</span><span class="pn-empty">' . _t('没有上一篇了') . '</span>'
        );
        ?>
    </div>
    <div class="pn-item pn-next">
        <?php
        $this->theNext(
            '<span class="pn-dir">' . _t('下一篇') . '</span><span class="pn-title">%s</span>',
            '<span class="pn-dir">' . _t('下一篇') . '</span><span class="pn-empty">' . _t('没有下一篇了') . '</span>'
        );
        ?>
    </div>
</nav>

<div class="card comments-card" id="comments">
    <?php $this->need('comments.php'); ?>
</div>

<?php $this->need('footer.php'); ?>
