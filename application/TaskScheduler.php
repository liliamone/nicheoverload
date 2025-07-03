<?php

namespace TooMuchNiche\application;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\components\Scheduler;
use TooMuchNiche\application\components\Task;

/**
 * TaskScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class TaskScheduler extends Scheduler
{
    const CRON_TAG = 'tmniche_task';

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function initAction()
    {
        self::initSchedule();
        parent::initAction();
    }

    public static function run()
    {
        @set_time_limit(270);
        \set_transient('tmn_last_import_time', time(), \HOUR_IN_SECONDS);
        Task::getInstance()->proccessArticles();
    }

    public static function initSchedule()
    {
        \add_filter('cron_schedules', array(__CLASS__, 'addSchedule'));
    }

    public static function addSchedule($schedules)
    {
        $schedules['one_min'] = [
            'interval' => 60,
            'display' => __('Every 1 minute'),
        ];

        return $schedules;
    }
}
