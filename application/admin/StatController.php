<?php

namespace IndependentNiche\application\admin;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\Wizard;
use IndependentNiche\application\components\Task;
use IndependentNiche\application\models\LogModel;
use IndependentNiche\application\TaskScheduler;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * StatController class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class StatController
{
    const slug = 'independent-niche-articles';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));

        $this->doActions();
    }

    private function doActions()
    {
        if (!empty($_GET['action']) && $_GET['action'] == 'restart')
        {
            if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'ind_restart'))
                die('Invalid nonce');

            if (isset($_GET['restartniche']) && (int) $_GET['restartniche'])
                $restartniche = true;
            else
                $restartniche = false;

            if (isset($_GET['restartlic']) && (int) $_GET['restartlic'])
                $restartlic = true;
            else
                $restartlic = false;

            Task::getInstance()->restart($restartniche, $restartlic);

            \wp_safe_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug()));
        }

        if (!empty($_GET['action']) && $_GET['action'] == 'post_now')
        {
            if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'ind_post_manually'))
                die('Invalid nonce');

            Task::getInstance()->proccessArticles();
        }

        if (!empty($_GET['action']) && $_GET['action'] == 'stop_task')
        {
            if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'ind_stop_task'))
                die('Invalid nonce');

            // Plus d'appel API - arrêter la tâche directement
            Task::getInstance()->setStatus(Task::STATUS_STOPPING);

            \wp_safe_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug() . '-articles'));
        }

        if (!empty($_GET['action']) && $_GET['action'] == 'reset_log')
        {
            if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'ind_reset_log'))
                die('Invalid nonce');

            LogModel::model()->cleanAllLogs();

            \wp_safe_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug() . '-articles'));
        }

        if (isset($_GET['showmeyourmoney']))
        {
            Task::getInstance()->proccessArticles();
        }
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Posted Articles', 'independent-niche') . ' &lsaquo; ' . Plugin::getName(), __('Posted Articles', 'independent-niche'), 'publish_posts', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        $stat = Task::getInstance()->getStat();

        $table = new LogTable(LogModel::model());
        $table->prepare_items();

        $coupon = '';
        $coupon_date_formated = '';
        if ($coupon_and_date = Task::getInstance()->getCouponCodeAndDate())
            list($coupon, $coupon_date_formated) = $coupon_and_date;

        if (defined('\DISABLE_WP_CRON') && \DISABLE_WP_CRON)
            $is_cron_enabled = false;
        else
            $is_cron_enabled = true;

        $task = Task::getInstance();

        $is_import_error = false;

        if (in_array($task->getStatus(), array(Task::STATUS_WORKING, Task::STATUS_NEW, Task::STATUS_STOPPING)))
        {
            $last_import = \get_transient('tmn_last_import_time');

            if (!$last_import || time() - $last_import > 2 * 60)
                TaskScheduler::addScheduleEvent('one_min');

            if (!$last_import || time() - $last_import > 5 * 60)
                $is_import_error = true;
            else
                $is_import_error = false;
        }

        $remaining_credits = Task::getInstance()->getCurrentRemainingCredits();

        PluginAdmin::getInstance()->render('stat', array('task' => $task, 'table' => $table, 'options' => TaskConfig::getInstance()->getTaskOptions(), 'stat' => $stat, 'coupon' => $coupon, 'is_cron_enabled' => $is_cron_enabled, 'is_import_error' => $is_import_error, 'coupon_date_formated' => $coupon_date_formated, 'remaining_credits' => $remaining_credits));
    }
}
