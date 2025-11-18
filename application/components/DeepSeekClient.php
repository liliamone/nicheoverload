<?php

namespace IndependentNiche\application\components;

defined('\ABSPATH') || exit;

use IndependentNiche\application\admin\AiConfig;

/**
 * DeepSeekClient class file
 * Client DeepSeek API pour remplacer l'API KeywordRush
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class DeepSeekClient
{
    private $api_key;
    private $timeout = 60;
    private $base_url = 'https://api.deepseek.com/v1/';

    public function __construct()
    {
        $this->api_key = AiConfig::getInstance()->option('deepseek_api_key');

        if (empty($this->api_key)) {
            throw new \Exception(__('DeepSeek API key not configured.', 'independent-niche'));
        }
    }

    public function generateNicheData($niche, $language = 'English')
    {
        $prompt = sprintf(
            "Generate niche research data for '%s' in %s language. Return valid JSON with: keywords (array of 10 keywords), recipes (array with 'Product Roundup', 'Product Overview', 'Informative Article'), trending_topics (array of 5 trending topics).",
            sanitize_text_field($niche),
            sanitize_text_field($language)
        );

        return $this->makeRequest('chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a niche research expert. Always respond with valid JSON only, no markdown or explanations.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.3
        ]);
    }

    public function generateArticle($title, $keywords, $niche, $recipe_type = 'article')
    {
        $creativity = floatval(AiConfig::getInstance()->option('creativity_level', 0.7));
        $tone = AiConfig::getInstance()->option('tone_of_voice', '');

        $prompt = sprintf(
            "Write a comprehensive %s about '%s' for the %s niche. Include these keywords naturally: %s. %s Write in HTML format with proper headings (h2, h3), paragraphs, and lists. Make it SEO-optimized and engaging. Minimum 1000 words.",
            sanitize_text_field($recipe_type),
            sanitize_text_field($title),
            sanitize_text_field($niche),
            sanitize_text_field(is_array($keywords) ? implode(', ', $keywords) : $keywords),
            !empty($tone) ? "Tone: " . sanitize_text_field($tone) . ". " : ''
        );

        return $this->makeRequest('chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional content writer. Write engaging, SEO-optimized articles in HTML format.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => $creativity
        ]);
    }

    private function makeRequest($endpoint, $data)
    {
        if (empty($endpoint) || !is_array($data)) {
            return new \WP_Error('invalid_params', __('Invalid request parameters.', 'independent-niche'));
        }

        $args = [
            'method' => 'POST',
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'Independent-Niche-Generator/1.0'
            ],
            'body' => wp_json_encode($data),
            'sslverify' => true
        ];

        $response = wp_remote_post($this->base_url . $endpoint, $args);

        if (is_wp_error($response)) {
            error_log('DeepSeek API Error: ' . $response->get_error_message());
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            error_log('DeepSeek API HTTP Error: ' . $status_code);
            return new \WP_Error('api_error', sprintf(__('API request failed with status: %d', 'independent-niche'), $status_code));
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DeepSeek API JSON Error: ' . json_last_error_msg());
            return new \WP_Error('json_error', __('Invalid API response format.', 'independent-niche'));
        }

        return $decoded;
    }
}
