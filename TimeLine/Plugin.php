<?php

/**
 * 配合自定义字段功能实现部分文章按照时间线展示
 *
 * @package TimeLine
 * @author Echlorine
 * @version 1.0.0
 * @link https://blogs.echocolate.xyz
 * 
 * Version 1.0.0 (2023-01-20)
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
use Widget\Options;

class TimeLine_Plugin implements PluginInterface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('TimeLine_Plugin', 'hide');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('TimeLine_Plugin', 'hide');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form
     */
    public static function config(Form $form)
    {
        $choose = new Radio('choose', array("open" => "开启功能", "close" => "关闭功能"), "open", _t('是否开启时间线功能'));
        $form->addInput($choose);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    public static function hide($con, $obj, $text)
    {
        $text = empty($text) ? $con : $text;
        if (!$obj->is('single')) {
            if (self::isTimeLine($obj->categories)) {
                $text = htmlspecialchars("此为<" . $obj->title . ">日志的时间轴页面，请点击日志标题查看详情。");
            }
        }
        return $text;
    }

    /**
     * 判断是否为时间线分类的文章
     */
    public static function isTimeLine($categories)
    {
        $options = Options::alloc()->plugin('TimeLine');
        if (($options->choose == "close")) {
            return false;
        }
        for ($i = 0; $i < count($categories); $i++) {
            if (strcmp($categories[$i]["slug"], "timeline") == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 渲染 HTML 文本
     */
    public static function print_html($object, $siteUrl)
    {
        self::print_header($object->title, $object->permalink, $siteUrl, $object->author->name, $object->author->permalink);
        self::print_main($object->content);
        self::print_css();
    }

    private static function print_header($title, $titlePermalink, $siteUrl, $name, $namePermalink)
    {
        echo <<<EOF
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>$title</title>
</head>

<body>
    <h1 id="title"><a href="$titlePermalink">$title</a></h1>
    <div id="nav">
        <ul class="clearfix" id="nav_menu">
            <li><a href="$siteUrl">Home</a></li>
            <li><a href="$namePermalink">$name</a></li>
        </ul>
    </div>
EOF;
    }

    private static function print_main($content)
    {
        $content = preg_replace("/<p>(.*?)<\/p>/sm", '$1', $content);
        // echo $content;
        $lines = explode('<br>', $content);
        $main = <<<EOF
    <div id="main">
        <div id="line"></div>
        <div id="coos">
EOF;
        $colors = array("yellow", "pink", "black", "green", "blue", "purple");
        //打乱颜色
        shuffle($colors);
        $i = 0;
        foreach ($lines as $line) {
            $main .= self::parse_line($line, $colors[$i % 6]);
            $i++;
        }
        $main .= <<<EOF
        </div>
    </div>
</body>

</html>
EOF;
        echo $main;
    }

    private static function parse_line($line, $color)
    {
        $event = explode('|', $line);
        $date = $event[0];
        if (count($event) > 2 && strlen($event[2]) > 0) {
            $date = "<a href=\"" . preg_replace("/.*\"(.*?)\".*/sm", '$1', $event[2]) . "\" target=\"_blank\"># $event[0]</a>";
        }
        $div = <<<EOF
            <div class="lis">
                <div class="spot"></div>
                <div class="ke">
                    <div class="g-lin"></div>
                    <div class="item $color">
                        <h2 class="tag">$date</h2>
                        <div class="des">$event[1]</div>
                    </div>
                </div>
            </div>
EOF;
        return $div;
    }

    private static function print_css()
    {
        echo <<<EOF
<style type="text/css">
    * {
        margin: 0;
        padding: 0;
        font-family: "微软雅黑";
        box-sizing: border-box;
        font-size: 16px;
    }

    /*body {
        background:-webkit-linear-gradient(right,#BE93C5,#7BC6CC);
        background:linear-gradient(to left,#BE93C5,#7BC6CC)
    }
    */

    body {
        background-color: #f6f7f8;
        font-family: Microsoft Yahei, "微软雅黑", "Helvetica Neue", Helvetica, Hiragino Sans GB, WenQuanYi Micro Hei, sans-serif;
    }

    #title {
        text-align: center;
        padding: 40px 0;
        letter-spacing: 2px
    }

    #title a {
        font-size: 32px;
        color: #000;
    }

    #nav {
        margin: 0 auto;
        text-align: center;
    }

    #nav li a {
        text-decoration: none;
        color: #606060;
    }

    #nav ul {
        display: inline;
    }

    #nav ul li {
        display: inline;
        margin-left: 10px;
        text-decoration: none;
        width: auto;
        text-align: center;
    }

    #main {
        overflow: hidden;
        height: auto;
        width: 1100px;
        margin: 30px auto;
        position: relative;
    }

    #line {
        width: 4px;
        height: 100%;
        background-color: #666666;
        position: absolute;
        top: 0;
        left: 50%;
        margin-left: -2px
    }

    .lis {
        width: 100%;
        height: 160px;
        margin-top: 40px;
    }

    .spot {
        width: 20px;
        height: 20px;
        position: absolute;
        left: 50%;
        margin-left: -10px;
        background-color: #666666;
        border: 4px solid #f6f7f8;
        border-radius: 20px;
        margin-top: 5px;
    }

    .ke {
        width: 50%;
        height: 100%;
    }

    .g-lin {
        width: 200px;
        height: 4px;
        background-color: #666666;
        position: relative;
        top: 12px;
        z-index: -1;
        float: right;
        right: 0px;
    }

    .item {
        width: 480px;
        height: auto;
        float: left;
        background-color: #fff;
        box-shadow: rgba(0, 0, 0, 0.08) 0px 1px 20px 8px;
        border-radius: 6px;
        padding: 14px;
        position: relative;
    }

    .date {
        position: absolute;
        top: -28px;
        right: 0px;
        padding: 3px 8px 3px;
        background-color: #fff;
        color: #666;
        font-size: 12px;
        border-radius: 2px;
    }

    .tag {
        font-size: 16px;
        color: #fff;
    }

    .tag a {
        color: #fff !important;
    }

    .des {
        color: #fff;
    }

    #coos .lis:nth-child(even) .ke {
        float: right;
    }

    #coos .lis:nth-child(even) .g-lin {
        float: left;
        left: 0;
    }

    #coos .lis:nth-child(even) .item {
        float: right !important;
        background-color: #fff;
    }

    #coos .lis:nth-child(even) .date {
        left: 0px;
        right: inherit;
    }

    #next {
        width: 100px;
        height: 36px;
        border-radius: 4px;
        text-align: center;
        line-height: 36px;
        margin: 10px auto;
        color: #838383;
    }

    #next a {
        font-size: 14px;
        color: #737373;
        text-decoration: none;
    }

    .yellow {
        background-color: #F7B32D !important;
    }

    .pink {
        background-color: #FF5F5F !important;
    }

    .black {
        background-color: #2C2C2C !important;
    }

    .green {
        background-color: #8bc24c !important;
    }

    .blue {
        background-color: #118DF0 !important;
    }

    .white {
        background-color: #fff !important;
    }

    .white .des, .white .title a, .white .title span {
        color: #121111 !important;
    }

    .purple {
        background-color: #515bd4 !important;
    }

    @media screen and (max-width:1200px) {
        .ke {
            width: 50%;
            height: 100%;
        }

        #main {
            width: 98%
        }

        .item, #coos .lis:nth-child(even) .item {
            width: 84%
        }

        .g-lin {
            float: right;
            left: 0;
            top: 12px
        }

    }

    @media screen and (max-width:800px) {
        * {
            font-size: 14px;
        }

        #line {
            left: 10%
        }

        .lis {
            width: 90%;
            margin-left: 10%
        }

        .ke {
            width: 100%
        }

        .spot {
            left: 10%
        }

        .item {
            float: right;
        }

        .g-lin {
            float: none;
        }

        .item, #coos .lis:nth-child(even) .item {
            width: 88%;
            padding: 10px;
            border-radius: 2px
        }

        #coos .lis:nth-child(even) .date, .date {
            left: 0px !important;
            right: inherit !important;
        }

        .des {
            font-size: 12px;
        }
    }
</style>
EOF;
    }
}
