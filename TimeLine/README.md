# 概要
实现逻辑：在`post.php`中页面输出语句之前抢先生效。

## 详细步骤
在主题文件夹下`post.php`中的第一行或者第二行插入下列代码。
```php
<?php
if (array_key_exists('TimeLine', Typecho_Plugin::export()['activated'])) {
    if (TimeLine_Plugin::isTimeLine($this->categories)) {
        TimeLine_Plugin::print_html($this, $this->options->siteUrl);
        return "";
    }
}
?>
```

以`default`主题为例，最终`post.php`文件内容如下：
```php
<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
if (array_key_exists('TimeLine', Typecho_Plugin::export()['activated'])) {
    if (TimeLine_Plugin::isTimeLine($this->categories)) {
        TimeLine_Plugin::print_html($this, $this->options->siteUrl);
        return "";
    }
}
?>
...
```

# 时间线写作规则
与`markdown`中表格类似，按照`时间|事件|相关报道`这种形式来写，相关报道为可选内容，但必须为**超链接**的形式，可以参考下面。

示例：
```markdown
2023-11-11|发生了什么
2023-11-12|发生什么2|
2023-11-13|发生什么3|[超链接](https://www.baidu.com)
```

# To do
1. 前端优化