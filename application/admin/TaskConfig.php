<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\components\Task;
use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\WizardBootConfig;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

/**
 * TaskConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class TaskConfig extends WizardBootConfig
{

    public function getTitle()
    {
        return __('Create Task', 'independent-niche');
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
                'description' => '<div class="lead">' . __('You\'re all set! Start the task now.', 'independent-niche') . '</div>'
                    . '<br />',
            ),
            'email_notice' => array(
                'title' => __('Send notification', 'independent-niche'),
                'description' => sprintf(__('Notify admin via email upon task completion.', 'independent-niche'), \get_option('admin_email')),
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

        // Plus d'appel API externe - démarrer la tâche directement
        try {
            Task::getInstance()->start();
            return true;
        } catch (\Exception $e) {
            \add_settings_error('hidden', 'hidden', __("Can't create the task.", 'independent-niche') . ' ' . $e->getMessage());
            return false;
        }
    }

    private static function getCeModuleSettings($module_id)
    {
        if (!$module_id)
            return false;

        $config = \ContentEgg\application\components\ModuleManager::configFactory($module_id);
        return $config->getOptionValues();
    }
}
