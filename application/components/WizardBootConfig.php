<?php

namespace IndependentNiche\application\components;

use IndependentNiche\application\admin\CeConfig;
use IndependentNiche\application\components\Wizard;
use IndependentNiche\application\admin\PluginAdmin;
use IndependentNiche\application\admin\KeywordConfig;
use IndependentNiche\application\admin\NicheConfig;
use IndependentNiche\application\Plugin;

defined('\ABSPATH') || exit;

/**
 * WizardBootConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
abstract class WizardBootConfig extends BootConfig
{
    abstract function getTitle();

    protected function __construct()
    {
        \add_action('pre_update_option', array($this, 'setNextStep'), 10, 3);
        parent::__construct();
    }

    public function page_slug()
    {
        return Plugin::slug . '-wizard';
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Create Task', 'independent-niche') . ' &lsaquo; ' . Plugin::getName(), __('Create Task', 'independent-niche'), 'publish_posts', $this->page_slug(), array($this, 'settings_page'));
    }

    public function setNextStep($value, $option, $old_value)
    {
        if ($option !== $this->option_name())
            return $value;

        if (!$value)
            return $value;

        if ($this->preSettingsWarning() || \get_settings_errors())
            return $value;

        $step = Wizard::getInstance()->getCurrentStep();

        if ($step == 4 && !NicheConfig::isCeIntegration())
        {
            $step++;
            \delete_option(CeConfig::getInstance()->option_name());
        }

        Wizard::getInstance()->setCurrentStep($step + 1);

        return $value;
    }

    public static function addBlankDropdownItem(array $options, $item_title = '')
    {
        $options = array_reverse($options);
        $options[''] = $item_title;
        $options = array_reverse($options);
        return $options;
    }

    public function preSettingsWarning()
    {
        return '';
    }

    public function settings_page()
    {
        PluginAdmin::render('settings', array('that' => $this, 'page_slug' => $this->page_slug(), 'title' => $this->getTitle()));
    }
}
