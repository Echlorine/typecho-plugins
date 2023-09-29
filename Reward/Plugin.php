<?php
/**
 * 文章底部添加打赏
 *
 * @package Reward
 * @author Echlorine
 * @version 1.0.0
 * @link https://blogs.echocolate.xyz
 * 
 * Version 1.0.0 (2022-08-20)
 * 
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Exception;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Cookie;
use Widget\Options;

class Reward_Plugin implements PluginInterface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate() {
        return _t('请配置收款码地址');
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
        $wechatCode=new Text('wechatCode', NULL, '', _t('微信打赏二维码'), _t('填入微信打赏二维码路径'));
        $form->addInput($wechatCode);
        $alipayCode=new Text('alipayCode', NULL, '', _t('支付宝打赏二维码'), _t('填入支付宝打赏二维码路径'));
        $form->addInput($alipayCode);
        $jqueryURL=new Text('jqueryURL', NULL, 'https://cdn.jsdelivr.net/gh/jquery/jquery@3.2.1/dist/jquery.min.js', _t('CDN设置'), _t('填入合适的js路径'));
        $form->addInput($jqueryURL);
        $styleCSS = new Textarea(
            'styleCSS',
            NULL,
            "",
            _t('自定义赞赏按钮CSS样式'),
            _t('注：div class="post-sponsor-tag"')
        );
        $form->addInput($styleCSS);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form) {}

    /**
     * 从数据库取得超级变量值
     * 后续版本启用
     */
    public static function getNum() {
        $v1 = 'count(1)';
        $v2 = 'sum(CHAR_LENGTH(`text`))';
        $v3 = 'FROM_UNIXTIME(min(`created`), "%Y-%m-%d")';
        $db = Typecho_Db::get();
        $query = $db->select($v1, $v2, $v3)->from('table.contents')
        ->where('type = ?', 'post');
        // $result = $db->fetchAll($query);
        // echo $result[0][$v1];
        $result = $db->fetchRow($query);
        $PostNum = $result[$v1];
        $CharNum = $result[$v2];
        $PostTime = $result[$v3];
    }

    /**
     * 在页脚显示赞赏按钮
     */
    public static function showRewardFooter($jqueryURL, $wechatCode, $alipayCode, $styleCSS, $themeUrl) {
        print <<<EOT
        <script src=$jqueryURL></script>
        <div class="post-content post-sponsor-tag">
            <p>如果你认为这篇文章还不错，可以考虑
                <a href="javascript:void(0)" onclick="reward()" title="打赏，支持一下">为作者充电 ⚡️</a>
            </p>
        </div>
        <div class="hide_qr" style="display: none;"></div>
        <div class="show_qr" style="display: none;">
            <a class="qr_close" href="javascript:void(0)" onclick="reward()" title="关闭">
                <img src="{$themeUrl}/img/close.png" alt="取消">
            </a>
            <div class="reward_img">
                <img src=$wechatCode alt="收款二维码">
            </div>
            <div class="reward_bg">
                <div class="pay_box choice" qr_code=$wechatCode>
                    <span class="pay_box_span"></span>
                    <span class="qr_code">
                        <img src="{$themeUrl}/img/wechat.svg" alt="微信二维码">
                    </span>
                </div>
                <div class="pay_box" qr_code=$alipayCode>
                    <span class="pay_box_span"></span>
                    <span class="qr_code">
                        <img src="{$themeUrl}/img/alipay.svg" alt="支付宝二维码">
                    </span>
                </div>
            </div>
        </div>
        <script>
            $(function() {
                $(".pay_box").click(function() {
                    $(this).addClass('choice').siblings('.pay_box').removeClass('choice');
                    var qr_code = $(this).attr('qr_code');
                    $(".reward_img img").attr("src", qr_code);
                });
                $(".hide_qr").click(function() {
                    reward();
                });
            });
            function reward() {
                $(".hide_qr").fadeToggle();
                $(".show_qr").fadeToggle();
            }
        </script>
        <style>
            $styleCSS
            .hide_qr {
                z-index: 999;
                background: #000;
                opacity: .5;
                -moz-opacity: .5;
                left: 0;
                top: 0;
                height: 100%;
                width: 100%;
                position: fixed;
                display: none;
            }

            .show_qr {
                width: 23vw;
                background-color: #fff;
                border-radius: 6px;
                position: fixed;
                z-index: 1000;
                left: 50%;
                top: 50%;
                margin-left: -11.5vw;
                margin-top: -15%;
                display: none;
            }

            .show_qr a.qr_close {
                display: inline-block;
                top: 10px;
                position: absolute;
                right: 10px;
            }

            .show_qr img {
                border: none;
                border-width: 0;
                border-radius: 6px 6px 0 0;
                width: 100%;
                height: auto;
                margin: 0;
                box-shadow: none;
            }

            .show_qr a.qr_close img {
                border-radius: 0;
            }

            .reward_bg {
                text-align: center;
                margin: 0 auto;
                cursor: pointer;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }

            .pay_box {
                display: inline-block;
                margin-right: 10px;
                padding: 15px 0;
            }

            .pay_box img {
                width: auto;
            }

            span.pay_box_span {
                width: 16px;
                height: 16px;
                background: url({$themeUrl}/img/noselect.svg);
                display: block;
                float: left;
                margin-top: 6px;
                margin-right: 5px;
            }

            .pay_box.choice span.pay_box_span {
                background: url({$themeUrl}/img/select.svg);
            }

            .reward_bg img {
                display: inline !important;
            }
        </style>
        EOT;
    }

    /**
     * 插件实现方法
     *
     * @access public
     */
    public static function show($themeUrl) {
        $rewardSet = Options::alloc()->plugin('Reward');
        $jqueryURL = $rewardSet->jqueryURL;
        $wechatCode = $rewardSet->wechatCode;
        $alipayCode = $rewardSet->alipayCode;
        $styleCSS = $rewardSet->styleCSS;
        if (empty($styleCSS)) {
            $styleCSS = <<<EOT
                .post-sponsor-tag {
                    margin-bottom: 60px;
                }

                .post-sponsor-tag a {
                    display: inline-block;
                    position: relative;
                    text-decoration: none;
                    line-height: 1.4em;
                    z-index: 0;
                    transition: all .25s ease;
                    padding: 0 3px
                }

                .post-sponsor-tag a::after {
                    content: "";
                    position: absolute;
                    display: block;
                    left: 0;
                    bottom: 0;
                    width: 100%;
                    height: 40%;
                    z-index: -1;
                    transition: all .25s ease;
                    background-color: rgba(132, 231, 25, 0.3);
                }

                .post-sponsor-tag a:hover::after {
                    height: 100%;
                    background-color: rgba(132, 231, 25, 0.3);
                }
            EOT;
        }
        Reward_Plugin::showRewardFooter($jqueryURL, $wechatCode, $alipayCode, $styleCSS, $themeUrl);
    }
}
