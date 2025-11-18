<?php

namespace IndependentNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * EmailHelper class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class EmailHelper
{

    public static function mail($to, $subject, $message, $headers = array(), $attachments = array())
    {
        if (!is_array($to))
            $to = array($to);

        foreach ($to as $email)
        {
            $res = \wp_mail($email, $subject, $message, $headers, $attachments);
        }

        return $res;
    }
}
