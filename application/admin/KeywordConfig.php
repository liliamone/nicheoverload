<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\components\NicheInit;
use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\WizardBootConfig;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

/**
 * KeywordConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class KeywordConfig extends WizardBootConfig
{

    public function getTitle()
    {
        return __('Article Settings', 'independent-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_keywords';
    }

    protected function options()
    {
        $options = array();

        \add_action('admin_enqueue_scripts', array($this, 'loadCodemirror'));

        $recipes = NicheInit::getInstance()->getInitRecipes(NicheConfig::isCeIntegration());
        $i = 0;
        foreach ($recipes as $recipe_id => $recipe_name)
        {
            $option_id = 'quantities' . $recipe_id;
            $options[$option_id] = array(
                'callback' => array($this, 'render_article_quantities'),
            );

            if ($i == 0)
                $options[$option_id]['validator'] = array(array('call' => array($this, 'validateArticleQuantities')));
            $i++;
        }

        $d = __('Article Keywords & Products', 'independent-niche') . ' ';
        if (NicheConfig::isManualModule())
            $d .= __('(Required)', 'independent-niche');
        else
            $d .= __('(Optional)', 'independent-niche');

        $d = '<div class="mt-4">' . $d . '</div>';

        if (NicheConfig::isManualModule())
        {
            $d .= '<div class="form-text"><p>'
                . sprintf(__('For the %s module, you must manually set <a target="_blank" href="%s">keywords</a> and <a target="_blank" href="%s">products</a> for each article. Automatic product research is not currently supported for this module.', 'independent-niche'), NicheConfig::getMainModuleName(), 'https://tmniche-docs.keywordrush.com/advanced-use/select-keywords', 'https://tmniche-docs.keywordrush.com/advanced-use/select-products') . '</p>'
                . '</div>';
        }
        else
        {
            $d .= '<div class="form-text"><p>'
                . sprintf(__('You can set <a target="_blank" href="%s">keywords</a> and <a target="_blank" href="%s">products</a> for each article, or just for some, and let the plugin find the rest.', 'independent-niche'), 'https://tmniche-docs.keywordrush.com/advanced-use/select-keywords', 'https://tmniche-docs.keywordrush.com/advanced-use/select-products') . '</p>'
                . '<p>' . __('If you leave these fields empty, the plugin will select keywords automatically.', 'independent-niche') . '</p>'
                . '</div>';
        }

        $options['notice'] = array(
            'callback' => array($this, 'render_text'),
            'description' => $d,
            'default' => '',
        );

        $options['kdata'] = array(
            'callback' => array($this, 'render_keywords_accordion'),
            'validator' => array(array('call' => array($this, 'prepareKdata'), 'type' => 'filter')),
        );

        return $options;
    }

    public function render_article_quantities($args)
    {
        $ce_integration = NicheConfig::isCeIntegration();
        if (!$init_quantities = NicheInit::getInstance()->getArticleQuantities($ce_integration))
            return;

        if (!$total = NicheInit::getInstance()->getRemainingCredits())
            return;

        $recipes = NicheInit::getInstance()->getInitRecipes($ce_integration);
        $recipe_id = (int) str_replace('quantities', '', $args['name']);
        $recipe_name = $recipes[$recipe_id];

        if ($args['value'] === '')
            $value = $init_quantities[$recipe_id];
        else
            $value = $args['value'];

        if ($recipe_id == array_key_first($recipes))
        {
            echo '<div class="row">';
            echo '<div class="col-md-9 col-lg-7">';
            echo '<div class="mb-1 d-flex justify-content-between">Generate articles: <span id="counter" class="">' . esc_html($total) . '</span></div>';
            echo '<div class="text-muted border-bottom mb-3 d-flex justify-content-between">Remaining article credits: ' . '<span>' . esc_html($total) . '</span></div>';
            echo '</div>';
            echo '</div>';
        }
        echo '<div class="row">';
        echo '<div class="mt-2 col-md-9 col-lg-7">';
        echo '<div class="slidecontainer">';
        echo '<label for="recipeRange' . esc_attr($recipe_id) . '" class="form-label mb-0 d-flex justify-content-between">' . esc_html(sprintf(__('%s:', 'independent-niche'), $recipe_name)) . ' <span id="articleCountDisplay' . esc_attr($recipe_id) . '" class="value ms-auto fw-bold">0</span></label>';

        echo '<input name="' . esc_attr($args['option_name']) . '[' . esc_attr($args['name']) . ']" type="range" class="slider form-range" value="' . esc_attr($value) . '" min="0" max="' . esc_attr($total) . '" id="recipeRange' . esc_attr($recipe_id) . '">';
        echo '<span class="max visually-hidden">0</span>';

        echo '</div>';
        echo '</div>';
        echo '</div>';

        if ($recipe_id == array_key_first($recipes))
        {

            echo '<style>';
            echo
            '
    .slidecontainer input[type="range"] {
        background: transparent;
    }

.slidecontainer input[type="range"]::-webkit-slider-runnable-track {
    background: linear-gradient(to right, #4a90e2 var(--value), #7fdbb6 0, #37b77b var(--max), #e9ecef 0);
    height: 0.3rem;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.slidecontainer input[type="range"]::-moz-range-track {
    background: linear-gradient(to right, #4a90e2 var(--value), #7fdbb6 0, #37b77b var(--max), #e9ecef 0);
    height: 0.3rem;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.slidecontainer input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 1em;
    height: 1em;
    background: #007bff;
    border: 2px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.slidecontainer input[type="range"]::-moz-range-thumb {
    width: 1em;
    height: 1em;
    background: #007bff;
    border: 2px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.slidecontainer input[type="range"]::-webkit-slider-thumb:hover,
.slidecontainer input[type="range"]::-moz-range-thumb:hover {
    transform: scale(1.2);
    background: #0056b3;
}
';
            echo '</style>';

            echo '<script>';
            echo '
    document.addEventListener("DOMContentLoaded", function() {
        const inputs = [...document.querySelectorAll(".slidecontainer input")];

        for (let input of inputs) {
            input.addEventListener("input", update);
        }

        update();

        function update(e) {
            const total=' . esc_attr($total) . ';
            if (e && e.target.valueAsNumber > +e.target.dataset.max) {
                e.target.value = e.target.dataset.max;
            }

            let sumOfAllInputs = inputs.reduce((sum, input) => sum + input.valueAsNumber, 0);
            document.querySelector("#counter").textContent = sumOfAllInputs;

            if (sumOfAllInputs > 0)
                document.querySelector("#counter").className = "text-primary fw-bold";
            else
                document.querySelector("#counter").className = "text-danger fw-bold";

            for (let input of inputs) {
                const max = total + input.valueAsNumber - sumOfAllInputs;
                const container = input.closest(".slidecontainer");
                container.querySelector(".max").textContent = input.dataset.max = max;
                container.querySelector(".value").textContent = input.valueAsNumber;

                const x1 = input.valueAsNumber * 100 / total;
                const x2 = max * 100 / total;

                input.style.setProperty("--value", x1 + "%");
                input.style.setProperty("--max", x2 + "%");
            }
        }
    });
';

            echo '</script>';
        }
    }

    public function validateArticleQuantities($value)
    {
        if (!$total = NicheInit::getInstance()->getRemainingCredits())
            return false;

        $sum = 0;
        $first_key = null;
        foreach ($this->input as $key => $v)
        {
            if (!strstr($key, 'quantities'))
                continue;

            if (!$first_key)
                $first_key = $key;

            $sum += (int) $v;
        }

        if ($sum > $total)
        {
            \add_settings_error($first_key, $first_key, sprintf(__('The total count of articles must be less than %d.', 'independent-niche'), $total));
            return false;
        }

        if ($sum <= 0)
        {
            \add_settings_error($first_key, $first_key, __('The number of articles must be greater than zero.', 'independent-niche'));
            return false;
        }

        return true;
    }

    public function getCurrentArticleTotal()
    {
        $options = $this->getOptionValues();
        $total = 0;
        foreach ($options as $option => $value)
        {
            if (strpos($option, 'quantities') === 0)
                $total += (int) $value;
        }

        if ($total)
            return $total;
        else
            return NicheInit::getInstance()->getTotalArticles();
    }

    private static function getFirstKey($array)
    {
        foreach ($array as $key => $value)
        {
            return $key;
        }
    }

    public function prepareKdata($data)
    {
        if (!is_array($data))
            return array();

        $clean_data = array();

        foreach ($data as $section_id => $groups)
        {
            if (!is_array($groups))
                continue;

            $clean_groups = array();

            foreach ($groups as $group_id => $group)
            {
                if (!is_array($group))
                    continue;

                $keyword = isset($group['keyword']) ? sanitize_text_field($group['keyword']) : '';
                if (empty($keyword))
                    continue;

                $products_raw = isset($group['products']) ? sanitize_textarea_field($group['products']) : '';
                if (!empty($products_raw))
                {

                    $products_raw = trim($products_raw, '[]'); // old syntax for products
                    $products_array = array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $products_raw)));

                    if (empty($products_array))
                        continue;

                    $products = implode("\n", $products_array);
                }
                else
                    $products = '';

                $as_title_raw = isset($group['as_title']) ? sanitize_text_field($group['as_title']) : '';
                $as_title = (strtolower($as_title_raw) === 'true') ? 'true' : 'false';

                $post_id = isset($group['post_id']) ? intval($group['post_id']) : 0;

                $clean_groups[$group_id] = [
                    'keyword'   => $keyword,
                    'as_title'  => $as_title,
                    'products'  => $products,
                    'post_id'  => $post_id,
                ];
            }

            if (!empty($clean_groups))
                $clean_data[$section_id] = $clean_groups;
        }

        return $clean_data;
    }

    public function getAmazonAutosuggestUri()
    {
        $language = NicheConfig::getInstance()->option('language');

        $language2Uri = array(
            'Arabic' => 'https://completion.amazon.sa/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=ar_AE&last-prefix=xbo&avg-ks-time=1258&fb=1&predicted_text_accepted=&session-id=262-8467824-1817465&request-id=SERPXNPK0YZNNERNNSM3&mid=A17E79C6D8DWNP&plain-mid=338811&client-info=search-ui',
            'Dutch' => 'https://completion.amazon.nl/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=nl_NL&last-prefix=xb&avg-ks-time=257&fb=1&predicted_text_accepted=&session-id=261-5267231-0559402&request-id=2MZ7GV9TT5PN0QFVJ0RN&mid=A1805IZSGTT6HS&plain-mid=328451&client-info=search-ui',
            'English' => 'https://completion.amazon.com/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=en_US&last-prefix=xb&avg-ks-time=245&fb=1&predicted_text_accepted=&session-id=132-7162756-3661837&request-id=P38YHEQPZD35BX9QPJ49&mid=ATVPDKIKX0DER&plain-mid=1&client-info=search-ui&ni=1',
            'French' => 'https://completion.amazon.fr/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=fr_FR&last-prefix=xb&avg-ks-time=285&fb=1&predicted_text_accepted=&session-id=261-2722076-6694759&request-id=MEMWPPDKHKB5MWHGATPG&mid=A13V1IB3VIYZZH&plain-mid=5&client-info=search-ui',
            'German' => 'https://completion.amazon.de/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=en_GB&last-prefix=xbo&avg-ks-time=257&fb=1&predicted_text_accepted=&session-id=260-1709139-1272610&request-id=0043TYBMHZ7B628A7JZA&mid=A1PA6795UKMFR9&plain-mid=4&client-info=search-ui',
            'Hindi' => 'https://completion.amazon.in/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=en_IN&last-prefix=xb&avg-ks-time=240&fb=1&predicted_text_accepted=&session-id=259-9952934-8266920&request-id=EK4DMA4XVWS3FDKMZXBM&mid=A21TJRUUN4KGV&plain-mid=44571&client-info=search-ui',
            'Italian' => 'https://completion.amazon.it/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=it_IT&last-prefix=xb&avg-ks-time=288&fb=1&predicted_text_accepted=&session-id=257-2458373-8688022&request-id=QMAXC1YK131WAC4HPGWD&mid=APJ6JRA9NG5V4&plain-mid=35691&client-info=search-ui',
            'Japanese' => 'https://completion.amazon.co.jp/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=en_US&last-prefix=xb&avg-ks-time=298&fb=1&predicted_text_accepted=&session-id=355-8321660-4855802&request-id=RBRQYF1ZX23P5XJ9CMK1&mid=A1VC38T7YXB528&plain-mid=6&client-info=search-ui',
            'Polish' => 'https://completion.amazon.pl/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=pl_PL&last-prefix=xbo&avg-ks-time=284&fb=1&predicted_text_accepted=&session-id=262-2350565-4812740&request-id=JGKY0PEAV6N7CSRK8KRE&mid=A1C3SOZRARQ6R3&plain-mid=712115121&client-info=search-ui',
            'Portuguese (Brazil)' => 'https://completion.amazon.com.br/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=pt_BR&last-prefix=xb&avg-ks-time=301&fb=1&predicted_text_accepted=&session-id=135-4090171-8306504&request-id=Y8H3SP30ABJQX2J5NNCT&mid=A2Q3Y263D00KWC&plain-mid=526970&client-info=search-ui',
            'Spanish' => 'https://completion.amazon.es/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=pt_PT&last-prefix=xbo&avg-ks-time=284&fb=1&predicted_text_accepted=&session-id=262-1743633-9986503&request-id=EZNZY7T64GW468P6BP9G&mid=A1RKKUPIHCS9HS&plain-mid=44551&client-info=search-ui',
            'Swedish' => 'https://completion.amazon.se/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=sv_SE&last-prefix=xbo&avg-ks-time=579&fb=1&predicted_text_accepted=&session-id=257-2694168-1118128&request-id=S5Y653PZ59WD9Z3HF541&mid=A2NODRKZP88ZB9&plain-mid=704403121&client-info=search-ui',
            'Turkish' => 'https://completion.amazon.com.tr/api/2017/suggestions?limit=11&prefix=%KEYWORD%&suggestion-type=WIDGET&suggestion-type=KEYWORD&page-type=Gateway&alias=aps&site-variant=desktop&version=3&event=onkeypress&wc=&lop=tr_TR&last-prefix=xbo&avg-ks-time=270&fb=1&predicted_text_accepted=&session-id=261-0626143-6372003&request-id=KRJHRDRM4XXJRXGGR3GP&mid=A33AVAJ2PDY3EV&plain-mid=338851&client-info=search-ui',
        );

        return isset($language2Uri[$language]) ? $language2Uri[$language] : $language2Uri['English'];
    }

    public function getEbayAutosuggestUri()
    {
        $language = NicheConfig::getInstance()->option('language');

        $language2Uri = array(
            'Dutch' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=146&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'English' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=0&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=0&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'French' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=71&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'German' => 'https://autosug.ebay.com/autosug?kwd=%KEYWORD%&sId=77&_ch=0&_ac=0&_f=acNewLayout&_cp=5&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Hindi' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=203&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Italian' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=101&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Japanese' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=0&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=0&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Polish' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=212&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Portuguese (Brazil)' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=0&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=0&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
            'Spanish' => 'https://autosug.ebaystatic.com/autosug?kwd=%KEYWORD%&sId=186&_ch=0&_rs=1&_richres=1&callback=0&_store=1&_help=1&_richsug=1&_eprogram=1&_td=1&_nearme=1&_nls=0',
        );

        return isset($language2Uri[$language]) ? $language2Uri[$language] : $language2Uri['English'];
    }

    public  function loadCodemirror()
    {
        \wp_enqueue_style('tmn-codemirror', \IndependentNiche\PLUGIN_RES . '/codemirror/codemirror.min.css');
        \wp_enqueue_script('tmn-codemirror', \IndependentNiche\PLUGIN_RES . '/codemirror/codemirror.min.js');
        \wp_enqueue_script('tmn-codemirror-placeholder', \IndependentNiche\PLUGIN_RES . '/codemirror/placeholder.js');
    }

    public function render_keywords_accordion($args)
    {
        // Get recipes and any saved data
        $recipes = NicheInit::getInstance()->getInitRecipes(NicheConfig::isCeIntegration());
        $savedGroups = isset($args['value']) && is_array($args['value']) ? $args['value'] : array();
?>
        <script>
            const savedGroups = <?php echo json_encode($savedGroups); ?>;
        </script>

        <!-- Bulk Import Modal -->
        <div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="bulkImportModalLabel"><?php echo esc_html(__('Import Keywords & Products', 'independent-niche')); ?> - <span id="bulkImportSectionName"></span></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'independent-niche'); ?>"></button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo esc_html(__('Please paste your data in the following format (CSV):', 'independent-niche')); ?></p>
                        <pre class="bg-light p-3 rounded">
"Keyword","Use As Title","Product IDs"
"Keyword 1","true","prod1;prod2;prod3"
"Keyword 2","false","prod4;prod5"
"Keyword 3","true","prod6;prod7;prod8"
                    </pre>
                        <textarea class="form-control" id="bulkImportTextarea" rows="10" placeholder="<?php echo esc_attr(__('Paste your CSV data here...', 'independent-niche')); ?>"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo esc_html(__('Close', 'independent-niche')); ?></button>
                        <button type="button" class="btn btn-primary" id="importBulkData"><?php echo esc_html(__('Import', 'independent-niche')); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Bulk Import Modal -->

        <!-- Keyword Tool Modal -->
        <div class="modal fade" id="keywordToolModal" tabindex="-1" aria-labelledby="keywordToolModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title"><?php echo esc_html(__('Keyword Tool', 'independent-niche')); ?> - <span id="keywordToolSectionName"></span></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'independent-niche'); ?>"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" id="keyword-tool-input" class="form-control" placeholder="<?php echo esc_attr(__('Type your keyword here...', 'independent-niche')); ?>">
                        </div>
                        <ul class="list-group suggestions-list" id="keyword-tool-suggestions-list"></ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_attr_e('Close', 'independent-niche'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Keyword Tool Modal -->

        <!-- Bootstrap Accordion for Recipes -->
        <div class="accordion" id="productSectionsAccordion">
            <?php
            foreach ($recipes as $id => $recipe):
                $headingId = "heading-{$id}";
                $collapseId = "collapse-{$id}";
                $isFirst = ($id === self::getFirstKey($recipes));
                $section_id = $id;
            ?>
                <div class="accordion-item" id="accordionItem<?php echo esc_attr($section_id); ?>">
                    <h2 class="accordion-header" id="<?php echo esc_attr($headingId); ?>">
                        <button class="accordion-button <?php echo $isFirst ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr($collapseId); ?>" aria-expanded="<?php echo $isFirst ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($collapseId); ?>">
                            <?php echo esc_html($recipe); ?>
                        </button>
                    </h2>
                    <div id="<?php echo esc_attr($collapseId); ?>" class="accordion-collapse collapse <?php echo $isFirst ? 'show' : ''; ?>" aria-labelledby="<?php echo esc_attr($headingId); ?>" data-bs-parent="#productSectionsAccordion">
                        <div class="accordion-body">
                            <div class="groups-container mb-2" data-section="<?php echo esc_attr($id); ?>"></div>
                            <div class="btn-group ms-4" role="group">
                                <button class="add-more-keywords-btn btn btn-primary btn-sm" data-section="<?php echo esc_attr($id); ?>">
                                    <?php echo esc_html(__('Add keyword', 'independent-niche')); ?>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm keyword-tool-btn" data-section="<?php echo esc_attr($id); ?>" data-bs-toggle="modal" data-bs-target="#keywordToolModal">
                                    <?php echo esc_html(__('Keyword tool', 'independent-niche')); ?>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm bulk-import-btn" data-section="<?php echo esc_attr($id); ?>" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                                    <?php echo esc_html(__('Import', 'independent-niche')); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- End of Accordion -->

        <!-- Hidden Template for Keyword Group -->
        <template id="keywordGroupTemplate">
            <div class="keyword-group m-0 mb-2 border1">
                <div class="position-relative m-0 p-0 ps-2">
                    <button type="button" class="remove-btn position-absolute top-0 end-0 me-1 mt-1" title="<?php echo esc_attr(__('Remove this keyword', 'independent-niche')); ?>" aria-label="Remove keyword">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                    <span class="article-number badge bg-primary position-absolute top-0 start-0 translate-middle mt-3">1</span>

                    <div class="row mt-1 ms-3">
                        <div class="col-7 col-md-8 col-xl-9 p-0">
                            <input
                                type="text"
                                maxlength="255"
                                class="form-control form-control-sm keyword-input"
                                placeholder="<?php echo esc_attr(__('Main keyword or Article title', 'independent-niche')); ?>" />

                            <input
                                type="hidden"
                                class="form-control form-control-sm post-id-input" />

                        </div>

                        <div class="col-auto d-flex align-items-center">
                            <div class="form-check">
                                <label class="form-check-label" title="<?php echo esc_attr(__('Use this keyword as the article title', 'independent-niche')); ?>">
                                    <input class="form-check-input title-switch" value="true" type="checkbox">
                                    <span style="font-size:0.85rem;"><?php echo esc_html(__('Use as Title', 'independent-niche')); ?></span>
                                </label>
                            </div>
                        </div>

                        <?php if (NicheConfig::getInstance()->option('ce_integration') == 'yes'): ?>
                            <div class="col-auto">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary add-products-link position-relative"
                                    title="<?php echo esc_attr(__('Add products', 'independent-niche')); ?>"
                                    data-product-textarea=""
                                    data-bs-toggle="collapse"
                                    aria-expanded="false"
                                    aria-controls="">
                                    <span class="products_added_flag position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none">
                                        <span class="visually-hidden"><?php esc_html_e('Products added', 'independent-niche'); ?></span>
                                    </span>
                                    <span class="dashicons dashicons-products"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (NicheConfig::getInstance()->option('ce_integration') == 'yes'): ?>
                        <div class="row mt-2">
                            <div class="collapse col ms-3">
                                <div class="mb-2">
                                    <textarea class="product-urls"></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </template>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const template = document.getElementById('keywordGroupTemplate').content;
                let currentImportSectionId = null;
                let currentKeywordToolSectionId = null;

                /**
                 * articleCounts keeps track of how many articles/keywords a section can have
                 * This is typically updated by a range slider or similar input.
                 */
                const articleCounts = {};
                <?php foreach ($recipes as $id => $recipe): ?>
                    articleCounts["<?php echo esc_attr($id); ?>"] = parseInt(document.getElementById('recipeRange<?php echo esc_attr($id); ?>').value);
                <?php endforeach; ?>

                function toggleBadgeBanger(group) {
                    const postIdInput = group.querySelector('.post-id-input');
                    const badgeSpan = group.querySelector('.article-number');

                    if (postIdInput && badgeSpan) {
                        const postIdValue = postIdInput.value.trim();

                        if (postIdValue !== '' && postIdValue !== '0') {
                            badgeSpan.classList.add('text-bg-danger');
                            badgeSpan.classList.remove('bg-primary');
                            badgeSpan.setAttribute('title', 'Post ID: ' + postIdValue);
                        } else {
                            badgeSpan.classList.add('bg-primary');
                            badgeSpan.classList.remove('text-bg-danger');
                            badgeSpan.removeAttribute('title');
                        }
                    }
                }

                // Removes any empty keyword groups from a given section
                function removeEmptyKeywords(sectionId) {
                    const existingGroups = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`);
                    existingGroups.forEach(group => {
                        const keywordInput = group.querySelector('.keyword-input');
                        if (keywordInput && keywordInput.value.trim() === '') {
                            group.remove();
                        }
                    });
                }

                // Adds a new keyword group to a given section, optionally with default values
                function addNewGroup(sectionId, defaults = {}) {
                    // If the article count is 0, do not add
                    if (articleCounts[sectionId] === 0) return;

                    // If we've reached the limit, do not add more
                    if (document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`).length >= articleCounts[sectionId]) {
                        alert('<?php echo esc_js(__('Maximum number of keyword groups reached for this article type.', 'independent-niche')); ?>');
                        return;
                    }

                    const clone = template.cloneNode(true);
                    const newGroup = clone.querySelector('.keyword-group');

                    const keywordInput = newGroup.querySelector('.keyword-input');
                    const productUrls = newGroup.querySelector('.product-urls');
                    const titleSwitch = newGroup.querySelector('.title-switch');
                    const postIdInput = newGroup.querySelector('.post-id-input');

                    keywordInput.value = defaults.keyword ? defaults.keyword : '';
                    if (productUrls) {
                        productUrls.value = defaults.products ? defaults.products : '';
                    }
                    titleSwitch.checked = (defaults.as_title && defaults.as_title === 'true');
                    if (postIdInput) {
                        postIdInput.value = (typeof defaults.post_id !== 'undefined') ? defaults.post_id : '';
                    }

                    toggleBadgeBanger(newGroup);

                    const groupsContainer = document.querySelector(`.groups-container[data-section="${sectionId}"]`);
                    groupsContainer.appendChild(newGroup);

                    renumberKeywordGroups(sectionId);
                }

                // Toggles the "products_added_flag" if a product-urls textarea is not empty
                function updateProductsAddedFlag(button, textarea) {
                    const flag = button.querySelector('.products_added_flag');
                    const value = textarea.value.trim();
                    if (value !== '') {
                        flag.classList.remove('d-none');
                    } else {
                        flag.classList.add('d-none');
                    }
                }

                // Updates the state of all "products_added_flag" in the entire form
                function updateAllProductsAddedFlags() {
                    const allGroups = document.querySelectorAll('.keyword-group');
                    allGroups.forEach(group => {
                        const addProductsLink = group.querySelector('.add-products-link');
                        const productTextarea = group.querySelector('.product-urls');
                        if (addProductsLink && productTextarea) {
                            updateProductsAddedFlag(addProductsLink, productTextarea);
                        }
                    });
                }

                // Renumber the groups in a section, ensuring inputs have unique names/IDs
                function renumberKeywordGroups(sectionId) {
                    const groups = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`);
                    groups.forEach((group, index) => {
                        const articleNumber = index + 1;
                        const numberSpan = group.querySelector('.article-number');
                        if (numberSpan) {
                            numberSpan.textContent = articleNumber;
                        }

                        // Title switch
                        const switchInput = group.querySelector('.title-switch');
                        const switchLabel = group.querySelector('.form-check-label');
                        if (switchInput && switchLabel) {
                            const newSwitchId = `useAsTitleSwitch-${sectionId}-${articleNumber}`;
                            switchInput.id = newSwitchId;
                            switchInput.name = `too-much-niche_keywords[kdata][${sectionId}][${articleNumber}][as_title]`;
                            switchLabel.setAttribute('for', newSwitchId);
                        }

                        // Products link & collapse area
                        const addProductsLink = group.querySelector('.add-products-link');
                        const collapseDiv = group.querySelector('.collapse');
                        if (addProductsLink && collapseDiv) {
                            const newCollapseId = `productsCollapse-${sectionId}-${articleNumber}`;
                            collapseDiv.id = newCollapseId;
                            addProductsLink.setAttribute('data-bs-target', `#${newCollapseId}`);
                            addProductsLink.setAttribute('aria-controls', newCollapseId);
                        }

                        // Keyword & product-urls
                        const keywordInput = group.querySelector('.keyword-input');
                        const productTextarea = group.querySelector('.product-urls');
                        if (keywordInput) {
                            keywordInput.name = `too-much-niche_keywords[kdata][${sectionId}][${articleNumber}][keyword]`;
                        }
                        if (productTextarea && addProductsLink) {
                            productTextarea.name = `too-much-niche_keywords[kdata][${sectionId}][${articleNumber}][products]`;
                            productTextarea.addEventListener('input', function() {
                                updateProductsAddedFlag(addProductsLink, productTextarea);
                            });
                        }

                        // Post ID
                        const postIdInput = group.querySelector('.post-id-input');
                        if (postIdInput) {
                            postIdInput.name = `too-much-niche_keywords[kdata][${sectionId}][${articleNumber}][post_id]`;
                        }

                        // Initialize CodeMirror if the product textarea exists
                        if (productTextarea) {
                            const textareaId = `productUrls-${sectionId}-${articleNumber}`;
                            productTextarea.id = textareaId;
                            if (addProductsLink) {
                                addProductsLink.setAttribute('data-product-textarea', textareaId);
                            }
                            initCodeMirror(productTextarea, addProductsLink);
                        }
                    });

                    updateAddButtonState(sectionId);
                }

                // Initialize CodeMirror on a given textarea if not already done
                function initCodeMirror(textarea, addProductsLink) {
                    if (textarea.classList.contains("cm-initialized")) return; // Prevent re-initialization

                    var editor = CodeMirror.fromTextArea(textarea, {
                        lineNumbers: true,
                        lineWrapping: false,
                        autofocus: true,
                        placeholder: "<?php echo esc_js(__('Enter product IDs or URLs, one per line', 'independent-niche')); ?>"
                    });

                    editor.getWrapperElement().style.fontSize = "0.85rem";
                    editor.setSize(null, "150px");

                    // Set initial content
                    editor.setValue(textarea.value);

                    // Sync changes back to the underlying textarea
                    editor.on("change", function() {
                        textarea.value = editor.getValue();
                        updateProductsAddedFlag(addProductsLink, textarea);
                    });

                    // Limit the number of lines
                    var maxLines = 10;
                    editor.on("beforeChange", function(editor, change) {
                        if (change.origin && change.origin.indexOf("delete") === 0) return;
                        var addedLines = change.text.length - 1;
                        var currentLineCount = editor.lineCount();
                        if (currentLineCount + addedLines > maxLines) {
                            change.cancel();
                        }
                    });

                    textarea.classList.add("cm-initialized");
                    textarea.CodeMirrorEditor = editor;
                }

                // Setup remove buttons for each section
                function setupRemoveListener(sectionId) {
                    const groupsContainer = document.querySelector(`.groups-container[data-section="${sectionId}"]`);
                    groupsContainer.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-btn')) {
                            const group = e.target.closest('.keyword-group');
                            if (group) {
                                group.remove();
                                renumberKeywordGroups(sectionId);
                            }
                        }
                    });
                }

                // Setup the "Add keyword" button for each section
                function setupAddButton(sectionId) {
                    const addBtn = document.querySelector(`.add-more-keywords-btn[data-section="${sectionId}"]`);
                    addBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        addNewGroup(sectionId);
                    });
                }

                // Enable/disable "Add keyword" button based on current count vs. articleCounts
                function updateAddButtonState(sectionId) {
                    const currentCount = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`).length;
                    const addBtn = document.querySelector(`.add-more-keywords-btn[data-section="${sectionId}"]`);
                    if (!addBtn) return;

                    if (currentCount >= articleCounts[sectionId] || articleCounts[sectionId] === 0) {
                        addBtn.disabled = true;
                    } else {
                        addBtn.disabled = false;
                    }
                }

                // Setup slider handling for controlling max article keywords in each section
                function setupSlider(sectionId) {
                    const slider = document.getElementById(`recipeRange${sectionId}`);
                    const display = document.getElementById(`articleCountDisplay${sectionId}`);
                    const accordionItem = document.getElementById(`accordionItem${sectionId}`);
                    const accordionButton = accordionItem.querySelector('.accordion-button');

                    slider.addEventListener('input', function() {
                        const newValue = parseInt(this.value);
                        articleCounts[sectionId] = newValue;
                        display.textContent = newValue;

                        // Toggle the accordion if newValue is 0
                        if (newValue === 0) {
                            accordionButton.classList.add('disabled');
                            accordionButton.setAttribute('aria-disabled', 'true');
                            const collapseId = accordionItem.querySelector('.accordion-collapse').id;
                            const collapseElement = new bootstrap.Collapse(document.getElementById(collapseId), {
                                toggle: false
                            });
                            collapseElement.hide();
                        } else {
                            accordionButton.classList.remove('disabled');
                            accordionButton.removeAttribute('aria-disabled');
                        }

                        // If we have more groups than the newValue, remove the extras
                        const currentCount = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`).length;
                        if (currentCount > newValue) {
                            const groups = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`);
                            for (let i = newValue; i < groups.length; i++) {
                                groups[i].remove();
                            }
                            renumberKeywordGroups(sectionId);
                        }
                        updateAddButtonState(sectionId);
                    });
                }

                // Parsing helper to split on delimiter and strip quotes
                function parseLine(line, delimiter) {
                    return line
                        .split(delimiter)
                        .map(field => field.trim().replace(/^"|"$/g, ''));
                }

                // Handles the "Import" button in the Bulk Import Modal
                function handleBulkImport() {
                    const bulkData = document.getElementById('bulkImportTextarea').value.trim();
                    if (!bulkData) {
                        alert('<?php echo esc_js(__('Please paste your data.', 'independent-niche')); ?>');
                        return;
                    }

                    // Split lines
                    const lines = bulkData
                        .split('\n')
                        .map(l => l.trim())
                        .filter(l => l !== '');
                    if (lines.length === 0) {
                        alert('<?php echo esc_js(__('No data found.', 'independent-niche')); ?>');
                        return;
                    }

                    // Detect delimiter
                    let delimiter = '\t';
                    if (lines[0].includes(',')) {
                        delimiter = ',';
                    }

                    // Parse the first line
                    const firstLineFields = parseLine(lines[0], delimiter).map(h => h.toLowerCase());

                    let hasHeader = false;
                    let keywordIndex = -1;
                    let useAsTitleIndex = -1;
                    let productIdsIndex = -1;
                    let postIdIndex = -1;

                    // If the first line has 'keyword', consider it a header
                    keywordIndex = firstLineFields.indexOf('keyword');
                    if (keywordIndex !== -1) {
                        hasHeader = true;
                        useAsTitleIndex = firstLineFields.indexOf('use as title');
                        productIdsIndex = firstLineFields.indexOf('product ids');
                        postIdIndex = firstLineFields.indexOf('post id');
                    } else {
                        // No recognized header: assume columns in order: Keyword, Use As Title, Product IDs, Post ID
                        keywordIndex = 0;
                        useAsTitleIndex = 1;
                        productIdsIndex = 2;
                        postIdIndex = 3;
                    }

                    const startIndex = hasHeader ? 1 : 0;
                    if (lines.length <= startIndex) {
                        alert('<?php echo esc_js(__('No data lines found.', 'independent-niche')); ?>');
                        return;
                    }

                    // The section we are importing into
                    const sectionId = currentImportSectionId;
                    if (!sectionId || articleCounts[sectionId] === 0) {
                        alert('<?php echo esc_js(__('Invalid section selected for import.', 'independent-niche')); ?>');
                        return;
                    }

                    removeEmptyKeywords(sectionId);

                    for (let i = startIndex; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (!line) continue;

                        const fields = parseLine(line, delimiter);
                        const keyword = fields[keywordIndex] || '';
                        if (!keyword) continue;

                        const useAsTitle = (useAsTitleIndex >= 0 && fields[useAsTitleIndex]) || '';
                        const productIds = (productIdsIndex >= 0 && fields[productIdsIndex]) || '';
                        const postIdValue = (postIdIndex >= 0 && fields[postIdIndex]) || '';

                        const currentCount = document.querySelectorAll(`.groups-container[data-section="${sectionId}"] .keyword-group`).length;
                        if (currentCount >= articleCounts[sectionId]) {
                            alert('<?php echo esc_js(__('Maximum number of keyword groups reached for this article type.', 'independent-niche')); ?>');
                            break;
                        }

                        let productIdsArray = productIds
                            .split(';')
                            .map(id => id.trim())
                            .filter(Boolean)
                            .join('\n');

                        const defaults = {
                            keyword: keyword,
                            as_title: (useAsTitle.toLowerCase() === 'true' ? 'true' : ''),
                            products: productIdsArray,
                            post_id: postIdValue
                        };

                        addNewGroup(sectionId, defaults);
                    }

                    updateAllProductsAddedFlags();
                    document.getElementById('bulkImportTextarea').value = '';
                    const bulkImportModal = bootstrap.Modal.getInstance(document.getElementById('bulkImportModal'));
                    bulkImportModal.hide();
                }

                // Prepare bulk-import buttons (one per section)
                function setupBulkImportButtons() {
                    const bulkImportButtons = document.querySelectorAll('.bulk-import-btn');
                    bulkImportButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const sectionId = this.getAttribute('data-section');
                            currentImportSectionId = sectionId;
                            const recipeName = document.querySelector(`.accordion-item#accordionItem${sectionId} .accordion-button`).textContent;
                            document.getElementById('bulkImportSectionName').textContent = recipeName;
                            document.getElementById('bulkImportTextarea').value = '';
                        });
                    });
                }

                // Initialize sections on load, adding existing saved groups or a few default groups
                function initializeSections() {
                    const recipeIds = <?php echo json_encode(array_keys($recipes)); ?>;
                    recipeIds.forEach(sectionId => {
                        setupAddButton(sectionId);
                        setupRemoveListener(sectionId);
                        setupSlider(sectionId);

                        // Load any saved group data
                        const sectionValues = savedGroups[sectionId] || {};
                        const groupKeys = Object.keys(sectionValues);
                        if (groupKeys.length > 0) {
                            groupKeys.forEach(articleNumber => {
                                const groupData = sectionValues[articleNumber];
                                addNewGroup(sectionId, groupData);
                            });
                        } else {
                            // If no saved data, start with up to 3 groups or the max count
                            let max = Math.min(articleCounts[sectionId], 3);
                            for (let i = 0; i < max; i++) {
                                addNewGroup(sectionId);
                            }
                        }
                        updateAddButtonState(sectionId);
                        updateAllProductsAddedFlags();
                    });
                }

                // Initialize any keyword groups that exist on page load, hooking up CodeMirror if needed
                function initializeInitialGroups() {
                    const initialGroups = document.querySelectorAll('.keyword-group');
                    initialGroups.forEach(group => {
                        const textarea = group.querySelector('.product-urls');
                        const addProductsLink = group.querySelector('.add-products-link');
                        if (textarea) {
                            initCodeMirror(textarea, addProductsLink);
                        }
                    });
                }

                // Refresh CodeMirror editors when an accordion panel is shown
                document.querySelectorAll('.accordion-collapse').forEach(panel => {
                    panel.addEventListener('shown.bs.collapse', function() {
                        panel.querySelectorAll('textarea.cm-initialized').forEach(textarea => {
                            if (textarea.CodeMirrorEditor) {
                                textarea.CodeMirrorEditor.refresh();
                            }
                        });
                    });
                });

                // When user clicks "Import" in the bulk modal
                document.getElementById('importBulkData').addEventListener('click', handleBulkImport);

                // Initialize everything
                initializeSections();
                initializeInitialGroups();
                setupBulkImportButtons();

                // Keyword Tool Implementation
                let debounceTimer;
                const keywordToolInput = document.getElementById('keyword-tool-input');
                const keywordToolSuggestionsList = document.getElementById('keyword-tool-suggestions-list');

                function debounce(func, delay) {
                    return function() {
                        const context = this;
                        const args = arguments;
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => func.apply(context, args), delay);
                    };
                }

                async function fetchKeywordToolSuggestions(query) {
                    if (query.length === 0) {
                        keywordToolSuggestionsList.innerHTML = '';
                        return;
                    }

                    let url = "<?php echo htmlspecialchars_decode($this->getEbayAutosuggestUri(), ENT_QUOTES); ?>";
                    url = url.replace('%KEYWORD%', encodeURIComponent(query));

                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const data = await response.json();
                        const suggestions = (data?.richRes?.sug || []).map(item => ({
                            value: item.kwd
                        }));

                        displayKeywordToolSuggestions(suggestions);
                    } catch (error) {
                        console.error('Error fetching suggestions:', error);
                        keywordToolSuggestionsList.innerHTML = '<li class="list-group-item"><?php echo esc_js(__('Error fetching suggestions.', 'independent-niche')); ?></li>';
                    }
                }

                function displayKeywordToolSuggestions(suggestions) {
                    keywordToolSuggestionsList.innerHTML = '';
                    if (!suggestions || suggestions.length === 0) {
                        keywordToolSuggestionsList.innerHTML = '<li class="list-group-item"><?php echo esc_js(__('No suggestions found.', 'independent-niche')); ?></li>';
                    } else {
                        suggestions.forEach(suggestion => {
                            const listItem = document.createElement('a');
                            listItem.classList.add('list-group-item', 'list-group-item-action');
                            listItem.href = "javascript:void(0);";
                            listItem.textContent = suggestion.value;

                            listItem.addEventListener('click', function() {
                                if (currentKeywordToolSectionId) {
                                    removeEmptyKeywords(currentKeywordToolSectionId);
                                    addNewGroup(currentKeywordToolSectionId, {
                                        keyword: suggestion.value,
                                        as_title: '',
                                        products: '',
                                        post_id: ''
                                    });
                                    listItem.remove();
                                }
                            });

                            keywordToolSuggestionsList.appendChild(listItem);
                        });
                    }
                }

                keywordToolInput.addEventListener('input', debounce(function() {
                    const query = this.value.trim();
                    fetchKeywordToolSuggestions(query);
                }, 300));

                const keywordToolButtons = document.querySelectorAll('.keyword-tool-btn');
                keywordToolButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const sectionId = this.getAttribute('data-section');
                        currentKeywordToolSectionId = sectionId;
                        const recipeName = document.querySelector(`.accordion-item#accordionItem${sectionId} .accordion-button`).textContent;
                        document.getElementById('keywordToolSectionName').textContent = recipeName.trim();
                        keywordToolInput.value = '';
                        keywordToolSuggestionsList.innerHTML = '';
                    });
                });

            });
        </script>
<?php
    }
}
