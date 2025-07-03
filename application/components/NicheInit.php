<?php

namespace TooMuchNiche\application\components;

use \TooMuchNiche\application\Plugin;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * NicheInit class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class NicheInit
{
    private $niche = null;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        $this->niche = \get_option(Plugin::slug . '_init_niche', array());
    }

    public function initializeNicheFromApi()
    {
        $result = NicheApi::get('/init');

        if ($result && !empty($result['status']) && $result['status'] == 'success')
        {
            if (!empty($result['niche']) && is_array($result['niche']))
            {
                $this->setNiche($result['niche']);
                return true;
            }
            else
                return false;
        }

        return false;
    }

    public function setNiche(array $niche)
    {
        $this->niche = $niche;
        \update_option(Plugin::slug . '_init_niche', $this->niche);
        return $this->niche;
    }

    public function getNiche()
    {
        return $this->niche;
    }

    public function clearCache()
    {
        $this->setNiche(array());
        \delete_option(Plugin::slug . '_init_niche');
    }

    public function deleteNiche()
    {
        $this->clearCache();
    }

    public function getRemainingCredits()
    {
        if (empty($this->niche['remaining_credits']))
            return 0;

        return (int) $this->niche['remaining_credits'];
    }

    public function getTotalArticles()
    {
        if (empty($this->niche['recipes']))
            return 0;

        $total = 0;
        foreach ($this->niche['recipes'] as $r)
        {
            $total += (int) $r['articles'];
        }

        return $total;
    }

    public function getArticleQuantities($is_ce_enabled)
    {
        if (empty($this->niche['recipes']))
            return array();

        $res = array();
        foreach ($this->niche['recipes'] as $r)
        {
            if (!$is_ce_enabled && filter_var($r['ce_required'], FILTER_VALIDATE_BOOLEAN))
                continue;

            $recipes[] = $r;
        }

        $recipes = self::recipesAllocateCredits($recipes, $this->getRemainingCredits());
        $res = array();
        foreach ($recipes as $r)
        {
            $res[$r['id']] = $r['allocated_credits'];
        }

        return $res;
    }

    static public function recipesAllocateCredits(array $recipes, $available_credits)
    {
        $total_initial_articles = array_sum(array_column($recipes, 'articles'));

        // Calculate the proportion of each recipe and allocate credits
        $sum_allocated_credits = 0;
        foreach ($recipes as $i => $recipe)
        {
            $recipes[$i]['proportion'] = $recipes[$i]['articles'] / $total_initial_articles;
            $recipes[$i]['allocated_credits'] = floor($recipes[$i]['proportion'] * $available_credits);
            $sum_allocated_credits += $recipes[$i]['allocated_credits'];
        }

        // Adjust the credits to ensure the total matches the available credits
        $difference = $available_credits - $sum_allocated_credits;
        if ($difference != 0)
        {
            foreach ($recipes as $i => $recipe)
            {
                $recipes[$i]['allocated_credits'] += 1;
                if (array_sum(array_column($recipes, 'allocated_credits')) >= $available_credits)
                    break;
            }
        }

        return $recipes;
    }

    public function getInitRecipes($is_ce_enabled)
    {
        $res = array();
        foreach ($this->niche['recipes'] as $r)
        {
            if (!$is_ce_enabled && filter_var($r['ce_required'], FILTER_VALIDATE_BOOLEAN))
                continue;

            $res[$r['id']] = $r['title'];
        }

        return $res;
    }

    public function isCeRequired()
    {
        if (empty($this->niche['recipes']))
            return array();

        foreach ($this->niche['recipes'] as $r)
        {
            if (filter_var($r['ce_required'], FILTER_VALIDATE_BOOLEAN))
                return true;
        }

        return false;
    }
}
