<?php

namespace IndependentNiche\application\components;

use \IndependentNiche\application\Plugin;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * NicheInit class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
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
        try {
            $deepseek = new DeepSeekClient();
            $niche_config = \IndependentNiche\application\admin\NicheConfig::getInstance();

            $niche_text = $niche_config->option('niche');
            $language = $niche_config->option('language', 'English');

            $result = $deepseek->generateNicheData($niche_text, $language);

            if (is_wp_error($result)) {
                error_log('Independent Niche: DeepSeek API Error - ' . $result->get_error_message());
                return false;
            }

            if ($result && is_array($result)) {
                $niche_data = $this->parseDeepSeekResponse($result);
                if ($niche_data) {
                    $this->setNiche($niche_data);
                    error_log('Independent Niche: Successfully initialized niche data from DeepSeek');
                    return true;
                } else {
                    error_log('Independent Niche: Failed to parse DeepSeek response - Invalid JSON format');
                }
            } else {
                error_log('Independent Niche: DeepSeek returned empty or invalid response');
            }

            return false;

        } catch (\Exception $e) {
            error_log('Independent Niche Generator Error: ' . $e->getMessage());
            return false;
        }
    }

    private function parseDeepSeekResponse($response)
    {
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];

            // Clean the content - remove markdown code blocks if present
            $content = trim($content);
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/i', '', $content);
            $content = trim($content);

            // Try to extract JSON from the response
            $json_data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                // Transform DeepSeek response to expected niche format
                return array(
                    'keywords' => isset($json_data['keywords']) ? $json_data['keywords'] : array(),
                    'recipes' => $this->transformRecipes($json_data),
                    'trending_topics' => isset($json_data['trending_topics']) ? $json_data['trending_topics'] : array(),
                    'remaining_credits' => 100, // Set default value for independent usage
                );
            } else {
                error_log('Independent Niche: JSON Parse Error - ' . json_last_error_msg() . ' | Content: ' . substr($content, 0, 200));
            }
        }
        return false;
    }

    private function transformRecipes($json_data)
    {
        $recipes = array();
        if (isset($json_data['recipes']) && is_array($json_data['recipes'])) {
            $id = 1;
            foreach ($json_data['recipes'] as $recipe_title) {
                $recipes[] = array(
                    'id' => $id++,
                    'title' => $recipe_title,
                    'articles' => 10,
                    'ce_required' => false,
                    'allocated_credits' => 0,
                );
            }
        }
        return $recipes;
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
        if (empty($this->niche['remaining_credits'])) {
            // Return default credits when DeepSeek data is not available
            // This allows the wizard to function without DeepSeek integration
            return 30; // Default: 30 articles
        }

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
        // If no recipes exist, create default ones
        if (empty($this->niche['recipes'])) {
            return $this->getDefaultRecipes($is_ce_enabled);
        }

        $res = array();
        foreach ($this->niche['recipes'] as $r)
        {
            if (!$is_ce_enabled && filter_var($r['ce_required'], FILTER_VALIDATE_BOOLEAN))
                continue;

            $res[$r['id']] = $r['title'];
        }

        return $res;
    }

    private function getDefaultRecipes($is_ce_enabled)
    {
        // Default recipes when DeepSeek data is not available
        // MUST return proper format for getInitRecipes() which expects id => title mapping
        if ($is_ce_enabled) {
            return array(
                1 => __('Product Roundup', 'independent-niche'),
                2 => __('Product Overview', 'independent-niche'),
                3 => __('Informative Article', 'independent-niche'),
            );
        } else {
            return array(
                1 => __('Informative Article', 'independent-niche'),
                2 => __('How-To Guide', 'independent-niche'),
                3 => __('Tips & Tricks', 'independent-niche'),
            );
        }
    }

    public function getDefaultNicheData($is_ce_enabled)
    {
        // Initialize default niche structure when DeepSeek is not available
        $recipes = array();
        $default_recipes = $this->getDefaultRecipes($is_ce_enabled);

        foreach ($default_recipes as $id => $title) {
            $recipes[] = array(
                'id' => $id,
                'title' => $title,
                'articles' => 10,
                'ce_required' => $is_ce_enabled,
                'allocated_credits' => 0,
            );
        }

        return array(
            'keywords' => array(),
            'recipes' => $recipes,
            'trending_topics' => array(),
            'remaining_credits' => 30,
        );
    }

    public function isCeRequired()
    {
        if (empty($this->niche['recipes']))
            return false;

        foreach ($this->niche['recipes'] as $r)
        {
            if (filter_var($r['ce_required'], FILTER_VALIDATE_BOOLEAN))
                return true;
        }

        return false;
    }
}
