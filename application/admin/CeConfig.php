<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\WizardBootConfig;

use function IndependentNiche\prnx;

/**
 * CeConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class CeConfig extends WizardBootConfig
{
    const MIN_CE_VERSION = '15.3.0';
    const PRICE_COMPARISON_MODULES = array('Amazon', 'AmazonNoApi', 'Bestbuy', 'Bolcom', 'CjProducts', 'Ebay2', 'Kelkoo', 'Kieskeurignl', 'Viglink', 'TradedoublerProducts', 'TradetrackerProducts', 'Walmart', 'Webgains');

    public function getTitle()
    {
        return sprintf(__('%s integration', 'independent-niche'), 'Content Egg');
    }

    public function option_name()
    {
        return Plugin::slug . '_ce';
    }

    protected function options()
    {
        return array(
            'price_comparison' => array(
                'title' => __('Price comparison', 'independent-niche'),
                'description' => __('Activate modules with EAN search to add a price comparison feature.', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'help_url' => 'https://tmniche-docs.keywordrush.com/advanced-use/price-comparison#how-price-comparison-works',
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'independent-niche'),
                    'disabled' => __('Disabled', 'independent-niche'),
                ),
                'default' => 'disabled',

            ),
            'add_youtube' => array(
                'title' => __('YouTube videos', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'independent-niche'),
                    'disabled' => __('Disabled', 'independent-niche'),
                ),
                'default' => 'disabled',
                'validator' => array(
                    array(
                        'call' => array($this, 'checkYoutubeModule'),
                        'message' => sprintf(__('Make sure the <a target="_blank" href="%s">Youtube module</a> is activated and configured correctly.', 'independent-niche'), \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-modules--Youtube')),
                    ),
                ),
            ),
            'featured_image' => array(
                'title' => __('Featured images source', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Default Content Egg behavior', 'independent-niche'),
                    'main' => __('Main affiliate module', 'independent-niche'),
                    'pixabay' => __('Pixabay images', 'independent-niche'),
                    'main_pixabay' => __('Main module for product articles + Pixabay for info articles', 'independent-niche'),
                ),
                'default' => 'main',
            ),
            'settings_transfer' => array(
                'description' => '<div class="py-3" role="alert">'
                    . __('Notice: Your module settings will be passed to our automation system to search for affiliate products.', 'independent-niche')
                    . '</div>',

                'callback' => array($this, 'render_text'),
            ),
        );
    }

    public function checkYoutubeModule($value)
    {
        if ($value == 'disabled')
            return true;

        $manager = \ContentEgg\application\components\ModuleManager::getInstance();

        if (!$manager->isModuleActive('Youtube'))
            return false;

        return true;
    }
}
