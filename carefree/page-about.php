<?php
/**
 * 关于页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$aboutOptions = sleek_options();
$aboutTitle = trim((string) $aboutOptions->title);
$aboutBio = trim((string) sleek_option('headerSignature', ''));
$aboutDescription = trim((string) $aboutOptions->description);

if ($aboutBio === '') {
    $aboutBio = $aboutDescription;
}

$aboutContent = trim((string) $this->content);
$aboutContent = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $aboutContent);

if ($aboutContent === '') {
    $aboutContent = '<p>' . htmlspecialchars($aboutBio !== '' ? $aboutBio : $aboutTitle, ENT_QUOTES, 'UTF-8') . '</p>';
}
?>

<div class="about-simple">
    <section class="about-simple-hero">
        <span class="about-simple-avatar">
            <img src="<?php echo htmlspecialchars(sleek_avatar_url(), ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($aboutTitle, ENT_QUOTES, 'UTF-8'); ?>"
                 onerror="this.remove()">
        </span>

        <div class="about-simple-copy">
            <span class="about-simple-kicker"><?php _e('ABOUT'); ?></span>
            <h1><?php echo htmlspecialchars($aboutTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php if ($aboutBio !== ''): ?>
                <p><?php echo htmlspecialchars($aboutBio, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>

        <a class="about-simple-rss" href="<?php echo htmlspecialchars(rtrim((string) $aboutOptions->siteUrl, '/') . '/feed/', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <?php sleek_icon('rss'); ?><?php _e('订阅'); ?>
        </a>
    </section>

    <section class="about-simple-card">
        <span class="about-simple-kicker"><?php _e('INTRO'); ?></span>
        <div class="about-simple-prose">
            <?php echo $aboutContent; ?>
        </div>
    </section>
</div>

<?php $this->need('footer.php'); ?>
