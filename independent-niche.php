<?php

namespace IndependentNiche;

defined('\ABSPATH') || exit;

/*
Plugin Name: Independent Niche Generator
Plugin URI: https://github.com/independent-niche-generator
Description: Générateur de contenu de niche indépendant avec intégration Content Egg Pro et DeepSeek API. Dashboard professionnel avec configuration API simplifiée.
Version: 2.1.0
Author: Independent Developer
Text Domain: independent-niche
Domain Path: /languages
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined('\ABSPATH') || die('No direct script access allowed!');

if (!function_exists('add_action')) {
    echo 'Hi there! I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');
define(NS . 'PLUGIN_PATH', \plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_FILE', __FILE__);
define(NS . 'PLUGIN_RES', \plugins_url('res', __FILE__));

require_once PLUGIN_PATH . 'loader.php';

\add_action('plugins_loaded', array('\IndependentNiche\application\Plugin', 'getInstance'));
if (\is_admin())
{
    \register_activation_hook(__FILE__, array(\IndependentNiche\application\Installer::getInstance(), 'activate'));
    \register_deactivation_hook(__FILE__, array(\IndependentNiche\application\Installer::getInstance(), 'deactivate'));
    \register_uninstall_hook(__FILE__, array('\IndependentNiche\application\Installer', 'uninstall'));
    \add_action('init', array('\IndependentNiche\application\admin\PluginAdmin', 'getInstance'));
}
