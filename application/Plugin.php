<?php

namespace TooMuchNiche\application;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\components\logger\Logger;
use TooMuchNiche\application\components\ArticlePoster;

/**
 * Plugin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class Plugin
{
    const version = '4.2.0';
    const db_version = 22;
    const wp_requires = '6.2';
    const product_id = 800;
    const slug = 'too-much-niche';
    const short_slug = 'tmniche';
    const name = 'Too Much Niche';
    const api_base = 'https://www.keywordrush.com/api/v1';
    const api_niche = 'https://www.keywordrush.com/niche/api1';
    const website = 'https://www.keywordrush.com';
    const supportUri = 'https://www.keywordrush.com/contact';
    const panelUri = 'https://www.keywordrush.com/panel';

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        self::initLogger();
        $this->loadTextdomain();
        ArticlePoster::initAction();
        TaskScheduler::initAction();
        //PostScheduler::initAction();
        CommentScheduler::initAction();
    }

    static public function version()
    {
        return self::version;
    }

    static public function slug()
    {
        return self::slug;
    }

    public static function isActivated()
    {
        if (\TooMuchNiche\application\admin\LicConfig::getInstance()->option('license_key'))
            return true;
        else
            return false;
    }

    public static function getSlug()
    {
        return self::slug;
    }

    public static function getShortSlug()
    {
        return self::short_slug;
    }

    public static function getName()
    {
        return self::name;
    }

    public static function getWebsite()
    {
        return self::website;
    }

    private function loadTextdomain()
    {
        \load_plugin_textdomain('too-much-niche', false, \TooMuchNiche\PLUGIN_PATH . 'languages');
    }

    public static function isDevEnvironment()
    {
        if (defined('TOO_MUCH_NICHE_DEBUG') && TOO_MUCH_NICHE_DEBUG)
            return true;
        else
            return false;
    }

    public static function getApiBase()
    {
        if (Plugin::isDevEnvironment())
            return 'https://www.keywordrush-develop.com:8890/api/v1';
        else
            return self::api_base;
    }

    public static function getApiNiche()
    {
        if (Plugin::isDevEnvironment())
            return 'https://www.keywordrush-develop.com:8890/niche/api1';
        else
            return self::api_niche;
    }

    public static function pluginSiteUrl()
    {
        return self::getWebsite() . '/toomuchniche?utm_source=toomuchniche&utm_medium=referral&utm_campaign=wpadmin';
    }

    public static function logger()
    {
        return Logger::getInstance();
    }

    public static function initLogger()
    {
        $logger = self::logger();
        $logger->getDispatcher()->targets['db']->enabled = true;
        $logger->getDispatcher()->targets['email']->enabled = false;
        $levels = array(Logger::LEVEL_ERROR, Logger::LEVEL_INFO, Logger::LEVEL_WARNING);
        $logger->getDispatcher()->targets['db']->levels = $levels;
    }
}
