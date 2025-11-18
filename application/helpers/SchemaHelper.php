<?php

namespace IndependentNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * SchemaHelper class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
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
