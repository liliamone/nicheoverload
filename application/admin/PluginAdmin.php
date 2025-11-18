<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\admin\RebuildMetabox;
use IndependentNiche\application\components\Task;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

/**
 * PluginAdmin class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class PluginAdmin
{
    const MIN_CE_VERSION = '15.5.1';
    const MIN_TMN_VERSION_FOR_REBUILD = '4.0.0';

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        if (!\is_admin())
            die('You are not authorized to perform the requested action.');

        \add_action('admin_enqueue_scripts', array($this, 'admin_load_scripts'));
        \add_filter('parent_file', array($this, 'highlight_admin_menu'));
        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('after_plugin_row_independent-niche/independent-niche.php', array($this, 'after_row_notice'));

        new RebuildMetabox;
        new WizardController;
        new StatController;
    }

    function admin_load_scripts()
    {
        if ($GLOBALS['pagenow'] != 'admin.php' || empty($_GET['page']))
            return;

        $page_pats = explode('-', $_GET['page']);

        if (count($page_pats) < 3 || $page_pats[0] . '-' . $page_pats[1] . '-' . $page_pats[2] != Plugin::slug())
            return;

        \wp_enqueue_style('ind-bootstrap', \IndependentNiche\PLUGIN_RES . '/bootstrap/css/bootstrap.css');
        \wp_enqueue_script('ind-bootstrap', \IndependentNiche\PLUGIN_RES . '/bootstrap/js/bootstrap.bundle.min.js');

        $v = Plugin::version();
        if (Plugin::isDevEnvironment())
            $v .= '_' . rand(1, 100);
        \wp_enqueue_style(Plugin::slug() . '-admin', \IndependentNiche\PLUGIN_RES . '/css/admin.css', array(), $v);
    }

    public function add_admin_menu()
    {
        \add_menu_page(Plugin::getName(), Plugin::getName(), 'publish_posts', Plugin::getSlug(), null, 'dashicons-grid-view');
    }

    public static function render($view_name, $_data = null)
    {
        if (is_array($_data))
            extract($_data, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data;

        include \IndependentNiche\PLUGIN_PATH . 'application/admin/views/' . PluginAdmin::sanitize($view_name) . '.php';
    }

    function highlight_admin_menu($file)
    {
        global $plugin_page;

        if ($file != 'options.php' || substr($plugin_page, 0, strlen(Plugin::slug())) !== Plugin::slug())
            return $file;

        if (strstr($plugin_page, Plugin::slug() . '-settings-'))
            $plugin_page = 'independent-niche-settings';

        return $file;
    }

    public function after_row_notice()
    {
        $screen = \get_current_screen();
        if ($screen->id !== 'plugins')
            return;

        $status = Task::getInstance()->getStatus();
        if ($status != Task::STATUS_WORKING)
            return;

        $m = __('Article generation is currently in progress. Please do not uninstall the plugin!', 'independent-niche');
        echo '<tr class="active indniche-notice-row"><th class="check-column"></th><td colspan="4" class="plugin-title column-primary"><div style="padding: 10px;background-color: #fff3cd;border: 1px solid #ffeeba;margin: 5px 0;color:#856404;" class="indniche-custom-notice">' . $m . '</div></td></tr>';
    }

    static public function sanitize($str)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $str);
    }

    public static function apiRequest($api_url, array $options = array())
    {
        if (Plugin::isDevEnvironment())
            $options['sslverify'] = false;

        $response = \wp_remote_request($api_url, $options);
        //echo (\wp_remote_retrieve_body($response));

        if (\is_wp_error($response))
            return false;

        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code == 200)
            return $response;
        else
            return false;
    }
}
