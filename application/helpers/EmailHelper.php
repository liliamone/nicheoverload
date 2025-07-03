<?php

namespace TooMuchNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * EmailHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
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
