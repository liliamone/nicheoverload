<?php

namespace TooMuchNiche\application\components;

use TooMuchNiche\application\admin\CeConfig;
use TooMuchNiche\application\components\Wizard;
use TooMuchNiche\application\admin\PluginAdmin;
use TooMuchNiche\application\admin\KeywordConfig;
use TooMuchNiche\application\admin\NicheConfig;
use TooMuchNiche\application\Plugin;

defined('\ABSPATH') || exit;

/**
 * WizardBootConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
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
        return Plugin::slug . '';
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Create Task', 'too-much-niche') . ' &lsaquo; ' . Plugin::getName(), __('Create Task', 'too-much-niche'), 'publish_posts', $this->page_slug(), array($this, 'settings_page'));
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
