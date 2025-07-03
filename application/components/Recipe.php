<?php

namespace TooMuchNiche\application\components;

defined('\ABSPATH') || exit;

/**
 * Recipe class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class Recipe
{
    const RECIPE_PRODUCT_ROUNDUP = 1;
    const RECIPE_PRODUCT_REVIEW = 2;
    const RECIPE_INFORMATIVE_ARTICLE = 3;
    const RECIPE_HOWTO_GUIDE = 4;
    const RECIPE_PRODUCT_VERSUS = 5;

    public static function getRecipeName($id)
    {
        $recipes = self::getRecipes();

        if (isset($recipes[$id]))
            return $recipes[$id];
        else
            return __('Article', 'too-much-niche');
    }

    public static function getRecipes()
    {
        return array(
            self::RECIPE_PRODUCT_ROUNDUP => __('Product Roundup', 'too-much-niche'),
            self::RECIPE_PRODUCT_REVIEW => __('Product Review', 'too-much-niche'),
            self::RECIPE_PRODUCT_VERSUS => __('Product Versus', 'too-much-niche'),
            self::RECIPE_INFORMATIVE_ARTICLE => __('Informative Article', 'too-much-niche'),
            self::RECIPE_HOWTO_GUIDE => __('How-to Guide', 'too-much-niche'),
        );
    }
}
