<?php
/**
 * SleekSticky — 文章置顶
 *
 * 在撰写文章页添加「置顶文章」选项，并在首页/列表页优先显示置顶文章。
 *
 * @package SleekSticky
 * @author  心斋
 * @version 1.0.3
 * @link    https://www.xinzhai.work/
 */

namespace TypechoPlugin\SleekSticky;

use Typecho\Db;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;

class Plugin implements PluginInterface
{
    const VERSION = '1.0.3';

    public static function activate()
    {
        \Typecho\Plugin::factory('Widget\Archive')->select = __CLASS__ . '::archiveSelect';
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = __CLASS__ . '::writePost';
        \Typecho\Plugin::factory('Widget\Contents\Post\Edit')->finishPublish = __CLASS__ . '::finishPublish';
        \Typecho\Plugin::factory('Widget\Contents\Post\Edit')->finishSave = __CLASS__ . '::finishSave';

        return _t('SleekSticky 已启用：文章可设置置顶。');
    }

    public static function deactivate()
    {
        return _t('SleekSticky 已停用。');
    }

    public static function config(Form $form)
    {
    }

    public static function personalConfig(Form $form)
    {
    }

    /**
     * 首页/列表查询时，置顶文章优先
     */
    public static function archiveSelect($archive)
    {
        $select = $archive->select('table.contents.*');

        return $select->order('table.contents.order', Db::SORT_DESC)->order('table.contents.created', Db::SORT_DESC);
    }

    /**
     * 撰写文章页的置顶选项
     */
    public static function writePost($post = null)
    {
        $cid = $post && isset($post->cid) ? (int) $post->cid : 0;
        $order = 0;

        if ($cid > 0) {
            try {
                $db = Db::get();
                $row = $db->fetchRow(
                    $db->select()->from('table.contents')->where('cid = ?', $cid)
                );

                if ($row && isset($row['order'])) {
                    $order = (int) $row['order'];
                }
            } catch (\Throwable $e) {
                $order = 0;
            }
        }

        echo '<style>
            .sleek-sticky-box {
                margin: 0 0 12px;
                padding: 10px 12px;
                border: 1px solid #e2e8f0;
                border-left: 3px solid #4a90e2;
                border-radius: 8px;
                background: #f8fbff;
                font: 13px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", sans-serif;
            }
            .sleek-sticky-box label {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #273449;
                font-weight: 600;
                cursor: pointer;
            }
            .sleek-sticky-box input {
                width: 15px;
                height: 15px;
                margin: 0;
                accent-color: #4a90e2;
            }
            .sleek-sticky-box p {
                margin: 4px 0 0 23px;
                color: #8b96a5;
                font-size: 11px;
            }
        </style>'
            . '<div class="sleek-sticky-box" id="sleek-sticky-box">'
            . '<label><input type="checkbox" name="sleek_sticky" value="1"' . ($order > 0 ? ' checked' : '') . '>置顶文章</label>'
            . '<p>置顶后优先显示在首页和文章列表最前面。</p>'
            . '</div>'
            . '<script>(function(){var box=document.getElementById("sleek-sticky-box");var text=document.getElementById("text");if(box&&text&&text.parentNode){text.parentNode.insertBefore(box,text);}})();</script>'
        . '<script>(function(){function hideInternalFields(){var section=document.getElementById("custom-field");if(!section){return;}var rows=section.querySelectorAll("tr");Array.prototype.forEach.call(rows,function(row){var input=row.querySelector("input[name=\"fieldNames[]\"]");if(!input){return;}var name=(input.value||"").trim();if(name==="sleek_likes"||name==="sleek_views"){row.hidden=true;row.style.display="none";}});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",hideInternalFields);}else{hideInternalFields();}var section=document.getElementById("custom-field");if(section&&window.MutationObserver){new MutationObserver(hideInternalFields).observe(section,{childList:true,subtree:true});}})();</script>';
    }

    public static function finishPublish($contents, $post)
    {
        self::saveSticky($post);
    }

    public static function finishSave($contents, $post)
    {
        self::saveSticky($post);
    }

    private static function saveSticky($post)
    {
        $cid = $post && isset($post->cid) ? (int) $post->cid : 0;

        if ($cid <= 0) {
            return;
        }

        $order = isset($_POST['sleek_sticky']) && (string) $_POST['sleek_sticky'] === '1' ? 1 : 0;

        try {
            $db = Db::get();
            $db->query(
                $db->update('table.contents')
                    ->rows(array('order' => $order))
                    ->where('cid = ?', $cid)
            );
        } catch (\Throwable $e) {
            // 保存失败时不阻断文章发布
        }
    }
}
