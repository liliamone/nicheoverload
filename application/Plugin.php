<?php

namespace IndependentNiche\application;

defined('\ABSPATH') || exit;

use IndependentNiche\application\components\logger\Logger;
use IndependentNiche\application\components\ArticlePoster;

/**
 * Plugin class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class Plugin
{
    const version = '1.0.0';
    const db_version = 22;
    const wp_requires = '6.4';
    const product_id = 900;
    const slug = 'independent-niche';
    const short_slug = 'indniche';
    const name = 'Independent Niche Generator';
    const api_base = '';
    const api_niche = '';
    const website = 'https://github.com/independent-niche-generator';
    const supportUri = 'https://github.com/independent-niche-generator/issues';
    const panelUri = 'https://github.com/independent-niche-generator';

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
        // Plus de vérification de licence - toujours actif
        return true;
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
        \load_plugin_textdomain('independent-niche', false, \IndependentNiche\PLUGIN_PATH . 'languages');
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
