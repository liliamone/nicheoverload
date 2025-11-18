<?php

namespace IndependentNiche\application\components;

use IndependentNiche\application\admin\SiteConfig;
use IndependentNiche\application\models\ArticleModel;
use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\Theme;
use IndependentNiche\application\helpers\ImageHelper;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * ArticlePoster class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class ArticlePoster
{
    const ARTICLE_META_FIELD = '_tmniche_meta';

    public static function initAction()
    {
        \add_action('delete_post', array(__CLASS__, 'beforePostDeleted'), 10);
    }

    public function decorateContent($content, $theme_id)
    {
        if (!Theme::isThemeIdExists($theme_id))
            $theme_id = Theme::THEME_HTML;

        $theme = Theme::factory($theme_id);
        return $theme->decorate($content);
    }

    public function rebuildPost($post_id, $theme_id = null, $restore_products = false)
    {
        if (!$article = ArticleModel::model()->find(array('where' => array('post_id = %d', array($post_id)))))
            return false;

        $article['content'] = unserialize($article['content']);
        if ($theme_id === null)
            $theme_id = $article['theme_id'];

        $post = array(
            'ID' => $post_id,
            'post_content' => $this->decorateContent($article['content'], $theme_id),
        );

        \wp_update_post($post);

        $save['id'] = $article['id'];
        $save['last_build'] = \current_time('mysql');
        $save['theme_id'] = $theme_id;
        ArticleModel::model()->save($save);

        if ($restore_products && $ce_data = unserialize($article['ce_data']))
            $this->saveCeData($post_id, $ce_data);

        return true;
    }

    public function processPost(array $article)
    {
        return $this->createPost($article);
    }

    public function createPost(array $article)
    {
        $post_id = (int) $article['post_id'];

        if (!$orig_post = get_post($post_id))
            $article['post_id'] = $post_id = 0;

        $theme_id = (int) SiteConfig::getInstance()->option('theming');
        $author_id = (int) SiteConfig::getInstance()->option('post_author');
        $post_status = SiteConfig::getInstance()->option('post_status');

        $post = array(
            'ID' => $post_id,
            'post_title' => $article['title'],
            'post_content' => $this->decorateContent($article['content'], $theme_id),
            'post_author' => $author_id,
        );

        if (!$post_id)
        {
            $post['post_category'] = array((int) SiteConfig::getInstance()->option('category' . $article['recipe_id']));

            if (!empty($article['tags']))
                $post['tags_input'] = $article['tags'];

            if (!empty($article['slug']))
                $post['post_name'] = $article['slug'];

            if ($post_status == 'schedule')
            {
                $post['post_status'] = 'future';
                $published = self::getNextPublishDate($article['order_id']);

                if (rand(0, 1))
                    $published += rand(0, 7200);
                else
                    $published -= rand(0, 7200);
            }
            else
            {
                $published = time();
                $post['post_status'] = $post_status;

                if (rand(0, 2) == 0)
                    $published = $published + rand(0, 900);
                else
                    $published = $published - rand(60, 10000);
            }

            $timezone = \get_option('timezone_string');
            if (!$timezone)
                $timezone = 'UTC';
            $date = new \DateTime(date('Y-m-d H:i:s', $published), new \DateTimeZone($timezone));

            $post['post_date'] = $date->format('Y-m-d H:i:s');
            $post['post_date_gmt'] = gmdate('Y-m-d H:i:s', $date->getTimestamp());

            if (!empty($article['image']))
                $image = $article['image'];
            else
                $image = '';
        }
        else
        {
            $image = '';
            $published = time();
            $post['post_status'] = $orig_post->post_status;
        }

        if (!empty($article['comments']))
            $comments = $article['comments'];
        else
            $comments = array();

        if (!empty($article['ce_data']))
            $ce_data = $article['ce_data'];
        else
            $ce_data = array();

        \wp_set_current_user($author_id);

        //\remove_filter('content_save_pre', 'wp_filter_post_kses');
        //\remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        $post_id = \wp_insert_post($post);

        //\add_filter('content_save_pre', 'wp_filter_post_kses');
        //\add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        if (!$post_id)
        {
            Plugin::logger()->error('An unexpected error has occurred while creating a post.');
            return false;
        }

        $this->saveImage($post_id, $image, $article['title'], $article['slug']);
        $this->saveCeData($post_id, $ce_data);
        $this->saveArticleData($post_id, $article, $theme_id, $published);
        $this->saveArticleMeta($post_id, $article, $theme_id);

        if (class_exists('\ContentEgg\application\components\FeaturedImage'))
            \ContentEgg\application\components\FeaturedImage::doAction($post_id);

        if (class_exists('\ContentEgg\application\components\ContentManager'))
            $items = \ContentEgg\application\components\ContentManager::getViewProductData($post_id);
        else
            $items = array();

        if ($article['post_id'])
            $m = __('has been updated', 'independent-niche');
        elseif ($post_status == 'schedule')
            $m = __('has been sheduled', 'independent-niche');
        elseif ($post_status == 'draft')
            $m = __('has been posted with draft status', 'independent-niche');
        elseif ($post_status == 'pending')
            $m = __('has been posted with pending status', 'independent-niche');
        else
            $m = __('has been posted', 'independent-niche');

        $message = sprintf(__('The <em>%s</em> <a target="_blank" href="%s">%s</a>', 'independent-niche'), Recipe::getRecipeName($article['recipe_id']), \get_permalink($post_id), $article['title']);
        $message .= ' ' . $m . '.';
        $message .= '<div class="text-muted">' . sprintf(__('Words: %d', 'independent-niche'), $article['word_count']);

        if (count($items))
            $message .= sprintf(__('&nbsp;&nbsp;&nbsp;Products: %d', 'independent-niche'), count($items));

        if (count($comments))
            $message .= sprintf(__('&nbsp;&nbsp;&nbsp;Comments: %d', 'independent-niche'), self::countAllComments($comments));

        $message .= '</div>';

        Plugin::logger()->info($message);
        Plugin::logger()->flush();

        return $post_id;
    }

    public function saveArticleData($post_id, array $article, $theme_id, $published)
    {
        ArticleModel::model()->deleteAll(array('post_id = %d', array($post_id)));

        $save = array();
        $save['post_id'] = $post_id;
        $save['recipe_id'] = (int) $article['recipe_id'];
        $save['unique_id'] = (int) $article['unique_id'];
        $save['task_id'] = (int) $article['task_id'];
        $save['order_id'] = (int) $article['order_id'];
        $save['title'] = $article['title'];
        $save['content'] = $article['content'];
        $save['published'] = $published;

        if (!empty($article['ce_data']))
            $save['ce_data'] = $article['ce_data'];

        $save['theme_id'] = $theme_id;

        ArticleModel::model()->save($save);
    }

    public function saveImage($post_id, $image_uri, $title, $slug)
    {
        if (!$image_uri)
            return;

        // external featured image? pixabay does not allow hotlinking
        if (!strstr($image_uri, 'pixabay.com'))
        {
            if (\is_plugin_active('content-egg/content-egg.php') && class_exists('\ContentEgg\application\admin\GeneralConfig'))
            {
                $external_featured_images = \ContentEgg\application\admin\GeneralConfig::getInstance()->option('external_featured_images');
                if (in_array($external_featured_images, array('enabled_internal_priority', 'enabled_external_priority')))
                {
                    \ContentEgg\application\components\ExternalFeaturedImage::updateExternalMeta($image_uri, $post_id);
                    return;
                }
            }
        }

        ImageHelper::setFeaturedImage($post_id, $image_uri, $title, $slug);
    }

    public function saveCeData($post_id, array $ce_data)
    {
        if (!$ce_data)
            return;

        if (!class_exists('\ContentEgg\application\components\ContentManager'))
            return;

        foreach ($ce_data as $module_id => $data)
        {
            if (!\ContentEgg\application\components\ModuleManager::getInstance()->moduleExists($module_id))
                continue;

            \ContentEgg\application\components\ContentManager::saveData($data, $module_id, $post_id);
        }
    }

    public static function beforePostDeleted($post_id)
    {
        ArticleModel::model()->deleteAll(array('post_id = %d', array($post_id)));
    }

    public static function getPublishInterval()
    {
        $post_frequency = SiteConfig::getInstance()->option('post_frequency');

        if (!$post_frequency)
            return 0;

        $parts = explode(':', $post_frequency);
        $limit = $parts[0];
        $limit_parts = explode('-', $limit);
        if (count($limit_parts) == 2)
            $limit = rand($limit_parts[0], $limit_parts[1]);

        $limit = (int) abs($limit);
        if (!$limit)
            return 0;

        if ($parts[1] == 'week')
            $frequency = 604800;
        else
            $frequency = 86400;

        return round($frequency / $limit);
    }

    public static function getNextPublishDate($order_id)
    {
        $scheduled_start = SiteConfig::getInstance()->option('scheduled_start');

        if ($scheduled_start == 'after_all')
            $order_id = null;

        $last = self::getLastScheduledDate($order_id);

        if (!$last)
            return time();

        $interval = self::getPublishInterval();
        $next = $last + $interval;

        if (!$next)
            $next = time();

        return $next;
    }

    public static function getLastScheduledDate($order_id = null)
    {
        $article_table = ArticleModel::model()->tableName();
        $db = ArticleModel::model()->getDb();
        $sql = '
            SELECT article.published
            FROM ' . $article_table . ' article
            INNER JOIN ' . $db->posts . ' post
            ON post.ID = article.post_id
            WHERE post.post_status != "trash"';

        if ($order_id)
            $sql .=  $db->prepare(' AND article.order_id = %d', $order_id);

        $sql .= ' ORDER BY article.published DESC LIMIT 1';

        if (!$r = $db->get_row($sql, \ARRAY_A))
            return 0;

        return $r['published'];
    }

    public static function countAllComments(array $comments)
    {
        $totalCount = 0;

        foreach ($comments as $comment)
        {
            $totalCount++;

            if (isset($comment['replies']) && is_array($comment['replies']))
            {
                $totalCount += count($comment['replies']);
            }
        }

        return $totalCount;
    }

    public static function saveArticleMeta($post_id, $article, $theme_id)
    {
        $meta = [
            'created_at' => time(),
            'created_with' => Plugin::version(),
            'recipe_id' => $article['recipe_id'],
            'theme_id' => $theme_id,
            'unique_id' => $article['unique_id'],
        ];

        \update_post_meta($post_id, self::ARTICLE_META_FIELD, $meta);
    }

    public static function getArticleMeta($post_id)
    {
        return \get_post_meta($post_id, self::ARTICLE_META_FIELD, true);
    }
}
