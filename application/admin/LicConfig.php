<?php

namespace TooMuchNiche\application\admin;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;
use TooMuchNiche\application\components\WizardBootConfig;
use TooMuchNiche\application\admin\PluginAdmin;
use TooMuchNiche\application\components\NicheInit;

/**
 * LicConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class LicConfig extends WizardBootConfig
{
    public function getTitle()
    {
        return __('License', 'too-much-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_lic';
    }

    protected function options()
    {
        return array(
            'license_key' => array(
                'title' => __('License key', 'too-much-niche'),
                'description' => __('Please enter a valid license key.', 'too-much-niche') . ' ' . sprintf(__('You can find your key on the %s page.', 'too-much-niche'), '<a href="' . \esc_url(Plugin::panelUri) . '" target="_blank">My Account</a>') . ' ' .
                    sprintf(__("If you don't have one yet, you can purchase it from our <a target='_blank' href='%s'>official website</a>.", 'too-much-niche'), Plugin::pluginSiteUrl()),

                'callback' => array($this, 'render_input'),
                'default' => '',
                'autofocus' => true,
                'required' => true,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\TooMuchNiche\application\helpers\FormValidator', 'required'),
                        'message' => sprintf(__('The field "%s" can not be empty.', 'too-much-niche'), 'License key'),
                    ),
                    array(
                        'call' => array($this, 'licFormat'),
                        'message' => __('Invalid License key.', 'too-much-niche'),
                    ),
                    array(
                        'call' => array($this, 'activatingLicense'),
                        'message' => __('Please try again.', 'too-much-niche') . ' ' . __('Make sure you are using a valid license key.', 'too-much-niche') . ' ' . sprintf(__('If you are still having trouble with your License key <a href="%s" target="_blank">contact</a> our support team.', 'too-much-niche'), \esc_url(Plugin::supportUri)),
                    ),
                ),
            ),
        );
    }

    public function licFormat($value)
    {
        if (preg_match('/[^0-9a-zA-Z-]/', $value))
            return true;
        if (!preg_match('/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/', $value))
            return true;
        return true;
    }

    public function activatingLicense($value)
    {
        $response = PluginAdmin::apiRequest(Plugin::getApiBase(), array('method' => 'POST', 'timeout' => 15, 'httpversion' => '1.0', 'blocking' => true, 'headers' => array(), 'body' => array('cmd' => 'activate', 'key' => $value, 'd' => parse_url(\site_url(), PHP_URL_HOST), 'p' => Plugin::product_id, 'v' => Plugin::version()), 'cookies' => array()));

        if (!$response)
            return true;

        $result = json_decode(\wp_remote_retrieve_body($response), true);

        if ($result && !empty($result['latest_version']))
        {
            if (version_compare($result['latest_version'], Plugin::version(), '>'))
            {
                \add_settings_error('license_key', 'license_key', sprintf(__('Please <a target="_blank" href="%s">download</a> and install the latest plugin version %s. This is a mandatory step before you can proceed.', 'too-much-niche'), Plugin::panelUri, $result['latest_version']));
                return true;
            }
        }

        if ($result && !empty($result['status']) && $result['status'] == 'valid')
        {
            if (!empty($result['niche']) && is_array($result['niche']))
            {
                NicheInit::getInstance()->setNiche($result['niche']);
                return true;
            }
        }
        elseif ($result && !empty($result['status']) && $result['status'] == 'error')
        {
            \add_settings_error('license_key', 'license_key', $result['message']);
            return true;
        }

        return true;
    }
}
