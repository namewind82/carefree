<?php
/**
 * 友链页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$sleekFromPlugin = function_exists('sleek_links_items');
$sleekLinks = $sleekFromPlugin ? sleek_links_items() : array();

if (!empty($sleekLinks)) {
    $sleekFromPlugin = true;
} else {
    $sleekFromPlugin = false;
    $sleekRaw = trim((string) $this->fields->links);

    if ($sleekRaw !== '') {
        foreach (preg_split('/\r\n|\r|\n/', $sleekRaw) as $sleekLine) {
            $sleekLine = trim($sleekLine);

            if ($sleekLine === '' || $sleekLine[0] === '#') {
                continue;
            }

            $sleekParts = array_map('trim', explode('|', $sleekLine));

            if (count($sleekParts) < 2 || $sleekParts[0] === '' || $sleekParts[1] === '') {
                continue;
            }

            $sleekLinks[] = array(
                'name' => $sleekParts[0],
                'url' => $sleekParts[1],
                'desc' => isset($sleekParts[2]) ? $sleekParts[2] : '',
                'avatar' => isset($sleekParts[3]) ? $sleekParts[3] : '',
            );
        }
    }

    if (count($sleekLinks) > 1) {
        shuffle($sleekLinks);
    }
}

$sleekFaces = array_slice($sleekLinks, 0, 5);
?>

<section class="links-hero">
    <div class="links-hero-copy">
        <span class="links-kicker"><?php _e('FRIENDS'); ?></span>
        <h1 class="links-title"><?php $this->title(); ?></h1>
        <p class="links-sub">
            <?php if (!empty($sleekLinks)): ?>
                <?php printf(_t('共 %d 位朋友，感谢一路同行'), count($sleekLinks)); ?>
            <?php else: ?>
                <?php _e('还没有友链，欢迎来交换。'); ?>
            <?php endif; ?>
        </p>

        <?php if ($this->allow('comment')): ?>
            <a class="links-apply" href="#comments" data-links-apply>
                <?php sleek_icon('circle'); ?><?php _e('一键添加友链'); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($sleekFaces)): ?>
        <div class="links-hero-faces" aria-hidden="true">
            <?php foreach ($sleekFaces as $sleekFace): ?>
                <?php
                $faceInitial = function_exists('mb_substr')
                    ? mb_substr($sleekFace['name'], 0, 1, 'UTF-8')
                    : substr($sleekFace['name'], 0, 1);
                $faceHue = crc32($sleekFace['name']) % 360;
                ?>
                <span class="links-face" style="--hue: <?php echo (int) $faceHue; ?>">
                    <span><?php echo htmlspecialchars($faceInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($sleekFace['avatar'] !== ''): ?>
                        <img src="<?php echo htmlspecialchars(sleek_abs_url($sleekFace['avatar']), ENT_QUOTES, 'UTF-8'); ?>"
                             alt="" loading="lazy" onerror="this.style.display='none'">
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
            <?php if (count($sleekLinks) > 5): ?>
                <span class="links-face links-face-more">+<?php echo count($sleekLinks) - 5; ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($sleekLinks)): ?>
    <div class="links-grid">
        <?php foreach ($sleekLinks as $sleekLink): ?>
            <?php
            $sleekInitial = function_exists('mb_substr')
                ? mb_substr($sleekLink['name'], 0, 1, 'UTF-8')
                : substr($sleekLink['name'], 0, 1);
            $sleekHue = crc32($sleekLink['name']) % 360;
            $sleekHost = (string) parse_url($sleekLink['url'], PHP_URL_HOST);
            $sleekHost = preg_replace('/^www\./i', '', $sleekHost);
            $sleekDesc = trim((string) $sleekLink['desc']);
            ?>
            <a class="link-card" href="<?php echo htmlspecialchars($sleekLink['url'], ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener nofollow">
                <span class="link-top">
                    <span class="link-avatar" style="--hue: <?php echo (int) $sleekHue; ?>">
                        <span class="link-initial"><?php echo htmlspecialchars($sleekInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if ($sleekLink['avatar'] !== ''): ?>
                            <img src="<?php echo htmlspecialchars(sleek_abs_url($sleekLink['avatar']), ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($sleekLink['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy" onerror="this.style.display='none'">
                        <?php endif; ?>
                    </span>
                    <span class="link-arrow"><?php sleek_icon('arrow-right'); ?></span>
                </span>
                <span class="link-body">
                    <span class="link-name"><?php echo htmlspecialchars($sleekLink['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="link-desc<?php echo $sleekDesc === '' ? ' is-empty' : ''; ?>">
                        <?php echo htmlspecialchars($sleekDesc !== '' ? $sleekDesc : _t('这位朋友还没有留下简介'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </span>
                <span class="link-foot">
                    <span class="link-domain"><?php echo htmlspecialchars($sleekHost !== '' ? $sleekHost : 'website', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="link-visit"><?php _e('访问'); ?><?php sleek_icon('arrow-right'); ?></span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($this->content): ?>
    <section class="links-exchange" id="exchange">
        <div class="links-exchange-head">
            <span class="links-exchange-icon"><?php sleek_icon('link'); ?></span>
            <div>
                <span class="links-kicker"><?php _e('EXCHANGE'); ?></span>
                <h2><?php _e('友链说明'); ?></h2>
            </div>
        </div>
        <div class="links-exchange-content">
            <?php $this->content(); ?>
        </div>
    </section>
<?php elseif (empty($sleekLinks)): ?>
    <div class="card empty-state">
        <span class="empty-icon"><?php sleek_icon('link'); ?></span>
        <h2><?php _e('还没有友链'); ?></h2>
        <p><?php _e('在后台「Links」插件中添加友链。'); ?></p>
    </div>
<?php endif; ?>

<?php if ($this->allow('comment')): ?>
    <div class="links-comment-note">
        <span class="links-comment-note-icon"><?php sleek_icon('chat'); ?></span>
        <div>
            <strong><?php _e('申请友链'); ?></strong>
            <p><?php _e('按「站点名称 / 链接 / 图标 / 描述」留言，审核后添加到好友链。'); ?></p>
        </div>
    </div>

    <div class="card comments-card links-comments" id="comments">
        <?php $this->need('comments.php'); ?>
    </div>

    <script>
    (function () {
        var buttons = document.querySelectorAll('[data-links-apply]');

        Array.prototype.forEach.call(buttons, function (button) {
            button.addEventListener('click', function (event) {
                var comments = document.getElementById('comments');
                var textarea = document.querySelector('#comment-form textarea[name="text"]');

                if (!comments) {
                    return;
                }

                event.preventDefault();
                comments.scrollIntoView({ behavior: 'smooth', block: 'start' });

                if (textarea && !textarea.value.trim()) {
                    textarea.value = '站点名称：\n站点链接：\n图标链接：\n一句话描述：\n';
                }

                if (textarea) {
                    window.setTimeout(function () {
                        textarea.focus();
                    }, 350);
                }
            });
        });
    })();
    </script>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
