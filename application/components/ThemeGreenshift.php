<?php

namespace TooMuchNiche\application\components;

use TooMuchNiche\application\helpers\HtmlToGreenshift;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * ThemeGreenshift class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */

class ThemeGreenshift extends ThemeGutenberg
{

    public function decorateSectionHtml($section)
    {
        if (is_array($section))
            $html = $section['content'];
        else
            $html = $section;

        return HtmlToGreenshift::convert($html);
    }

    public function decorateBlockVersusPost(array $section)
    {
        $prod_shortcodes = $this->findSectionsAll('ProductShortcode', '', true);
        $gallery_shortcodes = $this->findSectionsAll('GalleryRowShortcode', '', true);
        $total = count($prod_shortcodes);
        $formatted_table = '';

        if ($total == 2)
        {
            $content = $section['content'];
            $table_data = array();
            if (preg_match('/<table>.+?<\/table>/', $content, $matches))
            {
                $table_html = $matches[0];
                if ($table_data = self::table2Array($table_html))
                {
                    if (count($table_data[0]) == $total + 1)
                    {
                        $formatted_table = $this->buildVersusTable($table_data);
                        $section['content'] = preg_replace('/<table>.+?<\/table>/', '', $content);
                    }
                }
            }
        }

        $paragraphs = explode("<p>", $section['content']);

        $conclusion = '';
        if (count($paragraphs) > 1)
        {

            // conclusion
            $last = end($paragraphs);
            $last = '<p>' . $last;
            $last = str_replace('Final Summary:', 'Final Summary: ', $last);
            unset($paragraphs[count($paragraphs) - 1]);
            $pre_last = end($paragraphs);
            $pre_last = trim($pre_last);

            if (preg_match('/<h3>[^>]+<\/h3>$/si', $pre_last, $matches))
            {
                $last = $matches[0] . $last;
                $paragraphs[count($paragraphs) - 1] = str_replace($matches[0], '', $paragraphs[count($paragraphs) - 1]);
            }

            $section['content'] = join('<p>', $paragraphs);
            $conclusion = $this->buildVersusConclusion($last);
        }

        $res = $formatted_table;

        $formatted_content = $this->decorateSectionHtml($section);

        // add gallery
        $parts = explode('</h3><!-- /wp:greenshift-blocks/heading -->', $formatted_content);
        if (count($parts) >= $total)
        {
            foreach ($parts as $i => $p)
            {
                if ($i < count($parts) - 1)
                    $parts[$i] .= '</h3><!-- /wp:greenshift-blocks/heading -->';
                if (isset($gallery_shortcodes[$i]))
                    $parts[$i] .= $this->decorateSectionShortcode($gallery_shortcodes[$i]);
            }
            $formatted_content = join('', $parts);
        }

        $res .= $formatted_content . $conclusion;
        return $res;
    }

    public function buildVersusConclusion($html_text)
    {
        $res = '';

        $id = self::generateId();
        $inline = '.gs-box{padding:20px;border-left:5px solid transparent;margin-bottom:25px}.gs-box-text > p{margin-bottom: 20px;margin-top:0}.gs-box-text > p:last-of-type{margin-bottom:0}.gs-box.infolight_type{color:#155724;border:2px solid #c3e6cb;border-radius:12px}.gs-box.infolight_type svg{fill:#8bc799}.gs-box.icon_type{display:flex}.gs-box.icon_type .gs-box-icon{width:28px;min-width:28px}.gs-box.icon_type .gs-box-text{flex-grow:1;margin:0 15px}';
        $res .= '<!-- wp:greenshift-blocks/infobox {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"type":"infolight"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-infobox gspb_infoBox gspb_infoBox-id-gsbp-' . $id . '" id="gspb_infoBox-id-gsbp-' . $id . '"><div class="gs-box infolight_type icon_type"><div class="gs-box-icon"><svg x="0px" y="0px" viewBox="0 0 512 512"> <g><g> <path d="M256,0C114.497,0,0,114.507,0,256c0,141.503,114.507,256,256,256c141.503,0,256-114.507,256-256 C512,114.497,397.492,0,256,0z M256,472c-119.393,0-216-96.615-216-216c0-119.393,96.615-216,216-216 c119.393,0,216,96.615,216,216C472,375.393,375.384,472,256,472z"></path> </g> </g> <g> <g> <path d="M256,214.33c-11.046,0-20,8.954-20,20v128.793c0,11.046,8.954,20,20,20s20-8.955,20-20.001V234.33 C276,223.284,267.046,214.33,256,214.33z"></path> </g> </g> <g> <g> <circle cx="256" cy="162.84" r="27"></circle> </g> </g> </svg></div>';
        $res .= '<div class="gs-box-text">';

        $res .= $this->decorateSectionHtml($html_text);

        if ($shortcode = $this->findSection('ProductConclusionGridShortcode', '', true))
            $res .= $this->decorateShortcode($shortcode['content']);

        $res .= '</div></div></div>';
        $res .= '<!-- /wp:greenshift-blocks/infobox -->';

        return $res;
    }

    public function buildVersusTable(array $table_data)
    {
        $images = $this->findSectionsAll('ProductImageShortcode', '', true);
        $short_title_sections = $this->findSectionsAll('ShortProductTitle', '', true);
        $titles = array();
        foreach ($short_title_sections as $s)
        {
            $titles[] = $s['content'];
        }
        $title = join(' vs. ', $titles);
        if (mb_strlen($title) > 60)
            $title = '';
        $title = '<strong>' . $title . '</strong>';

        $res = '';

        // container
        $id = self::generateId();
        $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . '} -->';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';

        // header row
        $id = self::generateId();
        $id2 = self::generateId();
        $id3 = self::generateId();
        $inline = '#gspb_row-id-gsbp-gsbp-' . $id . '{justify-content: space-between;margin-top: 0px;margin-bottom: 0px;display: flex;flex-wrap: wrap;}#gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content {display: flex;justify-content: space-between;margin: 0 auto;width: 100%;flex-wrap: wrap;}.gspb_row{position:relative;}div[id^=gspb_col-id]{padding:15px min(3vw, 20px);box-sizing:border-box;position:relative;}#gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content{align-items:center;}body.gspb-bodyfront #gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content{max-width:var(\u002d\u002dwp\u002d\u002dstyle\u002d\u002dglobal\u002d\u002dwide-size, 1200px);}#gspb_row-id-gsbp-gsbp-' . $id . '{align-content:center;}#gspb_row-id-gsbp-gsbp-' . $id . '{background-color:#f7f7f7;}';
        $res .= '<!-- wp:greenshift-blocks/row {"id":"gsbp-gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"rowLayout":"8","columnPosition":"center","displayStyles":false,"background":{"color":"#f7f7f7"},"isVariation":""} -->';
        $inline = '#gspb_col-id-gsbp-gsbp-' . $id2 . '.gspb_row__col\u002d\u002d3{width:25%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-gsbp-' . $id2 . '.gspb_row__col\u002d\u002d3{width:100%;}}';
        $res .= '<div class="wp-block-greenshift-blocks-row gspb_row gspb_row-id-gsbp-gsbp-' . $id . '" id="gspb_row-id-gsbp-gsbp-' . $id . '"><div class="gspb_row__content"> <!-- wp:greenshift-blocks/row-column {"id":"gsbp-gsbp-' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"columnSize":"3","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
        $inline = '@media (max-width: 575.98px){#gspb_heading-id-gsbp-' . $id3 . ', #gspb_heading-id-gsbp-' . $id3 . ' .gsap-g-line{text-align:center!important;}}';
        $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--3 gspb_col-id-gsbp-gsbp-' . $id2 . '" id="gspb_col-id-gsbp-gsbp-' . $id2 . '"><!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id3 . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($title) . ',"typography":{"textShadow":{},"alignment":[null,null,null,"center"]},"enablesubTitle":false,"subTitle":"","enablehighlight":false} -->';
        $res .= '<div id="gspb_heading-id-gsbp-' . $id3 . '" class="gspb_heading gspb_heading-id-gsbp-' . $id3 . ' ">' . $title . '</div>';
        $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
        $res .= '<!-- /wp:greenshift-blocks/row-column -->';
        // .vs title

        $id = self::generateId();
        $id2 = self::generateId();
        $id3 = self::generateId();
        $inline = '#gspb_col-id-gsbp-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:75%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:100%;}}';
        $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"flexbox":{"type":"","flexDirection":["column"],"justifyContent":["center"],"alignItems":["center"],"enable":false},"columnSize":"9","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
        $inline = '.gspb_container-id-gsbp-gsbp-' . $id2 . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:20px;column-gap:20px;}@media (max-width: 575.98px){#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container{grid-template-columns:repeat(3,minmax(0,1fr));}}';
        $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--9 gspb_col-id-gsbp-gsbp-' . $id . '" id="gspb_col-id-gsbp-gsbp-' . $id . '">';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-gsbp-' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"grid","enable":false,"gridcolumns":[3,null,null,3],"columngap":["20"],"rowgap":[20]}} -->';
        $inline = '.gspb_container-id-gsbp-' . $id3 . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id3 . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id3 . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-gsbp-' . $id2 . '" id="gspb_container-id-gsbp-gsbp-' . $id2 . '">';

        foreach ($titles as $j => $title)
        {
            if ($j > 0)
            {
                // VS column
                $id = self::generateId();
                $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
                $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"]}} -->';
                $res .=  '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';
                $id = self::generateId();
                $inline = '#gspb_heading-id-gsbp-' . $id . '{font-size:13px;line-height:13px;}#gspb_heading-id-gsbp-' . $id . ', #gspb_heading-id-gsbp-' . $id . ' .gsap-g-line{text-align:center!important;}#gspb_heading-id-gsbp-' . $id . '{padding-top:11px;padding-right:9px;padding-bottom:11px;padding-left:9px;}#gspb_heading-id-gsbp-' . $id . '{border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;}#gspb_heading-id-gsbp-' . $id . '{background-color:#efefef;}';
                $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":"VS","background":{"color":"#efefef"},"border":{"borderRadius":{"values":{"topLeft":50,"topRight":50,"bottomRight":50,"bottomLeft":50},"unit":"px","locked":true},"style":{},"size":{},"color":{},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[11],"right":[9],"bottom":[11],"left":[9]},"unit":["px","px","px","px"],"locked":false}},"typography":{"alignment":["center"],"textShadow":{},"size":[13],"line_height":[13]}} -->';
                $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                $res .= 'VS</div>';
                $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                $res .= '<!-- /wp:greenshift-blocks/container -->';
                // .VS
            }
            // img shortcode
            $id = self::generateId();
            $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
            $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"],"shrinkzero":false},"className":"cegg-versus-img"} -->';
            $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . ' cegg-versus-img" id="gspb_container-id-gsbp-' . $id . '">';
            if (isset($images[$j]))
                $res .= $this->decorateShortcode($images[$j]['content']);
            $res .= '</div><!-- /wp:greenshift-blocks/container -->';
        }

        $res .= '</div><!-- /wp:greenshift-blocks/container --></div>';
        $res .= '<!-- /wp:greenshift-blocks/row-column --> </div></div>';
        $res .= '<!-- /wp:greenshift-blocks/row -->';
        // .header

        for ($i = 0; $i < count($table_data); $i++)
        {
            $values = array();
            $j = 0;
            foreach ($table_data[$i] as $tb)
            {
                if ($j == 0)
                    $feature = $tb;
                else
                    $values[$j - 1] =  $tb;
                $j++;
            }

            // row
            $id = self::generateId();
            $inline = '#gspb_row-id-gsbp-' . $id . '{justify-content: space-between;margin-top: 0px;margin-bottom: 0px;display: flex;flex-wrap: wrap;}#gspb_row-id-gsbp-' . $id . ' > .gspb_row__content {display: flex;justify-content: space-between;margin: 0 auto;width: 100%;flex-wrap: wrap;}.gspb_row{position:relative;}div[id^=gspb_col-id]{padding:15px min(3vw, 20px);box-sizing:border-box;position:relative;}#gspb_row-id-gsbp-' . $id . ' > .gspb_row__content{align-items:center;}body.gspb-bodyfront #gspb_row-id-gsbp-' . $id . ' > .gspb_row__content{max-width:var(\u002d\u002dwp\u002d\u002dstyle\u002d\u002dglobal\u002d\u002dwide-size, 1200px);}#gspb_row-id-gsbp-' . $id . '{align-content:center;}';
            $res .= '<!-- wp:greenshift-blocks/row {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"rowLayout":"8","columnPosition":"center","displayStyles":false,"isVariation":""} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row gspb_row gspb_row-id-gsbp-' . $id . '" id="gspb_row-id-gsbp-' . $id . '"><div class="gspb_row__content">';

            // feature column
            $id = self::generateId();
            $inline = '#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d3{width:25%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d3{width:100%;}}';
            $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"columnSize":"3","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--3 gspb_col-id-gsbp-' . $id . '" id="gspb_col-id-gsbp-' . $id . '">';

            // feature name
            $feature = '<strong>' . $feature . '</strong>';
            $id = self::generateId();
            $inline = '@media (max-width: 575.98px){#gspb_heading-id-' . $id . ', #gspb_heading-id-' . $id . ' .gsap-g-line{text-align:center!important;}}';
            $res .= '<!-- wp:greenshift-blocks/heading {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($feature) . ',"typography":{"textShadow":{},"alignment":[null,null,null,"center"]},"enablesubTitle":false,"subTitle":"","enablehighlight":false} -->';
            $res .= '<div id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . ' ">';
            $res .= $feature;
            $res .= '</div><!-- /wp:greenshift-blocks/heading -->';

            // close feature column
            $res .= '</div><!-- /wp:greenshift-blocks/row-column -->';

            $id = self::generateId();
            $inline = '#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:75%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:100%;}}';
            $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"flexbox":{"type":"","flexDirection":["column"],"justifyContent":["center"],"alignItems":["center"],"enable":false},"columnSize":"9","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--9 gspb_col-id-gsbp-' . $id . '" id="gspb_col-id-gsbp-' . $id . '">';
            $id = self::generateId();
            $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:20px;column-gap:20px;}@media (max-width: 575.98px){#gspb_container-id-gsbp-' . $id . '.gspb_container{grid-template-columns:repeat(3,minmax(0,1fr));}}';
            $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"grid","enable":false,"gridcolumns":[3,null,null,3],"columngap":["20"],"rowgap":[20]}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';

            foreach ($values as $key => $value)
            {
                if ($key > 0)
                {
                    // VS column
                    $id = self::generateId();
                    $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
                    $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"]}} -->';
                    $res .=  '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';
                    $id = self::generateId();
                    $inline = '#gspb_heading-id-gsbp-' . $id . '{font-size:13px;line-height:13px;}#gspb_heading-id-gsbp-' . $id . ', #gspb_heading-id-gsbp-' . $id . ' .gsap-g-line{text-align:center!important;}#gspb_heading-id-gsbp-' . $id . '{padding-top:11px;padding-right:9px;padding-bottom:11px;padding-left:9px;}#gspb_heading-id-gsbp-' . $id . '{border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;}#gspb_heading-id-gsbp-' . $id . '{background-color:#efefef;}';
                    $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":"VS","background":{"color":"#efefef"},"border":{"borderRadius":{"values":{"topLeft":50,"topRight":50,"bottomRight":50,"bottomLeft":50},"unit":"px","locked":true},"style":{},"size":{},"color":{},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[11],"right":[9],"bottom":[11],"left":[9]},"unit":["px","px","px","px"],"locked":false}},"typography":{"alignment":["center"],"textShadow":{},"size":[13],"line_height":[13]}} -->';
                    $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                    $res .= 'VS</div>';
                    $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                    $res .= '<!-- /wp:greenshift-blocks/container -->';
                }

                // value column
                $id = self::generateId();
                $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":".gspb_container-id-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}","flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"],"shrinkzero":false}} -->';
                $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-' . $id . '" id="gspb_container-id-' . $id . '">';
                $id = self::generateId();
                $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","headingTag":"div","headingContent":' . self::jsonEncode($value) . ',"typography":{"textShadow":{},"color":""}} -->';
                $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                $res .= $value;
                $res .= '</div>';
                $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                $res .= '<!-- /wp:greenshift-blocks/container -->';
            }

            //---
            $res .= '</div><!-- /wp:greenshift-blocks/container --></div>';
            $res .= '<!-- /wp:greenshift-blocks/row-column --> ';

            // close row
            $res .= '</div></div><!-- /wp:greenshift-blocks/row -->';
        }
        // close container
        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    public function buildVersusPros($section)
    {
        $res = '';

        // heading
        $inline = '#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item__text{margin-left: 15px;}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item{display:flex;flex-direction:row;align-items:center;position:relative;}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item svg path{fill:#ffffff !important}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item svg, #gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item img{width:16px !important; height:16px !important; min-width: 16px}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__iconbox{border-radius:50%; padding:10px; background-color:#00d084; display:inline-flex}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item{font-size:18px;}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList .gspb_iconsList__item{font-weight:bold!important;}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList{margin-top:20px;margin-bottom:10px;}#gspb_iconsList-id-gsbp-7f5b331.gspb_iconsList [data-id=\'0\'] svg{margin:0px !important;}';
        $res .= '<!-- wp:greenshift-blocks/iconlist {"id":"gsbp-7f5b331","inlineCssStyles":' . json_encode($inline) . ',"iconsList":[{"icon":{"icon":{"font":"rhicon rhi-thumbs-up","svg":"","image":""},"fill":"","fillhover":"","iconSize":[null,null,null,null],"rotateY":false,"rotateX":false,"type":"font"},"content":' . self::jsonEncode($section['heading']) . '}],"colorGlobal":"#ffffff","sizeGlobal":16,"iconBox":true,"iconBoxBg":"#00d084","spacing":{"margin":{"values":{"bottom":["10px"],"top":["20px"]},"locked":false},"padding":{"values":{},"locked":false}},"typography":{"textShadow":{},"color":"","size":["18px"],"customweight":"bold"},"icon_spacing":{"margin":{"values":{},"locked":false},"padding":{"values":{},"locked":false}},"blockWidth":{"customWidth":{"value":[],"unit":["px","px","px","px"]}}} --><div class="wp-block-greenshift-blocks-iconlist gspb_iconsList gspb_iconsList-id-gsbp-7f5b331" id="gspb_iconsList-id-gsbp-7f5b331"><div class="gspb_iconsList__item" data-id="0"><span class="gspb_iconsList__iconbox"><svg class="" style="display:inline-block;vertical-align:middle" width="18" height="18" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path style="fill:#565D66" d="M968.517 573.851c22.662-26.155 35.565-69.381 35.565-110.541 0-27.17-5.899-50.974-17.056-68.842-14.526-23.259-37.762-36.069-65.426-36.069h-134.053c72.966-132.683 91.408-232.587 54.766-297.302-25.534-45.096-72.366-61.098-104.714-61.098-12.811 0-23.65 9.469-25.368 22.165-9.147 67.554-60.811 148.131-141.742 221.074-77.518 69.869-172.765 125.768-270.642 159.208-12.317-26.010-38.811-44.046-69.448-44.046h-153.6c-42.347 0-76.8 34.453-76.8 76.8v460.8c0 42.347 34.453 76.8 76.8 76.8h153.6c32.437 0 60.222-20.226 71.459-48.718 100.421 12.57 138.195 32.754 174.794 52.314 45.802 24.482 89.062 47.605 230.547 47.605 36.854 0 71.587-9.624 97.8-27.101 25.61-17.074 41.968-41.006 47.4-68.755 20.414-8.283 38.544-27.426 52.454-55.893 13.53-27.688 22.272-63.077 22.272-90.166 0-5.069-0.296-9.726-0.89-14.014 12.944-9.528 24.56-24.243 34.152-43.592 13.837-27.912 22.099-62.866 22.099-93.494 0-21.694-4.027-39.802-11.968-53.822-0.645-1.128-1.312-2.234-2.003-3.31zM230.4 921.6h-153.6c-14.115 0-25.6-11.485-25.6-25.6v-460.8c0-14.115 11.485-25.6 25.6-25.6h153.6c14.115 0 25.6 11.485 25.6 25.6v460.738c0 0.022 0 0.043 0 0.066-0.002 14.114-11.486 25.597-25.6 25.597zM938.944 526.014c-7.739 15.546-15.57 21.186-18.944 21.186-14.139 0-25.6 11.461-25.6 25.6s11.461 25.6 25.6 25.6c2.149 0 3.699 0 5.971 4.008 3.378 5.965 5.315 16.382 5.315 28.582 0 22.77-6.427 49.883-16.771 70.754-10.131 20.437-20.451 27.856-24.915 27.856-14.139 0-25.6 11.461-25.6 25.6 0 9.067 4.715 17.034 11.827 21.582 1.581 16.206-5.976 59.629-25.627 87.947-7.438 10.722-15.238 16.87-21.4 16.87-14.139 0-25.6 11.461-25.6 25.6 0 45.072-49.765 65.6-96 65.6-128.659 0-164.691-19.259-206.413-41.56-38.992-20.84-82.864-44.29-193.587-58.085v-419.179c107.558-35.258 212.589-96.114 297.566-172.704 81.554-73.502 135.12-152.979 153.286-226.603 13.933 4.477 29.651 13.896 39.706 31.656 17.096 30.192 29.896 107.299-76.43 284.506-4.746 7.909-4.87 17.758-0.325 25.784s13.053 12.987 22.277 12.987h178.32c10.17 0 16.749 3.586 21.998 11.99 5.986 9.586 9.283 24.402 9.283 41.72 0 21.733-5.211 45.174-13.938 62.702z"></path></svg></span><span class="gspb_iconsList__item__text">' . $section['heading'] . '</span></div></div><!-- /wp:greenshift-blocks/iconlist -->';

        // list
        $res .= $this->decorateAdvancedList($section['content'], array('colorGlobal' => '#00d084', 'icon1' => 'rhi-plus'));

        return $res;
    }

    public function buildVersusCons($section)
    {
        $res = '';

        // heading
        $inline = '#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item__text{margin-left: 15px;}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item{display:flex;flex-direction:row;align-items:center;position:relative;}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item svg path{fill:#ffffff !important}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item svg, #gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item img{width:16px !important; height:16px !important; min-width: 16px}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__iconbox{border-radius:50%; padding:10px; background-color:#de1414; display:inline-flex}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item{font-size:18px;}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList .gspb_iconsList__item{font-weight:bold!important;}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList{margin-top:20px;margin-bottom:10px;}#gspb_iconsList-id-gsbp-7r5f8fcg-5e31.gspb_iconsList [data-id=\'0\'] svg{margin:0px !important;}';
        $res .= '<!-- wp:greenshift-blocks/iconlist {"id":"gsbp-7r5f8fcg-5e31","inlineCssStyles":' . json_encode($inline) . ',"iconsList":[{"icon":{"icon":{"font":"rhicon rhi-thumbs-down","svg":"","image":""},"fill":"","fillhover":"","iconSize":[null,null,null,null],"rotateY":false,"rotateX":false,"type":"font"},"content":' . self::jsonEncode($section['heading']) . '}],"colorGlobal":"#ffffff","sizeGlobal":16,"iconBox":true,"iconBoxBg":"#de1414","spacing":{"margin":{"values":{"bottom":["10px"],"top":["20px"]},"locked":false},"padding":{"values":{},"locked":false}},"typography":{"textShadow":{},"color":"","size":["18px"],"customweight":"bold"},"icon_spacing":{"margin":{"values":{},"locked":false},"padding":{"values":{},"locked":false}},"blockWidth":{"customWidth":{"value":[],"unit":["px","px","px","px"]}}} --><div class="wp-block-greenshift-blocks-iconlist gspb_iconsList gspb_iconsList-id-gsbp-7r5f8fcg-5e31" id="gspb_iconsList-id-gsbp-7r5f8fcg-5e31"><div class="gspb_iconsList__item" data-id="0"><span class="gspb_iconsList__iconbox"><svg class="" style="display:inline-block;vertical-align:middle" width="18" height="18" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path style="fill:#565D66" d="M968.517 450.149c22.662 26.155 35.565 69.381 35.565 110.541 0 27.17-5.899 50.974-17.056 68.842-14.526 23.259-37.762 36.069-65.426 36.069h-134.053c72.966 132.683 91.408 232.587 54.766 297.302-25.534 45.096-72.366 61.098-104.714 61.098-12.811 0-23.65-9.469-25.368-22.165-9.147-67.554-60.811-148.131-141.742-221.074-77.518-69.869-172.765-125.768-270.642-159.208-12.317 26.010-38.811 44.046-69.448 44.046h-153.6c-42.347 0-76.8-34.453-76.8-76.8v-460.8c0-42.347 34.453-76.8 76.8-76.8h153.6c32.437 0 60.222 20.226 71.459 48.718 100.421-12.57 138.195-32.754 174.794-52.314 45.802-24.482 89.062-47.605 230.547-47.605 36.854 0 71.587 9.624 97.8 27.101 25.61 17.074 41.968 41.006 47.4 68.755 20.414 8.283 38.544 27.426 52.454 55.893 13.53 27.688 22.272 63.077 22.272 90.166 0 5.069-0.296 9.726-0.89 14.014 12.944 9.528 24.56 24.243 34.152 43.592 13.837 27.912 22.099 62.866 22.099 93.494 0 21.694-4.027 39.802-11.968 53.822-0.645 1.128-1.312 2.234-2.003 3.31zM230.4 102.4h-153.6c-14.115 0-25.6 11.485-25.6 25.6v460.8c0 14.115 11.485 25.6 25.6 25.6h153.6c14.115 0 25.6-11.485 25.6-25.6v-460.738c0-0.022 0-0.043 0-0.066-0.002-14.114-11.486-25.597-25.6-25.597zM938.944 497.986c-7.739-15.546-15.57-21.186-18.944-21.186-14.139 0-25.6-11.461-25.6-25.6s11.461-25.6 25.6-25.6c2.149 0 3.699 0 5.971-4.008 3.378-5.965 5.315-16.382 5.315-28.582 0-22.77-6.427-49.883-16.771-70.754-10.131-20.437-20.451-27.856-24.915-27.856-14.139 0-25.6-11.461-25.6-25.6 0-9.067 4.715-17.034 11.827-21.582 1.581-16.206-5.976-59.629-25.627-87.947-7.438-10.722-15.238-16.87-21.4-16.87-14.139 0-25.6-11.461-25.6-25.6 0-45.072-49.765-65.6-96-65.6-128.659 0-164.691 19.259-206.413 41.56-38.992 20.84-82.864 44.29-193.587 58.085v419.179c107.558 35.258 212.589 96.114 297.566 172.704 81.554 73.502 135.12 152.979 153.286 226.603 13.933-4.477 29.651-13.896 39.706-31.656 17.096-30.192 29.896-107.299-76.43-284.506-4.746-7.909-4.87-17.758-0.325-25.784s13.053-12.987 22.277-12.987h178.32c10.17 0 16.749-3.586 21.998-11.99 5.986-9.586 9.283-24.402 9.283-41.72 0-21.733-5.211-45.174-13.938-62.702z"></path></svg></span><span class="gspb_iconsList__item__text">' . $section['heading'] . '</span></div></div><!-- /wp:greenshift-blocks/iconlist -->';

        // list
        $res .= $this->decorateAdvancedList($section['content'], array('colorGlobal' => '#de1414', 'icon' => 'rhi-times-circle-solid'));

        return $res;
    }

    public function buildVersusFlexbox($content)
    {
        $id = self::generateId();

        $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{padding-top:0px;padding-right:15px;padding-bottom:15px;padding-left:15px;}';
        $res = '';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"","flexDirection":["row"],"columngap":["15px"],"rowgap":["15px"],"enable":false},"spacing":{"margin":{"values":{},"locked":false},"padding":{"values":{"top":["0px"],"right":["15px"],"bottom":["15px"],"left":["15px"]},"locked":false}},"isVariation":"flexbox"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';
        $res .= $content;
        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    public function buildVersusRow($content, $col_count = 2)
    {
        $id = self::generateId();

        $inline = '#gspb_row-id-' . $id . '{justify-content: space-between;margin-top: 0px;margin-bottom: 0px;display: flex;flex-wrap: wrap;}#gspb_row-id-' . $id . ' > .gspb_row__content {display: flex;justify-content: space-between;margin: 0 auto;width: 100%;flex-wrap: wrap;}.gspb_row{position:relative;}div[id^=gspb_col-id]{padding:15px min(3vw, 20px);box-sizing:border-box;position:relative;}#gspb_row-id-' . $id . ' > .gspb_row__content{max-width:1200px;}#gspb_row-id-' . $id . '{margin-top:0px;margin-bottom:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;}#gspb_row-id-' . $id . '{border-style:solid;border-width:1px;border-color:#cecece;}';

        $res = '<!-- wp:greenshift-blocks/row {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"rowLayout":"' . $col_count . '","displayStyles":false,"border":{"borderRadius":{"values":{},"locked":true},"style":{"all":["solid"]},"size":{"all":[1]},"color":{"all":["#cecece"]},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{"top":["0px"],"bottom":["0px"]},"locked":false},"padding":{"values":{"top":["0px"],"right":["0px"],"bottom":["0px"],"left":["0px"]},"locked":false}},"isVariation":""} -->';
        $res .= '<div class="wp-block-greenshift-blocks-row gspb_row gspb_row-id-' . $id . '" id="gspb_row-id-' . $id . '">';
        $res .= '<div class="gspb_row__content"> ';
        $res .= $content;
        $res .= '</div></div>';
        $res .= '<!-- /wp:greenshift-blocks/row -->';

        return $res;
    }

    public function buildVersusColumn($content, $num, $col_count = 2)
    {
        $col_size = round(12 / $col_count);

        if ($num >= $col_count - 1)
            $border_right = 0;
        else
            $border_right = 2;

        $col_with = 100 / $col_count;

        $id = self::generateId();

        $inline = '#gspb_col-id-' . $id . '.gspb_row__col--' . $col_size . '{width:' . $col_with . '%;}@media (max-width: 575.98px){#gspb_col-id-' . $id . '.gspb_row__col--' . $col_size . '{width:100%;}}.gspb_row #gspb_col-id-' . $id . '.gspb_row__col--' . $col_size . '{margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;}#gspb_col-id-' . $id . '.gspb_row__col--' . $col_size . '{border-right-style:dashed;border-right-width:' . $border_right . 'px;border-right-color:#cecece;}';

        $res = '<!-- wp:greenshift-blocks/row-column {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"columnSize":"' . $col_size . '","border":{"borderRadius":{"values":{},"locked":true},"style":{"right":["dashed"]},"size":{"right":[' . $border_right . ']},"color":{"right":["#cecece"]},"styleHover":{},"sizeHover":{},"colorHover":{},"custom":{},"customEnabled":{}},"spacing":{"margin":{"values":{"top":["0px"],"right":["0px"],"bottom":["0px"],"left":["0px"]},"locked":false},"padding":{"values":{"top":["0px"],"right":["0px"],"bottom":["0px"],"left":["0px"]},"locked":false}},"className":"tmn-versus-col"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--' . $col_size . ' gspb_col-id-' . $id . ' tmn-versus-col" id="gspb_col-id-' . $id . '">';
        $res .= $content;
        $res .= '</div>';
        $res .= '<!-- /wp:greenshift-blocks/row-column --> ';

        return $res;
    }

    public function buildVersusRating($rating)
    {
        if (!$rating)
            return '';

        $res = '';

        // container
        $id = self::generateId();
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":".gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;justify-content:center;}","flexbox":{"enable":true,"justifyContent":["center"]}} -->';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';

        $id = self::generateId();
        $inline = '.gspb_bar-id-' . $id . ' .gspb-progressbar_circle{height:100px;width:100px;border-radius:50%;justify-content:center;align-items:center;display:flex;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle svg{height:100px;width:100px;position:absolute;z-index:0;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle_value{font-size:32px;font-weight:700;color:#111111;z-index:1;position:relative;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle svg path{stroke-width:6px;stroke:#2184f9;fill:#f2fffe;}';
        $stroke = $rating * 10 + 0.3;
        $res .= '<!-- wp:greenshift-blocks/progressbar {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"progress":' . json_encode($rating) . ',"maxvalue":10,"progressheight":[6],"fontsize":[32],"circleSize":[100],"progressline":"#2184f9","progressbg":"#f2fffe","typebar":"circle"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-progressbar gs-progressbar gs-progressbar-wrapper gspb_bar-id-' . $id . '" style="padding-top:10px;padding-bottom:10px;margin-top:0;margin-bottom:0"><div class="gs-progressbar-wrapper"><div class="gspb-progressbar_circle"><svg viewBox="0 0 36 36"><path id="ld-' . $id . '" d="M18 2a16 16 0 010 32 16 16 0 010-32" style="stroke-dasharray:' . $stroke . ', 100" clip-path="url(#clip-' . $id . ')"></path><clipPath id="clip-' . $id . '"><use xlink:href="#ld-' . $id . '"></use></clipPath></svg><div class="gspb-progressbar_circle_value">' . $rating . '</div></div></div></div>';
        $res .= '<!-- /wp:greenshift-blocks/progressbar -->';

        // container
        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    public function wrapperStart($section, $class = null)
    {
        if (!$tag = $this->getWrapperTag($section))
            return '';

        if (!$class)
            $class = $this->getWrapperClass($section);

        $class .= ' is-layout-constrained';

        $id = self::generateId();

        $res = '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","htmlTag":"' . $tag . '","className":"' . $class . '"} -->';
        $res .= '<' . $tag . ' id="gspb_container-id-' . $id . '" class="gspb_container gspb_container-' . $id . ' wp-block-greenshift-blocks-container ' . $class . '">';

        return $res;
    }

    public function wrapperEnd($section)
    {
        if (!$tag = $this->getWrapperTag($section))
            return '';

        $res = '</' . $tag . '>';
        $res .= '<!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    public function decorateBlockCtaText(array $section)
    {
        $res = '';
        $id = self::generateId();
        $inline = '#gspb_container-id-' . $id . '.gspb_container{position: relative;flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id . '.gspb_container .gspb_container__content{margin:auto;}#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}body.gspb-bodyfront #gspb_container-id-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;row-gap:0px;}#gspb_container-id-' . $id . '.gspb_container{box-shadow: 0 15px 25px 0 rgba(0, 0, 0, 0.1);}#gspb_container-id-' . $id . '.gspb_container{padding-top:10px;padding-right:30px;padding-bottom:30px;padding-left:30px;}#gspb_container-id-' . $id . '.gspb_container{border-width:11px;border-top-left-radius:15px;border-top-right-radius:15px;border-bottom-right-radius:15px;border-bottom-left-radius:15px;}#gspb_container-id-' . $id . '.gspb_container > .gspb_backgroundOverlay{border-top-left-radius:15px;border-top-right-radius:15px;border-bottom-right-radius:15px;border-bottom-left-radius:15px;}#gspb_container-id-' . $id . '.gspb_container{background-color:#f2ffe8;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"enable":false,"type":"flexbox","flexDirection":["column"],"justifyContent":["center"],"alignItems":["center"],"columngap":[null],"rowgap":[0]},"background":{"color":"#f2ffe8"},"border":{"borderRadius":{"values":{"topLeft":["15"],"topRight":["15"],"bottomRight":["15"],"bottomLeft":["15"]},"unit":"px","locked":true},"style":{},"size":{"all":[11]},"color":{},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[10],"right":[30],"bottom":[30],"left":[30]},"unit":["px","px","px","px"],"locked":false}},"shadow":{"hoffset":0,"voffset":15,"blur":25,"spread":0,"color":"rgba(0, 0, 0, 0.1)","position":null}} -->';
        $res .= '<div id="gspb_container-id-' . $id . '" class="gspb_container gspb_container-' . $id . ' wp-block-greenshift-blocks-container"><!-- wp:paragraph --><p>';
        $res .= $section['content'];
        $res .= '</p><!-- /wp:paragraph -->';

        if ($shortcode = $this->findSection('CeShortcodeCtaButton', $section['group'], true))
            $res .= $this->decorateShortcode($shortcode['content']);

        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    // schema embedded in accordion element
    public function decorateBlockFaqSchema($content)
    {
        return  '';
    }

    public function decorateSectionFaq(array $section)
    {
        HtmlToGreenshift::setListIconMode('fixed');

        $res = '';

        $id = self::generateId();
        $id2 = self::generateId();
        $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container \u003e p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . '} -->';

        $inline = '\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item.gsclose \u003e .gs-accordion-item__content{display:none}\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title \u003e .gs-accordion-item__heading{outline:0;text-decoration:none;margin:0 !important;padding:0!important;flex-grow:1}\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title{z-index:1;margin:0;cursor:pointer;transition:all .3s ease-in-out; position: relative;display: flex;justify-content: space-between;align-items: center;flex-wrap:nowrap}\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title span.iconfortoggle{display:inline-block;height:14px;width:14px; position:relative}\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content{z-index:0;position:relative;}\n\t\t#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content.stuckMoveDownOpacity{animation:stuckMoveDownOpacity .6s}\n\t\t@keyframes stuckMoveDownOpacity{0%{transform:translateY(-15px);opacity:0}100%{transform:translateY(0);opacity:1}}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title{background-color:#f9f9f9;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title{border-style:solid;border-width:1px;border-color:#00000012;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content{background-color:#ffffff;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content{border-style:solid;border-width:1px;border-color:#00000012;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content{border-top-style:solid;border-top-width:1px;border-top-color:#00000000;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title span.iconfortoggle{margin-left:15px;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item{margin-bottom:10px;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__title{padding-top:15px;padding-right:20px;padding-bottom:15px;padding-left:20px;}#gspb_accordion-id-gsbp-' . $id2 . ' .gs-accordion-item \u003e .gs-accordion-item__content{padding-top:15px;padding-right:20px;padding-bottom:15px;padding-left:20px;}.gs-accordion .gs-accordion-item.gsopen .gs-accordion-item__title .gs-iconafter{transform:rotate(0)}.gs-accordion .gs-accordion-item__title span.iconfortoggle .gs-iconbefore{content:\'\';width:14px;height:2px;border-radius:2px;background-color:#111;position:absolute;top:6px;left:0}\n\t\t.gs-accordion .gs-accordion-item__title span.iconfortoggle .gs-iconafter{content:\' \';width:14px;height:2px;border-radius:2px;background-color:#111;position:absolute;top:6px;transform:rotate(90deg);transition:all .3s ease-in-out; left:0}';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '"><!-- wp:greenshift-blocks/accordion {"id":"gsbp-' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"seoschema":true,"tabs":2,"typography":{"textShadow":{},"size":["100%"]},"enableIcon":false,"iconBox_icon":{"icon":{"font":"rhicon rhi-times-circle-solid","svg":"","image":""},"fill":"currentColor","fillhover":"currentColor","type":"font","iconSize":[16]}} -->';

        $res .= '<div class="wp-block-greenshift-blocks-accordion gs-accordion gspb_accordion-id-gsbp-' . $id2 . '" id="gspb_accordion-id-gsbp-' . $id2 . '" itemscope itemtype="https://schema.org/FAQPage">';
        foreach ($section['content'] as $faq)
        {
            $id3 = self::generateId();

            $res .= '<!-- wp:greenshift-blocks/accordionitem {"id":"gsbp-' . $id3 . '","title":' . self::jsonEncode($faq['question']) . ',"open":true,"seoschema":true,"enableIcon":false,"iconBox_icon":{"icon":{"font":"rhicon rhi-times-circle-solid","svg":"","image":""},"fill":"currentColor","fillhover":"currentColor","type":"font","iconSize":[16]}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-accordionitem gs-accordion-item gspb_accordionitem-gsbp-' . $id3 . ' gsopen" id="gspb_accordionitem-gsbp-' . $id3 . '" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"><div class="gs-accordion-item__title" aria-expanded="true" role="button" tabindex="0" aria-controls="gspb-accordion-item-content-gsbp-' . $id3 . '"><div class="gs-accordion-item__heading">' . $faq['question'] . '</div><meta itemprop="name" content=' . self::jsonEncode($faq['question']) . '/><span class="iconfortoggle"><span class="gs-iconbefore"></span><span class="gs-iconafter"></span></span></div><div class="gs-accordion-item__content" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer" id="gspb-accordion-item-content-gsbp-' . $id3 . '" aria-hidden="false"><div class="gs-accordion-item__text" itemprop="text">';

            $res .= $this->decorateSectionHtml($faq['answer']);

            $res .= '</div></div></div> ';
            $res .= '<!-- /wp:greenshift-blocks/accordionitem -->';
        }
        $res .= '</div> ';
        $res .= '<!-- /wp:greenshift-blocks/accordion --></div> ';
        $res .= '<!-- /wp:greenshift-blocks/container --> ';

        return $res;
    }

    public function decorateHeadingProductCardShortcode($section)
    {
        if (empty($section['position']))
            return $this->decorateHeading($section['heading'], 'h2');

        return $this->decorateHeadingNumbered($section['heading'], $section['position']);
    }

    public function decorateBlockReviewConclusion($section)
    {
        $res = '';

        if ($rating_section = $this->findSection('Rating', $section['group'], true))
            $rating = $rating_section['content'];
        else
            $rating = 0;

        $id1 = self::generateId();
        $id2 = self::generateId();
        $id3 = self::generateId();
        $id4 = self::generateId();

        $stroke = $rating * 10 + 0.3;

        $inline = '#gspb_container-id-' . $id1 . '.gspb_container{position: relative;flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id1 . '.gspb_container .gspb_container__content{margin:auto;}#gspb_container-id-' . $id1 . '.gspb_container > p:last-of-type{margin-bottom:0}body.gspb-bodyfront #gspb_container-id-' . $id1 . '.gspb_container{display:flex;flex-direction:row;row-gap:20px;column-gap:20px;}@media (max-width: 575.98px){body.gspb-bodyfront #gspb_container-id-' . $id1 . '.gspb_container{flex-direction:column;align-items:center;}}#gspb_container-id-' . $id1 . '.gspb_container{box-shadow: 0 15px 25px 0 rgba(0, 0, 0, 0.1);}#gspb_container-id-' . $id1 . '.gspb_container{margin-bottom:30px;padding-top:15px;padding-right:20px;padding-bottom:25px;padding-left:15px;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id1 . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","flexDirection":["row",null,null,"column"],"columngap":[20],"rowgap":[20],"alignItems":[null,null,null,"center"]},"spacing":{"margin":{"values":{"bottom":[30]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"bottom":[25],"top":[15],"left":[15],"right":[20]},"unit":["px","px","px","px"],"locked":false}},"shadow":{"hoffset":0,"voffset":15,"blur":25,"spread":0,"color":"rgba(0, 0, 0, 0.1)","position":null}} -->';
        $res .= '<div id="gspb_container-id-' . $id1 . '" class="gspb_container gspb_container-' . $id1 . ' wp-block-greenshift-blocks-container">';

        $id = self::generateId();
        $inline = '.gspb_bar-id-' . $id . ' .gspb-progressbar_circle{height:100px;width:100px;border-radius:50%;justify-content:center;align-items:center;display:flex;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle svg{height:100px;width:100px;position:absolute;z-index:0;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle_value{font-size:32px;font-weight:700;color:#111111;z-index:1;position:relative;}.gspb_bar-id-' . $id . ' .gspb-progressbar_circle svg path{stroke-width:6px;stroke:#2184f9;fill:#f2fffe;}';

        $res .= '<!-- wp:greenshift-blocks/progressbar {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"progress":' . json_encode($rating) . ',"maxvalue":10,"progressheight":[6],"fontsize":[32],"circleSize":[100],"progressline":"#2184f9","progressbg":"#f2fffe","typebar":"circle"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-progressbar gs-progressbar gs-progressbar-wrapper gspb_bar-id-' . $id . '" style="padding-top:10px;padding-bottom:10px;margin-top:0;margin-bottom:0"><div class="gs-progressbar-wrapper"><div class="gspb-progressbar_circle"><svg viewBox="0 0 36 36"><path id="ld-' . $id . '" d="M18 2a16 16 0 010 32 16 16 0 010-32" style="stroke-dasharray:' . $stroke . ', 100" clip-path="url(#clip-' . $id . ')"></path><clipPath id="clip-' . $id . '"><use xlink:href="#ld-' . $id . '"></use></clipPath></svg><div class="gspb-progressbar_circle_value">' . $rating . '</div></div></div></div>';
        $res .= '<!-- /wp:greenshift-blocks/progressbar -->';

        $inline = '#gspb_container-id-' . $id2 . '.gspb_container{position: relative;flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id2 . '.gspb_container .gspb_container__content{margin:auto;}#gspb_container-id-' . $id2 . '.gspb_container > p:last-of-type{margin-bottom:0}body.gspb-bodyfront #gspb_container-id-' . $id2 . '.gspb_container{display:flex;flex-direction:column;row-gap:10px;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"enable":true,"marginUnit":["px","px","px","px"],"marginLock":false,"marginTop":[null,null,null,null],"marginBottom":[null,null,null,null],"marginLeft":[null,null,null,null],"marginRight":[null,null,null,null],"type":"flexbox","flexDirection":["column"],"columngap":[null],"rowgap":[10]}} -->';
        $res .= '<div id="gspb_container-id-' . $id2 . '" class="gspb_container gspb_container-' . $id2 . ' wp-block-greenshift-blocks-container">';

        $inline = '#gspb_heading-id-' . $id3 . ', #gspb_heading-id-' . $id3 . ' .wp-block{font-size:26px;line-height:32px;}#gspb_heading-id-' . $id3 . ', #gspb_heading-id-' . $id3 . ' .wp-block{font-weight:normal!important;}#gspb_heading-id-' . $id3 . '{padding-top:0px;padding-bottom:5px;}';
        $res .= '<!-- wp:greenshift-blocks/heading {"id":"' . $id3 . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($section['heading']) . ',"spacing":{"margin":{"values":{"top":[null]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[0],"bottom":[5]},"unit":["px","px","px","px"],"locked":false}},"typography":{"textShadow":{},"size":["26px"],"customweight":"normal","line_height":["32px"]}} -->';
        $res .= '<div id="gspb_heading-id-' . $id3 . '" class="gspb_heading gspb_heading-id-' . $id3 . ' ">' . $section['heading'] . '</div><!-- /wp:greenshift-blocks/heading -->';
        $res .= '<!-- wp:greenshift-blocks/text {"id":"' . $id4 . '","textContent":' . self::jsonEncode($section['content']) . ',"typography":{"textShadow":{},"customweight":null}} -->';
        $res .= '<div id="gspb_text-id-' . $id4 . '" class="gspb_text gspb_text-id-' . $id4 . ' ">' . $section['content'] . '</div>';
        $res .= '<!-- /wp:greenshift-blocks/text --></div>';
        $res .= '<!-- /wp:greenshift-blocks/container --></div>';
        $res .= '<!-- /wp:greenshift-blocks/container -->';

        return $res;
    }

    public function decorateHeadingReviewConclusion(array $section)
    {
        return '';
    }

    public function decorateSectionHtmlH($content)
    {
        if (!preg_match_all('/<h(\d)>(.+?)<\/h\d>/ims', $content, $matches))
            return $content;

        foreach ($matches[1] as $i => $h)
        {
            $heading_content = $matches[2][$i];
            $heading_tag = 'h' . $h;

            $id = self::generateId();
            $params = array(
                'id' => $id,
                'headingTag' => $heading_tag,
                'headingContent' => $heading_content,
            );

            $search = '<' . $heading_tag . '>' . $heading_content . '</' . $heading_tag . '>';

            $replace = '<!-- wp:greenshift-blocks/heading ' . self::jsonEncode($params) . ' --><' . $heading_tag . ' id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . '">';
            $replace .= $heading_content;
            $replace .= '</' . $heading_tag . '><!-- /wp:greenshift-blocks/heading -->';

            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    public function decorateBlockFeaturesList($section)
    {
        $res = '';

        $id = self::generateId();

        $inline = '.gs-titlebox{margin-bottom:30px;background:#fff;line-height:24px;font-size:90%;box-shadow: 0 10px 10px #00000007;margin-bottom:25px}.gs-titlebox .gs-title-inbox{display:flex;align-content:center;padding:15px 20px;font-weight:700;font-size:115%}.gs-titlebox .gs-title-inbox-label{flex-grow:1}.gs-titlebox .gs-titlebox-text{padding:20px}.gs-titlebox-text>p{margin-bottom:20px !important; margin-top:0}.gs-titlebox-text>p:last-of-type{margin-bottom:0 !important}#gspb_titleBox-id-' . $id . ' .gs-titlebox{border-radius: 15px}#gspb_titleBox-id-' . $id . ' .gs-title-inbox {border-radius:15px 15px 0 0}#gspb_titleBox-id-' . $id . ' .gs-titlebox .gs-title-inbox{background-color:#ffc107;}#gspb_titleBox-id-' . $id . ' .gs-titlebox{box-shadow: 0 15px 25px 0 rgba(0, 0, 0, 0.1);}';

        $res .= '<!-- wp:greenshift-blocks/titlebox {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"title":' . self::jsonEncode($section['heading']) . ',"radius":15,"shadow":{"hoffset":0,"voffset":15,"blur":25,"spread":0,"color":"rgba(0, 0, 0, 0.1)","position":null},"titlebox_icon":{"icon":{"font":"rhicon rhi-info-circle","svg":"","image":""},"fill":"#ffffff","fillhover":"#2184f9","type":"font"},"titlebackground":{"color":"#ffc107"}} -->';
        $res .= '<div id="gspb_titleBox-id-' . $id . '" class="gspb_titleBox gspb_titleBox-id-' . $id . ' wp-block-greenshift-blocks-titlebox"><div class="gs-titlebox"><div class="gs-title-inbox"><div class="gs-titlebox-icon"></div><div class="gs-title-inbox-label">' . $section['heading'] . '</div></div><div class="gs-titlebox-text">';
        $res .= $this->decorateAdvancedList($section['content'], array('size' => '110%', 'colorGlobal' => '#FFC107', 'icon' => 'rhi-check-circle-solid'));
        $res .= '</div></div></div>';
        $res .= '<!-- /wp:greenshift-blocks/titlebox -->';

        return $res;
    }

    public function decorateBlockProductSpecifications($section)
    {
        $res = '';
        if ($section['heading'])
            $res .= $this->decorateHeading($section['heading'], 'div');

        $res .= $this->decorateAdvancedList($section['content'], array());
        return $res;
    }

    public function decorateAdvancedList2($items, $colorGlobal, $svgIcon)
    {

        $block_id = self::generateId();
        $inlineCssStyles = "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item__text{margin-left: 15px;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item{display:flex;flex-direction:row;align-items:center;position:relative;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg path{fill:{$colorGlobal} !important;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg, #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item img{width:18px !important; height:18px !important; min-width: 18px;}"
            . "body #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item svg, body #gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item img{margin:0px !important;}"
            . "#gspb_iconsList-id-{$block_id}.gspb_iconsList .gspb_iconsList__item{margin-bottom:10px;}";

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

    public function decorateAdvancedList(array $content, $params = array())
    {
        $id = self::generateId();

        if (isset($params['icon']))
            $icon = $params['icon'];
        else
            $icon = 'rhi-check';

        $content = self::addStrongToList($content);

        $icons_list = [];
        foreach ($content as $c)
        {
            $icons_list[] = [
                "icon" => [
                    "icon" => [
                        "font" => "rhicon " . $icon,
                        "svg" => "",
                        "image" => "",
                    ],
                    "fill" => "",
                    "fillhover" => "",
                    "iconSize" => [null, null, null, null],
                    "rotateY" => false,
                    "rotateX" => false,
                    "type" => "font",
                ],
                "content" => $c,
            ];
        }

        if (isset($params['colorGlobal']))
            $color = $params['colorGlobal'];
        else
            $color = '#2184F9';

        if (isset($params['size']))
            $size = $params['size'];
        else
            $size = '100%';

        $inline = '#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item__text{margin-left: 15px;}#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item{justify-content:flex-start;display:flex;flex-direction:row;align-items:center;position:relative;}#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item svg path{fill:' . $color . ' !important}#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item svg, #gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item img{width:18px !important; height:18px !important; min-width: 18px}#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item__text, #gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item__text .wp-block{font-size:' . $size . '}#gspb_iconsList-id-' . $id . '.gspb_iconsList .gspb_iconsList__item{margin-bottom:12px;}#gspb_iconsList-id-' . $id . '.gspb_iconsList ul{margin:0;padding:0}';

        $block_params = [
            "id" => $id,
            "inlineCssStyles" => $inline,
            "iconsList" => $icons_list,
            "direction" => "flex-start",
            "textTag" => "li",
            "sizeGlobal" => 18,
            "iconBox" => false,
            "iconRight" => false,
            "listInline" => false,
            "iconBoxBg" => "#f1f0ff",
            "iconBoxPadding" => 7,
            "currentItem" => "0",
            "spacingList" => [
                "margin" => [
                    "values" => ["bottom" => [12]],
                    "unit" => ["px", "px", "px", "px"],
                    "locked" => false,
                ],
                "padding" => [
                    "values" => [],
                    "unit" => ["px", "px", "px", "px"],
                    "locked" => false,
                ],
                "overflow" => [null, null, null, null],
            ],
            "flexcolumns" => false,
            "flexbox" => ["type" => "grid"],
        ];

        if (isset($params['size']))
            $block_params['typography'] = ["size" => [$params['size']]];

        if (isset($params['colorGlobal']))
            $block_params['colorGlobal'] = $params['colorGlobal'];

        if ($icon == 'rhi-check-circle-solid')
            $icon_svg = 'M1008 512c0 273.934-222.066 496-496 496s-496-222.066-496-496 222.066-496 496-496 496 222.066 496 496zM454.628 774.628l368-368c12.496-12.496 12.496-32.758 0-45.254l-45.254-45.254c-12.496-12.498-32.758-12.498-45.256 0l-300.118 300.116-140.118-140.118c-12.496-12.496-32.758-12.496-45.256 0l-45.254 45.254c-12.496 12.496-12.496 32.758 0 45.254l208 208c12.498 12.498 32.758 12.498 45.256 0.002z';
        elseif ($icon == 'rhi-times-circle-solid')
            $icon_svg = 'M512 16c-274 0-496 222-496 496s222 496 496 496 496-222 496-496-222-496-496-496zM755.2 642.2c9.4 9.4 9.4 24.6 0 34l-79.2 79c-9.4 9.4-24.6 9.4-34 0l-130-131.2-130.2 131.2c-9.4 9.4-24.6 9.4-34 0l-79-79.2c-9.4-9.4-9.4-24.6 0-34l131.2-130-131.2-130.2c-9.4-9.4-9.4-24.6 0-34l79.2-79.2c9.4-9.4 24.6-9.4 34 0l130 131.4 130.2-131.2c9.4-9.4 24.6-9.4 34 0l79.2 79.2c9.4 9.4 9.4 24.6 0 34l-131.4 130 131.2 130.2z';
        else
            $icon_svg = 'M871.696 166.932l-526.088 526.088-193.304-193.304c-9.372-9.372-24.568-9.372-33.942 0l-56.568 56.568c-9.372 9.372-9.372 24.568 0 33.942l266.842 266.842c9.372 9.372 24.568 9.372 33.942 0l599.626-599.626c9.372-9.372 9.372-24.568 0-33.942l-56.568-56.568c-9.372-9.372-24.568-9.372-33.94 0z';

        $res = '<!-- wp:greenshift-blocks/iconlist ' . self::jsonEncode($block_params) . ' -->';
        $res .= '<div id="gspb_iconsList-id-' . $id . '" class="gspb_iconsList gspb_iconsList-id-' . $id . ' wp-block-greenshift-blocks-iconlist"><ul>';

        foreach ($content as $i => $c)
        {
            $res .= '<li class="gspb_iconsList__item" data-id="' . $i . '"><svg class="" style="display:inline-block;vertical-align:middle" width="18" height="18" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path style="fill:#565D66" d=' . json_encode($icon_svg) . '></path></svg><span class="gspb_iconsList__item__text">' . $c . '</span></li>';
        }

        $res .= '</ul></div>';
        $res .= '<!-- /wp:greenshift-blocks/iconlist -->';

        return $res;
    }

    public function decorateProsCons(array $section)
    {
        HtmlToGreenshift::resetIconPointer();

        $pros = $this->findSection('Pros', $section['group'], true);
        $cons = $this->findSection('Cons', $section['group'], true);

        if (!$pros || !$cons)
            return '';

        $cols = [
            'pros' => [
                'items' => $pros['content'],
                'heading' => $pros['heading'],
                'iconColor' => '#00D084',
                'iconSvg' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>',
            ],
            'cons' => [
                'items' => $cons['content'],
                'heading' => $cons['heading'],
                'iconColor' => '#DE1414',
                'iconSvg' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>',
            ],
        ];

        $res = '';

        // wrapper start
        $id = self::generateId();
        $inline = '.gspb_container-id-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}#gspb_container-id-' . $id . '.gspb_container{position:relative;}#gspb_container-id-' . $id . '.gspb_container{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:35px;}@media (max-width: 575.98px){#gspb_container-id-' . $id . '.gspb_container{grid-template-columns:repeat(1,minmax(0,1fr));}}#gspb_container-id-' . $id . '.gspb_container{margin-bottom:30px;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"grid","enable":false,"gridcolumns":[2,null,null,1],"columngap":["35"],"rowgap":[null]},"animation":{"duration":700,"easing":"ease","usegsap":false,"y":26},"spacing":{"margin":{"values":{"bottom":["30px"]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"shadow":{"hoffset":0,"voffset":15,"blur":30,"spread":0,"color":"","position":null}} --><div class="wp-block-greenshift-blocks-container gspb_container gspb_container-' . $id . '" id="gspb_container-id-' . $id . '">';

        foreach ($cols as $col)
        {
            // column start
            $id = self::generateId();
            $inline = '.gspb_container-id-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}#gspb_container-id-' . $id . '.gspb_container{position:relative;}';
            $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"animation":{"duration":700,"easing":"ease","usegsap":false,"y":"","o":"","stagger":"","x":"","z":"","s":"","r":"","rx":"","ry":"","text":"","texttype":"","textdelay":"","textrandom":"","staggerdelay":"","staggerrandom":"","origin":"","anchor":""},"minHeightUnit":["px","px","px","px"],"width":[null,null,null,null],"widthUnit":["%","%","%","%"],"minHeight":[null,null,null,null],"className":"tmn-pros"} --><div class="wp-block-greenshift-blocks-container gspb_container gspb_container-' . $id . ' tmn-pros" id="gspb_container-id-' . $id . '">';

            // heading
            $id = self::generateId();
            $headingContent = $col['heading'];
            $inline = '#gspb_heading-id-' . $id . '{font-size:19px;}#gspb_heading-id-' . $id . '{font-weight:bold!important;}#gspb_heading-id-' . $id . '{margin-bottom:22px;}';
            $res .= '<!-- wp:greenshift-blocks/heading {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($headingContent) . ',"spacing":{"margin":{"values":{"bottom":["22px"]},"locked":false},"padding":{"values":{},"locked":false}},"typography":{"textShadow":{},"themeFontsizePresetEnable":false,"themeFontsizePreset":"small","size":["19px"],"line_height":[0],"customweight":"bold"}} -->';
            $res .= '<div id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . ' ">' . $headingContent . '</div>';
            $res .= '<!-- /wp:greenshift-blocks/heading -->';

            // list
            $res .= $this->decorateAdvancedList2($col['items'], $col['iconColor'], $col['iconSvg']);

            // column end
            $res .= '</div><!-- /wp:greenshift-blocks/container -->';
        }

        // wrapper end
        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

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

    public function decorateHeading($heading_content, $heading_tag = 'h2', $params = array())
    {
        $id = self::generateId();

        if ($heading_tag == 'div')
        {
            $inline = '#gspb_heading-id-' . $id . ', #gspb_heading-id-' . $id . ' .wp-block{font-size:115%;}#gspb_heading-id-' . $id . ', #gspb_heading-id-' . $id . ' .wp-block{font-weight:bold!important;}';
            $res = '<!-- wp:greenshift-blocks/heading {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($heading_content) . ',"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"typography":{"textShadow":{},"customweight":"bold","size":["115%"]}} -->';
            $res .= '<div id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . ' ">' . $heading_content . '</div>';
            $res .= '<!-- /wp:greenshift-blocks/heading -->';
            return $res;
        }

        $params = array(
            'id' => $id,
            'headingTag' => $heading_tag,
            'headingContent' => $heading_content,
        );

        $res = '<!-- wp:greenshift-blocks/heading ' . self::jsonEncode($params) . ' --><' . $heading_tag . ' id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . '">';
        $res .= $heading_content;
        $res .= '</' . $heading_tag . '><!-- /wp:greenshift-blocks/heading -->';

        return $res;
    }

    public function decorateBlockPopularBrands($section)
    {
        return $this->decorateAdvancedList($section['content']);
    }

    public function decorateHeadingArticleStepSection($section)
    {
        if (empty($section['position']))
            return $this->decorateHeading($section['heading'], 'h2');

        return $this->decorateHeadingNumbered($section['heading'], $section['position']);
    }

    public function decorateBlockMaterialsList($section)
    {
        return $this->decorateAdvancedList($section['content']);
    }

    public function decorateBlockTips($section)
    {
        return $this->decorateAdvancedList($section['content'], array('colorGlobal' => '#4dd4ac', 'icon' => 'rhi-check-circle-solid'));
    }

    public function decorateHeadingNumbered($heading_content, $num, $heading_tag = 'h2')
    {
        $res = '';
        $id = self::generateId();
        $num = (int) $num;
        $res .= '<!-- wp:greenshift-blocks/heading {"id":"' . $id . '","inlineCssStyles":"#gspb_heading-id-' . $id . '{margin-top:0px;margin-bottom:0px;}.gspb_heading_sep_' . $id . '{display:flex; align-items:center;}.gspb_heading_sep_' . $id . ' > .gspb_heading_sep{width:40px;}.gspb_heading_sep_' . $id . ' .gs-numhead__circle { border: 3px solid #2184f9; border-radius: 50%; box-sizing: content-box; display: inline-block;  font-weight: 700; text-align: center; overflow: hidden; color:#2184f9;   }.gspb_heading_sep_' . $id . ' .gs-numhead__circle{width:calc(40px - 6px);min-width:calc(40px - 6px);height:calc(40px - 6px);line-height:calc(40px - 6px);font-size:24px;}.gspb_heading_sep_' . $id . ' .gspb_heading_sep_before{margin-right:17px;}.gspb_heading_sep_' . $id . '{margin-top:2.5rem;margin-bottom:1.4rem;}","headingContent":' . self::jsonEncode($heading_content) . ',"spacing":{"margin":{"values":{"bottom":["0px"],"top":["0px"]},"locked":false},"padding":{"values":{},"locked":false}},"headingSeparator":true,"headingSepSpacing":{"margin":{"values":{"right":["17px"]},"locked":false},"padding":{"values":{},"locked":false}},"spacingBlock":{"margin":{"values":{"top":["2.5rem"],"bottom":["1.4rem"]},"locked":false},"padding":{"values":{},"locked":false}},"headingSepWidth":[40,null,null,null],"headingSepVJustify":"center","headingSepType":"circle","numberCircle":"' . $num . '","enablesubTitle":false,"spacingsubTitle":{"margin":{"values":{"top":["2px"]},"locked":false},"padding":{"values":{},"locked":false}},"typographysubTitle":{"textShadow":{},"customweight":"normal"},"isVariation":"circleheading"} -->';
        $res .= '<div class="gspb_heading_sep_' . $id . '"><div class="gspb_heading_sep gspb_heading_sep_before"><div class="gs-numhead__circle"><span>' . $num . '</span></div></div><h2 id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . ' ">' . esc_html($heading_content) . '</h2></div>';
        $res .= '<!-- /wp:greenshift-blocks/heading -->';
        return $res;
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

    public static function generateId()
    {
        return 'gsbp-' . self::randomStr(8) . '-' . self::randomStr(4);
    }

    public function decorateBlockCriteriaRatings($section, $marginTop = 0, $marginBottom = 40)
    {
        $criterias = $section['content'];
        $res = '';
        $id = self::generateId();

        $inlineCssContainer = '.gspb_container-id-' . $id . '{flex-direction: column;box-sizing: border-box;}' .
            '#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}' .
            '#gspb_container-id-' . $id . '.gspb_container{position:relative;}' .
            '#gspb_container-id-' . $id . '.gspb_container{margin-top:' . $marginTop . 'px;margin-bottom:' . $marginBottom . 'px;}';

        $spacing = [
            "margin" => [
                "values" => [
                    "top" => [$marginTop . "px"],
                    "bottom" => [$marginBottom . "px"]
                ],
                "locked" => false
            ],
            "padding" => [
                "values" => new \stdClass(),
                "locked" => false
            ]
        ];

        $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":' . json_encode($inlineCssContainer) . ',"spacing":' . json_encode($spacing) . ',"className":"tmn-review-criterias"} -->';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-' . $id . ' tmn-review-criterias" id="gspb_container-id-' . $id . '">';

        foreach ($criterias as $criteria)
        {
            $criteria['criteria'] = htmlspecialchars($criteria['criteria'], ENT_QUOTES, 'UTF-8');
            $criteria['rating'] = round((float)$criteria['rating'], 1);
            $criteriaId = self::generateId();
            $ratingPercentage = round(($criteria['rating'] / 10) * 100);

            $inlineCssProgressbar = '.gspb_bar-id-' . $criteriaId . '{margin-bottom:20px;}' .
                '.gspb_bar-id-' . $criteriaId . ' .gs-progressbar__labels{display:flex;justify-content:space-between;align-items:center;}' .
                '.gspb_bar-id-' . $criteriaId . ' .gs-progressbar__labels{margin-bottom:5px;}' .
                '.gspb_bar-id-' . $criteriaId . ' .gs-progressbar__progress{height:8px;border-radius:8px;overflow:hidden;background-color:#e0e3f0;}' .
                '.gspb_bar-id-' . $criteriaId . ' .gs-progressbar__bar{height:100%;border-radius:inherit;}' .
                '.gspb_bar-id-' . $criteriaId . ' .gs-progressbar__bar{background-color:var(--cegg-progress-bar-bg, #0d6efd);}';

            $res .= '<!-- wp:greenshift-blocks/progressbar {"title":"' . $criteria['criteria'] . '","id":"' . $criteriaId . '","inlineCssStyles":' . json_encode($inlineCssProgressbar) . ',"spacing":{"margin":{"values":{"bottom":["20px"]},"locked":false},"padding":{"values":{},"locked":false}},"label":"' . $criteria['rating'] . '","progress":' . $criteria['rating'] . ',"maxvalue":10,"textColor":"","labelColor":"","progressline":"var(--cegg-progress-bar-bg, #0d6efd)","progresslinegradient":null,"className":"tmn-rating-bar"} -->';
            $res .= '<div class="wp-block-greenshift-blocks-progressbar gs-progressbar gs-progressbar-wrapper gspb_bar-id-' . $criteriaId . ' tmn-rating-bar">';
            $res .= '<div class="gs-progressbar__labels">';
            $res .= '<div class="gs-progressbar__title">' . $criteria['criteria'] . '</div>';
            $res .= '<div class="gs-progressbar__label">' . $criteria['rating'] . '</div>';
            $res .= '</div>';
            $res .= '<div class="gs-progressbar__progress">';
            $res .= '<div class="gs-progressbar__bar" style="width:' . $ratingPercentage . '%"></div>';
            $res .= '</div></div><!-- /wp:greenshift-blocks/progressbar -->';
        }

        $res .= '</div><!-- /wp:greenshift-blocks/container -->';
        return $res;
    }

    public function decorateBlockNumberedHeading(array $section)
    {
        return self::decorateHeadingNumbered2($section['content'], $section['group'], 'h2');
    }

    public function decorateHeadingNumbered2($headingContent, $numberCircle, $headingTag = 'h2')
    {
        $blockId = self::generateId();

        $numberCircle = (int) $numberCircle;

        $blockAttributes = [
            "id" => $blockId,
            "inlineCssStyles" =>
            "#gspb_heading-id-{$blockId}{margin-top:0px;margin-bottom:0px;}" .
                ".gspb_heading_sep_{$blockId}{display:flex; align-items:center;}" .
                ".gspb_heading_sep_{$blockId} > .gspb_heading_sep{width:40px;}" .
                ".gspb_heading_sep_{$blockId} .gs-numhead__circle {" .
                "border: 3px solid #0d6efd;" .
                "border-radius: 50%;" .
                "box-sizing: content-box;" .
                "display: inline-block;" .
                "font-weight: 700;" .
                "text-align: center;" .
                "overflow: hidden;" .
                "color:#0d6efd;" .
                "}" .
                ".gspb_heading_sep_{$blockId} .gs-numhead__circle{" .
                "width:calc(40px - 6px);" .
                "min-width:calc(40px - 6px);" .
                "height:calc(40px - 6px);" .
                "line-height:calc(40px - 6px);" .
                "font-size:24px;" .
                "}" .
                ".gspb_heading_sep_{$blockId} .gspb_heading_sep_before{margin-right:17px;}" .
                ".gspb_heading_sep_{$blockId}{margin-bottom:35px;}",
            "headingContent" => $headingContent,
            "spacing" => [
                "margin" => [
                    "values" => [
                        "bottom" => ["0px"],
                        "top" => ["0px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "headingSeparator" => true,
            "headingSepSpacing" => [
                "margin" => [
                    "values" => [
                        "right" => ["17px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "spacingBlock" => [
                "margin" => [
                    "values" => [
                        "bottom" => ["35px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "headingSepWidth" => [40, null, null, null],
            "headingSepColor" => "#0d6efd",
            "headingSepVJustify" => "center",
            "headingSepType" => "circle",
            "numberCircle" => (string)$numberCircle,
            "enablesubTitle" => false,
            "spacingsubTitle" => [
                "margin" => [
                    "values" => [
                        "top" => ["2px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "typographysubTitle" => [
                "textShadow" => [],
                "customweight" => "normal"
            ],
            "isVariation" => "circleheading"
        ];

        $jsonAttrs = self::jsonEncode($blockAttributes);

        $html  = "<!-- wp:greenshift-blocks/heading {$jsonAttrs} -->";
        $html .= "<div class=\"gspb_heading_sep_{$blockId}\">";
        $html .=   "<div class=\"gspb_heading_sep gspb_heading_sep_before\">";
        $html .=       "<div class=\"gs-numhead__circle\"><span>{$numberCircle}</span></div>";
        $html .=   "</div>";
        $html .=   "<{$headingTag} id=\"gspb_heading-id-{$blockId}\" class=\"gspb_heading gspb_heading-id-{$blockId}\">";
        $html .=       esc_html($headingContent);
        $html .=   "</{$headingTag}>";
        $html .= "</div>";

        $html .= "<!-- /wp:greenshift-blocks/heading -->";

        return $html;
    }

    public function decorateBlockProductDescription(array $section)
    {
        HtmlToGreenshift::setListIconMode('fixed');
        return $this->decorateSectionHtml($section);
    }

    public function decorateBlockBuyersGuide(array $section)
    {
        HtmlToGreenshift::setListIconMode('fixed');
        return $this->decorateSectionHtml($section);
    }

    public function decorateBlockArticleSection(array $section)
    {
        HtmlToGreenshift::setListIconMode('fixed');
        return $this->decorateSectionHtml($section);
    }

    public function decorateBlockNumberedHeadingWithSubhiding(array $section)
    {
        $number = $section['content']['number'];
        $heading = $section['content']['heading'];
        $subheading = $section['content']['subheading'];

        return self::decorateHeadingNumberedWithSubheading($number, $heading, $subheading);
    }

    public function decorateHeadingNumberedWithSubheading($number, $heading, $subheading, $headingTag = 'h2')
    {
        $blockId = self::generateId();
        $number = (int) $number;

        $blockAttributes = [
            "id" => $blockId,
            "inlineCssStyles" =>
            "#gspb_heading-id-{$blockId}{margin-top:0px;margin-bottom:0px;}" .
                ".gspb_heading_sep_{$blockId}{display:flex; align-items:center;}" .
                ".gspb_heading_sep_{$blockId} > .gspb_heading_sep{width:50px;}" .
                ".gspb_heading_sep_{$blockId} .gs-numhead__circle {" .
                "border: 1px solid #ffffff;" .
                "border-radius: 50%;" .
                "box-sizing: content-box;" .
                "display: inline-block;" .
                "font-weight: 700;" .
                "text-align: center;" .
                "overflow: hidden;" .
                "color:#ffffff;" .
                "box-shadow:0px 17px 30px 0px rgb(0 0 0 / 7%);" .
                "background:var(--cegg-progress-bar-bg, #0d6efd);" .
                "}" .
                ".gspb_heading_sep_{$blockId} .gs-numhead__circle{" .
                "width:calc(50px - 2px);" .
                "min-width:calc(50px - 2px);" .
                "height:calc(50px - 2px);" .
                "line-height:calc(50px - 2px);" .
                "font-size:30px;" .
                "}" .
                ".gspb_heading_sep_{$blockId} .gspb_heading_sep_before{margin-right:17px;}" .
                ".gspb_heading_sep_{$blockId}{margin-bottom:35px;}" .
                "#gspb_heading-id-{$blockId} .gspb_heading_subtitle, #gspb_subheading-id-{$blockId}{" .
                "display:block; font-size:17px; line-height:22px; margin-top:5px;" .
                "font-weight:normal!important;" .
                "color:var(--cegg-secondary-color, #6c757d);" .
                "}" .
                "#gspb_heading-id-{$blockId} .gspb_heading_subtitle, #gspb_subheading-id-{$blockId}{margin-top:2px;}",
            "headingContent" => $heading,
            "spacing" => [
                "margin" => [
                    "values" => [
                        "bottom" => ["0px"],
                        "top" => ["0px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "typography" => [
                "textShadow" => new \stdClass(),
                "color" => ""
            ],
            "headingSeparator" => true,
            "headingSepSpacing" => [
                "margin" => [
                    "values" => [
                        "right" => ["17px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "spacingBlock" => [
                "margin" => [
                    "values" => [
                        "bottom" => ["35px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "headingSepColor" => "#ffffff",
            "headingSepStroke" => 1,
            "headingSepFill" => "var(--cegg-progress-bar-bg, #0d6efd)",
            "headingSepVJustify" => "center",
            "headingSepBColor" => "#ffffff",
            "headingSepShadow" => true,
            "headingSepType" => "circle",
            "numberCircle" => (string)$number,
            "enablesubTitle" => true,
            "subTitleOutside" => true,
            "subTitle" => $subheading,
            "spacingsubTitle" => [
                "margin" => [
                    "values" => [
                        "top" => ["2px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "typographysubTitle" => [
                "textShadow" => [],
                "customweight" => "normal",
                "color" => "var(--cegg-secondary-color, #6c757d)"
            ],
            "isVariation" => "circleheading"
        ];

        $jsonAttrs = self::jsonEncode($blockAttributes);

        $html  = "<!-- wp:greenshift-blocks/heading {$jsonAttrs} -->";
        $html .= "<div class=\"gspb_heading_sep_{$blockId}\">";
        $html .=   "<div class=\"gspb_heading_sep gspb_heading_sep_before\">";
        $html .=       "<div class=\"gs-numhead__circle\"><span>{$number}</span></div>";
        $html .=   "</div>";
        $html .=   "<div>";
        $html .=       "<{$headingTag} id=\"gspb_heading-id-{$blockId}\" class=\"gspb_heading gspb_heading-id-{$blockId}\">" . esc_html($heading) . "</{$headingTag}>";
        $html .=       "<span class=\"gspb_heading_subtitle\" id=\"gspb_subheading-id-{$blockId}\">" . esc_html($subheading) . "</span>";
        $html .=   "</div>";
        $html .= "</div>";
        $html .= "<!-- /wp:greenshift-blocks/heading -->";

        return $html;
    }

    public function decorateHeadingZigzag($heading, $headingTag = 'h2')
    {
        $blockId = self::generateId();

        $blockAttributes = [
            "id" => $blockId,
            "inlineCssStyles" =>
            "#gspb_heading-id-{$blockId}{font-size:20px;}" .
                "#gspb_heading-id-{$blockId}{margin-top:0px;margin-bottom:0px;}" .
                ".gspb_heading_sep_{$blockId}{display:flex; align-items:center;}" .
                ".gspb_heading_sep_{$blockId} > .gspb_heading_sep{width:50px;}" .
                ".gspb_heading_sep_{$blockId} svg{stroke-width: 3px;stroke: var(--cegg-progress-bar-bg, #0d6efd);width:100%}" .
                ".gspb_heading_sep_{$blockId} .gspb_heading_sep_before{margin-right:17px;}" .
                ".gspb_heading_sep_{$blockId}{margin-bottom:25px;}",
            "headingContent" => $heading,
            "spacing" => [
                "margin" => [
                    "values" => [
                        "bottom" => ["0px"],
                        "top" => ["0px"]
                    ],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "typography" => [
                "textShadow" => new \stdClass(),
                "color" => "",
                "size" => ["20px"]
            ],
            "headingSeparator" => true,
            "headingSepSpacing" => [
                "margin" => [
                    "values" => ["right" => ["17px"]],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "spacingBlock" => [
                "margin" => [
                    "values" => ["bottom" => ["25px"]],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "headingSepColor" => "var(--cegg-progress-bar-bg, #0d6efd)",
            "headingSepFill" => "var(--cegg-progress-bar-bg, #0d6efd)",
            "headingSepVJustify" => "center",
            "headingSepBColor" => "#ffffff",
            "headingSepShadow" => false,
            "headingSepType" => "zigzagsmall",
            "numberCircle" => "1",
            "enablesubTitle" => false,
            "subTitleOutside" => true,
            "subTitle" => "",
            "spacingsubTitle" => [
                "margin" => [
                    "values" => ["top" => ["2px"]],
                    "locked" => false
                ],
                "padding" => [
                    "values" => [],
                    "locked" => false
                ]
            ],
            "typographysubTitle" => [
                "textShadow" => [],
                "customweight" => "normal",
                "color" => "var(--cegg-secondary-color, #6c757d)"
            ],
            "isVariation" => "circleheading"
        ];

        $jsonAttrs = self::jsonEncode($blockAttributes);

        $html  = "<!-- wp:greenshift-blocks/heading {$jsonAttrs} -->";
        $html .= "<div class=\"gspb_heading_sep_{$blockId}\">";
        $html .=   "<div class=\"gspb_heading_sep gspb_heading_sep_before\">";
        $html .=       "<svg width=\"161\" height=\"11\" viewBox=\"0 0 161 11\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M161 9c-8.05 0-8.05-7-16.099-7-8.043 0-8.043 7-16.088 7-8.047 0-8.047-7-16.095-7-8.05 0-8.05 7-16.097 7-8.045 0-8.045-7-16.095-7-8.05 0-8.05 7-16.099 7-8.047 0-8.047-7-16.092-7-8.05 0-8.05 7-16.104 7-8.059 0-8.059-7-16.116-7s-8.057 7-16.116 7\" fill=\"none\"></path></svg>";
        $html .=   "</div>";
        $html .=   "<{$headingTag} id=\"gspb_heading-id-{$blockId}\" class=\"gspb_heading gspb_heading-id-{$blockId}\">" . esc_html($heading) . "</{$headingTag}>";
        $html .= "</div>";
        $html .= "<!-- /wp:greenshift-blocks/heading -->";

        return $html;
    }

    public function decorateHeadingHowToRequirements($section)
    {
        return '';
    }

    public function decorateBlockHowToRequirements(array $section)
    {
        return self::decorateHeadingZigzag($section['heading']) . $this->decorateSectionHtml($section);
    }

    public function decorateBlockComparativeFeatures(array $section)
    {
        $data = $section['content'];
        $images = $this->findSectionsAll('ProductImageShortcode', '', true);
        $short_title_sections = $this->findSectionsAll('ShortProductTitle', '', true);
        $titles = array();
        foreach ($short_title_sections as $s)
        {
            $titles[] = $s['content'];
        }
        $title = join(' vs. ', $titles);
        if (mb_strlen($title) > 60)
            $title = '';
        $title = '<strong>' . $title . '</strong>';

        $res = '';

        // container
        $id = self::generateId();
        $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . '} -->';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';

        // header row
        $id = self::generateId();
        $id2 = self::generateId();
        $id3 = self::generateId();
        $inline = '#gspb_row-id-gsbp-gsbp-' . $id . '{justify-content: space-between;margin-top: 0px;margin-bottom: 0px;display: flex;flex-wrap: wrap;}#gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content {display: flex;justify-content: space-between;margin: 0 auto;width: 100%;flex-wrap: wrap;}.gspb_row{position:relative;}div[id^=gspb_col-id]{padding:15px min(3vw, 20px);box-sizing:border-box;position:relative;}#gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content{align-items:center;}body.gspb-bodyfront #gspb_row-id-gsbp-gsbp-' . $id . ' > .gspb_row__content{max-width:var(\u002d\u002dwp\u002d\u002dstyle\u002d\u002dglobal\u002d\u002dwide-size, 1200px);}#gspb_row-id-gsbp-gsbp-' . $id . '{align-content:center;}#gspb_row-id-gsbp-gsbp-' . $id . '{background-color:#f7f7f7;}';
        $res .= '<!-- wp:greenshift-blocks/row {"id":"gsbp-gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"rowLayout":"8","columnPosition":"center","displayStyles":false,"background":{"color":"#f7f7f7"},"isVariation":""} -->';
        $inline = '#gspb_col-id-gsbp-gsbp-' . $id2 . '.gspb_row__col\u002d\u002d3{width:25%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-gsbp-' . $id2 . '.gspb_row__col\u002d\u002d3{width:100%;}}';
        $res .= '<div class="wp-block-greenshift-blocks-row gspb_row gspb_row-id-gsbp-gsbp-' . $id . '" id="gspb_row-id-gsbp-gsbp-' . $id . '"><div class="gspb_row__content"> <!-- wp:greenshift-blocks/row-column {"id":"gsbp-gsbp-' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"columnSize":"3","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
        $inline = '@media (max-width: 575.98px){#gspb_heading-id-gsbp-' . $id3 . ', #gspb_heading-id-gsbp-' . $id3 . ' .gsap-g-line{text-align:center!important;}}';
        $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--3 gspb_col-id-gsbp-gsbp-' . $id2 . '" id="gspb_col-id-gsbp-gsbp-' . $id2 . '"><!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id3 . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($title) . ',"typography":{"textShadow":{},"alignment":[null,null,null,"center"]},"enablesubTitle":false,"subTitle":"","enablehighlight":false} -->';
        $res .= '<div id="gspb_heading-id-gsbp-' . $id3 . '" class="gspb_heading gspb_heading-id-gsbp-' . $id3 . ' ">' . $title . '</div>';
        $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
        $res .= '<!-- /wp:greenshift-blocks/row-column -->';
        // .vs title

        $id = self::generateId();
        $id2 = self::generateId();
        $id3 = self::generateId();
        $inline = '#gspb_col-id-gsbp-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:75%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:100%;}}';
        $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"flexbox":{"type":"","flexDirection":["column"],"justifyContent":["center"],"alignItems":["center"],"enable":false},"columnSize":"9","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
        $inline = '.gspb_container-id-gsbp-gsbp-' . $id2 . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:20px;column-gap:20px;}@media (max-width: 575.98px){#gspb_container-id-gsbp-gsbp-' . $id2 . '.gspb_container{grid-template-columns:repeat(3,minmax(0,1fr));}}';
        $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--9 gspb_col-id-gsbp-gsbp-' . $id . '" id="gspb_col-id-gsbp-gsbp-' . $id . '">';
        $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-gsbp-' . $id2 . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"grid","enable":false,"gridcolumns":[3,null,null,3],"columngap":["20"],"rowgap":[20]}} -->';
        $inline = '.gspb_container-id-gsbp-' . $id3 . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id3 . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id3 . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
        $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-gsbp-' . $id2 . '" id="gspb_container-id-gsbp-gsbp-' . $id2 . '">';

        foreach ($titles as $j => $title)
        {
            if ($j > 0)
            {
                // VS column
                $id = self::generateId();
                $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
                $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"]}} -->';
                $res .=  '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';
                $id = self::generateId();
                $inline = '#gspb_heading-id-gsbp-' . $id . '{font-size:13px;line-height:13px;}#gspb_heading-id-gsbp-' . $id . ', #gspb_heading-id-gsbp-' . $id . ' .gsap-g-line{text-align:center!important;}#gspb_heading-id-gsbp-' . $id . '{padding-top:11px;padding-right:9px;padding-bottom:11px;padding-left:9px;}#gspb_heading-id-gsbp-' . $id . '{border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;}#gspb_heading-id-gsbp-' . $id . '{background-color:#efefef;}';
                $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":"VS","background":{"color":"#efefef"},"border":{"borderRadius":{"values":{"topLeft":50,"topRight":50,"bottomRight":50,"bottomLeft":50},"unit":"px","locked":true},"style":{},"size":{},"color":{},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[11],"right":[9],"bottom":[11],"left":[9]},"unit":["px","px","px","px"],"locked":false}},"typography":{"alignment":["center"],"textShadow":{},"size":[13],"line_height":[13]}} -->';
                $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                $res .= 'VS</div>';
                $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                $res .= '<!-- /wp:greenshift-blocks/container -->';
                // .VS
            }
            // img shortcode
            $id = self::generateId();
            $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
            $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"],"shrinkzero":false},"className":"cegg-versus-img"} -->';
            $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . ' cegg-versus-img" id="gspb_container-id-gsbp-' . $id . '">';
            if (isset($images[$j]))
                $res .= $this->decorateShortcode($images[$j]['content']);
            $res .= '</div><!-- /wp:greenshift-blocks/container -->';
        }

        $res .= '</div><!-- /wp:greenshift-blocks/container --></div>';
        $res .= '<!-- /wp:greenshift-blocks/row-column --> </div></div>';
        $res .= '<!-- /wp:greenshift-blocks/row -->';
        // .header

        for ($i = 0; $i < count($data); $i++)
        {
            $feature = $data[$i]['name'];
            $values = $data[$i]['values'];

            // row
            $id = self::generateId();
            $inline = '#gspb_row-id-gsbp-' . $id . '{justify-content: space-between;margin-top: 0px;margin-bottom: 0px;display: flex;flex-wrap: wrap;}#gspb_row-id-gsbp-' . $id . ' > .gspb_row__content {display: flex;justify-content: space-between;margin: 0 auto;width: 100%;flex-wrap: wrap;}.gspb_row{position:relative;}div[id^=gspb_col-id]{padding:15px min(3vw, 20px);box-sizing:border-box;position:relative;}#gspb_row-id-gsbp-' . $id . ' > .gspb_row__content{align-items:center;}body.gspb-bodyfront #gspb_row-id-gsbp-' . $id . ' > .gspb_row__content{max-width:var(\u002d\u002dwp\u002d\u002dstyle\u002d\u002dglobal\u002d\u002dwide-size, 1200px);}#gspb_row-id-gsbp-' . $id . '{align-content:center;}';
            $res .= '<!-- wp:greenshift-blocks/row {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"rowLayout":"8","columnPosition":"center","displayStyles":false,"isVariation":""} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row gspb_row gspb_row-id-gsbp-' . $id . '" id="gspb_row-id-gsbp-' . $id . '"><div class="gspb_row__content">';

            // feature column
            $id = self::generateId();
            $inline = '#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d3{width:25%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d3{width:100%;}}';
            $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"columnSize":"3","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--3 gspb_col-id-gsbp-' . $id . '" id="gspb_col-id-gsbp-' . $id . '">';

            // feature name
            $feature = '<strong>' . $feature . '</strong>';
            $id = self::generateId();
            $inline = '@media (max-width: 575.98px){#gspb_heading-id-' . $id . ', #gspb_heading-id-' . $id . ' .gsap-g-line{text-align:center!important;}}';
            $res .= '<!-- wp:greenshift-blocks/heading {"id":"' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":' . self::jsonEncode($feature) . ',"typography":{"textShadow":{},"alignment":[null,null,null,"center"]},"enablesubTitle":false,"subTitle":"","enablehighlight":false} -->';
            $res .= '<div id="gspb_heading-id-' . $id . '" class="gspb_heading gspb_heading-id-' . $id . ' ">';
            $res .= $feature;
            $res .= '</div><!-- /wp:greenshift-blocks/heading -->';

            // close feature column
            $res .= '</div><!-- /wp:greenshift-blocks/row-column -->';

            $id = self::generateId();
            $inline = '#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:75%;}@media (max-width: 575.98px){#gspb_col-id-gsbp-' . $id . '.gspb_row__col\u002d\u002d9{width:100%;}}';
            $res .= '<!-- wp:greenshift-blocks/row-column {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"enableExtendedWidth":false,"width":[null],"flexbox":{"type":"","flexDirection":["column"],"justifyContent":["center"],"alignItems":["center"],"enable":false},"columnSize":"9","spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":true}}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-row-column gspb_row__col--9 gspb_col-id-gsbp-' . $id . '" id="gspb_col-id-gsbp-' . $id . '">';
            $id = self::generateId();
            $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:20px;column-gap:20px;}@media (max-width: 575.98px){#gspb_container-id-gsbp-' . $id . '.gspb_container{grid-template-columns:repeat(3,minmax(0,1fr));}}';
            $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"grid","enable":false,"gridcolumns":[3,null,null,3],"columngap":["20"],"rowgap":[20]}} -->';
            $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';

            foreach ($values as $key => $value)
            {
                if ($key > 0)
                {
                    // VS column
                    $id = self::generateId();
                    $inline = '.gspb_container-id-gsbp-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-gsbp-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-gsbp-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}';
                    $res .= '<!-- wp:greenshift-blocks/container {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"]}} -->';
                    $res .=  '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-gsbp-' . $id . '" id="gspb_container-id-gsbp-' . $id . '">';
                    $id = self::generateId();
                    $inline = '#gspb_heading-id-gsbp-' . $id . '{font-size:13px;line-height:13px;}#gspb_heading-id-gsbp-' . $id . ', #gspb_heading-id-gsbp-' . $id . ' .gsap-g-line{text-align:center!important;}#gspb_heading-id-gsbp-' . $id . '{padding-top:11px;padding-right:9px;padding-bottom:11px;padding-left:9px;}#gspb_heading-id-gsbp-' . $id . '{border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;}#gspb_heading-id-gsbp-' . $id . '{background-color:#efefef;}';
                    $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","inlineCssStyles":' . json_encode($inline) . ',"headingTag":"div","headingContent":"VS","background":{"color":"#efefef"},"border":{"borderRadius":{"values":{"topLeft":50,"topRight":50,"bottomRight":50,"bottomLeft":50},"unit":"px","locked":true},"style":{},"size":{},"color":{},"styleHover":{},"sizeHover":{},"colorHover":{}},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[11],"right":[9],"bottom":[11],"left":[9]},"unit":["px","px","px","px"],"locked":false}},"typography":{"alignment":["center"],"textShadow":{},"size":[13],"line_height":[13]}} -->';
                    $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                    $res .= 'VS</div>';
                    $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                    $res .= '<!-- /wp:greenshift-blocks/container -->';
                }

                // value column
                $id = self::generateId();
                $res .= '<!-- wp:greenshift-blocks/container {"id":"' . $id . '","inlineCssStyles":".gspb_container-id-' . $id . '{flex-direction: column;box-sizing: border-box;}#gspb_container-id-' . $id . '.gspb_container > p:last-of-type{margin-bottom:0}.gspb_container{position:relative;}#gspb_container-id-' . $id . '.gspb_container{display:flex;flex-direction:column;justify-content:center;align-items:center;}","flexbox":{"type":"flexbox","justifyContent":["center"],"alignItems":["center"],"flexDirection":["column"],"shrinkzero":false}} -->';
                $res .= '<div class="wp-block-greenshift-blocks-container gspb_container gspb_container-' . $id . '" id="gspb_container-id-' . $id . '">';
                $id = self::generateId();
                $res .= '<!-- wp:greenshift-blocks/heading {"id":"gsbp-' . $id . '","headingTag":"div","headingContent":' . self::jsonEncode($value) . ',"typography":{"textShadow":{},"color":""}} -->';
                $res .= '<div id="gspb_heading-id-gsbp-' . $id . '" class="gspb_heading gspb_heading-id-gsbp-' . $id . ' ">';
                $res .= $value;
                $res .= '</div>';
                $res .= '<!-- /wp:greenshift-blocks/heading --></div>';
                $res .= '<!-- /wp:greenshift-blocks/container -->';
            }

            //---
            $res .= '</div><!-- /wp:greenshift-blocks/container --></div>';
            $res .= '<!-- /wp:greenshift-blocks/row-column --> ';

            // close row
            $res .= '</div></div><!-- /wp:greenshift-blocks/row -->';
        }
        // close container
        $res .= '</div><!-- /wp:greenshift-blocks/container -->';

        return $res;
    }
}
