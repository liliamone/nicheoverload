<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\admin\PluginAdmin;
use IndependentNiche\application\components\NicheInit;
use IndependentNiche\application\components\WizardBootConfig;

use function IndependentNiche\prnx;

/**
 * NicheConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class NicheConfig extends WizardBootConfig
{
    public function getTitle()
    {
        return __('Niche settings', 'independent-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_niche';
    }

    public static function getMainModules()
    {
        return array(
            'Amazon' => 'Amazon (API)',
            'AmazonNoApi' => 'Amazon No Api',
            'Bolcom' => 'Bolcom',
            'AE__booking' => 'AE:Booking.com',
            'AE__etsy' => 'AE:Etsy.com',
            'AE__udemycom' => 'AE:Udemy.com',
            'AE__amazonae' => 'AE:Amazon.ae',
            'AE__amazonca' => 'AE:Amazon.ca',
            'AE__amazoncom' => 'AE:Amazon.com',
            'AE__amazoncomau' => 'AE:Amazon.com.ua',
            'AE__amazoncombe' => 'AE:Amazon.com.be',
            'AE__amazoncombr' => 'AE:Amazon.com.br',
            'AE__amazoncommx' => 'AE:Amazon.com.mx',
            'AE__amazoncomtr' => 'AE:Amazon.com.tr',
            'AE__amazoncouk' => 'AE:Amazon.co.uk',
            'AE__amazonde' => 'AE:Amazon.de',
            'AE__amazoneg' => 'AE:Amazon.eg',
            'AE__amazones' => 'AE:Amazon.es',
            'AE__amazonfr' => 'AE:Amazon.fr',
            'AE__amazonit' => 'AE:Amazon.it',
            'AE__amazonin' => 'AE:Amazon.in',
            'AE__amazonpl' => 'AE:Amazon.pl',
            'AE__amazonsa' => 'AE:Amazon.sa',
            'AE__amazonse' => 'AE:Amazon.se',
            'AE__amazonsg' => 'AE:Amazon.sg',
            'AE__sconticasait' => 'AE:Sconticasa.it (custom)',
            'AE__shopissimoit' => 'AE:Shopissimo.it (custom)',
        );
    }

    public static function getManualModules()
    {
        return array('AE__etsy', 'AE__udemycom');
    }

    public static function isManualModule()
    {
        $module_id = NicheConfig::getInstance()->option('main_module');
        $manual_modules = NicheConfig::getManualModules();
        return in_array($module_id, $manual_modules);
    }

    protected function options()
    {
        $options = array(
            'notice' => array(
                'callback' => array($this, 'render_text'),
                'description' => '<div class="alert alert-light col-7" role="alert">' . sprintf(__('Please refer to the <a target="_blank" href="%s">Quick start guide</a>.', 'independent-niche'), 'https://tmniche-docs.keywordrush.com/getting-started/quick-start') . '</div>'
            ),
            'niche' => array(
                'title' => __('Niche', 'independent-niche'),
                'description' => __('Describe your site\'s focus (e.g., <em>Outdoor and Adventure - Camping, hiking, and survival gear</em>)'),
                'help_url' => 'https://tmniche-docs.keywordrush.com/getting-started/niche-selection',
                'callback' => array($this, 'render_textarea'),
                'default' => '',
                'autofocus' => true,
                'required' => true,
                'maxlength' => 600,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\IndependentNiche\application\helpers\FormValidator', 'required'),
                        'message' => sprintf(__('The field "%s" can not be empty.', 'independent-niche'), __('Niche', 'independent-niche')),
                    ),
                    array(
                        'call' => array('\IndependentNiche\application\helpers\FormValidator', 'min_length'),
                        'arg' => 3,
                        'message' => sprintf(__('The field "%s" should contain at least %d characters.', 'independent-niche'), __('Niche', 'independent-niche'), 3),
                    ),
                    array(
                        'call' => array('\IndependentNiche\application\helpers\FormValidator', 'max_length'),
                        'arg' => 600,
                        'message' => sprintf(__('The field "%s" should contain at most %d characters.', 'independent-niche'), __('Niche', 'independent-niche'), 600),
                    ),
                    array(
                        'call' => array($this, 'regexMatch'),
                        'arg' => '~^[^\pL]~ui',
                        'message' => sprintf(__('The field "%s" must begin with a letter.', 'independent-niche'), __('Niche', 'independent-niche')),
                    ),
                    array(
                        'call' => array($this, 'initNiche'),
                        'message' => __('Could not initialize niche data with DeepSeek. Please check your API key in the Dashboard.', 'independent-niche'),
                    ),
                ),
            ),
            'language' => array(
                'title' => __('Language', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getLanguagesList(),
                'default' => self::getDefaultLang(),
            ),
            'ce_integration' => array(
                'title' => __('Integrate affiliate products into articles?', 'independent-niche'),
                'callback' => array($this, 'render_radio'),
                'dropdown_options' => array(
                    'no' => __('<b>Info Articles Only:</b> Create content without affiliate products', 'independent-niche'),
                    'yes' => __('<b>Product Articles:</b> Include affiliate products (requires Content Egg Pro)', 'independent-niche'),
                ),
                'default' => 'yes',
                'validator' => array(
                    array(
                        'call' => array($this, 'validateIsCeInstalled'),
                        'message' => self::getCeInstalledError(),
                    )
                ),
            ),
            'main_module' => array(
                'title' => __('Main affiliate module', 'independent-niche') . ' ' . __('(required)', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::addBlankDropdownItem(self::getMainModules(), '- ' . __('Please select a module', 'independent-niche') . ' -'),
                'default' => '',
                'required' => true,
                'validator' => array(
                    array(
                        'call' => array('\IndependentNiche\application\helpers\FormValidator', 'required'),
                        'when'    => array('ce_integration', 'yes'),
                        'message' => sprintf(__('The field "%s" can not be empty.', 'independent-niche'), 'Main affiliate module'),
                    ),
                    array(
                        'call' => array($this, 'checkMainModuleApi'),
                        'when'    => array('ce_integration', 'yes'),
                        'message' => __('Make sure the module is configured correctly and returns search results.', 'independent-niche'),
                    ),
                ),
            ),
            'render_js' => array(
                'callback' => array($this, 'render_js')
            ),
        );

        if (!NicheInit::getInstance()->isCeRequired())
        {
            $options['ce_integration'] = array(
                'callback' => array($this, 'render_hidden'),
                'default' => 'no',
            );
        }

        return $options;
    }

    public static function getLanguagesList()
    {
        return array_combine(array_values(self::getLanguages()), array_values(self::getLanguages()));
    }

    public static function getLanguages()
    {
        return array(
            'ar' => 'Arabic',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'tl' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'no' => 'Norwegian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt_BR' => 'Portuguese (Brazil)',
            'pt_PT' => 'Portuguese (Portugal)',
            'ro' => 'Romanian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'vi' => 'Vietnamese',
        );
    }

    public static function getDefaultLang()
    {
        $locale = \get_locale();
        if (isset($languages[$locale]))
            return $languages[$locale];

        $parts = explode('_', $locale);
        $lang = strtolower(reset($parts));
        $languages = self::getLanguages();

        if (isset($languages[$lang]))
            return $languages[$lang];
        else
            return 'English';
    }

    public function validateIsCeInstalled($value)
    {
        if ($value == 'no')
            return true;

        if (!self::isCeInstalledAndActive())
            return false;

        return true;
    }

    public static function isCeInstalledAndActive()
    {
        if (
            !\is_plugin_active('content-egg/content-egg.php') ||
            !class_exists('\ContentEgg\application\admin\LicConfig') ||
            !\ContentEgg\application\admin\LicConfig::getInstance()->option('license_key') ||
            \version_compare(PluginAdmin::MIN_CE_VERSION, \ContentEgg\application\Plugin::version(), '>')
        )
            return false;
        else
            return true;
    }

    public static function getCeInstalledError()
    {
        return sprintf(__('To add affiliate products to your site, <em>%s</em> requires <strong>%s %s+</strong> to be installed and active.', 'independent-niche'), Plugin::getName(), 'Content Egg Pro', PluginAdmin::MIN_CE_VERSION) .
            ' ' . sprintf(__("If you don't have one yet, you can purchase it from our <a target='_blank' href='%s'>official website</a>.", 'independent-niche'), Plugin::getWebsite() . '/contentegg/pricing?utm_source=toomuchniche&utm_medium=referral&utm_campaign=wpadmin');
    }

    public static function isCeIntegration()
    {
        return filter_var(NicheConfig::getInstance()->option('ce_integration'), FILTER_VALIDATE_BOOLEAN);
    }

    public function checkMainModuleApi($module_id)
    {
        $manager = \ContentEgg\application\components\ModuleManager::getInstance();

        if (!$manager->isModuleActive($module_id))
        {
            $settings_uri = \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-modules--' . $module_id);
            if (strstr($module_id, 'AE__'))
                $error = sprintf(__('Activate the %s module.', 'independent-niche'), self::getModuleName($module_id));
            else
                $error = sprintf(__('Activate the <a target="_blank" href="%s">%s module</a>.', 'independent-niche'), $settings_uri, self::getModuleName($module_id));

            \add_settings_error('main_module', 'main_module', $error);
            return false;
        }

        $error = '';

        if (!Plugin::isDevEnvironment() && in_array($module_id, array('Amazon', 'Bolcom')))
        {
            $parser = $manager->parserFactory($module_id);
            $api_error = '';
            try
            {
                $parser->getConfigInstance()->applayCustomOptions(array('entries_per_page' => 1));
                $test_keyword = date('Y');
                $data = $parser->doMultipleRequests($test_keyword);
            }
            catch (\Exception $e)
            {
                $api_error = $e->getMessage();
            }

            if (!$data)
                $error = sprintf(__('The %s module did not return products for test request.', 'independent-niche'), $parser->getName());

            if ($api_error)
                $error .= '<br>' . sprintf(__('API response: "%s"', 'independent-niche'), $api_error);
        }

        if ($error)
        {
            \add_settings_error('main_module', 'main_module', $error);
            return false;
        }

        return true;
    }

    public static function getModuleName($module_id)
    {
        $modules = self::getMainModules();
        if (isset($modules[$module_id]))
            return $modules[$module_id];
        else
            return $module_id;
    }

    public function render_js()
    {
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ceIntegrationSection = document.getElementById('ce_integration_section');
                const mainModuleSection = document.getElementById('main_module_section');
                const mainModuleSelect = document.getElementById('label-main_module');

                const ceIntegrationRadios = ceIntegrationSection.querySelectorAll('input[name="independent-niche_niche[ce_integration]"]');

                function toggleMainModuleSection() {
                    const selectedValue = document.querySelector('input[name="independent-niche_niche[ce_integration]"]:checked').value;
                    if (selectedValue === 'no') {
                        mainModuleSection.style.display = 'none';
                        mainModuleSelect.removeAttribute('required');
                    } else {
                        mainModuleSection.style.display = 'block';
                        mainModuleSelect.setAttribute('required', 'required');
                    }
                }

                ceIntegrationRadios.forEach(function(radio) {
                    radio.addEventListener('change', toggleMainModuleSection);
                });

                toggleMainModuleSection();
            });
        </script>

<?php
    }

    public function initNiche($value)
    {
        // Check if DeepSeek API key is configured first
        $api_key = \IndependentNiche\application\admin\AiConfig::getInstance()->option('deepseek_api_key');

        if (empty($api_key)) {
            \add_settings_error('niche', 'niche',
                sprintf(__('Please configure your DeepSeek API Key in the <a href="%s">Dashboard</a> before continuing.', 'independent-niche'),
                    \get_admin_url(\get_current_blog_id(), 'admin.php?page=independent-niche')
                )
            );
            return false;
        }

        // Try to initialize niche data from DeepSeek
        if (NicheInit::getInstance()->initializeNicheFromApi()) {
            return true;
        } else {
            // Log error but don't block - allow user to continue and fix later
            error_log('Independent Niche: Could not initialize niche data from DeepSeek API');
            \add_settings_error('niche', 'niche',
                __('Warning: Could not generate niche data from DeepSeek. You can continue and the system will retry later.', 'independent-niche'),
                'warning'
            );
            // Return true to not block the wizard
            return true;
        }
    }

    public static function getMainModuleName()
    {
        $module_id = NicheConfig::getInstance()->option('main_module');
        return self::getModuleName($module_id);
    }
}
