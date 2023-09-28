<?php
/**
 * 设置内容登录/回复可见
 *
 * @package Hide
 * @author Echlorine
 * @version 1.0.0
 * @link https://blogs.echocolate.xyz
 * 
 * Version 1.0.0 (2022-09-20)
 * 
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Exception;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Widget\Options;

class Hide_Plugin implements PluginInterface {

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate() {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Hide_Plugin','Answer');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Hide_Plugin','Answer');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Hide_Plugin','Login');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Hide_Plugin','Login');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate() {}

    /**
     * 获取插件配置面板
     *
     * @param Form $form
     */
    public static function config(Form $form) {
        $hideChoose = new Radio('hideChoose', array("open" => "开启功能", "close" => "关闭功能"), "open", _t('是否开启登录/回复可见'));
        $form->addInput($hideChoose);
        $commentShow = new Textarea(
            'commentShow',
            NULL,
            "",
            _t('请绑定按钮点击事件'),
            _t('&lt;a id="comment_show" href="#comments"&gt;回复&lt;/a&gt;')
        );
        $form->addInput($commentShow);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form) {}

    /**
     * 处理缩略内容和feed暴露问题-回复可见
     */
    public static function Answer($con,$obj,$text) {
        $text = empty($text) ? $con : $text;
        if(!$obj->is('single')) {
            $text = preg_replace("/\[hide\](.*?)\[\/hide\]/sm",'此处内容已隐藏',$text);
        }
        return $text;
    }

    /**
     * 处理缩略内容和feed暴露问题-登录
     */
    public static function Login($con, $obj, $text) {
        $text = empty($text) ? $con : $text;
        if(!$obj->is('single')) {
            $text = preg_replace("/\[login\](.*?)\[\/login\]/sm",'此处内容已隐藏',$text);
        }
        return $text;
    }

    public static function commentClick() {
        $hideSet = Options::alloc()->plugin('Hide');
        $hideChoose = $hideSet->hideChoose;
        $commentShow = $hideSet->commentShow;
        if ($hideChoose == 'open') {
            print <<<EOT
            <!-- 显示评论事件 -->
            $commentShow
            EOT;
        }
    }

    /**
     * 插件实现方法
     *
     * @access public
     */
    public static function parse_content($content, $cid, $mail, $login, $loginUrl){
        $hideSet = Options::alloc()->plugin('Hide');
        $hideChoose = $hideSet->hideChoose;
        // 隐藏不可见内容
        $db = Typecho_Db::get();
        $query = $db->select()->from('table.comments')
        ->where('cid = ?',$cid)
        ->where('mail = ?', $mail)
        ->where('status = ?', 'approved')
        ->limit(1);
        $result = $db->fetchAll($query);
        $answerStyle = <<<EOT
        <div class="reply2view" style="font-weight:bolder; color:#336699;">您需要<a id="comment_show" href="#comments">回复</a>才能显示此处隐藏内容。</div>
        EOT;
        $loginStyle = <<<EOT
        <div class="reply2view" style="font-weight:bolder; color:#336699;">您需要<a href="{$loginUrl}">登录</a>才能显示此处隐藏内容。</div>
        EOT;
        // 评论可见
        if($hideChoose == 'close' || $login || $result) {
            $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm",'$1',$content);
        }
        else{
            $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm",$answerStyle,$content);
        }
        // 登录可见
        if($hideChoose == 'close' || $login) {
            $content = preg_replace("/\[login\](.*?)\[\/login\]/sm",'$1',$content);
        }
        else{
            $content = preg_replace("/\[login\](.*?)\[\/login\]/sm",$loginStyle,$content);
        }
        return $content;
    }
}
