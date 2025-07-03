<?php

namespace TooMuchNiche;

defined('\ABSPATH') || exit;

/*
Plugin Name: NicheOverload
Plugin URI: https://old-admin.festingervault.com/p/niche-overflow/
Version: 4.2.0
Description: Unlock hidden niche markets with NicheOverload! Discover untapped opportunities, optimize content, and dominate search results.
Author: Festinger Vault
Author URI: https://www.keywordrush.com
Text Domain: too-much-niche
Requires at least: 6.4
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/licenses.html

NicheOverload available through Festinger Vault is an independent version maintained by our team. We are not affiliated, endorsed, or associated with Too Much Niche or keywordrush.com in any way. Our support is exclusively for the forked version available in Festinger Vault. If you require official updates, premium features, or priority support from the original developers, we strongly recommend purchasing a valid license from them.
*/

/*
 * Copyright (c)  www.keywordrush.com  (email: support@keywordrush.com)
 */

defined('\ABSPATH') || die('No direct script access allowed!');

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');
define(NS . 'PLUGIN_PATH', \plugin_dir_path(__FILE__));
define(NS . 'PLUGIN_FILE', __FILE__);
define(NS . 'PLUGIN_RES', \plugins_url('res', __FILE__));

require_once PLUGIN_PATH . 'loader.php';

\add_action('plugins_loaded', array('\TooMuchNiche\application\Plugin', 'getInstance'));
if (\is_admin())
{
    \register_activation_hook(__FILE__, array(\TooMuchNiche\application\Installer::getInstance(), 'activate'));
    \register_deactivation_hook(__FILE__, array(\TooMuchNiche\application\Installer::getInstance(), 'deactivate'));
    \register_uninstall_hook(__FILE__, array('\TooMuchNiche\application\Installer', 'uninstall'));
    \add_action('init', array('\TooMuchNiche\application\admin\PluginAdmin', 'getInstance'));
}