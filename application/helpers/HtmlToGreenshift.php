<?php

namespace IndependentNiche\application\helpers;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * HtmlToGutenberg class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */

class HtmlToGreenshift extends HtmlToGutenberg
{
    private static $icon_pointer = 0;
    private static $list_icon_mode = 'sequential'; //sequential or fixed

    public static function listIcons()
    {
        $icons = [
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-square" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/><path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/></svg>',
        ];

        if (self::$list_icon_mode == 'fixed')
        {
            $icons = array_merge(
                $icons,
                [
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/></svg>',
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16"><path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/><path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/></svg>',
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-square" viewBox="0 0 16 16"><path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5z"/><path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0"/></svg>',
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16"><path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/></svg>'
                ]
            );
        }

        return $icons;
    }

    public static function setListIconMode($mode)
    {
        self::$list_icon_mode = $mode;
    }

    public static function setIconPointer($pointer)
    {
        self::$icon_pointer = $pointer;
    }

    public static function resetIconPointer()
    {
        self::setIconPointer(0);
    }

    public static function getNextIcon()
    {
        $icons = self::listIcons();
        $icon = $icons[self::$icon_pointer];
        self::$icon_pointer++;
        if (self::$icon_pointer >= count($icons))
        {
            self::$icon_pointer = 0;
        }
        return $icon;
    }

    public static function buildListBlock($listNode, $ordered = false)
    {
        if ($ordered)
        {
            return parent::buildListBlock($listNode, $ordered);
        }

        $listItems = array();

        foreach ($listNode->childNodes as $child)
        {
            if (strtolower($child->nodeName) === 'li')
            {
                $liContent = self::getInnerHTML($child);
                $listItems[] = $liContent;
            }
        }

        $iconColor = '#0d6efd';

        if (self::$list_icon_mode === 'fixed')
        {
            $icons = self::listIcons();

            if (mb_strlen($listItems[0], 'utf-8') >= 200)
                $iconSvg = $icons[3]; // check2
            elseif (count($listItems) == 1)
                $iconSvg = $icons[6]; // chevron-right
            elseif (count($listItems) == 2)
                $iconSvg = $icons[3]; // check2
            elseif (count($listItems) == 3)
                $iconSvg = $icons[0]; // check-square
            elseif (count($listItems) == 4)
                $iconSvg = $icons[2]; // right-circle
            else
                $iconSvg = $icons[1]; //chevron-right
        }
        else
            $iconSvg = self::getNextIcon();

        return self::decorateIconList($listItems, $iconColor, $iconSvg);
    }

    private static function decorateIconList($items, $colorGlobal, $svgIcon)
    {
        $block_id = self::generateId();
        $inlineCssStyles = "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item__text{margin-left: 15px;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item{display:flex;flex-direction:row;align-items:center;position:relative;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg path{fill:{$colorGlobal} !important;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg, #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item img{width:18px !important; height:18px !important; min-width: 18px;}"
            . "body #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg, body #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item img{margin:0px !important;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item{margin-bottom:10px;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList{margin-bottom:30px;}";

        if (!strpos($svgIcon, 'style="'))
            $svgIcon = str_replace('width="16"', 'style="width:5rem;height:5rem;margin:10px" width="16"', $svgIcon);

        $iconsList = [];
        foreach ($items as $index => $item)
        {
            $iconsList[] = [
                "icon" => [
                    "icon" => [
                        "svg" => $svgIcon,
                        "font" => "custom",
                        "image" => ""
                    ],
                    "fill" => "",
                    "fillhover" => "",
                    "iconSize" => [null, null, null, null],
                    "rotateY" => false,
                    "rotateX" => false,
                    "type" => "svg"
                ],
                "content" => $item
            ];
        }

        $block_data = [
            "id" => $block_id,
            "inlineCssStyles" => $inlineCssStyles,
            "iconsList" => $iconsList,
            "colorGlobal" => $colorGlobal,
            "sizeGlobal" => 16,
            "currentItem" => "1",
            "spacing" => [
                "margin" => [
                    "values" => ["bottom" => ["30px"]],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "spacingList" => [
                "margin" => ["values" => ["bottom" => ["10px"]], "locked" => false],
                "padding" => ["values" => [], "locked" => false]
            ]
        ];

        $block_content = '<!-- wp:greenshift-blocks/iconlist ' . self::jsonEncode($block_data) . ' -->';
        $block_content .= '<div class="wp-block-greenshift-blocks-iconlist gspb_iconsList gspb_iconsList-id-' . $block_id . '" id="gspb_iconsList-id-' . $block_id . '">';

        foreach ($iconsList as $index => $list_item)
        {
            $block_content .= '<div class="gspb_iconsList__item" data-id="' . $index . '">';
            $block_content .= $svgIcon;
            $block_content .= '<span class="gspb_iconsList__item__text">' . $list_item['content'] . '</span>';
            $block_content .= '</div>';
        }

        $block_content .= '</div>';
        $block_content .= '<!-- /wp:greenshift-blocks/iconlist -->';

        return $block_content;
    }

    public static function jsonEncode($params)
    {
        if (!$params)
            return '';

        $r = ' ' . json_encode($params, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        $r = self::escapeJsonString($r);

        return $r;
    }

    public static function escapeJsonString($value)
    {
        $escapers = array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    public static function generateId()
    {
        return 'gsbp-' . self::randomStr(8) . '-' . self::randomStr(4);
    }

    public static function randomStr($len = 8)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $str = array();
        for ($i = 0; $i < $len; $i++)
        {
            $n = rand(0, strlen($alphabet) - 1);
            $str[] = $alphabet[$n];
        }
        return implode($str);
    }
}
