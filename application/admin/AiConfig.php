<?php

namespace IndependentNiche\application\admin;

defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\WizardBootConfig;

use function IndependentNiche\prnx;

/**
 * AiConfig class file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class AiConfig extends WizardBootConfig
{
    const ARTICLE_SIZE_SMALL = 50;
    const ARTICLE_SIZE_MEDIUM = 70;
    const ARTICLE_SIZE_LARGE = 100;

    public function getTitle()
    {
        return __('AI Settings', 'independent-niche');
    }

    public function option_name()
    {
        return Plugin::slug . '_ai';
    }

    protected function options()
    {
        return array(
            'deepseek_api_key' => array(
                'title' => __('DeepSeek API Key', 'independent-niche'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'required' => true,
                'description' => __('Enter your DeepSeek API key. Get one at https://platform.deepseek.com', 'independent-niche'),
                'validator' => array(
                    array(
                        'call' => array('\IndependentNiche\application\helpers\FormValidator', 'required'),
                        'message' => sprintf(__('The field "%s" can not be empty.', 'independent-niche'), 'DeepSeek API Key'),
                    ),
                ),
            ),
            'article_size' => array(
                'title' => __('Article size', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    self::ARTICLE_SIZE_LARGE . '.'  => __('Large', 'independent-niche') . ' (' . self::ARTICLE_SIZE_LARGE . '%)',
                    self::ARTICLE_SIZE_MEDIUM . '.'  => __('Medium', 'independent-niche') . ' (' . self::ARTICLE_SIZE_MEDIUM . '%)',
                    self::ARTICLE_SIZE_SMALL . '.' => __('Small', 'independent-niche') . ' (' . self::ARTICLE_SIZE_SMALL . '%)',
                    self::ARTICLE_SIZE_MEDIUM . '+' . self::ARTICLE_SIZE_LARGE  => __('Medium or Large', 'independent-niche') . ' (' . self::ARTICLE_SIZE_MEDIUM . '% or ' . self::ARTICLE_SIZE_LARGE . '%)',
                    self::ARTICLE_SIZE_SMALL . '+' . self::ARTICLE_SIZE_MEDIUM . '+' . self::ARTICLE_SIZE_LARGE => __('Small, Medium, or Large', 'independent-niche') . ' (' . self::ARTICLE_SIZE_SMALL . '% or ' . self::ARTICLE_SIZE_MEDIUM . '% or ' . self::ARTICLE_SIZE_LARGE . '%)',
                    self::ARTICLE_SIZE_SMALL . '+' . self::ARTICLE_SIZE_MEDIUM  => __('Small or Medium', 'independent-niche') . ' (' . self::ARTICLE_SIZE_SMALL . '% or ' . self::ARTICLE_SIZE_MEDIUM . '%)',
                    self::ARTICLE_SIZE_MEDIUM . '-' . self::ARTICLE_SIZE_LARGE => __('From Medium to Large', 'independent-niche') . ' (' . self::ARTICLE_SIZE_MEDIUM . '% - ' . self::ARTICLE_SIZE_LARGE . '%)',
                    self::ARTICLE_SIZE_SMALL . '-' . self::ARTICLE_SIZE_MEDIUM => __('From Small to Medium', 'independent-niche') . ' (' . self::ARTICLE_SIZE_SMALL . '% - ' . self::ARTICLE_SIZE_MEDIUM . '%)',
                    self::ARTICLE_SIZE_SMALL . '-' . self::ARTICLE_SIZE_LARGE => __('From Small to Large', 'independent-niche') . ' (' . self::ARTICLE_SIZE_SMALL . '% - ' . self::ARTICLE_SIZE_LARGE . '%)',

                ),
                'default' => self::ARTICLE_SIZE_MEDIUM . '+' . self::ARTICLE_SIZE_LARGE,
            ),
            'temperature' => array(
                'title' => __('Creativity level', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getCreativitiesList(),
                'default' => '0.75',
            ),
            'point_of_view' => array(
                'title' => __('Point of view (optional)', 'independent-niche'),
                'description' => __('Note: Enabling the "First person" perspective may result in content that reads as if the writer has personal experience with the products.', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array_merge(array('' => __('- none -', 'independent-niche')) + self::getPointOfViews()),
                'default' => '',
            ),
            'tone' => array(
                'title' => __('Tone of voice (optional)', 'independent-niche'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array_merge(array('' => __('- none -', 'independent-niche')) + self::getTonesList()),
                'default' => '',
            ),
            'ai_instructions' => array(
                'title' => __('Custom AI instructions (optional)', 'independent-niche'),
                'description' => __('Guide the AI by providing specific instructions related to tone, audience, or language style. For example: "Use an informal tone in German (casual, use the \'du\' form)" or "Use British English spelling."', 'independent-niche'),
                'callback' => array($this, 'render_input'),
                'maxlength' => 255,
                'default' => '',
            ),
            'ai_police' => array(
                'description' => '<div class="py-3" role="alert">'
                    . sprintf(__('By using this plugin, you agree to OpenAI\'s <a target="_blank" href="%s">Usage policy</a> and <a target="_blank" href="%s">Sharing & Publication policy</a>.', 'independent-niche'), 'https://openai.com/policies/usage-policies', 'https://openai.com/policies/sharing-publication-policy')
                    . '</div>',

                'callback' => array($this, 'render_text'),
            ),
        );
    }
    public static function getTonesList()
    {
        return array_combine(array_values(self::getTones()), array_values(self::getTones()));
    }

    public static function getPointOfViews()
    {
        return array(
            'first_person_singular' => __('First person singular (I, me, my)', 'independent-niche'),
            'first_person_plural' => __('First person plural (we, us, our)', 'independent-niche'),
            'second_person' => __('Second person (you, your, yours)', 'independent-niche'),
            'third_person' => __('Third person (he, she, it, they)', 'independent-niche'),
        );
    }

    public static function getTones()
    {
        return array(

            // Friendly and approachable tones
            'Caring',
            'Comforting, expert yet familiar',
            'Friendly',
            'Friendly and informal',
            'Friendly expert',
            'Friendly humor',
            'Friendly practical',
            'Friendly, practical, and family-oriented',
            'Friendly, practical, and motivational',
            'Warm, inviting, and practical',
            'Sympathetic',
            'Casual and funny',

            // Energetic and enthusiastic tones
            'Energetic',
            'Energetic and tech-focused',
            'Enthusiastic and outdoor-savvy',
            'Excited',
            'Motivational',

            // Conversational and playful tones
            'Conversational',
            'Playful',
            'Quirky, clever, and trendy',
            'Sarcastic',
            'Polite',
            'Marketing',

            // Informative and technical tones
            'Informative',
            'Neutral',
            'Persuasive',
            'Tech-savvy, more technical',
            'Straightforward, practical, and slightly techier',
            'Wirecutter style',

            // Simple and direct tones
            'Simple',
            'Sincere',

            // Authoritative tones
            'Authoritarian',
            'Authoritative',
            'Confident',
            'Serious',
            'Serious, trustworthy, and data-driven',
            'Professional',
            'Formal',
        );
    }

    public static function getCreativitiesList()
    {
        return array(
            '0.0' => __('Min (more factual, but repetiteve)', 'independent-niche'),
            '0.5' => __('Low', 'independent-niche'),
            '0.75' => __('Optimal (default)', 'independent-niche'),
            '1.0' => __('Optimal+ (still good and a little more creative)', 'independent-niche'),
            '1.1' => __('Hight', 'independent-niche'),
            '1.3' => __('Max (less factual, but creative)', 'independent-niche'),
        );
    }
}
