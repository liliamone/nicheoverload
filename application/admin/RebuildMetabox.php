<?php

namespace TooMuchNiche\application\admin;

use TooMuchNiche\application\components\ArticlePoster;
use TooMuchNiche\application\models\ArticleModel;
use TooMuchNiche\application\components\Theme;
use TooMuchNiche\application\components\Recipe;
use TooMuchNiche\application\components\NicheInit;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * RebuildMetabox class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class RebuildMetabox
{
    public function __construct()
    {
        \add_action('add_meta_boxes', array($this, 'addMetabox'));
        \add_action('wp_ajax_tmniche_rebuild_article', array($this, 'ajaxRebuildArticle'));
    }

    public function addMetabox($post_type)
    {
        if ($post_type !== 'post')
            return;

        global $post;

        if (!ArticlePoster::getArticleMeta($post->ID) && !\get_post_meta($post->ID, '_tmniche_unique_id', 0))
            return;

        \add_meta_box('tmniche_article_rebuild_metabox', 'TMN', array($this, 'renderMetabox'), $post_type, 'side', 'default');
    }

    public static function renderMetabox($post)
    {
        if (!$article = ArticleModel::model()->find(array('select' => 'id,recipe_id,theme_id,ce_data', 'where' => 'post_id = ' . $post->ID)))
            return;

        $meta = ArticlePoster::getArticleMeta($post->ID);

        if (!$meta)
            $meta = array();

        $is_rebuild_allowed = isset($meta['created_with'])
            && version_compare($meta['created_with'], PluginAdmin::MIN_TMN_VERSION_FOR_REBUILD, '>=');

        if (NicheConfig::getInstance()->option('main_module') && NicheInit::getInstance()->getRemainingCredits() > 0 && NicheConfig::isCeIntegration())
            $is_regen_allowed = true;
        else
            $is_regen_allowed = false;

        echo '<div id="tmniche_metabox" class="components-flex components-h-stack components-v-stack">';

        if (!empty($article['recipe_id']))
        {
            echo '<div class="components-panel__row">';
            echo '<em>' . esc_html(Recipe::getRecipeName($article['recipe_id'])) . '</em>';
            echo '</div>';
        }

        if ($is_rebuild_allowed):

            echo '<div class="components-panel__row">';
            echo '<select id="tmniche_theme_id">';

            foreach (Theme::getThemesList() as $theme_id => $theme_name)
            {
                $selected = (isset($article['theme_id']) && $theme_id == $article['theme_id']) ? ' selected' : '';
                echo '<option value="' . esc_attr($theme_id) . '"' . esc_attr($selected) . '>' . esc_html($theme_name) . '</option>';
            }

            echo '</select>';
            echo '</div>';

            if (!empty($article['ce_data']) && ($ce_data = @unserialize($article['ce_data'])) !== false)
            {
                if (is_array($ce_data))
                {
                    echo '<div class="components-panel__row">';
                    echo '<label>';
                    echo '<input id="tmniche_restore_products" type="checkbox">';
                    echo esc_html(__('Restore products', 'too-much-niche')) . ' (' . esc_html(self::countProducts($ce_data)) . ')';
                    echo '</label>';
                    echo '</div>';
                }
            }

        endif; // $is_rebuild_allowed

        // Response Container
        echo '<div id="tmniche_rebuild_metabox_response"></div>';
        echo '</div>';
?>
        <?php if ($is_rebuild_allowed): ?>
            <div style="padding-top: 10px;">
                <?php \wp_nonce_field('tmniche_rebuild_article', 'tmn_nonce'); ?>
                <input type="submit" id="tmniche_rebuild_article" class="components-button is-secondary" value="<?php echo \esc_attr(__('Restore from Local Cache', 'too-much-niche')); ?>">
                <div class="clear"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $('#tmniche_rebuild_article').click(function(e) {
                        e.preventDefault();
                        var this_btn = $(this);
                        this_btn.attr('disabled', true);

                        var nonce = this_btn.parent().find("#tmn_nonce").val();
                        var theme_id = $("#tmniche_metabox").find("#tmniche_theme_id").val();
                        var restore_products = $("#tmniche_metabox").find("#tmniche_restore_products").is(':checked');
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: 'tmniche_rebuild_article',
                                tmn_nonce: nonce,
                                post_id: <?php echo esc_attr($post->ID); ?>,
                                theme_id: theme_id,
                                restore_products: restore_products,
                            },
                            success: function(data) {

                                $("#tmniche_rebuild_metabox_response").html(data);
                                location.reload();
                                this_btn.attr('disabled', false);
                            },
                            error: function(errorThrown) {
                                location.reload();
                            }
                        });
                        return false;
                    });
                });
            </script>
        <?php endif; ?>

        <?php if ($is_regen_allowed): ?>
            <br>
            <?php
            $regen_url = get_admin_url(get_current_blog_id(), 'admin.php?page=too-much-niche&action=regenerate&post_id=' . $post->ID);
            $regen_url = wp_nonce_url($regen_url, 'tmn_regenerate_article');
            ?>
            <a href="<?php echo esc_url($regen_url); ?>" id="tmniche_regenerate_article" class="components-button is-secondary is-destructive" style="padding-top: 0px; padding-bottom: 0px;"><?php echo \esc_attr(__('Regenerate Content (Beta)', 'too-much-niche')); ?></a>
            <div class="clear"></div>
        <?php endif; //$is_regen_allowed
        ?>
<?php

    }

    public function ajaxRebuildArticle()
    {
        if (!isset($_POST['tmn_nonce']) || !\wp_verify_nonce($_POST['tmn_nonce'], 'tmniche_rebuild_article'))
            \wp_die('Invalid nonce');

        if (!\current_user_can('edit_posts'))
            \wp_die('You don\'t have access to this page.');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (!$post_id)
            \wp_die('Invalid post ID');

        $theme_id = isset($_POST['theme_id']) ? (int) $_POST['theme_id'] : 0;
        $restore_products = isset($_POST['restore_products']) ? (bool) $_POST['restore_products'] : false;

        $ap = new ArticlePoster;
        if ($ap->rebuildPost($post_id, $theme_id, $restore_products))
            echo '<span style="color: green;">' . esc_html(__('Done!', 'too-much-niche')) . '</span>';
        else
            echo '<span style="color: red;">' . esc_html(__('Error', 'too-much-niche')) . '</span>';

        \wp_die();
    }

    private static function countProducts($ce_data)
    {
        if (!$ce_data || !is_array($ce_data))
            return 0;

        $total = 0;
        foreach ($ce_data as $data)
        {
            $total += count($data);
        }
        return $total;
    }
}
