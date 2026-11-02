<?php
/**
 * SleekDouban — 豆瓣电影与书籍
 *
 * 自动创建「豆瓣」页面，支持展示豆瓣用户的电影/书籍收藏；
 * 未配置用户 ID 时展示豆瓣热门电影与最新书籍。
 * 配置入口：后台 → 插件 → SleekDouban → 设置。
 *
 * @package SleekDouban
 * @author  心斋
 * @version 1.1.0
 * @link    https://www.xinzhai.work/
 */

namespace TypechoPlugin\SleekDouban;

use Typecho\Db;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;

class Plugin implements PluginInterface
{
    const VERSION = '1.1.0';

    public static function activate()
    {
        self::ensureConfig();
        self::ensurePage();

        return _t('SleekDouban 已启用：已创建/更新「豆瓣」页面。配置入口：后台 → 插件 → SleekDouban → 设置。');
    }

    public static function deactivate()
    {
        return _t('SleekDouban 已停用，豆瓣页面保留。');
    }

    public static function config(Form $form)
    {
        $form->addInput(new Text(
            'doubanId',
            null,
            '',
            _t('豆瓣用户 ID'),
            _t('填写个人主页中的 ID，例如 https://www.douban.com/people/xxxx/ 中的 xxxx。留空则显示豆瓣热门内容。')
        ));

        $form->addInput(new Text(
            'limit',
            null,
            '12',
            _t('每类显示数量'),
            _t('电影、书籍各显示多少条，建议 8–16。')
        ));

        $form->addInput(new Text(
            'cacheMinutes',
            null,
            '360',
            _t('缓存分钟数'),
            _t('豆瓣数据缓存时间，默认 360 分钟。')
        ));
    }

    public static function personalConfig(Form $form)
    {
    }

    /**
     * 确保 Typecho 插件配置行存在
     *
     * Typecho 的 Options::plugin() 在配置行不存在时会直接抛出
     * 「插件 xxx 的配置信息没有找到」，所以启用时必须写入默认配置。
     */
    private static function ensureConfig()
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        $ensured = true;

        try {
            $db = Db::get();
            $row = $db->fetchRow(
                $db->select('value')->from('table.options')
                    ->where('name = ?', 'plugin:SleekDouban')
                    ->where('user = ?', 0)
                    ->limit(1)
            );
            $defaults = array(
                'doubanId' => '',
                'limit' => '12',
                'cacheMinutes' => '360',
            );

            if (!$row) {
                $db->query(
                    $db->insert('table.options')->rows(array(
                        'name' => 'plugin:SleekDouban',
                        'value' => json_encode($defaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'user' => 0,
                    ))
                );
            } else {
                $raw = (string) $row['value'];
                $value = json_decode($raw, true);
                $isJson = is_array($value);

                if (!$isJson) {
                    $value = @unserialize($raw);
                }

                if (!is_array($value)) {
                    $value = array();
                }

                $merged = array_merge($defaults, $value);

                if (!$isJson || $merged !== $value) {
                    $encoded = json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $db->query(
                        $db->update('table.options')
                            ->rows(array('value' => $encoded))
                            ->where('name = ?', 'plugin:SleekDouban')
                            ->where('user = ?', 0)
                    );
                }
            }
        } catch (\Throwable $e) {
            // 配置行写入失败时不影响插件其他功能
        }
    }

    /**
     * 自动创建豆瓣页面
     */
    private static function ensurePage()
    {
        try {
            $db = Db::get();
            $row = $db->fetchRow(
                $db->select('cid')->from('table.contents')
                    ->where('slug = ?', 'douban')
                    ->where('type = ?', 'page')
                    ->limit(1)
            );

            if ($row) {
                $db->query(
                    $db->update('table.contents')
                        ->rows(array('template' => 'page-douban.php'))
                        ->where('cid = ?', $row['cid'])
                );

                return;
            }

            $author = $db->fetchRow(
                $db->select('uid')->from('table.users')->limit(1)
            );
            $authorId = $author ? (int) $author['uid'] : 1;
            $now = time();

            $db->query(
                $db->insert('table.contents')->rows(array(
                    'title' => '豆瓣',
                    'slug' => 'douban',
                    'created' => $now,
                    'modified' => $now,
                    'text' => '<!--markdown-->豆瓣电影与书籍。',
                    'order' => 0,
                    'authorId' => $authorId,
                    'template' => 'page-douban.php',
                    'type' => 'page',
                    'status' => 'publish',
                    'password' => null,
                    'commentsNum' => 0,
                    'allowComment' => '0',
                    'allowPing' => '0',
                    'allowFeed' => '1',
                    'parent' => 0,
                ))
            );
        } catch (\Throwable $e) {
            // 页面创建失败时不影响插件启用
        }
    }

    public static function assetUrl($file)
    {
        return rtrim((string) \Helper::options()->pluginUrl, '/') . '/SleekDouban/assets/' . ltrim($file, '/');
    }

    public static function imageUrl($url)
    {
        if ($url === '') {
            return '';
        }

        return self::assetUrl('img.php') . '?url=' . urlencode($url);
    }

    /**
     * 清除豆瓣页面缓存
     */
    public static function clearPageCache()
    {
        foreach (glob(__DIR__ . '/cache/page-*.json') ?: array() as $file) {
            @unlink($file);
        }
    }

    /**
     * 保存插件设置后清除缓存
     */
    public static function configCheck($settings)
    {
        self::ensureConfig();
        self::clearPageCache();

        return null;
    }

    /**
     * 页面数据
     */
    public static function pageData()
    {
        self::ensureConfig();

        $forceRefresh = isset($_GET['douban_refresh']) && $_GET['douban_refresh'] === '1';

        if ($forceRefresh) {
            self::clearPageCache();
        }

        $rawUserId = trim((string) self::setting('doubanId', ''));
        $userId = self::normalizeDoubanId($rawUserId);
        $limit = max(4, min(24, (int) self::setting('limit', '12')));
        $ttl = max(5, (int) self::setting('cacheMinutes', '360')) * 60;
        $cacheFile = __DIR__ . '/cache/page-' . md5($userId . '|' . $limit . '|collections-v2') . '.json';

        if (!$forceRefresh && is_file($cacheFile) && filemtime($cacheFile) > time() - $ttl) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);

            if (is_array($cached) && isset($cached['movies']) && isset($cached['books'])) {
                return $cached;
            }
        }

        $movies = array();
        $books = array();
        $userName = '';
        $movieSource = 'hot';
        $bookSource = 'hot';

        if ($userId !== '') {
            $userItems = self::fetchUserInterests($userId, $limit);
            $movies = $userItems['movies'];
            $books = $userItems['books'];
            $userName = isset($userItems['name']) ? $userItems['name'] : '';
            $movieSource = 'user';
            $bookSource = 'user';
        } else {
            $movies = self::fetchHotMovies($limit);
            $books = self::fetchLatestBooks($limit);
        }

        $data = array(
            'movies' => $movies,
            'books' => $books,
            'movieSource' => $movieSource,
            'bookSource' => $bookSource,
            'userId' => $userId,
            'userName' => $userName,
            'userEmpty' => $userId !== '' && empty($movies) && empty($books),
            'updated' => time(),
        );

        $dir = __DIR__ . '/cache';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (is_dir($dir)) {
            @file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        }

        return $data;
    }

    private static function fetchUserCollections($userId, $limit)
    {
        $result = array('movies' => array(), 'books' => array());
        $movieUrl = 'https://movie.douban.com/people/' . rawurlencode($userId) . '/collect?start=0&sort=time&rating=all&filter=all&mode=grid';
        $bookUrl = 'https://book.douban.com/people/' . rawurlencode($userId) . '/collect?start=0&sort=time&rating=all&filter=all&mode=grid';
        $movieHtml = self::httpGet($movieUrl, 'https://movie.douban.com/people/' . rawurlencode($userId) . '/');
        $bookHtml = self::httpGet($bookUrl, 'https://book.douban.com/people/' . rawurlencode($userId) . '/');

        if ($movieHtml !== '' && class_exists('DOMDocument')) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            if (@$dom->loadHTML('<?xml encoding="UTF-8">' . $movieHtml)) {
                $xpath = new \DOMXPath($dom);
                $nodes = $xpath->query('//div[contains(@class, "comment-item")]');
                $seen = array();

                foreach ($nodes as $node) {
                    if (count($result['movies']) >= $limit) {
                        break;
                    }

                    $linkNode = $xpath->query('.//div[contains(@class, "pic")]//a', $node)->item(0);
                    $imageNode = $xpath->query('.//div[contains(@class, "pic")]//img', $node)->item(0);
                    $titleNode = $xpath->query('.//li[contains(@class, "title")]//em', $node)->item(0);

                    if (!$titleNode) {
                        $titleNode = $xpath->query('.//li[contains(@class, "title")]//a', $node)->item(0);
                    }

                    $introNode = $xpath->query('.//li[contains(@class, "intro")]', $node)->item(0);

                    if (!$linkNode || !$titleNode) {
                        continue;
                    }

                    $link = trim((string) $linkNode->getAttribute('href'));
                    $title = trim(preg_replace('/\s+/u', ' ', (string) $titleNode->textContent));
                    $title = trim((string) preg_split('/\s+\/\s+/u', $title)[0]);
                    $meta = $introNode ? trim(preg_replace('/\s+/u', ' ', (string) $introNode->textContent)) : '豆瓣电影';

                    if ($link === '' || isset($seen[$link])) {
                        continue;
                    }

                    $seen[$link] = true;
                    $result['movies'][] = array(
                        'title' => $title,
                        'url' => $link,
                        'cover' => $imageNode ? trim((string) $imageNode->getAttribute('src')) : '',
                        'meta' => mb_substr($meta, 0, 64, 'UTF-8'),
                        'rating' => '',
                    );
                }
            }
        }

        if ($bookHtml !== '' && class_exists('DOMDocument')) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            if (@$dom->loadHTML('<?xml encoding="UTF-8">' . $bookHtml)) {
                $xpath = new \DOMXPath($dom);
                $nodes = $xpath->query('//li[contains(@class, "subject-item")]');
                $seen = array();

                foreach ($nodes as $node) {
                    if (count($result['books']) >= $limit) {
                        break;
                    }

                    $linkNode = $xpath->query('.//div[contains(@class, "pic")]//a', $node)->item(0);
                    $imageNode = $xpath->query('.//div[contains(@class, "pic")]//img', $node)->item(0);
                    $titleNode = $xpath->query('.//h2//a', $node)->item(0);
                    $pubNode = $xpath->query('.//div[contains(@class, "pub")]', $node)->item(0);
                    $ratingNode = $xpath->query('.//span[contains(@class, "rating_nums")]', $node)->item(0);

                    if (!$linkNode || !$titleNode) {
                        continue;
                    }

                    $link = trim((string) $linkNode->getAttribute('href'));

                    if ($link === '' || isset($seen[$link])) {
                        continue;
                    }

                    $seen[$link] = true;
                    $result['books'][] = array(
                        'title' => trim((string) $titleNode->textContent),
                        'url' => $link,
                        'cover' => $imageNode ? trim((string) $imageNode->getAttribute('src')) : '',
                        'meta' => $pubNode ? mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $pubNode->textContent)), 0, 64, 'UTF-8') : '豆瓣读书',
                        'rating' => $ratingNode ? trim((string) $ratingNode->textContent) : '',
                    );
                }
            }
        }

        return $result;
    }

    private static function normalizeDoubanId($value)
    {
        $value = trim((string) $value);
        $decoded = urldecode($value);

        if (preg_match('~douban\.com/people/([^/?#]+)~i', $decoded, $match)) {
            return trim($match[1]);
        }

        return trim($decoded, '/ ');
    }

    private static function fetchUserInterests($userId, $limit)
    {
        $result = array('movies' => array(), 'books' => array(), 'name' => '');
        $collections = self::fetchUserCollections($userId, $limit);

        if (!empty($collections['movies'])) {
            $result['movies'] = $collections['movies'];
        }

        if (!empty($collections['books'])) {
            $result['books'] = $collections['books'];
        }

        $useRssMovies = empty($collections['movies']);
        $useRssBooks = empty($collections['books']);

        $url = 'https://www.douban.com/feed/people/' . rawurlencode($userId) . '/interests';
        $xmlText = self::httpGet($url, 'https://www.douban.com/people/' . rawurlencode($userId) . '/');

        if ($xmlText === '') {
            return $result;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlText, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);

        if (!$xml || !isset($xml->channel)) {
            return $result;
        }

        $channelTitle = trim((string) $xml->channel->title);
        $result['name'] = trim(preg_replace('/\s*的收藏$/u', '', $channelTitle));

        if (!isset($xml->channel->item)) {
            return $result;
        }

        $seen = array();

        foreach ($xml->channel->item as $item) {
            $link = trim((string) $item->link);
            $title = trim((string) $item->title);
            $description = (string) $item->description;
            $kind = '';

            if (strpos($link, 'movie.douban.com') !== false) {
                $kind = 'movie';
            } elseif (strpos($link, 'book.douban.com') !== false) {
                $kind = 'book';
            } else {
                continue;
            }

            if (isset($seen[$link])) {
                continue;
            }

            $seen[$link] = true;
            $title = preg_replace('/^(最近)?(看过|在看|想看|读过|在读|想读|听过|在听|想听)\s*/u', '', $title);
            $title = trim((string) $title) !== '' ? trim((string) $title) : '豆瓣条目';
            $cover = '';
            $note = '';
            $rating = '';

            if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $description, $match)) {
                $cover = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
            }

            if (preg_match('/<p>\s*备注\s*[:：]\s*(.*?)<\/p>/isu', $description, $match)) {
                $note = trim(strip_tags(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')));
            }

            if (preg_match('/<p>\s*推荐\s*[:：]\s*(.*?)<\/p>/isu', $description, $match)) {
                $rating = trim(strip_tags(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')));
            }

            $metaParts = array();

            if ($rating !== '') {
                $metaParts[] = $rating;
            }

            if ($note !== '') {
                $metaParts[] = $note;
            }

            $entry = array(
                'title' => $title,
                'url' => $link,
                'cover' => $cover,
                'meta' => implode(' · ', $metaParts),
                'rating' => $rating,
            );

            if ($kind === 'movie' && $useRssMovies && count($result['movies']) < $limit) {
                $result['movies'][] = $entry;
            } elseif ($kind === 'book' && $useRssBooks && count($result['books']) < $limit) {
                $result['books'][] = $entry;
            }
        }

        if ($result['name'] === '') {
            $profile = self::httpGet('https://www.douban.com/people/' . rawurlencode($userId) . '/', 'https://www.douban.com/');

            if (preg_match('/<title>\s*(.*?)<\/title>/isu', $profile, $match)) {
                $name = trim(strip_tags(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')));
                $name = preg_replace('/\s*的豆瓣.*$/u', '', $name);
                $result['name'] = trim((string) $name);
            }
        }

        return $result;
    }

    private static function fetchHotMovies($limit)
    {
        $url = 'https://movie.douban.com/j/search_subjects?type=movie&tag=' . rawurlencode('热门') . '&page_limit=' . $limit . '&page_start=0';
        $json = self::httpGet($url, 'https://movie.douban.com/');
        $data = json_decode($json, true);
        $movies = array();

        if (!empty($data['subjects']) && is_array($data['subjects'])) {
            foreach ($data['subjects'] as $subject) {
                $movies[] = array(
                    'title' => (string) $subject['title'],
                    'url' => (string) $subject['url'],
                    'cover' => (string) $subject['cover'],
                    'meta' => !empty($subject['rate']) ? '豆瓣评分 ' . $subject['rate'] : '豆瓣热门电影',
                    'rating' => !empty($subject['rate']) ? (string) $subject['rate'] : '',
                );
            }
        }

        return $movies;
    }

    private static function fetchLatestBooks($limit)
    {
        $html = self::httpGet('https://book.douban.com/latest?subcat=all', 'https://book.douban.com/');
        $books = array();

        if ($html === '' || !class_exists('DOMDocument')) {
            return $books;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html);

        if (!$loaded) {
            return $books;
        }

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//li[contains(@class, "media")]');
        $seen = array();

        if (!$nodes) {
            return $books;
        }

        foreach ($nodes as $node) {
            if (count($books) >= $limit) {
                break;
            }

            $linkNode = $xpath->query('.//div[contains(@class, "media__img")]//a', $node)->item(0);
            $imageNode = $xpath->query('.//div[contains(@class, "media__img")]//img', $node)->item(0);
            $titleNode = $xpath->query('.//div[contains(@class, "media__body")]//h2//a', $node)->item(0);
            $pubNode = $xpath->query('.//p[contains(@class, "subject-abstract")]', $node)->item(0);

            if (!$linkNode || !$titleNode) {
                continue;
            }

            $link = trim((string) $linkNode->getAttribute('href'));

            if ($link === '' || strpos($link, 'book.douban.com/subject/') === false || isset($seen[$link])) {
                continue;
            }

            $seen[$link] = true;
            $meta = $pubNode ? trim(preg_replace('/\s+/u', ' ', (string) $pubNode->textContent)) : '豆瓣新书速递';

            $books[] = array(
                'title' => trim((string) $titleNode->textContent),
                'url' => $link,
                'cover' => $imageNode ? trim((string) $imageNode->getAttribute('src')) : '',
                'meta' => $meta,
                'rating' => '',
            );
        }

        return $books;
    }

    private static function httpGet($url, $referer = 'https://www.douban.com/')
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36';
        $headers = array(
            'Referer: ' . $referer,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
        );

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => $ua,
                CURLOPT_HTTPHEADER => $headers,
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
                'timeout' => 15,
                'follow_location' => 1,
                'user_agent' => $ua,
                'header' => implode("\r\n", $headers) . "\r\n",
            ),
        ));
        $body = @file_get_contents($url, false, $context);

        return is_string($body) ? $body : '';
    }

    private static function setting($key, $default = '')
    {
        static $config = null;
        static $loaded = false;

        if (!$loaded) {
            $loaded = true;

            try {
                $config = \Helper::options()->plugin('SleekDouban');
            } catch (\Throwable $e) {
                $config = null;
            }
        }

        if ($config && isset($config->{$key}) && (string) $config->{$key} !== '') {
            return (string) $config->{$key};
        }

        return $default;
    }
}
