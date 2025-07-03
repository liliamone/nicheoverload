<?php

namespace TooMuchNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * ArrayHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class ArrayHelper
{
    public static function addSlashesForQuotesRecursive($data)
    {
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                $data[$key] = self::addSlashesForQuotesRecursive($value);
            }
        }
        elseif (is_string($data))
        {
            $data = str_replace('"', '\\"', $data);
        }
        return $data;
    }
}
