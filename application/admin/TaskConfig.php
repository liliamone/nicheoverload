<?php

namespace TooMuchNiche\application\admin;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\components\NicheApi;
use TooMuchNiche\application\components\Task;
use TooMuchNiche\application\Plugin;
use TooMuchNiche\application\components\WizardBootConfig;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

/**
 * TaskConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class TaskConfig extends WizardBootConfig
{

    public function getTitle()
    {
        return __('Create Task', 'too-much-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_last';
    }

    public function preSettingsWarning()
    {
        if (NicheConfig::isCeIntegration())
            return CeConfig::getInstance()->preSettingsWarning();
    }

    protected function options()
    {
        return array(
            'hidden' => array(
                'callback' => array($this, 'render_input'),
                'type' => 'hidden',
                'validator' => array(
                    array(
                        'call' => array($this, 'submitTask'),
                    ),
                ),
            ),
            'notice' => array(
                'callback' => array($this, 'render_text'),
                'description' => '<div class="lead">' . __('You\'re all set! Start the task now.', 'too-much-niche') . '</div>'
                    . '<br />',
            ),
            'email_notice' => array(
                'title' => __('Send notification', 'too-much-niche'),
                'description' => sprintf(__('Notify admin via email upon task completion.', 'too-much-niche'), \get_option('admin_email')),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
        );
    }

    public function getTaskOptions()
    {
        $configs = array(
            NicheConfig::getInstance(),
            AiConfig::getInstance(),
            CeConfig::getInstance(),
            SiteConfig::getInstance(),
            TaskConfig::getInstance(),
            KeywordConfig::getInstance(),
        );

        $settings = array();
        foreach ($configs as $config)
        {
            $settings = array_merge($settings, $config->getOptionValues());
        }

        return $settings;
    }

    public function submitTask()
    {
        $settings = $this->getTaskOptions();
        $settings['modules'] = array();
        $settings['active_modules'] = array();

        if (NicheConfig::isCeIntegration())
        {
            if (!class_exists('\ContentEgg\application\components\ModuleManager'))
            {
                \add_settings_error('notice', 'notice', 'Content Egg Pro is not active.');
                return false;
            }

            $main_module = NicheConfig::getInstance()->option('main_module');
            if ($s = self::getCeModuleSettings($main_module))
                $settings['modules'][$main_module] = $s;

            if (CeConfig::getInstance()->option('add_youtube') == 'enabled' && $s = self::getCeModuleSettings('Youtube'))
                $settings['modules']['Youtube'] = $s;

            $settings['active_modules'] = \ContentEgg\application\components\ModuleManager::getInstance()->getModulesIdList(true);

            if (CeConfig::getInstance()->option('price_comparison') == 'enabled')
            {
                $comparison_modules = array_intersect($settings['active_modules'], CeConfig::PRICE_COMPARISON_MODULES);
                foreach ($comparison_modules as $module_id)
                {
                    $s = self::getCeModuleSettings($module_id);
                    if ($s)
                        $settings['modules'][$module_id] = $s;
                }
            }
        }

        $result = NicheApi::request('/task', $settings, 'POST');

        if ($result && !empty($result['status']) && $result['status'] == 'success')
        {
            Task::getInstance()->start();
            return true;
        }

        if ($result && !empty($result['status']) && $result['status'] == 'error')
        {
            \add_settings_error('hidden', 'hidden', __("Can't create the task.", 'too-much-niche'));

            $m = $result['message'];
            if (strstr($m, 'Please enter a new license key'))
            {
                $rurl = \get_admin_url(\get_current_blog_id(), 'admin.php?page=too-much-niche-articles&action=restart&_wpnonce=' . \wp_create_nonce('tmn_restart')) . '&restartniche=0';
                $m .= ' [<a href="' . $rurl . '">' . __('Restart with new key', 'too-much-niche') . '</a>]';
            }
            \add_settings_error('hidden', 'hidden', $m);
            return false;
        }

        return false;
    }

    private static function getCeModuleSettings($module_id)
    {
        if (!$module_id)
            return false;

        $config = \ContentEgg\application\components\ModuleManager::configFactory($module_id);
        return $config->getOptionValues();
    }
}
