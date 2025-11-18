<?php

namespace IndependentNiche\application;

use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * CommentScheduler class file (deprecated)
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class CommentScheduler
{
    public static function initAction()
    {
        \add_action('comments_clauses', array(__CLASS__, 'excludeScheduledComments'), 10, 2);
        \add_filter('get_comments_number', array(__CLASS__, 'filterCommentCountWithoutScheduled'), 10, 2);
    }

    public static function excludeScheduledComments($clauses, $wp_comment_query)
    {
        global $wpdb;

        if (\is_admin() || \current_user_can('edit_posts'))
            return $clauses;

        // Only include comments that are not scheduled for the future
        $clauses['where'] .= $wpdb->prepare(" AND comment_date <= %s", \current_time('mysql'));

        return $clauses;
    }

    public static function filterCommentCountWithoutScheduled($count, $post_id)
    {
        global $wpdb;

        if (\is_admin() || \current_user_can('edit_posts'))
            return $count;

        // Get the actual number of comments that are not scheduled
        $actual_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
        FROM $wpdb->comments
        WHERE comment_post_ID = %d
        AND comment_approved = '1'
        AND comment_date <= %s",
            $post_id,
            \current_time('mysql')
        ));

        return $actual_count;
    }
}
