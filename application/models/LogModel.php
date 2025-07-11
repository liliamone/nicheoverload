<?php

namespace TooMuchNiche\application\models;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;

/**
 * LogModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class LogModel extends Model
{
    const MAX_LOGS_AGE_DAYS = 0;

    public function tableName()
    {
        return $this->getDb()->prefix . Plugin::getShortSlug() . '_log';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    log_level tinyint(1) NOT NULL,
                    log_time double NOT NULL,
                    message text,
                    PRIMARY KEY  (id),
                    KEY log_level (log_level)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'log_level' => __('Level', 'external-importer'),
            'log_time' => __('Time', 'external-importer'),
            'message' => __('Message', 'external-importer'),
        );
    }

    public function cleanOldLogs($optimize = true)
    {
        if (!LogModel::MAX_LOGS_AGE_DAYS)
            return;

        $this->deleteAll(time() . ' - log_time >= ' . LogModel::MAX_LOGS_AGE_DAYS * 24 * 3600);
        if ($optimize)
            $this->optimizeTable();
    }

    public function cleanAllLogs($optimize = true)
    {
        $this->deleteAll('1=1');
        if ($optimize)
            $this->optimizeTable();
    }
}
