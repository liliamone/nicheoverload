<?php

namespace IndependentNiche\application\components;

use IndependentNiche\application\admin\KeywordConfig;
use IndependentNiche\application\Plugin;
use IndependentNiche\application\TaskScheduler;
use IndependentNiche\application\admin\NicheConfig;
use IndependentNiche\application\admin\TaskConfig;
use IndependentNiche\application\helpers\EmailHelper;
use IndependentNiche\application\components\NicheInit;
use IndependentNiche\application\models\ArticleModel;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * Task class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class Task
{
    const STATUS_UNKNOWN = 0;
    const STATUS_NEW = 0;
    const STATUS_WORKING = 2;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = -1;
    const STATUS_STOPPING = -5;

    const ARTICLE_LIMIT = 10;

    private $status = null;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function start()
    {
        $this->setStatus(self::STATUS_WORKING);
        \set_transient('tmn_last_import_time', time(), \HOUR_IN_SECONDS);
        TaskScheduler::addScheduleEvent('one_min', time() + 60);
    }

    public function stopSuccess()
    {
        $this->setStatus(self::STATUS_SUCCESS);
        TaskScheduler::clearScheduleEvent();

        if ((int) TaskConfig::getInstance()->option('email_notice') && !isset($_GET['showmeyourmoney']))
            $this->sendAdminEmailSuccess();
    }

    public function stopError()
    {
        $this->setStatus(self::STATUS_ERROR);
        TaskScheduler::clearScheduleEvent();
    }

    public function sendAdminEmailSuccess()
    {
        $to = \get_bloginfo('admin_email');
        $domain = preg_replace('/^https?:\/\//', '', \get_home_url());
        $subject = Plugin::getName() . ': ' . __('task completed successfully', 'independent-niche') . ' - ' . $domain;

        $options = TaskConfig::getInstance()->getTaskOptions();

        $message = '';
        $message .= sprintf(__('Website: %s', 'independent-niche'), $domain);
        $message .= "\r\n" . sprintf(__('Niche: %s', 'independent-niche'), wp_trim_words($options['niche'], 20));
        $message .= "\r\n" . sprintf(__('Language: %s', 'independent-niche'), $options['language']);
        $message .= "\r\n" . __('All articles have been successfully posted.', 'independent-niche');

        $remaining_credits = $this->getCurrentRemainingCredits();
        if ($this->getCurrentRemainingCredits() > 0)
            $message .= "\r\n\r\n" . sprintf(__('Remaining article credits: %d.', 'independent-niche'), $remaining_credits);
        else
            $message .= "\r\n\r\n" . __('Please note that all article generation credits have been used. To purchase a new key, visit:', 'independent-niche') . ' https://www.keywordrush.com/toomuchniche/pricing';

        if ($coupon_and_date = Task::getInstance()->getCouponCodeAndDate())
        {
            list($coupon, $coupon_date_formated) = $coupon_and_date;
            $message .= "\r\n" . sprintf(__('Use the coupon code %s for a 30%% discount.', 'independent-niche'), $coupon);
            $message .= " " . sprintf(__('Valid for 3 days until %s.', 'independent-niche'), $coupon_date_formated);
        }

        EmailHelper::mail($to, $subject, $message);
    }

    public function getStatus()
    {
        if ($this->status === null)
            $this->status = (int) \get_option(Plugin::slug . '_status', self::STATUS_UNKNOWN);

        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        \update_option(Plugin::slug . '_status', $this->status);
    }

    public function isStatusUnknown()
    {
        if ($this->getStatus() == self::STATUS_UNKNOWN)
            return true;
        else
            return false;
    }

    public function isStatusSuccess()
    {
        if ($this->getStatus() == self::STATUS_SUCCESS)
            return true;
        else
            return false;
    }

    public function isStatusWorking()
    {
        if ($this->getStatus() == self::STATUS_WORKING)
            return true;
        else
            return false;
    }

    public function isStatusError()
    {
        if ($this->getStatus() == self::STATUS_ERROR)
            return true;
        else
            return false;
    }

    public function isStatusStopping()
    {
        if ($this->getStatus() == self::STATUS_STOPPING)
            return true;
        else
            return false;
    }

    public function proccessArticles($limit = null)
    {
        $ac = new LocalArticleClient;
        $poster = new ArticlePoster;
        $c_poster = new CommentPoster;

        if ($limit === null)
            $limit = self::ARTICLE_LIMIT;

        if (Plugin::isDevEnvironment())
            $limit = 1;

        for ($i = 0; $i < $limit; $i++)
        {
            if ($i && !Plugin::isDevEnvironment())
                usleep(500000);

            if (!$ac->requestData())
                break;

            if ($stat = $ac->getStat())
                $this->setStat($stat);

            $post_id = 0;
            if ($article = $ac->getArticle())
            {
                $post_id = $poster->processPost($article);
            }

            if ($post_id)
            {
                if ($comments = $ac->getComments())
                {
                    if ($article['post_id'] && $article['post_id'] == $post_id)
                    {
                        $c_poster->removeComments($post_id);
                    }
                    $c_poster->createComments($comments, $post_id);
                }
            }

            if ($stat && $stat['in_queue'] == 0)
            {
                $this->stopSuccess();
                break;
            }
        }
    }

    public function setStat($stat)
    {
        if (isset($stat['in_queue']))
            $stat['in_queue'] = (int) $stat['in_queue'];

        if (isset($stat['remaining_credits']))
            $stat['remaining_credits'] = (int) $stat['remaining_credits'];

        \update_option(Plugin::slug . '_stat', $stat);
    }

    public function getStat()
    {
        return \get_option(Plugin::slug . '_stat', array());
    }

    public function getCurrentRemainingCredits()
    {
        $stat = $this->getStat();
        if (isset($stat['remaining_credits']))
            return $stat['remaining_credits'];
        else
            return null;
    }

    public function restart($del_niche = false, $del_lic = false)
    {
        $remaining_credits = NicheInit::getInstance()->getRemainingCredits();
        $step = ($remaining_credits > 0) ? 2 : 1;

        // Plus de licence à supprimer
        if ($del_lic)
        {
            $step = 1;
        }

        if ($del_niche)
            \delete_option(NicheConfig::getInstance()->option_name());

        \delete_option(KeywordConfig::getInstance()->option_name());

        Wizard::getInstance()->setCurrentStep($step);
    }

    public function getCouponCodeAndDate()
    {
        // Plus de système de coupon avec licence
        return false;

        $coupon_date_formated = '';
        $last = ArticleModel::model()->getLastCreateDate();

        if (!$last || NicheInit::getInstance()->getTotalArticles() <= 30)
            return false;

        $current_date = new \DateTime();
        $coupon_date = new \DateTime(date('Y-m-d 23:59:59.000000', strtotime($last)));
        $coupon_date->modify("+3 days");

        if (!$date_format = get_option('date_format'))
            $date_format = 'Y-m-d';

        $coupon_date_formated = $coupon_date->format($date_format);

        if ($current_date < $coupon_date)
            $coupon = 'TMN' . $coupon_part;
        else
            return false;

        return array($coupon, $coupon_date_formated);
    }
}
