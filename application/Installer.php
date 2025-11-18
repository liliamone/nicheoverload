<?php

namespace IndependentNiche\application;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\NicheInit;

use function IndependentNiche\prnx;

/**
 * Installer class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class Installer
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {

        if (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php')
        {
            \add_action('admin_init', array($this, 'requirements'), 0);
        }

        \add_action('admin_init', array($this, 'upgrade'));
        \add_action('admin_init', array($this, 'redirect_after_activation'));
    }

    static public function dbVesrion()
    {
        return Plugin::db_version;
    }

    public static function activate()
    {
        if (!\current_user_can('activate_plugins'))
            return;

        self::requirements();
        \add_option(Plugin::slug . '_do_activation_redirect', true);
        \add_option(Plugin::slug . '_first_activation_date', time());
        self::upgradeTables();

        //PostScheduler::maybeAddScheduleEvent();
    }

    public static function deactivate()
    {
        TaskScheduler::clearScheduleEvent();
        //PostScheduler::clearScheduleEvent();
    }

    public static function requirements()
    {
        $php_min_version = '7.2';
        $extensions = array();
        $plugins = array();

        $errors = array();

        global $wp_version;
        if (version_compare(Plugin::wp_requires, $wp_version, '>'))
            $errors[] = sprintf('You are using Wordpress %s. <em>%s</em> requires at least <strong>Wordpress %s</strong>.', $wp_version, Plugin::name, Plugin::wp_requires);

        $php_current_version = phpversion();
        if (version_compare($php_min_version, $php_current_version, '>'))
            $errors[] = sprintf('PHP is installed on your server %s. <em>%s</em> requires at least <strong>PHP %s</strong>.', $php_current_version, Plugin::name, $php_min_version);

        foreach ($extensions as $extension)
        {
            if (!extension_loaded($extension))
                $errors[] = sprintf('Requires PHP extension <strong>%s</strong>.', $extension);
        }
        foreach ($plugins as $plugin_id => $plugin)
        {
            if (!\is_plugin_active($plugin_id) || \version_compare($plugin['version'], self::getPluginVersion($plugin_id), '>'))
                $errors[] = sprintf('<em>%s</em> requires <strong>%s %s+</strong> to be installed and active.', Plugin::name, $plugin['name'], $plugin['version']);
        }

        if (!$errors)
            return;
        unset($_GET['activate']);
        \deactivate_plugins(\plugin_basename(\IndependentNiche\PLUGIN_FILE));
        $e = sprintf('<div class="error"><p>%1$s</p><p><em>%2$s</em> ' . 'cannot be installed!' . '</p></div>', join('</p><p>', $errors), Plugin::name);
        \wp_die($e);
    }

    public static function uninstall()
    {
        if (!current_user_can('activate_plugins'))
            return;

        \delete_option(Plugin::slug . '_db_version');
        \delete_option(Plugin::slug . '_status');
        \delete_option(Plugin::slug . '_current_step');
        \delete_option(Plugin::slug . '_stat');
        NicheInit::getInstance()->deleteNiche();

        $options = array('CeConfig', 'NicheConfig', 'SiteConfig', 'TaskConfig', 'AiConfig', 'KeywordConfig');
        foreach ($options as $option)
        {
            $m = "\\IndependentNiche\\application\\admin\\" . $option;
            \delete_option($m::getInstance()->option_name());
        }

        $models = array('ArticleModel', 'LogModel');

        foreach ($models as $model)
        {
            $m = "\\IndependentNiche\\application\\models\\" . $model;
            $m::model()->dropTable();
        }
    }

    public static function upgrade()
    {
        $db_version = get_option(Plugin::slug . '_db_version');
        if ($db_version >= self::dbVesrion())
            return;
        self::upgradeTables();
        \update_option(Plugin::slug . '_db_version', self::dbVesrion());
    }

    private static function upgradeTables()
    {
        $models = array('ArticleModel', 'LogModel');
        $sql = '';
        foreach ($models as $model)
        {
            $m = "\\IndependentNiche\\application\\models\\" . $model;
            $sql .= $m::model()->getDump();
            $sql .= "\r\n";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function redirect_after_activation()
    {

        if (\get_option(Plugin::slug . '_do_activation_redirect', false))
        {
            \update_option(Plugin::slug . '_do_activation_redirect', false);
            \delete_option(Plugin::slug . '_do_activation_redirect');
            \wp_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::slug()));
        }
    }

    public static function getPluginVersion($plugin_file)
    {
        $data = \get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
        if (isset($data['Version']))
            return $data['Version'];
        else
            return 0;
    }
}
