<?php

namespace TooMuchNiche\application\components;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;
use TooMuchNiche\application\admin\PluginAdmin;
use TooMuchNiche\application\admin\LicConfig;

use function TooMuchNiche\prnx;

/**
 * NicheApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class NicheApi
{
    public static function get($path)
    {
        return self::request($path);
    }

    public static function request($path, array $data = array(), $method = 'GET')
    {
        $base_api = Plugin::getApiNiche();
        $uri = trailingslashit($base_api) . ltrim($path, '/');

        $host = parse_url(site_url(), PHP_URL_HOST);
        $uri = add_query_arg('d', $host, $uri);

        $options = [
            'timeout'     => 15,
            'httpversion' => '1.0',
            'blocking'    => true,
            'method'      => strtoupper($method),
            'headers'     => array(
                'Authorization' => 'Bearer ' . LicConfig::getInstance()->option('license_key'),
            ),
        ];

        if ($options['method'] === 'POST')
        {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = json_encode($data);
        }
        elseif ($options['method'] === 'GET')
        {
            if (!empty($data))
                $uri = add_query_arg($data, $uri);
        }

        $response = PluginAdmin::apiRequest($uri, $options);

        if (!$response)
            return false;

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE)
            return false;

        return $result;
    }
}
