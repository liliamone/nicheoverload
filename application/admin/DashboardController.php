<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\Wizard;

/**
 * DashboardController class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class DashboardController
{
    const slug = 'independent-niche';

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'), 11);
    }

    public function add_admin_menu()
    {
        \add_submenu_page(
            Plugin::slug,
            __('Dashboard', 'independent-niche'),
            __('🏠 Dashboard', 'independent-niche'),
            'publish_posts',
            self::slug,
            array($this, 'actionIndex'),
            0  // Position 0 = first
        );
    }

    public function actionIndex()
    {
        // Handle API Key save
        if (isset($_POST['ind_save_api_key']) && isset($_POST['_wpnonce'])) {
            if (\wp_verify_nonce(\sanitize_key($_POST['_wpnonce']), 'ind_save_api_key')) {
                $api_key = isset($_POST['deepseek_api_key']) ? sanitize_text_field($_POST['deepseek_api_key']) : '';

                // Save API key
                $ai_options = get_option('independent-niche_ai', array());
                $ai_options['deepseek_api_key'] = $api_key;
                update_option('independent-niche_ai', $ai_options);

                echo '<div class="notice notice-success is-dismissible"><p><strong>✅ ' . __('API Key saved successfully!', 'independent-niche') . '</strong></p></div>';
            }
        }

        // Get current API key
        $ai_options = get_option('independent-niche_ai', array());
        $api_key = isset($ai_options['deepseek_api_key']) ? $ai_options['deepseek_api_key'] : '';

        // Get wizard step
        $wizard_step = Wizard::getInstance()->getCurrentStep();

        // Check configuration status
        $niche_config = NicheConfig::getInstance()->option('niche');
        $has_api_key = !empty($api_key);
        $has_niche = !empty($niche_config);

        $config_percentage = 0;
        if ($has_api_key) $config_percentage += 50;
        if ($has_niche) $config_percentage += 30;
        if ($wizard_step >= 7) $config_percentage += 20;

        PluginAdmin::render('dashboard', array(
            'api_key' => $api_key,
            'has_api_key' => $has_api_key,
            'wizard_step' => $wizard_step,
            'config_percentage' => $config_percentage,
            'has_niche' => $has_niche,
        ));
    }
}
