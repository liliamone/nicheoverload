<?php

namespace TooMuchNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * SchemaHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class SchemaHelper
{
    public static function getAuthorArray($user_id)
    {
        if (!$post_author = \get_userdata($user_id))
            return false;

        $author = array(
            '@type'    => 'Person',
            'name'    => $post_author->display_name,
            'url'    => \esc_url(\get_author_posts_url($user_id))
        );

        if ($description = \get_the_author_meta('description', $user_id))
            $author['description'] = strip_tags($description);

        return $author;
    }
}
