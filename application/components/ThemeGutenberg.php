<?php

namespace TooMuchNiche\application\components;

use TooMuchNiche\application\helpers\HtmlToGutenberg;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * ThemeGutenberg class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */

class ThemeGutenberg extends Theme
{
    public function wrapperStart($section, $class = null)
    {
        if (!$tag = $this->getWrapperTag($section))
            return '';

        if (!$class)
            $class = $this->getWrapperClass($section);

        $class .= ' is-layout-constrained';

        $res = '<!-- wp:group {"tagName":"' . $tag . '","className":"' . $class . '","layout":{"type":"constrained"}} -->';
        $res .= '<' . $tag . ' class="wp-block-group ' . $class . '">';

        return $res;
    }

    public function wrapperEnd($section)
    {
        if (!$tag = $this->getWrapperTag($section))
            return '';

        $res = '</' . $tag . '>';
        $res .= '<!-- /wp:group -->';

        return $res;
    }

    public function decorateBlockVersusTable(array $section)
    {
        $shortcodes = $this->findSectionsAll('ProductShortcode', '', true);
        $labels = $this->findSectionsAll('BestForLabel', '', true);
        $ratings = $this->findSectionsAll('Rating', '', true);
        $descriptions = $this->findSectionsAll('ProductBottomLine', '', true);
        $criteria_ratings = $this->findSectionsAll('CriteriaRatings', '', true);
        $short_titles = $this->findSectionsAll('ShortProductTitle', '', true);
        $pros = $this->findSectionsAll('Pros', '', true);
        $cons = $this->findSectionsAll('Cons', '', true);
        $buttons_shortcodes = $this->findSectionsAll('BuyNowButtonShortcode', '', true);

        $total = count($shortcodes);
        $res = $this->decorateParagraph(' ');

        // row 1
        $columns = array();
        for ($i = 0; $i < $total; $i++)
        {
            $content = $flexbox_content = '';
            if (isset($labels[$i]))
                $content = $this->buildVersusLabel($labels[$i]['content']);
            $flexbox_content = $this->decorateBlock($shortcodes[$i]['content']);
            $flexbox_content .= $this->buildVersusRating($ratings[$i]['content']);
            if (isset($descriptions[$i]))
                $flexbox_content .= $this->decorateParagraph($descriptions[$i]['content']);
            $content .= $this->buildVersusFlexbox($flexbox_content);
            $column = $this->buildVersusColumn($content, $i, $total);
            $columns[] = $column;
        }
        $res .= $this->buildVersusRow(join('', $columns), $total);

        // row 2 (criteria_ratings)
        if ($criteria_ratings)
        {
            $columns = array();
            for ($i = 0; $i < $total; $i++)
            {
                $content = $flexbox_content = '';
                $content = $this->buildVersusSubheading($short_titles[$i]['content']);
                $flexbox_content = $this->decorateBlockCriteriaRatings($criteria_ratings[$i], 20, 20);
                $content .= $this->buildVersusFlexbox($flexbox_content);
                $column = $this->buildVersusColumn($content, $i, $total);
                $columns[] = $column;
            }
            $res .= $this->buildVersusRow(join('', $columns), $total);
        }

        // row 3 (pros)
        if (self::dataExists(($pros)))
        {
            $columns = array();
            for ($i = 0; $i < $total; $i++)
            {
                $content = $flexbox_content = '';
                $content = $this->buildVersusSubheading($short_titles[$i]['content']);
                if (isset($pros[$i]))
                    $flexbox_content = $this->buildVersusPros($pros[$i]);
                $content .= $this->buildVersusFlexbox($flexbox_content);
                $column = $this->buildVersusColumn($content, $i, $total);
                $columns[] = $column;
            }
            $res .= $this->buildVersusRow(join('', $columns), $total);
        }

        // row 4 (cons)
        if (self::dataExists(($cons)))
        {
            $columns = array();
            for ($i = 0; $i < $total; $i++)
            {
                $content = $flexbox_content = '';
                $content = $this->buildVersusSubheading($short_titles[$i]['content']);
                if (isset($cons[$i]))
                    $flexbox_content .= $this->buildVersusCons($cons[$i]);
                $content .= $this->buildVersusFlexbox($flexbox_content);
                $column = $this->buildVersusColumn($content, $i, $total);
                $columns[] = $column;
            }
            $res .= $this->buildVersusRow(join('', $columns), $total);
        }

        // row 5 (buttons)
        $columns = array();
        for ($i = 0; $i < $total; $i++)
        {
            $content = $flexbox_content = '';
            $content = $this->decorateBlock($buttons_shortcodes[$i]['content']);
            $column = $this->buildVersusColumn($content, $i, $total);
            $columns[] = $column;
        }
        $res .= $this->buildVersusRow(join('', $columns), $total);

        return $res;
    }

    public function decorateBlockVersusPost(array $section)
    {
        $gallery_shortcodes = $this->findSectionsAll('GalleryRowShortcode', '', true);
        $formatted_content = $this->decorateSectionHtml($section);
        $res = '';

        // add gallery
        $parts = explode('</h3><!-- /wp:heading -->', $formatted_content);
        if (count($parts) >= count($gallery_shortcodes))
        {
            foreach ($parts as $i => $p)
            {
                if ($i < count($parts) - 1)
                    $parts[$i] .= '</h3><!-- /wp:heading -->';
                if (isset($gallery_shortcodes[$i]))
                    $parts[$i] .= $this->decorateSectionShortcode($gallery_shortcodes[$i]);
            }

            $formatted_content = join('', $parts);
        }
        $res .= $formatted_content;

        return $res;
    }

    public function buildVersusRow($content, $col_count = 2)
    {
        $res = '<!-- wp:columns --><div class="wp-block-columns">';
        $res .= $content;
        $res .= '</div><!-- /wp:columns -->';

        return $res;
    }

    public function buildVersusColumn($content, $num, $col_count = 2)
    {
        $res = '<!-- wp:column --><div class="wp-block-column">';
        $res .= $content;
        $res .= '</div><!-- /wp:column -->';
        return $res;
    }

    public function buildVersusLabel($content)
    {
        $colors = array('#28a745', '#dc3545', '#17a2b8', '#007bff', '#6200ea', '#304ffe', '#e91e63', '#880e4f', '#e53935');
        shuffle($colors);
        $color = reset($colors);
        $res = '<!-- wp:paragraph {"align":"center","style":{"color":{"background":"' . $color . '"},"spacing":{"padding":{"top":"10px","bottom":"10px","left":"var:preset|spacing|10","right":"var:preset|spacing|10"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"textColor":"white","className":"tmn-verus-title","fontSize":"small"} -->';
        $res .= '<p class="has-text-align-center tmn-verus-title has-white-color has-text-color has-background has-small-font-size" style="background-color:' . $color . ';margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-right:var(--wp--preset--spacing--10);padding-bottom:10px;padding-left:var(--wp--preset--spacing--10)">';
        $res .= $content;
        $res .= '</p>';
        $res .= '<!-- /wp:paragraph -->';

        return $res;
    }

    public function buildVersusFlexbox($content)
    {
        return $content;
    }

    public function buildVersusRating($rating)
    {
        if (!$rating)
            return '';

        $res = '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} --><div class="wp-block-group">';
        $res .= '<!-- wp:paragraph {"style":{"color":{"background":"dodgerblue"},"typography":{"fontSize":"28px"},"spacing":{"padding":{"top":"0rem","bottom":"0rem","left":"0.5rem","right":"0.5rem"}}},"textColor":"white"} --><p class="has-white-color has-text-color has-background" style="background-color:dodgerblue;padding-top:0rem;padding-right:0.5rem;padding-bottom:0rem;padding-left:0.5rem;font-size:28px"><strong>' . $rating . '</strong></p><!-- /wp:paragraph -->';
        $res .= '</div><!-- /wp:group -->';

        return $res;
    }

    public function buildVersusPros($section)
    {
        $res = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","right":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"0"},"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical"}} -->';
        $res .= '<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:0"><!-- wp:paragraph {"align":"center","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"var:preset|spacing|30","left":"0"}}}} -->';
        $res .= '<p class="has-text-align-center" style="padding-top:0;padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><strong>' . $section['heading'] . '</strong></p>';
        $res .= '<!-- /wp:paragraph -->';

        $res .= $this->decorateList($section['content'], false, array('style' => 'line-height:2'));

        $res .= '</div><!-- /wp:group -->';
        return $res;
    }

    public function buildVersusCons($section)
    {
        return $this->buildVersusPros($section);
    }

    public function buildVersusFeatures($features, $num)
    {
        $res = '';
        foreach ($features as $feature)
        {
            if (!isset($feature['values'][$num]))
                return '';

            if (!$value = $feature['values'][$num])
                $value = '-';

            $f = '<strong>' . $feature['name'] . '</strong><br>' . $value;
            $res .= $this->decorateParagraph($f);
        }

        return $res;
    }

    public function buildVersusSubheading($content)
    {
        $res = '<!-- wp:paragraph {"align":"center","style":{"color":{"background":"#cecece"},"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"textColor":"contrast","className":"tmn-verus-title2","fontSize":"small"} -->';
        $res .= '<p class="has-text-align-center tmn-verus-title2 has-contrast-color has-text-color has-background has-small-font-size" style="background-color:#cecece;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">' . $content . '</p>';
        $res .= '<!-- /wp:paragraph -->';

        return $res;
    }

    public function decorateBlockIntroduction(array $section)
    {
        $html = $this->decorateSectionHtml($section);
        return $html . '<!-- wp:more --><!--more--><!-- /wp:more -->';
    }

    /*
    public function decorateBlockArticleSection(array $section)
    {
        $content = $this->decorateSectionHtml($section);
        if ($shortcode = $this->findSection('ArticleSectionProductsShortcode', $section['group'], true))
        {
            $parts = explode('</p><!-- /wp:paragraph -->', $content, 2);
            $parts[0] .= '</p><!-- /wp:paragraph -->';
            $parts[0] .= $this->decorateShortcode($shortcode['content']);
            $content = join('', $parts);
        }

        return $content;
    }
    */

    public function decorateHeadingArticleStepSection($section)
    {
        if (!empty($section['position']))
            $section['heading'] = $section['position'] . '. ' . $section['heading'];

        $res = '<!-- wp:heading {"textAlign":"center","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}}},"className":"top-product-heading"} -->';
        $res .= '<h2 class="wp-block-heading has-text-align-center top-product-heading" id="boriwat-heat-massager-for-pai" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">' . $section['heading'] . '</h2>';
        $res .= '<!-- /wp:heading -->';

        return $res;
    }

    public function decorateBlockCtaText(array $section)
    {
        $res = '';

        $res .= '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"}},"border":{"color":"#00d184"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->';
        $res .= '<div class="wp-block-group has-border-color" style="border-color:#00d184;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"align":"center"} -->';
        $res .= '<p class="has-text-align-center">' . $section['content'] . '</p>';
        $res .= '<!-- /wp:paragraph -->';

        if ($shortcode = $this->findSection('CeShortcodeCtaButton', $section['group'], true))
            $res .= $this->decorateShortcode($shortcode['content']);

        $res .= '</div>';
        $res .= '<!-- /wp:group -->';

        return $res;
    }

    public function decorateSectionFaq(array $section)
    {
        $res = '';
        foreach ($section['content'] as $faq)
        {
            $res .= '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30","padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}},"border":{"color":"#dedede","width":"1px"}},"layout":{"type":"flex","orientation":"vertical"}} -->';
            $res .= '<div class="wp-block-group has-border-color" style="border-color:#dedede;border-width:1px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">';

            $res .= '<!-- wp:paragraph {"className":"' . self::CSS_PREFIX . 'faq-question"} -->';
            $res .= '<p class="' . self::CSS_PREFIX . 'faq-question">';
            $res .= '<strong>' . $faq['question'] . '</strong>';
            $res .= '</p><!-- /wp:paragraph -->';

            $res .= '<!-- wp:group {"className":"' . self::CSS_PREFIX . 'faq-answer","layout":{"type":"constrained"}} -->';
            $res .= '<div class="wp-block-group ' . self::CSS_PREFIX . 'faq-answer">';
            $res .= $this->decorateSectionHtml($faq['answer']);
            $res .= '</div><!-- /wp:group -->';

            $res .= '</div>';
            $res .= '<!-- /wp:group -->';
        }
        return $res;
    }

    public function decorateSectionSchema($section)
    {
        return  '<!-- wp:html -->' . trim(parent::decorateSectionSchema($section)) . '<!-- /wp:html -->' . "";
    }

    public function decorateHeadingProductCardShortcode($section)
    {
        if (!empty($section['position']))
            $section['heading'] = $section['position'] . '. ' . $section['heading'];

        $res = '<!-- wp:heading {"textAlign":"center","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}}},"className":"top-product-heading"} -->';
        $res .= '<h2 class="wp-block-heading has-text-align-center top-product-heading" id="boriwat-heat-massager-for-pai" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">' . $section['heading'] . '</h2>';
        $res .= '<!-- /wp:heading -->';

        return $res;
    }

    public function decorateBlockReviewConclusion($section)
    {
        $res = '';

        if ($rating_section = $this->findSection('Rating', $section['group'], true))
            $rating = $rating_section['content'];
        else
            $rating = 0;

        $res .= '<!-- wp:group {"tagName":"section","style":{"border":{"radius":"0px","color":"#dedede","width":"1px"},"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"},"blockGap":"111px","margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"left"}} -->';
        $res .= '<section class="wp-block-group has-border-color" style="border-color:#dedede;border-width:1px;border-radius:0px;margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","orientation":"horizontal"}} --><div class="wp-block-group">';

        if ($rating)
            $res .= '<!-- wp:paragraph {"style":{"color":{"background":"dodgerblue"},"typography":{"fontSize":"28px"},"spacing":{"padding":{"top":"0rem","bottom":"0rem","left":"0.5rem","right":"0.5rem"}}},"textColor":"white"} --><p class="has-white-color has-text-color has-background" style="background-color:dodgerblue;padding-top:0rem;padding-right:0.5rem;padding-bottom:0rem;padding-left:0.5rem;font-size:28px"><strong>' . $rating . '</strong></p><!-- /wp:paragraph -->';

        $res .= '<!-- wp:paragraph {"style":{"typography":{"fontSize":"26px"}}} --><p style="font-size:26px">' . $section['heading'] . '</p><!-- /wp:paragraph --></div><!-- /wp:group -->';
        $res .= '<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0","right":"0","bottom":"0","left":"0"},"padding":{"top":"var:preset|spacing|30","right":"0","bottom":"0","left":"0"}}}} --><p style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:0;padding-left:0">' . $section['content'] . '</p><!-- /wp:paragraph --></section><!-- /wp:group -->';

        return $res;
    }

    public function decorateHeadingReviewConclusion(array $section)
    {
        return '';
    }

    public function decorateSectionHtml($section)
    {
        if (is_array($section))
            $html = $section['content'];
        else
            $html = $section;

        return HtmlToGutenberg::convert($html);
    }

    public function decorateSectionHtmlH($content)
    {
        $tags = array(
            '<h1>' => '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">',
            '<h2>' => '<!-- wp:heading --><h2 class="wp-block-heading">',
            '<h3>' => '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">',
            '<h4>' => '<!-- wp:heading {"level":4} --><h4 class="wp-block-heading">',
            '<h5>' => '<!-- wp:heading {"level":5} --><h5 class="wp-block-heading">',
            '<h6>' => '<!-- wp:heading {"level":6} --><h6 class="wp-block-heading">',
            '</h1>' => '</h1><!-- /wp:heading -->',
            '</h2>' => '</h2><!-- /wp:heading -->',
            '</h3>' => '</h3><!-- /wp:heading -->',
            '</h4>' => '</h4><!-- /wp:heading -->',
            '</h5>' => '</h5><!-- /wp:heading -->',
            '</h6>' => '</h6><!-- /wp:heading -->',
        );

        return str_ireplace(array_keys($tags), array_values($tags), $content);
    }

    public function decorateHeadingFeaturesList($section)
    {
        return '';
    }

    public function decorateBlockFeaturesList($section)
    {
        $wp_group = [
            "style" => [
                "spacing" => [
                    "padding" => [
                        "top" => "var:preset|spacing|50",
                        "right" => "var:preset|spacing|50",
                        "bottom" => "var:preset|spacing|50",
                        "left" => "var:preset|spacing|50",
                    ],
                    "blockGap" => "var:preset|spacing|40",
                ],
                "border" => [
                    "radius" => "19px",
                    "color" => "#ffdecf",
                    "width" => "1px",
                ]
            ],
            "layout" => [
                "type" => "constrained",
            ]
        ];

        $res = '<!-- wp:group ' . self::jsonEncode($wp_group) . ' -->';
        $res .= '<div class="wp-block-group has-border-color" style="border-color:#ffdecf;border-width:1px;border-radius:19px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">';

        $res .= $this->decorateHeading($section['heading'], 'h3', array('class' => 'wp-block-heading'));
        $res .= $this->decorateList($section['content'], false, array('style' => 'line-height:2'));

        $res .= '';
        $res .= '</div><!-- /wp:group -->';

        return $res;
    }

    public function decorateProsCons(array $section)
    {
        $sections = array();

        if ($pros = $this->findSection('Pros', $section['group'], true))
            $sections[] = $pros;

        if ($cons = $this->findSection('Cons', $section['group'], true))
            $sections[] = $cons;

        $res = '';

        if (count($sections) == 2 && mb_strlen(join(' ', $pros['content'])) < 800)
            $add_columns = true;
        else
            $add_columns = false;

        if ($add_columns)
            $res .= '<!-- wp:columns {"style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"},"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"className":"' . self::CSS_PREFIX . 'pros-cons"} --><div class="wp-block-columns ' . self::CSS_PREFIX . 'pros-cons" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50);padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">';

        foreach ($sections as $section)
        {
            if ($section['block'] == 'Pros')
            {
                $color = '#f5fffb';
                $class = self::CSS_PREFIX . 'pros';
            }
            else
            {
                $color = '#fff5f5';
                $class = self::CSS_PREFIX . 'cons';
            }

            if ($add_columns)
                $res .= '<!-- wp:column {"style":{"color":{"background":"' . $color . '"}},"className":"' . $class . '"} --><div class="wp-block-column ' . $class . ' has-background" style="background-color:' . $color . '">';

            $res .= '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","right":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"0"},"blockGap":"0"},"color":{"background":"' . $color . '"}},"layout":{"type":"constrained"}} --><div class="wp-block-group has-background" style="background-color:' . $color . ';padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:0">';
            $res .= '<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"19px"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} --><p class="has-text-align-center" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);font-size:19px"><strong>' . $section['heading'] . '</strong></p><!-- /wp:paragraph -->';

            $res .= $this->decorateList($section['content'], false, array('style' => 'line-height:2'));

            $res .= '</div><!-- /wp:group -->';

            if ($add_columns)
                $res .= '</div><!-- /wp:column -->';
        }

        if ($add_columns)
            $res .= '</div><!-- /wp:columns -->';

        return $res;
    }

    public function decorateHeadingPros($section)
    {
        return '';
    }

    public function decorateHeadingCons($section)
    {
        return '';
    }

    public function decorateBlockPros(array $section)
    {
        return $this->decorateProsCons($section);
    }

    public function decorateBlockCons(array $section)
    {
        return $this->decorateProsCons($section);
    }

    public function decorateShortcode($content)
    {
        return  '<!-- wp:shortcode -->' . trim(parent::decorateShortcode($content)) . '<!-- /wp:shortcode -->' . "";
    }

    public function decorateParagraph($content)
    {
        return '<!-- wp:paragraph -->' . trim(parent::decorateParagraph($content)) . '<!-- /wp:paragraph -->' . "";
    }

    public function decorateList(array $content, $ordered = false, $params = array())
    {
        $content = self::addStrongToList($content);

        $wp_list = [];

        if (isset($params['style']) && strstr('line-height:2', $params['style']))
        {
            $wp_list = [
                "style" => [
                    "typography" => [
                        "lineHeight" => 2
                    ]
                ]
            ];
        }

        if ($ordered)
            $wp_list['ordered'] = true;

        return '<!-- wp:list ' . self::jsonEncode($wp_list) . ' -->' . trim(parent::decorateList($content, $ordered, $params)) . '<!-- /wp:list -->' . "";
    }

    public function decorateListItem($content)
    {
        return '<!-- wp:list-item -->' . trim(parent::decorateListItem($content)) . '<!-- /wp:list-item -->' . "";
    }

    public function decorateHeading($heading, $tag = 'h2', $params = array())
    {
        $level = (int) str_replace('h', '', $tag);

        if ($level)
            $wp_heading = array('level' => $level,);
        else
            $wp_heading = array();

        return '<!-- wp:heading ' . self::jsonEncode($wp_heading) . ' -->' . trim(parent::decorateHeading($heading, $tag)) . '<!-- /wp:heading -->' . "";
    }

    public function decorateBlockSubtitle(array $section)
    {
        return '<!-- wp:paragraph {"fontSize":"medium"} --><p class="has-medium-font-size">' . $section['content'] . '</p><!-- /wp:paragraph -->';
    }

    public function decorateBlockNumberedHeading(array $section)
    {
        return self::decorateHeading($section['content'], 'h2');
    }

    public function decorateBlockNumberedHeadingWithSubhiding(array $section)
    {
        return self::decorateHeading($section['content']['heading'], 'h2');
    }

    public function decorateBlockArticleSection(array $section)
    {
        return $this->decorateSectionHtml($section);
    }

    public function decorateBlockComparativeFeatures(array $section)
    {
        $data = $section['content'];
        $short_title_sections = $this->findSectionsAll('ShortProductTitle', '', true);

        $html = '<table class="tmn-comparative-features">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Features</th>';

        foreach ($short_title_sections as $title)
        {
            $html .= '<th>' . esc_html($title['content']) . '</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($data as $d)
        {
            $html .= '<tr>';
            $html .= '<td>' . esc_html($d['name']) . '</td>';

            foreach ($d['values'] as $v)
            {
                $html .= '<td>' . esc_html($v) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $this->decorateSectionHtml($html);
    }

    public function decorateBlockVideo(array $section)
    {
        $escaped_url = esc_url($section['content']);

        $video_block = "";
        $video_block .= "<!-- wp:video -->\n";
        $video_block .= "<figure class=\"wp-block-video\">";
        $video_block .= "<video controls src=\"$escaped_url\"></video>";
        $video_block .= "</figure>\n";
        $video_block .= "<!-- /wp:video -->";

        return $video_block;
    }
}
