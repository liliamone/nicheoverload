<?php

namespace TooMuchNiche\application\components;

use TooMuchNiche\application\admin\SiteConfig;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * CommentPoster class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class CommentPoster
{

    public function createComments(array $comments, $post_id)
    {
        $days = \apply_filters('tmniche_distribute_comments_days', 75);

        foreach ($comments as $comment)
        {
            $post_date = \get_post_time('U', false, $post_id);
            $days_later = strtotime('+' . $days . ' days', $post_date);
            $comment_time = (int) self::biasedRandomTime($post_date, $days_later, 3);

            $parent_id = $this->createComment($comment, $post_id, 0, $comment_time);
            if (!$parent_id)
                continue;

            if (isset($comment['replies']) && is_array($comment['replies']))
            {
                foreach ($comment['replies'] as $reply)
                {
                    $parent_comment_date = \get_comment_time('U', false, true, $parent_id);
                    $comment_time = (int) $parent_comment_date + rand(850, 129600);

                    $this->createComment($reply, $post_id, $parent_id, $comment_time);
                }
            }
        }
    }

    public function createComment(array $comment, $post_id, $parent_id = 0, $time = null)
    {
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_content' => $comment['comment'],
            'comment_author_email' => '',
            'comment_author_url' => '',
            'comment_type' => '',
            'comment_parent' => $parent_id,
            'user_id' => 0,
            'comment_approved' => 1,
            'comment_author' => $comment['username'],
        );

        if (isset($comment['username']) && $comment['username'] == 'admin')
        {
            $user_id = (int) SiteConfig::getInstance()->option('post_author');
            $user_info = \get_userdata($user_id);
            if ($user_info)
            {
                $comment_data['user_id'] = $user_info->ID;
                $comment_data['comment_author'] = $user_info->display_name;
                $comment_data['comment_author_email'] = $user_info->user_email;
                $comment_data['comment_author_url'] = $user_info->user_url;
            }
        }

        if ($time)
        {
            $comment_data['comment_date'] = date('Y-m-d H:i:s', $time);
            $comment_data['comment_date_gmt'] = \get_gmt_from_date(date('Y-m-d H:i:s', $time));
        }

        $comment_id =  \wp_insert_comment($comment_data);

        if (is_wp_error($comment_id))
            return false;

        return $comment_id;
    }

    public static function biasedRandomTime($start_time, $end_time, $bias_exponent = 3)
    {
        $rand_fraction = (mt_rand(0, 100) / 100) ** $bias_exponent; // Cubic bias towards 0
        return $start_time + ($end_time - $start_time) * $rand_fraction;
    }

    public function removeComments($post_id)
    {
        $comments = \get_comments(array('post_id' => $post_id));

        if (!empty($comments))
        {
            foreach ($comments as $comment)
            {
                \wp_delete_comment($comment->comment_ID, true);
            }
        }
    }
}
