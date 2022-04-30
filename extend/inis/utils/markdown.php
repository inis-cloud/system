<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: helper class - 解析markdown
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class markdown
 * @package inis\utils
 */
class markdown
{
    /**
     * 获取短代码属性数组
     * @param $text
     * @return array|string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L508
     */
    public static function shortcode($text)
    {
        $atts = [];
        $pattern = self::shortcodeAtts();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3])) $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5])) $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7])) $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]) && strlen($m[8])) $atts[] = stripcslashes($m[8]);
                elseif (isset($m[9])) $atts[] = stripcslashes($m[9]);
            }
            // Reject any unclosed HTML elements
            foreach ($atts as &$value) if (false !== strpos($value, '<')) if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) $value = '';
        } else $atts = ltrim($text);
        
        return $atts;
    }
    
    /**
     * Retrieve the shortcode attributes regex.
     *
     * @return string The shortcode attribute regular expression
     * @since 4.4.0
     *
     */
    public static function shortcodeAtts()
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }
    
    /**
     * 获取匹配短代码的正则表达式
     * @param null $tagnames
     * @return string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L254
     */
    public static function getShortcode($tagnames = null)
    {
        global $shortcode_tags;
        
        if (empty($tagnames)) $tagnames = array_keys($shortcode_tags);
        
        $tagregexp = join('|', array_map('preg_quote', $tagnames));
        
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return
            '\\['                                // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        // phpcs:enable
    }
    
    // 自定义 Tabs 标签
    public static function tabsParse($matches)
    {
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        $tabs_attr   = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $tabs_attrs  = self::shortcode($tabs_attr);
        
        $tabs_type   = (!empty($tabs_attrs['type']))  ? $tabs_attrs["type"]  : 'default';
        $tabs_class  = (!empty($tabs_attrs["class"])) ? $tabs_attrs["class"] : '';
        $tabs_title  = (!empty($tabs_attrs['title'])) ? $tabs_attrs['title'] : '';
        $title_show  = (!empty($tabs_title)) ? "display: block;" : "display: none;";
        
        $tab = "";
        $tabContents = "";
        $content = $matches[5];

        $pattern = self::getShortcode(['item']);
        preg_match_all("/$pattern/", $content, $matches);
        
        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $text = $matches[5][$i];
            $id = "tab-" . md5(uniqid()) . rand(0, 100) . $i;
            // 还原转义前的参数列表
            $attr   = htmlspecialchars_decode($item);
            // 获取短代码的参数
            $attrs  = self::shortcode($attr);
            $name        = (!empty($attrs['name'])) ? $attrs['name'] : "";
            $active      = (!empty($attrs['active'])) ? $attrs['active'] : "";
            $item_class  = (!empty($attrs['class'])) ? $attrs['class'] : "";
            
            $show    = "";
            $style = "style=\"";
            foreach ($attrs as $key => $value) if ($key !== "name" && $key !== "active" && $key !== "class") $style .= $key . ':' . $value . ';';
            
            $style .= "\"";

            if ($active == "true") {
                $active = "active";
                $show = "show";
            } else $active = "";
            
            if ($tabs_type == "default") {
                // Tab 头
                $tab .= "<li class=\"nav-item\">
                    <a href=\"#$id\" data-toggle=\"tab\" data-bs-toggle=\"tab\" aria-expanded=\"false\" class=\"nav-link $active\">
                        <span class=\"$item_class\" $style>$name</span>
                    </a>
                </li>";
                // Tab内容
                $tabContents .= "<div class=\"tab-pane $show $active\" id=\"$id\">$text</div>";
            } else if ($tabs_type == "left" or $tabs_type == "right") {
                // Tab 头
                $tab .= "<a class=\"nav-link $active $show\" id=\"$id-tab\" data-toggle=\"pill\" data-bs-toggle=\"pill\" href=\"#$id\" role=\"tab\" aria-controls=\"$id\" aria-selected=\"true\">
                    <span class=\"$item_class\">$name</span>
                </a>";
                // Tab内容
                $tabContents .= "<div class=\"tab-pane fade $active $show\" id=\"$id\" role=\"tabpanel\" aria-labelledby=\"$id-tab\">$text</div>";
            }
            
        }
        
        // 最后渲染结果
        if ($tabs_type == "default") {
            $result = "<div class=\"row\">
                <div class=\"col-lg-12\">
                    <div class=\"card\">
                        <div class=\"card-body\">
                            <h5 class=\"mb-3\" style=\"$title_show\">$tabs_title</h5>
                            <ul class=\"nav nav-tabs $tabs_class mb-3\">
                                $tab
                            </ul>
                            <div class=\"tab-content\">
                                $tabContents
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        } else if ($tabs_type == "left") {
            $result = "<div class=\"row\">
                <div class=\"col-lg-12\">
                    <div class=\"card\">
                        <div class=\"card-body\">
                            <h5 class=\"mb-3\" style=\"$title_show\">$tabs_title</h5>
                            <div class=\"row\">
                                <div class=\"col-sm-3 mb-2 mb-sm-0\">
                                    <div class=\"nav flex-column nav-pills\" id=\"v-pills-tab\" role=\"tablist\" aria-orientation=\"vertical\">
                                        $tab
                                    </div>
                                </div>
                                <div class=\"col-sm-9\">
                                    <div class=\"tab-content\" id=\"v-pills-tabContent\">
                                        $tabContents
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        } else if ($tabs_type == "right") {
            $result = "<div class=\"row\">
                <div class=\"col-lg-12\">
                    <div class=\"card\">
                        <div class=\"card-body\">
                            <h5 class=\"mb-3\" style=\"$title_show\">$tabs_title</h5>
                            <div class=\"row\">
                                <div class=\"col-sm-9\">
                                    <div class=\"tab-content\" id=\"v-pills-tabContent\">
                                        $tabContents
                                    </div>
                                </div>
                                <div class=\"col-sm-3 mb-2 mb-sm-0\">
                                    <div class=\"nav flex-column nav-pills\" id=\"v-pills-tab\" role=\"tablist\" aria-orientation=\"vertical\">
                                        $tab
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        
        return $result;
    }
    
    // 自定义 collapse 标签
    public static function collapseParse($matches)
    {
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        $items = "";
        $content = $matches[5];
        
        $pattern = self::getShortcode(['item']);
        preg_match_all("/$pattern/", $content, $matches);
        
        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $text = $matches[5][$i];
            $id = "collapse-" . md5(uniqid()) . rand(0, 100) . $i;
            // 还原转义前的参数列表
            $attr   = htmlspecialchars_decode($item);
            // 获取短代码的参数
            $attrs  = self::shortcode($attr);
            $name   = (!empty($attrs['name']))   ? $attrs['name']   : "";
            $active = (!empty($attrs['active'])) ? $attrs['active'] : "";
            $show   = "";
            $style  = "style=\"";
            foreach ($attrs as $key => $value) if ($key !== "name" && $key !== "active") $style .= $key . ':' . $value . ';';
            
            $style .= "\"";

            if ($active == "true") {
                $active = "active";
                $show = "show";
            } else $active = "";
            
            $items .= "<div class=\"card mb-0\">
                <div class=\"card-header\" id=\"$id\">
                    <h5 class=\"m-0\">
                        <a $style class=\"custom-accordion-title d-block pt-2 pb-2\" data-toggle=\"collapse\" data-bs-toggle=\"collapse\" href=\"#$id-item\" aria-expanded=\"true\" aria-controls=\"$id-item\">
                            $name
                            <span class=\"float-right\">
                                <svg class=\"$active\" t=\"1624267618081\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"3217\" width=\"16\" height=\"16\"><path d=\"M709.717333 546.901333a46.805333 46.805333 0 0 0 0-69.802666L368.981333 181.077333C340.949333 156.757333 298.666667 177.749333 298.666667 216.021333v591.957334c0 38.272 42.282667 59.306667 70.314666 34.944l340.736-296.021334z\" p-id=\"3218\" fill=\"#50b5ff\"></path></svg>
                            </span>
                        </a>
                    </h5>
                </div>
                <div id=\"$id-item\" class=\"collapse $show\" aria-labelledby=\"$id\" data-parent=\"#accordion\">
                    <div class=\"card-body\">
                        $text
                    </div>
                </div>
            </div>";
        }
        
        // 最后渲染结果
        $result = "<div class=\"row\">
            <div class=\"col-lg-12\">
                <div id=\"accordion\" class=\"custom-accordion mb-4\">
                    $items
                </div>
            </div>
        </div>";
        
        return $result;
    }
    
    // 自定义 tag 标签
    public static function tagParse($matches)
    {
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        // 还原转义前的参数列表
        $attr  = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        $class = (!empty($attrs['class'])) ? $attrs['class'] : "";
        
        if (empty($class)) $class = "badge-primary";
        
        $content = $matches[5];
        
        $result = "<span class=\"badge $class\">$content</span>";

        return $result;
    }
    
    // 自定义 Button 标签
    public static function buttonParse($matches)
    {
        // 不解析类似 [[post]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        // 对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        /* $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]); */
        // 还原转义前的参数列表
        $attr = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        
        $class = (!empty($attrs['class'])) ? $attrs['class'] : "btn-primary";
        $url   = (!empty($attrs['url']))   ? "window.open('{$attrs['url']}','_blank')" : "";
        
        $text  = $matches[5];
        
        $result = "<button type=\"button\" class=\"btn $class\" onclick=\"$url\">$text</button>";
        return $result;
    }
    
    // 自定义 info 标签
    public static function infoParse($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        // 还原转义前的参数列表
        $attr = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        $class = (!empty($attrs['class'])) ? $attrs['class'] : "alert-primary";
        
        $text   = $matches[5];
        
        $result = "<div class=\"alert $class\" role=\"alert\">
            $text
        </div>";
        return $result;
    }
    
    // 自定义 text 标签
    public static function textParse($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        // 还原转义前的参数列表
        $attr = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        $class = (!empty($attrs['class'])) ? $attrs['class'] : "text-primary";
        
        $text   = $matches[5];
        
        $result = "<span class=\"$class\">$text</span>";
        return $result;
    }
    
    // 自定义 album 标签
    public static function albumParse($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        // 还原转义前的参数列表
        $attr  = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        
        $text  = $matches[5];
        
        // 正则匹配所有 img 标签
        $text = self::matchImg($text);
        
        $img  = '';
        
        foreach ($text as $val) {
            $value = $val['value'];
            $src   = $val['src'];
            $alt   = $val['alt'];
            
            $img .= "<figure style=\"flex-grow: 88.8889;\">
                $value
                <span class=\"alt\">$alt</span>
            </figure>";
        }
        
        $result = "<div class=\"album\">$img</div>";
        return $result;
    }
    
    // 自定义 hide 标签
    public static function hideParse($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') return substr($matches[0], 1, -1);
        
        // 还原转义前的参数列表
        $attr  = htmlspecialchars_decode($matches[3]);
        // 获取短代码的参数
        $attrs = self::shortcode($attr);
        
        $text  = $matches[5];
        
        $result = "<div class=\"hide\">
            <div class=\"hide-description text-center\">
                <span class=\"badge badge-warning-lighten\">此处内容评论后可见</span>
            </div>
            <div class=\"hide-content\">$text</div>
        </div>";
        return $result;
    }
    
    /**
     * 一些公用的解析，文章、评论、时光机公用的，与用户状态无关
     * @param $content
     * @return null|string|string[]
     */
    public static function parse($content)
    {
        // Tabs 标签
        if (strpos($content, '[tabs') !== false) {
            $pattern = self::getShortcode(['tabs']);
            $content = preg_replace_callback("/$pattern/", ['self', 'tabsParse'], $content);
        }
        
        // collapse 标签 - 折叠框
        if (strpos($content, '[collapse') !== false) {
            $pattern = self::getShortcode(['collapse']);
            $content = preg_replace_callback("/$pattern/", ['self', 'collapseParse'], $content);
        }
        
        // tag 标签
        if (strpos($content, '[tag') !== false) {
            $pattern = self::getShortcode(['tag']);
            $content = preg_replace_callback("/$pattern/", ['self', 'tagParse'], $content);
        }
        
        // btn 标签 - 按钮
        if (strpos($content, '[btn') !== false) {
            $pattern = self::getShortcode(['btn']);
            $content = preg_replace_callback("/$pattern/", ['self', 'buttonParse'], $content);
        }
        
        // info 标签 - 带颜色的背景框
        if (strpos($content, '[info') !== false) {
            $pattern = self::getShortcode(['info']);
            $content = preg_replace_callback("/$pattern/", ['self', 'infoParse'], $content);
        }
        
        // text 标签 - 字体颜色
        if (strpos($content, '[text') !== false) {
            $pattern = self::getShortcode(['text']);
            $content = preg_replace_callback("/$pattern/", ['self', 'textParse'], $content);
        }
        
        // album 标签 - 相册
        if (strpos($content, '[album') !== false) {
            $pattern = self::getShortcode(['album']);
            $content = preg_replace_callback("/$pattern/", ['self', 'albumParse'], $content);
        }
        
        // hide 标签 - 评论可见
        if (strpos($content, '[hide') !== false) {
            $pattern = self::getShortcode(['hide']);
            $content = preg_replace_callback("/$pattern/", ['self', 'hideParse'], $content);
        }
        
        return $content;
    }
    
    // 过滤MD语法
    public static function filterMD($content)
    {
        // 排除摘要的collapse 公式
        if (strpos($content, '[collapse') !== false) {
            $pattern = self::getShortcode(['collapse']);
            $content = preg_replace("/$pattern/", '', $content);
        }
        if (strpos($content, '[tabs') !== false) {
            $pattern = self::getShortcode(['tabs']);
            $content = preg_replace("/$pattern/", '', $content);
        }
        
        // 排除摘要中的块级公式
        $content = preg_replace('/\$\$[\s\S]*\$\$/sm', '', $content);
        if (strpos($content, '[tag') !== false) {
            $pattern = self::getShortcode(['tag']);
            $content = preg_replace("/$pattern/", '', $content);
        }
        if (strpos($content, '[info') !== false) {
            $pattern = self::getShortcode(['info']);
            $content = preg_replace("/$pattern/", '', $content);
        }
        if (strpos($content, '[btn') !== false) {
            $pattern = self::getShortcode(['btn']);
            $content = preg_replace("/$pattern/", '', $content);
        }
        
        // 排除文档助手
        if (strpos($content, '>') !== false) {
            $content = preg_replace("/(@|√|!|x|i)&gt;/", '', $content);
        }
        
        return $content;
    }
    
    // 正则匹配img标签数据
    public static function matchImg($string)
    {
        $result = [];
        
        preg_match_all('/<img\s[^>]+>/i',$string,$matches);
        
        foreach ($matches[0] as $val) {
            
            preg_match_all("/<img.*alt\=[\"|\'](.*)[\"|\'].*>/i",$val,$alt);
            preg_match('/<img.+src=\"?(.+\.(jpeg|jpg|gif|bmp|bnp|png|svg))\"?.+>/i',$val,$src);
            
            array_push($result, ['value'=>@$val,'alt'=>implode(@$alt[1]),'src'=>@$src[1]]);
        }
        
        return $result;
    }
}