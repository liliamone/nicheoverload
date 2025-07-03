<?php

namespace TooMuchNiche\application\helpers;

use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * WpHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class WpHelper
{

    public static function isPluginInstalled($plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $all_plugins = get_plugins();

        foreach ($all_plugins as $plugin_path => $plugin_data)
        {
            if (strpos($plugin_path, $plugin_slug) !== false)
            {
                return true;
            }
        }

        return false;
    }

    public static function installAndActivatePlugin($plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // For plugins_api()

        // Get plugin information from WordPress API
        $api = plugins_api('plugin_information', array(
            'slug' => $plugin_slug,
            'fields' => array('sections' => false)
        ));

        if (is_wp_error($api))
        {
            return false;
        }

        // Prepare for installation
        $upgrader = new \Plugin_Upgrader(new \Automatic_Upgrader_Skin());

        // Install the plugin
        $result = $upgrader->install($api->download_link);

        if ($result && !is_wp_error($result))
        {
            $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug; // Path to the installed plugin
            $plugin_main_file = $plugin_path . '/' . $plugin_slug . '.php';

            if (!file_exists($plugin_main_file))
                $plugin_main_file = $plugin_path . '/plugin.php';

            // Activate the plugin
            if (file_exists($plugin_main_file))
            {
                activate_plugin($plugin_main_file);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        return true;
    }
}
