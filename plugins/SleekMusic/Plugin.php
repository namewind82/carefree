<?php
/**
 * SleekMusic · 首页音乐与 [music] 短代码播放器
 *
 * 支持：
 *   [music url="..." title="..." artist="..." cover="..."]
 *
 * 首页固定小播放器 + 文章内联播放器，均使用原生 HTML5 Audio。
 *
 * @package SleekMusic
 * @author  心斋
 * @version 1.9.19
 * @link    https://www.xinzhai.work/
 */

namespace TypechoPlugin\SleekMusic;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Text;

class Plugin implements PluginInterface
{
    const VERSION = '1.9.19';

    public static function activate()
    {
        \Typecho\Plugin::factory('Widget\Base\Contents')->contentEx = __CLASS__ . '::contentEx';
        \Typecho\Plugin::factory('Widget\Base\Contents')->excerptEx = __CLASS__ . '::excerptEx';
        \Typecho\Plugin::factory('Widget_Abstract_Contents')->contentEx = __CLASS__ . '::contentEx';
        \Typecho\Plugin::factory('Widget_Abstract_Contents')->excerptEx = __CLASS__ . '::excerptEx';
        \Typecho\Plugin::factory('Widget\Archive')->contentEx = __CLASS__ . '::contentEx';
        \Typecho\Plugin::factory('Widget\Archive')->header = __CLASS__ . '::header';
        \Typecho\Plugin::factory('Widget\Archive')->footer = __CLASS__ . '::footer';
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = __CLASS__ . '::writePost';
        \Typecho\Plugin::factory('admin/write-page.php')->bottom = __CLASS__ . '::writePage';

        return _t('SleekMusic 已启用：支持 [music] 短代码，并在全站显示固定小播放器。');
    }

    public static function deactivate()
    {
        return _t('SleekMusic 已停用。');
    }

    public static function config(Form $form)
    {
        $form->addInput(new Text(
            'url',
            null,
            'https://www.xinzhai.work/music/情怨.mp3',
            _t('首页音乐地址'),
            _t('首页固定播放器使用的 mp3 地址。留空则不显示首页播放器。')
        ));

        $form->addInput(new Text(
            'title',
            null,
            '情怨',
            _t('歌曲标题'),
            _t('显示在播放器上的歌曲名。')
        ));

        $form->addInput(new Text(
            'artist',
            null,
            '刘欢',
            _t('歌手'),
            _t('显示在播放器上的歌手名。')
        ));

        $form->addInput(new Text(
            'cover',
            null,
            'https://img-s.msn.cn/tenant/amp/entityid/AA2cZZcb.img?w=768&h=900&m=6&x=269&y=326&s=336&d=336',
            _t('封面地址'),
            _t('留空使用插件内置的默认封面。')
        ));

        $form->addInput(new Radio(
            'showOnHome',
            array(
                '1' => _t('显示'),
                '0' => _t('不显示')
            ),
            '1',
            _t('全站固定播放器'),
            _t('在首页、文章页和其他页面显示；切换页面时保留当前曲目和播放进度。')
        ));
    }

    public static function personalConfig(Form $form)
    {
    }

    public static function header()
    {
        $url = self::assetUrl('music.css') . '?v=' . self::VERSION;
        echo '<link rel="stylesheet" href="' . self::esc($url) . '">' . "\n";
    }

    /**
     * 最新一篇带 [music] 的文章
     */
    private static function latestTrack()
    {
        static $track = null;

        if ($track !== null) {
            return $track;
        }

        $fallbackCover = trim((string) self::setting('cover', 'https://img-s.msn.cn/tenant/amp/entityid/AA2cZZcb.img?w=768&h=900&m=6&x=269&y=326&s=336&d=336'));

        if (!self::isUrl($fallbackCover)) {
            $fallbackCover = self::assetUrl('cover.svg');
        }

        $track = array(
            'url' => trim((string) self::setting('url', 'https://www.xinzhai.work/music/情怨.mp3')),
            'title' => (string) self::setting('title', '情怨'),
            'artist' => (string) self::setting('artist', '刘欢'),
            'cover' => $fallbackCover,
        );

        $latestCreated = 0;

        try {
            $db = \Typecho\Db::get();
            $row = $db->fetchRow(
                $db->select('text', 'created')
                    ->from('table.contents')
                    ->where('type = ?', 'post')
                    ->where('status = ?', 'publish')
                    ->where('text LIKE ?', '%[music%')
                    ->order('created', \Typecho\Db::SORT_DESC)
                    ->limit(1)
            );

            if ($row && preg_match('/(?<!\[)\[music\b([^\]]*)\]/i', (string) $row['text'], $match)) {
                $latestCreated = (int) $row['created'];
                $attrs = self::parseAttrs($match[1]);
                $url = isset($attrs['url']) ? trim($attrs['url']) : '';

                if (self::isUrl($url)) {
                    $title = isset($attrs['title']) && $attrs['title'] !== '' ? $attrs['title'] : '音乐';
                    $artist = isset($attrs['artist']) && $attrs['artist'] !== '' ? $attrs['artist'] : '未知艺术家';
                    $cover = isset($attrs['cover']) ? trim($attrs['cover']) : '';

                    if (!self::isUrl($cover)) {
                        $cover = self::assetUrl('cover.svg');
                    }

                    $track = array(
                        'url' => $url,
                        'title' => $title,
                        'artist' => $artist,
                        'cover' => $cover,
                    );
                }
            }

            $row163 = $db->fetchRow(
                $db->select('text', 'created')
                    ->from('table.contents')
                    ->where('type = ?', 'post')
                    ->where('status = ?', 'publish')
                    ->where('text LIKE ?', '%music.163.com%')
                    ->order('created', \Typecho\Db::SORT_DESC)
                    ->limit(1)
            );

            if ($row163 && (int) $row163['created'] > $latestCreated) {
                $neteaseId = self::neteaseId((string) $row163['text']);
                $neteaseTrack = $neteaseId !== '' ? self::neteaseTrack($neteaseId) : null;

                if ($neteaseTrack) {
                    $track = $neteaseTrack;
                }
            }
        } catch (\Throwable $e) {
            // 数据库不可用时保留设置里的兜底曲目
        }

        return $track;
    }

    public static function writePost()
    {
        self::editorPanel();
    }

    public static function writePage()
    {
        self::editorPanel();
    }

    private static function editorPanel()
    {
        static $rendered = false;

        if ($rendered) {
            return;
        }

        $rendered = true;
        $file = __DIR__ . '/admin.html';

        if (is_file($file)) {
            echo file_get_contents($file);
        }
    }

    /**
     * 汇总所有文章里的音乐，形成播放列表
     */
    private static function playlistTracks()
    {
        static $tracks = null;

        if ($tracks !== null) {
            return $tracks;
        }

        $cacheDir = __DIR__ . '/cache';
        $cacheFile = $cacheDir . '/playlist-v4.json';
        $list = array();
        $signature = '';

        try {
            $db = \Typecho\Db::get();
            $meta = $db->fetchRow(
                $db->select('COUNT(*) AS total', 'MAX(created) AS latest', 'MAX(modified) AS modified')
                    ->from('table.contents')
                    ->where('type = ?', 'post')
                    ->where('status = ?', 'publish')
            );

            if ($meta) {
                $signature = (int) $meta['total'] . '|' . (int) $meta['latest'] . '|' . (int) $meta['modified'];
            }

            if (is_file($cacheFile) && filemtime($cacheFile) > time() - 300) {
                $cached = json_decode((string) file_get_contents($cacheFile), true);

                if (is_array($cached) && isset($cached['tracks']) && is_array($cached['tracks'])
                    && isset($cached['signature']) && (string) $cached['signature'] === $signature) {
                    $tracks = $cached['tracks'];
                    return $tracks;
                }
            }

            $rows = $db->fetchAll(
                $db->select('cid', 'title', 'slug', 'text', 'created')
                    ->from('table.contents')
                    ->where('type = ?', 'post')
                    ->where('status = ?', 'publish')
                    ->order('created', \Typecho\Db::SORT_DESC)
            );

            $seen = array();

            foreach ($rows as $row) {
                $text = (string) $row['text'];
                $postTitle = (string) $row['title'];
                $cid = (int) $row['cid'];

                $slug = trim((string) $row['slug']);
$postPath = $slug !== '' ? $slug : (string) $cid;
$postUrl = rtrim((string) \Helper::options()->siteUrl, '/') . '/archives/' . rawurlencode($postPath) . '.html';

        if (preg_match_all('/(?<![\[])\[music\b([^\]]*)\]/i', $text, $matches)) {
                    foreach ($matches[1] as $attributeText) {
                        $attrs = self::parseAttrs($attributeText);
                        $url = isset($attrs['url']) ? trim($attrs['url']) : '';

                        if (!self::isUrl($url) || isset($seen[$url])) {
                            continue;
                        }

                        $cover = isset($attrs['cover']) ? trim($attrs['cover']) : '';

                        if (!self::isUrl($cover)) {
                            $cover = self::assetUrl('cover.svg');
                        }

                        $seen[$url] = true;
                        $list[] = array(
                            'url' => $url,
                            'title' => isset($attrs['title']) && $attrs['title'] !== '' ? $attrs['title'] : _t('音乐'),
                            'artist' => isset($attrs['artist']) && $attrs['artist'] !== '' ? $attrs['artist'] : _t('未知艺术家'),
                            'cover' => $cover,
                            'post' => $postTitle,
                            'postUrl' => $postUrl,
                            'cid' => $cid,
                            'kind' => 'music',
                        );
                    }
                }

                $ids = array();

                if (preg_match_all('/\[163\b[^\]]*?\bid=["\']?(\d+)/i', $text, $matches)) {
                    foreach ($matches[1] as $id) {
                        $ids[$id] = true;
                    }
                }

                if (preg_match_all('/music\.163\.com\/(?:outchain\/player\?[^\s"\'<>]*?\bid=|song\?id=)(\d+)/i', $text, $matches)) {
                    foreach ($matches[1] as $id) {
                        $ids[$id] = true;
                    }
                }

                foreach (array_keys($ids) as $id) {
                    $track = self::neteaseTrack($id);

                    if (!$track || !self::isUrl($track['url']) || isset($seen[$track['url']])) {
                        continue;
                    }

                    $seen[$track['url']] = true;
                    $list[] = array(
                        'url' => $track['url'],
                        'title' => $track['title'],
                        'artist' => $track['artist'],
                        'cover' => $track['cover'],
                        'post' => $postTitle,
                        'postUrl' => $postUrl,
                        'cid' => $cid,
                        'kind' => '163',
                        'id' => $id,
                    );
                }
            }
        } catch (\Throwable $e) {
            // 数据库不可用时退回单曲
        }

        if (empty($list)) {
            $fallback = self::latestTrack();

            if (self::isUrl($fallback['url'])) {
                $list[] = array(
                    'url' => $fallback['url'],
                    'title' => $fallback['title'],
                    'artist' => $fallback['artist'],
                    'cover' => $fallback['cover'],
                    'post' => '',
                    'postUrl' => '',
                    'cid' => 0,
                    'kind' => 'music',
                );
            }
        }

        if (!empty($list)) {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }

            if (is_dir($cacheDir)) {
                @file_put_contents($cacheFile, json_encode(array(
                    'signature' => $signature,
                    'tracks' => $list,
                ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
            }
        }

        $tracks = $list;
        return $tracks;
    }

    public static function footer($widget = null)
    {
        static $rendered = false;

        if ($rendered) {
            return;
        }

        $rendered = true;
        $showPlayer = self::setting('showOnHome', '1') === '1';

        if ($showPlayer) {
            $playlist = self::playlistTracks();
            $track = !empty($playlist[0]) ? $playlist[0] : self::latestTrack();

            if (self::isUrl($track['url'])) {
                if (empty($playlist)) {
                    $playlist = array($track);
                }

                $json = json_encode($playlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                if ($json !== false) {
                    echo '<script type="application/json" id="sleek-music-playlist">' . $json . '</script>' . "\n";
                }

                echo self::dockHtml(
                    $track['url'],
                    $track['title'],
                    $track['artist'],
                    $track['cover']
                );
            }
        }

        $script = self::assetUrl('music.js') . '?v=' . self::VERSION;
        echo '<script src="' . self::esc($script) . '"></script>' . "\n";
    }

    public static function contentEx($content, $widget = null, $lastResult = null)
    {
        $content = (null !== $lastResult && '' !== $lastResult) ? $lastResult : $content;
        return self::parseShortcodes((string) $content);
    }

    public static function excerptEx($content, $widget = null, $lastResult = null)
    {
        $content = (null !== $lastResult && '' !== $lastResult) ? $lastResult : $content;
        return self::stripShortcodes((string) $content);
    }

    public static function renderFromContent($content)
    {
        if (preg_match('/(?<!\[)\[music\b([^\]]*)\]/i', (string) $content, $match)) {
            return self::render($match[1]);
        }

        return '';
    }

    public static function stripShortcodes($content)
    {
        $content = preg_replace('/(?<!\[)\[music\b[^\]]*\]/i', '', (string) $content);
        $content = preg_replace('/\[\/music\]/i', '', (string) $content);
        $content = preg_replace('/\[163\b[^\]]*\]/i', '', (string) $content);
        return $content;
    }

    private static function parseShortcodes($content)
    {
        if (false === strpos($content, '[music') && false === strpos($content, '[163')) {
            return $content;
        }

        $content = preg_replace_callback(
            '/(?<!\[)\[music\b([^\]]*)\]/i',
            function ($match) {
                return self::render($match[1]);
            },
            $content
        );

        $content = preg_replace_callback('/\[163\b([^\]]*)\]/i', function ($match) {
            $attrs = self::parseAttrs($match[1]);
            $id = isset($attrs['id']) ? preg_replace('/\D+/', '', $attrs['id']) : '';

            if ($id === '') {
                return '';
            }

            return self::neteasePlayer($id);
        }, $content);

        return preg_replace('/\[\/music\]/i', '', $content);
    }

    private static function render($attributeText)
    {
        $attrs = self::parseAttrs($attributeText);
        $url = isset($attrs['url']) ? trim($attrs['url']) : '';

        if (!self::isUrl($url)) {
            return '';
        }

        $title = isset($attrs['title']) && $attrs['title'] !== '' ? $attrs['title'] : _t('音乐');
        $artist = isset($attrs['artist']) && $attrs['artist'] !== '' ? $attrs['artist'] : _t('未知艺术家');
        $cover = isset($attrs['cover']) ? trim($attrs['cover']) : '';

        return self::playerHtml($url, $title, $artist, $cover);
    }

    private static function playerHtml($url, $title, $artist, $cover, $kind = 'music', $id = '')
    {
        if (!self::isUrl($url)) {
            return '';
        }

        $title = trim((string) $title) !== '' ? $title : _t('音乐');
        $artist = trim((string) $artist) !== '' ? $artist : _t('未知艺术家');

        if (!self::isUrl($cover)) {
            $cover = self::assetUrl('cover.svg');
        }

        $urlEsc = self::esc($url);
        $titleEsc = self::esc($title);
        $artistEsc = self::esc($artist);
        $coverEsc = self::esc($cover);
        $playLabel = self::esc(_t('播放'));

        return '<!--SLEEK_MUSIC_START-->'
            . '<div class="sleek-music" data-sm-player data-sm-kind="' . self::esc($kind) . '" data-sm-id="' . self::esc((string) $id) . '">'
            . '<audio data-sm-audio preload="none" playsinline src="' . $urlEsc . '"></audio>'
            . '<div class="sm-cover">'
            . '<img src="' . $coverEsc . '" alt="' . $titleEsc . '" loading="lazy">'
            . '<button type="button" class="sm-play" data-sm-toggle aria-label="' . $playLabel . '">'
            . self::icons()
            . '</button>'
            . '</div>'
            . '<div class="sm-main">'
            . '<div class="sm-title">' . $titleEsc . '</div>'
            . '<div class="sm-artist">' . $artistEsc . '</div>'
            . '<input type="range" class="sm-seek" min="0" max="0" value="0" step="0.1" aria-label="' . self::esc(_t('播放进度')) . '">'
            . '<div class="sm-foot">'
            . '<span class="sm-time">0:00 / 0:00</span>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '<!--SLEEK_MUSIC_END-->';
    }

    /**
     * 从内容中取网易云歌曲 ID
     */
    public static function neteaseId($content)
    {
        $content = html_entity_decode((string) $content, ENT_QUOTES, 'UTF-8');

        if (preg_match('/\[163\b[^\]]*?\bid=["\']?(\d+)/i', $content, $match)) {
            return $match[1];
        }

        if (preg_match('/music\.163\.com\/(?:outchain\/player\?[^\s"\'<>]*?\bid=|song\?id=)(\d+)/i', $content, $match)) {
            return $match[1];
        }

        if (preg_match('/music\.163\.com[^\s"\'<>]*?[?&]id=(\d+)/i', $content, $match)) {
            return $match[1];
        }

        return '';
    }

    /**
     * 网易云歌曲信息 + HTTPS 播放代理
     */
    public static function neteaseTrack($id)
    {
        $id = preg_replace('/\D+/', '', (string) $id);

        if ($id === '') {
            return null;
        }

        $cacheDir = __DIR__ . '/cache';
        $cacheFile = $cacheDir . '/163-v2-' . $id . '.json';

        if (is_file($cacheFile) && filemtime($cacheFile) > time() - 21600) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);

            if (is_array($cached) && !empty($cached['url'])) {
                return $cached;
            }
        }

        $track = array(
            'url' => 'https://music.163.com/song/media/outer/url?id=' . $id . '.mp3',
            'title' => _t('网易云音乐'),
            'artist' => '',
            'cover' => self::assetUrl('cover.svg'),
            'id' => $id,
        );

        $json = self::httpGet('https://music.163.com/api/song/detail/?id=' . $id . '&ids=[' . $id . ']');

        if ($json !== '') {
            $data = json_decode($json, true);
            $song = isset($data['songs'][0]) ? $data['songs'][0] : null;

            if ($song) {
                $artists = array();

                if (!empty($song['artists'])) {
                    foreach ($song['artists'] as $artist) {
                        if (!empty($artist['name'])) {
                            $artists[] = $artist['name'];
                        }
                    }
                }

                $track['title'] = !empty($song['name']) ? (string) $song['name'] : $track['title'];
                $track['artist'] = implode(' / ', $artists);
                $track['cover'] = !empty($song['album']['picUrl']) ? (string) $song['album']['picUrl'] : $track['cover'];
            }
        }

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        if (is_dir($cacheDir)) {
            @file_put_contents($cacheFile, json_encode($track, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        }

        return $track;
    }

    public static function neteasePlayer($id)
    {
        $track = self::neteaseTrack($id);

        if (!$track) {
            return '';
        }

        return self::playerHtml($track['url'], $track['title'], $track['artist'], $track['cover'], '163', $id);
    }

    private static function dockHtml($url, $title, $artist, $cover)
    {
        $title = trim($title) !== '' ? $title : _t('音乐');
        $artist = trim($artist) !== '' ? $artist : _t('未知艺术家');

        if (!self::isUrl($cover)) {
            $cover = self::assetUrl('cover.svg');
        }

        $urlEsc = self::esc($url);
        $titleEsc = self::esc($title);
        $artistEsc = self::esc($artist);
        $coverEsc = self::esc($cover);
        $playLabel = self::esc(_t('播放'));

        return '<div class="sleek-music-dock is-expanded" data-sm-dock data-sm-lyrics-url="' . self::esc(self::pluginBase() . '/lyrics.php') . '" hidden>'
            . '<audio data-sm-audio preload="none" playsinline src="' . $urlEsc . '"></audio>'
            . '<div class="sm-dock-row">'
            . '<button type="button" class="sm-dock-cover" data-sm-toggle aria-label="' . $playLabel . '">'
            . '<img src="' . $coverEsc . '" alt="' . $titleEsc . '" loading="lazy">'
            . '</button>'
            . '<div class="sm-dock-main">'
            . '<div class="sm-dock-title">' . $titleEsc . '</div>'
            . '<div class="sm-dock-artist">' . $artistEsc . '</div>'
            . '</div>'
            . '<div class="sm-dock-actions">'
            . '<button type="button" class="sm-dock-nav" data-sm-prev aria-label="' . self::esc(_t('上一首')) . '">' . self::navIcon('prev') . '</button>'
            . '<button type="button" class="sm-dock-play" data-sm-toggle aria-label="' . $playLabel . '">' . self::icons() . '</button>'
            . '<button type="button" class="sm-dock-nav" data-sm-next aria-label="' . self::esc(_t('下一首')) . '">' . self::navIcon('next') . '</button>'
            . '</div>'
            . '</div>'
            . '<div class="sm-dock-foot">'
            . '<input type="range" class="sm-dock-seek" min="0" max="0" value="0" step="0.1" aria-label="' . self::esc(_t('播放进度')) . '">'
            . '<span class="sm-dock-time">0:00 / 0:00</span>'
            . '<button type="button" class="sm-dock-lyrics" data-sm-lyrics-toggle aria-label="' . self::esc(_t('歌词')) . '" title="' . self::esc(_t('歌词')) . '">词</button>'
            . '<button type="button" class="sm-dock-mode" data-sm-mode-toggle data-mode="loop" aria-label="' . self::esc(_t('循环播放')) . '" title="' . self::esc(_t('循环播放')) . '">' . self::modeIcons() . '</button>'
            . '<button type="button" class="sm-dock-list" data-sm-playlist-toggle aria-label="' . self::esc(_t('播放列表')) . '" aria-expanded="false">' . self::listIcon() . '</button>'
            . '<button type="button" class="sm-dock-close" data-sm-close aria-label="' . self::esc(_t('关闭播放器')) . '">×</button>'
            . '</div>'
            . '<div class="sm-lyrics" data-sm-lyrics hidden>'
            . '<div class="sm-lyrics-head"><span>歌词</span><em data-sm-lyrics-song></em></div>'
            . '<div class="sm-lyrics-body">'
            . '<div class="sm-lyric-line" data-sm-lyric="0"></div>'
            . '<div class="sm-lyric-line is-current" data-sm-lyric="1"></div>'
            . '<div class="sm-lyric-line" data-sm-lyric="2"></div>'
            . '<div class="sm-lyric-line" data-sm-lyric="3"></div>'
            . '</div>'
            . '<div class="sm-lyrics-empty" data-sm-lyrics-empty hidden>此歌曲为本地歌曲，暂时无法提供歌词显示。</div>'
            . '</div>'
            . '<div class="sm-playlist" data-sm-playlist hidden></div>'
            . '</div>'
            . '<button type="button" class="sleek-music-reopen" data-sm-reopen aria-label="' . self::esc(_t('打开歌单')) . '"><img class="sm-icon sm-playlist-icon" src="' . self::esc(self::assetUrl('playlist-icon.png')) . '" alt="" width="16" height="16"><span>' . self::esc(_t('歌单')) . '</span></button>';
    }

    private static function modeIcons()
    {
        $loop = '<svg class="sm-mode-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h9V4l4 4-4 4V9H8a3 3 0 0 0-3 3H3a5 5 0 0 1 4-5zm10 10H8v3l-4-4 4-4v3h8a3 3 0 0 0 3-3h2a5 5 0 0 1-4 5z"/></svg>';
        $shuffle = '<svg class="sm-mode-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3l4 4-4 4V8h-2.6l-2 2-1.4-1.4L13.8 6H17V3zM3 6h5.9l9.4 9.4 1.7-1.7V17h-3.3l1.7-1.7L8.9 6H3zm14 7l4 4-4 4v-3h-3.3l1.7-1.7-1.4-1.4L16 15h1v-2zM3 16h5.9l2-2 1.4 1.4-2.4 2.6H3v-2z"/></svg>';

        return '<span class="sm-mode-icon sm-mode-loop">' . $loop . '</span>'
            . '<span class="sm-mode-icon sm-mode-single">' . $loop . '<em>1</em></span>'
            . '<span class="sm-mode-icon sm-mode-shuffle">' . $shuffle . '</span>';
    }

    private static function navIcon($direction)
    {
        if ($direction === 'prev') {
            return '<svg class="sm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5v14L6 12z"/></svg>';
        }

        return '<svg class="sm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5v14l9-7z"/></svg>';
    }

    private static function listIcon()
    {
        return '<svg class="sm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h11v2H4V7zm0 4h11v2H4v-2zm0 4h7v2H4v-2zm13-8v10.2a2.8 2.8 0 1 1-2-2.68V7h2z"/></svg>';
    }

    private static function icons()
    {
        return '<svg class="sm-icon sm-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>'
            . '<svg class="sm-icon sm-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>';
    }

    private static function parseAttrs($text)
    {
        $attrs = array();

        preg_match_all(
            '/([a-zA-Z][\w-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s\]]+))/u',
            (string) $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $value = '';

            if (isset($match[2]) && $match[2] !== '') {
                $value = $match[2];
            } elseif (isset($match[3]) && $match[3] !== '') {
                $value = $match[3];
            } elseif (isset($match[4])) {
                $value = $match[4];
            }

            $attrs[strtolower($match[1])] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        }

        return $attrs;
    }

    private static function setting($key, $default = '')
    {
        static $config = null;
        static $loaded = false;

        if (!$loaded) {
            $loaded = true;

            try {
                $config = \Helper::options()->plugin('SleekMusic');
            } catch (\Typecho\Plugin\Exception $e) {
                $config = null;
            }
        }

        if ($config && isset($config->{$key}) && (string) $config->{$key} !== '') {
            return (string) $config->{$key};
        }

        return $default;
    }

    private static function isUrl($url)
    {
        return is_string($url) && preg_match('#^https?://#i', trim($url));
    }

    private static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function assetUrl($file)
    {
        $base = rtrim((string) \Helper::options()->pluginUrl, '/');
        return $base . '/SleekMusic/assets/' . ltrim($file, '/');
    }

    private static function pluginBase()
    {
        return rtrim((string) \Helper::options()->pluginUrl, '/') . '/SleekMusic';
    }

    private static function httpGet($url)
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HTTPHEADER => array(
                    'Referer: https://music.163.com/',
                    'Accept: application/json,text/plain,*/*',
                ),
            ));
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($status >= 200 && $status < 400 && is_string($body)) {
                return $body;
            }
        }

        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 8,
                'follow_location' => 1,
                'user_agent' => $userAgent,
                'header' => "Referer: https://music.163.com/\r\nAccept: application/json,text/plain,*/*\r\n",
            ),
        ));
        $body = @file_get_contents($url, false, $context);

        return is_string($body) ? $body : '';
    }
}
