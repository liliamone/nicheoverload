<?php

namespace IndependentNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * ArrayHelper class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
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
