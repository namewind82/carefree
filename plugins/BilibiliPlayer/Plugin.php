<?php
/**
 * Bilibili 播放器
 *
 * 将 Bilibili 官方 iframe 播放器包装为响应式卡片，适配 Carefree 主题。
 *
 * @package BilibiliPlayer
 * @author  王叨叨
 * @version 1.1.1
 * @link    https://wangdaodao.com
 */

namespace TypechoPlugin\BilibiliPlayer;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Select;
use Typecho\Widget\Helper\Form\Element\Text;

class Plugin implements PluginInterface
{
    public static function activate()
    {
        \Typecho\Plugin::factory('Widget\Base\Contents')->contentEx = __CLASS__ . '::replacePlayer';
        \Typecho\Plugin::factory('Widget\Base\Contents')->excerptEx = __CLASS__ . '::replacePlayer';
        \Typecho\Plugin::factory('Widget_Abstract_Contents')->contentEx = __CLASS__ . '::replacePlayer';
        \Typecho\Plugin::factory('Widget_Abstract_Contents')->excerptEx = __CLASS__ . '::replacePlayer';

        return _t('插件已激活，Bilibili 播放器会以响应式卡片显示');
    }

    public static function deactivate()
    {
        return _t('插件已禁用，Bilibili 播放器恢复原始 iframe');
    }

    public static function config(Form $form)
    {
        $maxWidth = new Text(
            'maxWidth',
            null,
            '480',
            _t('播放器最大宽度'),
            _t('单位 px，建议 420–560；默认与首页视频播放器一致。')
        );
        $form->addInput($maxWidth);

        $ratio = new Select(
            'ratio',
            array(
                '16:9' => _t('16:9（推荐）'),
                '4:3' => _t('4:3'),
            ),
            '16:9',
            _t('播放器比例'),
            _t('跟随视频比例选择，默认 16:9。')
        );
        $form->addInput($ratio);

        $autoplay = new Radio(
            'autoplay',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '0',
            _t('自动播放'),
            _t('大多数移动浏览器会阻止带声音的自动播放。')
        );
        $form->addInput($autoplay);

        $danmaku = new Radio(
            'danmaku',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '1',
            _t('默认弹幕'),
            _t('控制播放器默认是否开启弹幕。')
        );
        $form->addInput($danmaku);

        $muted = new Radio(
            'muted',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '0',
            _t('默认静音'),
            _t('开启后播放器默认静音。')
        );
        $form->addInput($muted);

        $poster = new Radio(
            'poster',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '1',
            _t('显示封面'),
            _t('播放前显示 Bilibili 视频封面。')
        );
        $form->addInput($poster);

        $highQuality = new Radio(
            'highQuality',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '1',
            _t('优先高清'),
            _t('向播放器请求高清画质。')
        );
        $form->addInput($highQuality);

        $hideCoverInfo = new Radio(
            'hideCoverInfo',
            array('0' => _t('显示'), '1' => _t('隐藏')),
            '0',
            _t('封面下方信息'),
            _t('隐藏播放量、弹幕量等封面信息。')
        );
        $form->addInput($hideCoverInfo);

        $hasMuteButton = new Radio(
            'hasMuteButton',
            array('0' => _t('不显示'), '1' => _t('显示')),
            '0',
            _t('一键静音按钮'),
            _t('是否在播放器内显示一键静音按钮。')
        );
        $form->addInput($hasMuteButton);

        $hideDanmakuButton = new Radio(
            'hideDanmakuButton',
            array('0' => _t('不隐藏'), '1' => _t('隐藏')),
            '0',
            _t('隐藏弹幕按钮'),
            _t('隐藏播放器内的弹幕开关按钮。')
        );
        $form->addInput($hideDanmakuButton);

        $noFullScreenButton = new Radio(
            'noFullScreenButton',
            array('0' => _t('显示'), '1' => _t('隐藏')),
            '0',
            _t('隐藏全屏按钮'),
            _t('隐藏播放器内的全屏按钮。')
        );
        $form->addInput($noFullScreenButton);

        $fjw = new Radio(
            'fjw',
            array('0' => _t('关闭'), '1' => _t('开启')),
            '1',
            _t('记忆播放'),
            _t('开启后播放器会记住上次播放位置。')
        );
        $form->addInput($fjw);
    }

    public static function personalConfig(Form $form)
    {
    }

    public static function replacePlayer($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;

        if (strpos($content, 'player.bilibili.com/player.html') === false) {
            return $content;
        }

        $options = \Helper::options();
        $config = $options->plugin('BilibiliPlayer');

        if (!$config) {
            return $content;
        }

        $settings = array(
            'maxWidth' => $config->maxWidth ?? '480',
            'ratio' => $config->ratio ?? '16:9',
            'autoplay' => $config->autoplay ?: '0',
            'danmaku' => $config->danmaku ?: '1',
            'muted' => $config->muted ?: '0',
            'poster' => $config->poster ?: '1',
            'highQuality' => $config->highQuality ?? '1',
            'hideCoverInfo' => $config->hideCoverInfo ?: '0',
            'hasMuteButton' => $config->hasMuteButton ?: '0',
            'hideDanmakuButton' => $config->hideDanmakuButton ?: '0',
            'noFullScreenButton' => $config->noFullScreenButton ?: '0',
            'fjw' => $config->fjw ?: '1',
        );

        return self::renderPlayerHTML($content, $settings);
    }

    /**
     * 将内容中的 Bilibili iframe 替换为主题风格播放器。
     *
     * @param string $content
     * @param array  $settings
     * @return string
     */
    public static function renderPlayerHTML($content, $settings = array())
    {
        $settings = array_merge(array(
            'maxWidth' => '480',
            'ratio' => '16:9',
            'autoplay' => '0',
            'danmaku' => '1',
            'muted' => '0',
            'poster' => '1',
            'highQuality' => '1',
            'hideCoverInfo' => '0',
            'hasMuteButton' => '0',
            'hideDanmakuButton' => '0',
            'noFullScreenButton' => '0',
            'fjw' => '1',
        ), $settings);

        $maxWidth = max(280, min(1200, (int) $settings['maxWidth']));
        $ratioClass = $settings['ratio'] === '4:3' ? ' is-ratio-4x3' : '';
        $pattern = '/<iframe\b[^>]*\bsrc\s*=\s*(["\'])(?:https?:)?\/\/player\.bilibili\.com\/player\.html([^"\']*)\1[^>]*>\s*<\/iframe>/is';

        $content = preg_replace_callback($pattern, function ($matches) use ($settings, $maxWidth, $ratioClass) {
            $query = html_entity_decode(trim((string) $matches[2]), ENT_QUOTES, 'UTF-8');
            $query = ltrim($query, '?');
            $params = array();
            parse_str($query, $params);

            foreach (array(
                'autoplay', 'danmaku', 'muted', 'poster', 'high_quality',
                'hideCoverInfo', 'hasMuteButton', 'hideDanmakuButton',
                'noFullScreenButton', 'fjw',
            ) as $key) {
                unset($params[$key]);
            }

            $params['autoplay'] = $settings['autoplay'] === '1' ? '1' : '0';
            $params['danmaku'] = $settings['danmaku'] === '1' ? '1' : '0';
            $params['muted'] = $settings['muted'] === '1' ? '1' : '0';
            $params['poster'] = $settings['poster'] === '1' ? '1' : '0';
            $params['high_quality'] = $settings['highQuality'] === '1' ? '1' : '0';
            $params['hideCoverInfo'] = $settings['hideCoverInfo'] === '1' ? '1' : '0';
            $params['hasMuteButton'] = $settings['hasMuteButton'] === '1' ? '1' : '0';
            $params['hideDanmakuButton'] = $settings['hideDanmakuButton'] === '1' ? '1' : '0';
            $params['noFullScreenButton'] = $settings['noFullScreenButton'] === '1' ? '1' : '0';
            $params['fjw'] = $settings['fjw'] === '1' ? '1' : '0';

            $src = 'https://player.bilibili.com/player.html?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

            return '<span class="bili-player' . $ratioClass . '" style="--bili-max:' . $maxWidth . 'px" data-bili-player>'
                . '<span class="bili-player-frame">'
                . '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
                . ' title="Bilibili 视频" loading="lazy" allowfullscreen="true"'
                . ' frameborder="0" scrolling="no"'
                . ' allow="autoplay; fullscreen; picture-in-picture"'
                . ' referrerpolicy="no-referrer-when-downgrade"></iframe>'
                . '</span></span>';
        }, $content);

        return $content;
    }

}

if (!class_exists('BilibiliPlayer_Plugin', false)) {
    class_alias(Plugin::class, 'BilibiliPlayer_Plugin');
}
