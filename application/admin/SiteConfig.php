<?php

namespace TooMuchNiche\application\admin;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;
use TooMuchNiche\application\components\WizardBootConfig;
use TooMuchNiche\application\components\Theme;
use TooMuchNiche\application\components\NicheInit;
use TooMuchNiche\application\helpers\WpHelper;
use TooMuchNiche\application\models\ArticleModel;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

/**
 * SiteConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class SiteConfig extends WizardBootConfig
{

    public function getTitle()
    {
        return __('Site Settings', 'too-much-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_site';
    }

    protected function options()
    {
        $themes = array();
        foreach (Theme::getThemesList() as $id => $name)
        {
            if ($id == Theme::THEME_GREENSHIFT)
                $name .= ' (' . __('recommended', 'too-much-niche') . ')';
            $themes[$id . '.'] = $name;
        }

        $is_greenshift_installed = WpHelper::isPluginInstalled('greenshift-animation-and-page-builder-blocks');

        $options = array(
            'theming' => array(
                'title' => __('Theming', 'too-much-niche'),
                'description' => !$is_greenshift_installed ? __('The Greenshift plugin will be automatically installed and activated if it is not already installed.', 'too-much-niche') : '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => $themes,
                'default' => '2.',
                'validator' => array(
                    array(
                        'call' => array($this, 'checkGreenshiftInstalled'),
                        'message' => sprintf(__('Please install and activate the <a target="_blank" href="%s">Greenshift - animation and page builder blocks</a> plugin.', 'too-much-niche'), 'https://wordpress.org/plugins/greenshift-animation-and-page-builder-blocks/')
                    ),
                ),
            ),
            'post_status' => array(
                'title' => __('Post status', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'publish' => __('Publish immediately', 'too-much-niche'),
                    'pending' => __('Pending', 'too-much-niche'),
                    'draft' => __('Draft', 'too-much-niche'),
                    'schedule' => __('Scheduled', 'too-much-niche'),
                ),
                'default' => self::getDefaultPostStatus(),
            ),
            'post_frequency' => array(
                'title' => __('Post frequency', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(

                    '1-2:day' => __('1-2 per day', 'too-much-niche'),
                    '1-3:day' => __('1-3 per day', 'too-much-niche'),
                    '3-5:day' => __('3-5 per day', 'too-much-niche'),
                    '5-10:day' => __('5-10 per day', 'too-much-niche'),

                    '1-2:week' => __('1-2 per week', 'too-much-niche'),
                    '1-3:week' => __('1-3 per week', 'too-much-niche'),
                    '2-5:week' => __('2-5 per week', 'too-much-niche'),

                    '1:day' => __('1 per day', 'too-much-niche'),
                    '2:day' => __('2 per day', 'too-much-niche'),
                    '3:day' => __('3 per day', 'too-much-niche'),
                    '4:day' => __('4 per day', 'too-much-niche'),
                    '5:day' => __('5 per day', 'too-much-niche'),
                    '6:day' => __('6 per day', 'too-much-niche'),
                    '7:day' => __('7 per day', 'too-much-niche'),
                    '8:day' => __('8 per day', 'too-much-niche'),
                    '9:day' => __('9 per day', 'too-much-niche'),
                    '10:day' => __('10 per day', 'too-much-niche'),
                    '15:day' => __('15 per day', 'too-much-niche'),
                    '20:day' => __('20 per day', 'too-much-niche'),
                ),
                'default' => self::getDefaultPostFrequency(),
            ),
            'scheduled_start' => array(
                'title' => __('Scheduled start', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getScheduledStartOptions(),
                'default' => 'now',
            ),
            'generate_comments' => array(
                'title' => __('Generate user comments', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'too-much-niche'),
                    'disabled' => __('Disabled', 'too-much-niche'),
                ),
                'default' => 'enabled',
            ),
            /*
            'generate_slug' => array(
                'title' => __('Generate post slug', 'too-much-niche'),
                'callback' => array($this, 'render_hidden'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'too-much-niche'),
                    'disabled' => __('Disabled', 'too-much-niche'),
                ),
                'default' => 'enabled',
            ),
            */
            'generate_tags' => array(
                'title' => __('Generate tags', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'too-much-niche'),
                    'disabled' => __('Disabled', 'too-much-niche'),
                ),
                'default' => 'disabled',
            ),
            'post_author' => array(
                'title' => __('Post author', 'too-much-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getUsers(),
                'default' => \get_current_user_id() . '.',
            ),
            'separator' => array(
                'description' => '<div class="mb-2 mt-4">' . __('Choose Article Categories', 'too-much-niche') . '</div>',
                'callback' => array($this, 'render_text'),
            ),
            'render_js' => array(
                'callback' => array($this, 'render_js')
            ),
        );

        $recipes = NicheInit::getInstance()->getInitRecipes(NicheConfig::isCeIntegration());
        foreach ($recipes as $recipe_id => $recipe_name)
        {
            $title = sprintf(__('Category for %s', 'too-much-niche'), $recipe_name);
            $option_id = 'category' . $recipe_id;
            $options[$option_id] = array(
                'title' => $title,
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getCategories(),
                'default' => \get_option('default_category') . '.',
            );
        }

        return $options;
    }

    public function checkGreenshiftInstalled($value)
    {
        if ($value != Theme::THEME_GREENSHIFT)
            return true;

        // trying to install and activate the Greenshift plugin
        if (!WpHelper::isPluginInstalled('greenshift-animation-and-page-builder-blocks'))
            WpHelper::installAndActivatePlugin('greenshift-animation-and-page-builder-blocks');

        $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
        foreach ($active_plugins as $active_plugin)
        {
            if (stristr($active_plugin, 'greenshift'))
                return true;
        }

        return false;
    }

    protected static function getUsers()
    {
        $users = \get_users(array('fields' => array('ID', 'user_login', 'user_nicename'), 'capability' => array('unfiltered_html')));
        $res = array();
        foreach ($users as $user)
        {
            $res[$user->ID . '.'] = $user->user_login . ' (' . $user->user_nicename . ')';
        }
        return $res;
    }

    protected static function getCategories()
    {
        $categs = get_terms(array('taxonomy' => array('category'), 'hide_empty' => false));
        $res = array();
        foreach ($categs as $c)
        {
            $res[$c->term_id . '.'] = $c->name;
        }
        return $res;
    }

    public static function getScheduledStartOptions()
    {
        $options = array(
            'now' => __('Now', 'too-much-niche'),
        );

        if (ArticleModel::model()->count('order_id > 0'))
            $options['after_all'] =  __('After all other tasks', 'too-much-niche');

        return $options;
    }

    public static function getDefaultPostFrequency()
    {
        $total = KeywordConfig::getInstance()->getCurrentArticleTotal();

        if ($total <= 15)
            return '1-2:day';
        elseif ($total <= 35)
            return '1-3:day';
        elseif ($total <= 90)
            return '3-5:day';
        elseif ($total <= 300)
            return '5-10:day';
        else
            return '10:day';
    }

    public static function getDefaultPostStatus()
    {
        $total = KeywordConfig::getInstance()->getCurrentArticleTotal();

        if ($total > 30)
            return 'schedule';
        else
            return 'publish';
    }

    public function render_js()
    {
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const postStatusSelect = document.getElementById('label-post_status');
                const postFrequencySection = document.getElementById('post_frequency_section');
                const scheduledStartSection = document.getElementById('scheduled_start_section');

                function toggleSections() {
                    if (postStatusSelect.value === 'schedule') {
                        postFrequencySection.style.display = '';
                        scheduledStartSection.style.display = '';
                    } else {
                        postFrequencySection.style.display = 'none';
                        scheduledStartSection.style.display = 'none';
                    }
                }

                toggleSections();

                postStatusSelect.addEventListener('change', toggleSections);
            });
        </script>

<?php
    }
}
