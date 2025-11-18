<?php

namespace IndependentNiche\application\admin;

use IndependentNiche\application\components\ArticlePoster;
use IndependentNiche\application\components\NicheInit;
use IndependentNiche\application\components\Recipe;
use IndependentNiche\application\components\Task;
use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\Wizard;
use IndependentNiche\application\models\ArticleModel;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * WizardController class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class WizardController
{
    public function __construct()
    {
        // new task started
        if (!Wizard::getInstance()->getCurrentStep())
        {
            NicheInit::getInstance()->initializeNicheFromApi();
            Task::getInstance()->restart();

            // for stats
            Task::getInstance()->proccessArticles(1);

            \wp_safe_redirect(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug() . '-articles'));
        }

        $this->actionHandler();
    }

    public function actionHandler()
    {
        global $pagenow;

        if ($pagenow != 'admin.php' && $pagenow != 'options.php' && $pagenow != 'index.php')
            return;

        if (!empty($_GET['action']) && $_GET['action'] == 'regenerate')
        {
            $this->actionRegenerate();
        }

        if (!empty($_GET['action']) && $_GET['action'] == 'tmniche-previous')
        {
            $this->actionPrevius();
            return;
        }

        switch (Wizard::getInstance()->getCurrentStep())
        {
            case 1:
                $this->actionStep1();
                break;
            case 2:
                $this->actionStep2();
                break;
            case 3:
                $this->actionStep3();
                break;
            case 4:
                $this->actionStep4();
                break;
            case 5:
                $this->actionStep5();
                break;
            case 6:
                $this->actionStep6();
                break;
            case 7:
                $this->actionStep7();
                break;
            default:
                $this->actionStep1();
        }
    }

    private function actionRegenerate()
    {
        if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(\sanitize_key($_GET['_wpnonce']), 'ind_regenerate_article'))
            die('Invalid nonce');

        // Plus de vérification de licence - vérifier seulement la niche
        if (!NicheConfig::getInstance()->option('niche') || !NicheInit::getInstance()->getRemainingCredits())
            return;

        $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
        if (!$post_id)
            \wp_die('Invalid post ID');

        if (!$article = ArticleModel::model()->find(array('select' => 'id,recipe_id,theme_id,ce_data', 'where' => 'post_id = ' . $post_id)))
            return;

        $recipe_id = $article['recipe_id'];

        $config = array();
        $recipe_ids = array_keys(NicheInit::getInstance()->getInitRecipes(NicheConfig::isCeIntegration()));
        foreach ($recipe_ids as $rp)
        {
            if ($rp == $recipe_id)
                $config["quantities" . $rp] = "1";
            else
                $config["quantities" . $rp] = "0";
        }

        $products = array();

        if (in_array($recipe_id, array(Recipe::RECIPE_PRODUCT_ROUNDUP, Recipe::RECIPE_PRODUCT_REVIEW, Recipe::RECIPE_PRODUCT_VERSUS)))
        {
            $ce_data = unserialize($article['ce_data']);
            if (!$ce_data || !is_array($ce_data))
                $ce_data = array();

            if ($ce_data)
            {
                $module_id = NicheConfig::getInstance()->option('main_module');
                if (isset($ce_data[$module_id]))
                {
                    foreach ($ce_data[$module_id] as $p)
                    {
                        $products[] = $p['orig_url'];
                    }
                }
            }
        }

        $kdata = array(
            "keyword" => \get_the_title($post_id),
            "as_title" => "true",
            "products" => join("\n", $products),
            "post_id" => $post_id,
        );

        $config["kdata"] = array(
            $recipe_id => array(
                1 => $kdata,
            )
        );

        update_option('independent-niche_keywords', $config);

        Wizard::getInstance()->setCurrentStep(3);

        $redirect_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug());

        \wp_safe_redirect($redirect_url);
        exit;
    }

    public function actionStep1()
    {
        // Plus de configuration de licence nécessaire
        NicheConfig::getInstance()->adminInit();
    }

    public function actionStep2()
    {
        NicheConfig::getInstance()->adminInit();
    }

    public function actionStep3()
    {
        KeywordConfig::getInstance()->adminInit();
    }

    public function actionStep4()
    {
        AiConfig::getInstance()->adminInit();
    }

    public function actionStep5()
    {
        CeConfig::getInstance()->adminInit();
    }

    public function actionStep6()
    {
        SiteConfig::getInstance()->adminInit();
    }

    public function actionStep7()
    {
        TaskConfig::getInstance()->adminInit();
    }

    public function actionPrevius()
    {
        if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(\sanitize_key($_GET['_wpnonce']), 'wizard_nonce'))
            die('Invalid nonce');

        $step = Wizard::getInstance()->getCurrentStep();

        Wizard::getInstance()->setCurrentStep($step - 1);

        $redirect_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug());

        \wp_safe_redirect($redirect_url);
        exit;
    }
}
