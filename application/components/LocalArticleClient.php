<?php

namespace IndependentNiche\application\components;

defined('\ABSPATH') || exit;

/**
 * LocalArticleClient class file
 * Remplace ArticleClient.php pour utiliser DeepSeek local
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class LocalArticleClient
{
    private $data = array();
    private $deepseek_client;

    public function __construct()
    {
        try {
            $this->deepseek_client = new DeepSeekClient();
        } catch (\Exception $e) {
            error_log('LocalArticleClient Error: ' . $e->getMessage());
            $this->deepseek_client = null;
        }
    }

    public function requestData($title = null, $keywords = null, $niche = null, $recipe_type = 'article')
    {
        $this->data = array();

        if (!$this->deepseek_client) {
            return false;
        }

        try {
            // Si aucun paramètre n'est fourni, générer automatiquement à partir de la config
            if ($title === null || $niche === null) {
                $niche_config = \IndependentNiche\application\admin\NicheConfig::getInstance();
                $keyword_config = \IndependentNiche\application\admin\KeywordConfig::getInstance();
                $niche_init = \IndependentNiche\application\components\NicheInit::getInstance();

                if ($niche === null) {
                    $niche = $niche_config->option('niche', 'general');
                }

                if ($title === null) {
                    // Générer un titre à partir des mots-clés ou de la niche
                    $keywords_list = $keyword_config->option('keywords', array());
                    if (!empty($keywords_list) && is_array($keywords_list)) {
                        $title = $keywords_list[array_rand($keywords_list)];
                    } else {
                        $title = 'Best ' . $niche . ' Products';
                    }
                }

                if ($keywords === null) {
                    $keywords_list = $keyword_config->option('keywords', array());
                    $keywords = !empty($keywords_list) ? $keywords_list : array($niche);
                }
            }

            $response = $this->deepseek_client->generateArticle($title, $keywords, $niche, $recipe_type);

            if ($response && !is_wp_error($response)) {
                $this->data = $this->parseResponse($response, $title);
                return $this->data;
            }

        } catch (\Exception $e) {
            error_log('LocalArticleClient Error: ' . $e->getMessage());
        }

        return false;
    }

    private function parseResponse($response, $title)
    {
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];

            return array(
                'article' => array(
                    'title' => sanitize_text_field($title),
                    'content' => wp_kses_post($content),
                    'slug' => sanitize_title($title),
                    'tags' => $this->generateTags($content),
                    'comments' => array()
                ),
                'stat' => array(
                    'words' => str_word_count(strip_tags($content)),
                    'characters' => strlen($content)
                ),
                'status' => 'success'
            );
        }

        return false;
    }

    private function generateTags($content)
    {
        $words = str_word_count(strip_tags($content), 1);
        $common_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should');

        $tags = array();
        foreach ($words as $word) {
            if (strlen($word) > 4 && !in_array(strtolower($word), $common_words)) {
                $tags[] = ucfirst(strtolower($word));
            }
        }

        return array_unique(array_slice($tags, 0, 10));
    }

    // Méthodes compatibles avec l'interface existante
    public function getArticle()
    {
        return !empty($this->data['article']) ? $this->data['article'] : false;
    }

    public function getComments()
    {
        return !empty($this->data['article']['comments']) ? $this->data['article']['comments'] : array();
    }

    public function getTags()
    {
        return !empty($this->data['article']['tags']) ? $this->data['article']['tags'] : array();
    }

    public function getSlug()
    {
        return !empty($this->data['article']['slug']) ? $this->data['article']['slug'] : '';
    }

    public function getStat()
    {
        return !empty($this->data['stat']) ? $this->data['stat'] : false;
    }

    public function getStatus()
    {
        return !empty($this->data['status']) ? $this->data['status'] : false;
    }
}
