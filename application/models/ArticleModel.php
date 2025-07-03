<?php

namespace TooMuchNiche\application\models;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;

use function TooMuchNiche\prnx;

/**
 * ArticleModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class ArticleModel extends Model
{
    public function tableName()
    {
        return $this->getDb()->prefix . Plugin::getShortSlug() . '_article';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    order_id bigint(20) unsigned NOT NULL DEFAULT 0,
                    task_id bigint(20) unsigned NOT NULL DEFAULT 0,
                    unique_id bigint(20) unsigned NOT NULL,
                    post_id bigint(20) unsigned NOT NULL,
                    recipe_id tinyint(2) unsigned NOT NULL,
                    theme_id tinyint(2) unsigned NOT NULL,
                    create_date datetime NOT NULL,
                    last_build datetime NOT NULL,
                    published bigint(20) unsigned NOT NULL DEFAULT 0,
                    title text,
                    content longtext,
                    ce_data longtext,
                    comments longtext,
                    PRIMARY KEY  (id),
                    KEY order_id (order_id),
                    KEY task_id (task_id),
                    KEY post_id (post_id),
                    KEY published (published),
                    KEY pub_create_date (published,create_date)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        if (isset($item['id']))
            $item['id'] = (int) $item['id'];
        else
        {
            $item['id'] = null;
            $item['create_date'] = \current_time('mysql');
            $item['last_build'] = $item['create_date'];
            if (empty($item['ce_data']))
                $item['ce_data'] = array();
            if (empty($item['comments']))
                $item['comments'] = array();
        }

        if (isset($item['content']) && is_array($item['content']))
            $item['content'] = serialize($item['content']);

        if (isset($item['ce_data']) && is_array($item['ce_data']))
            $item['ce_data'] = serialize($item['ce_data']);

        if (isset($item['comments']) && is_array($item['comments']))
            $item['comments'] = serialize($item['comments']);

        return parent::save($item);
    }

    public function getLastCreateDate()
    {
        $last = $this->find(array('select' => 'create_date', 'order' => 'create_date DESC'));
        if (!$last)
            return null;

        return $last['create_date'];
    }
}
