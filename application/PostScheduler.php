<?php

namespace TooMuchNiche\application;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\admin\SiteConfig;
use TooMuchNiche\application\components\Scheduler;
use TooMuchNiche\application\models\ArticleModel;

use function TooMuchNiche\prnx;

/**
 * PostScheduler class file (deprecated)
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class PostScheduler extends Scheduler
{
    const CRON_TAG = 'tmniche_post_scheduler';

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function run()
    {
        if (SiteConfig::getInstance()->option('post_status') != 'schedule')
        {
            PostScheduler::clearScheduleEvent();
            return;
        }

        list($limit, $frequency) = self::getPostFrequency();

        if (!$limit || !$frequency)
            return;

        if ($cache_limit = \get_transient('tmniche_post_sheduler_last_limit'))
            $limit = $cache_limit;
        else
            \set_transient('tmniche_post_sheduler_last_limit', $limit, $frequency);

        $limit = (int) $limit;

        if (self::publishedCount($frequency) >= $limit)
            return;

        $interval = round($frequency / $limit);

        $published = self::publishedCount($interval);
        if ($published >= 1)
            return;

        $per_ran = ceil($limit / 24);

        self::publishNext($per_ran);
    }

    public static function getPostFrequency($post_frequency = null)
    {
        if (!$post_frequency)
            $post_frequency = SiteConfig::getInstance()->option('post_frequency');

        if (!$post_frequency)
            return array(0, 0);

        $parts = explode(':', $post_frequency);

        $limit = $parts[0];
        $limit_parts = explode('-', $limit);
        if (count($limit_parts) == 2)
            $limit = rand($limit_parts[0], $limit_parts[1]);

        $limit = (int) abs($limit);
        if ($limit == 0)
            return 0;

        if ($parts[1] == 'week')
            $frequency = 604800;
        else
            $frequency = 86400;

        return array($limit, $frequency);
    }

    public static function getPostInterval()
    {
    }

    public static function publishNext($limit = 1)
    {
        $article_table = ArticleModel::model()->tableName();
        $wpdb = ArticleModel::model()->getDb();
        $limit = (int) $limit;

        $sql = "SELECT article.post_id
            FROM {$article_table} article
            INNER JOIN  {$wpdb->posts} post
            ON article.post_id = post.ID
            WHERE post.post_status = 'draft'
            AND article.published = 0
            ORDER BY article.create_date ASC
            LIMIT {$limit}";

        $post_ids = $wpdb->get_col($sql);

        if (!$post_ids)
        {
            self::finishSuccessfully();
            return;
        }

        foreach ($post_ids as $post_id)
        {
            self::publishPost($post_id);
        }
    }

    public static function publishPost($post_id)
    {
        $author_id = (int) SiteConfig::getInstance()->option('post_author');
        \wp_set_current_user($author_id);

        \wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));

        //\clean_post_cache($post_id);
        //\wp_publish_post($post_id);

        $wpdb = ArticleModel::model()->getDb();
        $article_table = ArticleModel::model()->tableName();
        $sql = "UPDATE {$article_table} SET published = %d WHERE post_id = %d";
        $query = $wpdb->prepare($sql, array(time(), $post_id));
        $wpdb->query($query);
    }

    public static function finishSuccessfully()
    {
        $article_table = ArticleModel::model()->tableName();
        $wpdb = ArticleModel::model()->getDb();
        $time = time();

        $sql = "UPDATE {$article_table} SET published = {$time} WHERE published = 0";
        $wpdb->query($sql);

        PostScheduler::clearScheduleEvent();
    }

    public static function maybeAddScheduleEvent()
    {
        if (!self::needAddSchedule())
            return;

        PostScheduler::addScheduleEvent();
    }

    public static function needAddSchedule()
    {
        if (SiteConfig::getInstance()->option('post_status') != 'schedule')
            return false;

        if (!ArticleModel::model()->count('published = 0'))
            return false;

        return true;
    }

    public static function publishedCount($interval)
    {
        return (int) ArticleModel::model()->count(array('published >= %d', array(time() - $interval)));
    }
}
