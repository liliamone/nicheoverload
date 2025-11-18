<?php

namespace IndependentNiche\application\components;

defined('\ABSPATH') || exit;

/**
 * Recipe class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
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
            return __('Article', 'independent-niche');
    }

    public static function getRecipes()
    {
        return array(
            self::RECIPE_PRODUCT_ROUNDUP => __('Product Roundup', 'independent-niche'),
            self::RECIPE_PRODUCT_REVIEW => __('Product Review', 'independent-niche'),
            self::RECIPE_PRODUCT_VERSUS => __('Product Versus', 'independent-niche'),
            self::RECIPE_INFORMATIVE_ARTICLE => __('Informative Article', 'independent-niche'),
            self::RECIPE_HOWTO_GUIDE => __('How-to Guide', 'independent-niche'),
        );
    }
}
