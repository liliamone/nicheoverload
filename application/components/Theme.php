<?php

namespace IndependentNiche\application\components;

use IndependentNiche\application\admin\SiteConfig;
use IndependentNiche\application\helpers\ArrayHelper;
use IndependentNiche\application\helpers\SchemaHelper;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * Theme class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */

class Theme
{
	const THEME_HTML = 0;
	const THEME_GUTENBERG = 1;
	const THEME_GREENSHIFT = 2;
	const CSS_PREFIX = 'tmn-';
	protected $sections;

	public static $themes = array(
		Theme::THEME_HTML => Theme::class,
		Theme::THEME_GUTENBERG => ThemeGutenberg::class,
		Theme::THEME_GREENSHIFT => ThemeGreenshift::class,
	);

	public static function getThemesList()
	{
		return array(
			Theme::THEME_GREENSHIFT => __('Greenshift blocks', 'independent-niche'),
			Theme::THEME_GUTENBERG => __('Gutenberg blocks', 'independent-niche'),
			Theme::THEME_HTML => __('Basic HTML', 'independent-niche'),
		);
	}

	public static function isThemeIdExists($id)
	{
		$themes = self::getThemesList();
		if (isset($themes[$id]))
			return true;
		else
			return false;
	}

	protected function setSections(array $sections)
	{
		$this->sections = $sections;
	}

	public function decorate(array $sections)
	{
		$this->setSections($sections);

		$content = '';
		for ($i = 0; $i < count($sections); $i++)
		{
			$section = $this->sections[$i];

			if ($section['type'] == 'hidden')
				continue;

			$block_method = 'decorateBlock' . $section['block'];

			if ($section['type'] == 'custom' && !method_exists($this, $block_method))
				continue;

			if (!empty($section['heading']))
			{
				$block_method_heading = 'decorateHeading' . $section['block'];
				if (method_exists($this, $block_method_heading))
					$content .= $this->$block_method_heading($section);
				else
					$content .= $this->decorateSectionHeading($section);
			}

			if (method_exists($this, $block_method))
			{
				$content .= $this->$block_method($section);
				continue;
			}

			if ($section['content'] == '@content')
			{
				continue;
			}

			$type_method = 'decorateSection' . ucfirst($section['type']);

			if (method_exists($this, $type_method))
			{
				$content .= $this->$type_method($section);

				continue;
			}

			if (!$section['content'])
				continue;

			$content .= $this->decorateSectionDefault($section);
		}

		return $content;
	}

	public function getWrapperTag(array $section)
	{
		if (in_array($section['type'], ['block', 'shortcode', 'schema']))
			return '';

		if ($section['heading'])
			return 'section';
		else
			return 'div';
	}

	public function getWrapperClass(array $section)
	{
		$class = $section['block'];
		$class = preg_replace('/([a-z])([A-Z])/', '$1-$2', $class);
		$class = strtolower($class);
		$class = self::CSS_PREFIX . strtolower($class);
		return $class;
	}

	public function wrapperStart(array $section, $class = null)
	{
		if (!$tag = $this->getWrapperTag($section))
			return '';

		if (!$class)
			$class = $this->getWrapperClass($section);

		return '<' . $tag . ' class="' . $class . '">';
	}

	public function wrapperEnd(array $section)
	{
		if (!$tag = $this->getWrapperTag($section))
			return '';

		return '</' . $tag . '>';
	}

	public function decorateShortcode($content)
	{
		return  $content . "\n";
	}

	public function decorateBlock($content)
	{
		return  $content . "\n";
	}

	public function decorateBlockReviewSchema(array $section)
	{
		$schema = $section['content'];

		// add author
		$user_id = SiteConfig::getInstance()->option('post_author');
		if (isset($schema['review']) && $author = SchemaHelper::getAuthorArray($user_id))
		{
			$schema['review']['author'] = $author;
		}

		$section['content'] = $schema;
		return $this->decorateSectionSchema($section);
	}

	public function decorateSectionSchema(array $section)
	{
		$schema = \apply_filters('tmniche_section_schema_data', $section['content']);

		if ($schema && is_array($schema))
		{
			$schema = ArrayHelper::addSlashesForQuotesRecursive($schema);
			$json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			return '<script type="application/ld+json">' . $json . '</script>';
		}

		return '';
	}

	public function decorateSectionHtml($section)
	{
		if (!is_array($section))
		{
			$content = $section;
			$section = array();
			$section['content'] = $content;
		}

		$content = preg_replace("~\n+~", '', $section['content']);
		$content = \wp_kses_post($content);

		return $content;
	}

	public function decorateText($content)
	{
		return $this->decorateTextWithList($content);
	}

	public function decorateTextWithList($content)
	{
		$content = trim($content);
		$res = '';

		if (preg_match('/^1(\)|\.)/m', $content))
		{
			$lines = preg_split("~[\n\r]+~", $content, -1, PREG_SPLIT_NO_EMPTY);
			$list = array();

			foreach ($lines as $i => $line)
			{
				if (preg_match('/^\d+(\)|\.)/', $line))
				{
					$line = preg_replace('/^\d+(\)|\.)/', '', $line);
					$list[] = trim($line);

					if ($i < count($lines) - 1)
						continue;
				}
				if ($list)
				{
					$res .= $this->decorateList($list, true);
					$list = array();
				}
				else
					$res .= $this->decorateParagraph($line);
			}

			return $res;
		}
		else
		{
			$paragraphs = preg_split("~[\n\r]+~", $content, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($paragraphs as $paragraph)
			{
				$res .= $this->decorateParagraph($paragraph);
			}

			return $res;
		}
	}

	public function decorateParagraph($content)
	{
		return '<p>' . $content . '</p>' . "\n";
	}

	public static function addStrongToList(array $content)
	{
		$key_value = 0;
		foreach ($content as $i => $item)
		{
			$parts = preg_split('/(:\s|\s\-\s)/', $item, 2, PREG_SPLIT_DELIM_CAPTURE);
			if (count($parts) == 3 && mb_strlen($parts[0]) <= 50)
				$key_value++;
			else
				break;
		}

		if ($key_value == count($content))
		{
			foreach ($content as $i => $item)
			{
				$parts = preg_split('/(:\s|\s\-\s)/', $item, 2, PREG_SPLIT_DELIM_CAPTURE);
				$content[$i] = '<strong>' . $parts[0] . '</strong>' . $parts[1] . $parts[2];
			}
		}

		return $content;
	}

	public function decorateList(array $content, $ordered = false, $params = array())
	{
		if ($ordered)
			$tag_name = 'ol';
		else
			$tag_name = 'ul';

		$content = self::addStrongToList($content);

		$res = '<' . $tag_name . self::buildAttributes($params) . '>';

		foreach ($content as $item)
		{
			$res .= $this->decorateListItem($item);
		}

		$res .= '</' . $tag_name . '>' . "\n";

		return $res;
	}

	public function decorateListItem($content)
	{
		return '<li>' . $content . '</li>' . "\n";
	}

	public function decorateHeading($heading, $tag = 'h2', $params = array())
	{
		if (!$heading)
			return '';

		return '<' . $tag . self::buildAttributes($params) .  '>' . $heading . '</' . $tag . '>' . "\n";
	}

	public function decorateSectionHeading(array $section)
	{
		if ($section['type'] == 'list')
			$tag = 'h3';
		else
			$tag = 'h2';

		return $this->decorateHeading($section['heading'], $tag);
	}

	public function decorateSectionBlock(array $section)
	{
		return $this->decorateBlock($section['content']);
	}

	public function decorateSectionShortcode(array $section)
	{
		return $this->decorateShortcode($section['content']);
	}

	public function decorateSectionText(array $section)
	{
		return $this->decorateText($section['content']);
	}

	public function decorateSectionList(array $section)
	{
		return $this->decorateList($section['content']);
	}

	public function decorateSectionFaq(array $section)
	{
		$res = '';

		foreach ($section['content'] as $faq)
		{
			$res .= $this->decorateText('<strong>' . $faq['question'] . '</strong>') . "\n";
			$res .= $this->decorateText($faq['answer']);
		}

		return $res;
	}

	protected function decorateSectionDefault(array $section)
	{
		if (is_array($section['content']))
			return $this->decorateList($section['content']);
		else
			return $section['content'];
	}

	public function decorateHeadingTerminology(array $section)
	{
		return  $this->decorateHeading($section['heading'], 'h2');
	}

	public function decorateBlockIntroduction(array $section)
	{
		return $this->decorateSectionHtml($section) . '<!--more-->' . "\n";
	}

	public function decorateHeadingArticleSection($section)
	{
		if ($section['heading'] == 'Introduction')
			return '';

		if (self::strStartsWith($section['content'], '<h'))
			return '';
		else
			return $this->decorateHeading($section['heading'], 'h2');
	}

	public function decorateBlockCriteriaRatings($section)
	{
		$criterias = $section['content'];

		$list = array();
		foreach ($criterias as $criteria)
		{
			$list[] = $criteria['criteria'] . ' - ' . $criteria['rating'];
		}

		return $this->decorateList($list);

		return $res;
	}

	protected function findSection($block, $group = '', $hide = false)
	{
		foreach ($this->sections as $i => $section)
		{
			if ($group && $section['group'] != $group)
				continue;

			if ($section['block'] == $block)
			{
				if ($hide)
					$this->sections[$i]['type'] = 'hidden';

				return $section;
			}
		}

		return null;
	}

	protected function findSectionsAll($block, $group = '', $hide = false)
	{
		$res = array();

		foreach ($this->sections as $i => $section)
		{
			if ($group && $section['group'] != $group)
				continue;

			if ($section['block'] == $block)
			{
				if ($hide)
					$this->sections[$i]['type'] = 'hidden';

				$res[] = $section;
			}
		}

		return $res;
	}

	public static function factory($theme_id)
	{
		if (!isset(self::$themes[$theme_id]))
			throw new Exception(sprintf('Theme %s does not exists.', $theme_id));

		$className = self::$themes[$theme_id];
		$theme = new $className();

		return $theme;
	}

	public static function buildAttributes(array $params)
	{
		if (!$params)
			return '';

		$attributePairs = array();
		foreach ($params as $key => $val)
		{
			if (is_int($key))
				$attributePairs[] = $val;
			else
			{
				$val = htmlspecialchars($val, ENT_QUOTES);
				$attributePairs[] = "{$key}=\"{$val}\"";
			}
		}

		return ' ' . join(' ', $attributePairs);
	}

	public static function jsonEncode($params)
	{
		if (!$params)
			return '';

		$r = ' ' . json_encode($params, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		$r = self::escapeJsonString($r);

		return $r;
	}

	public static function escapeJsonString($value)
	{
		$escapers = array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}

	public static function strStartsWith($haystack, $needle)
	{
		return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}

	public static function table2Array($html)
	{
		$encoding_hint = '<?xml encoding="UTF-8">';
		$html = $encoding_hint . $html;

		$doc = new \DOMDocument();
		$doc->loadHTML($html);
		$xpath = new \DOMXPath($doc);
		$rows = $xpath->query('//table//tr');

		$productsArray = array();
		$headers = array();

		foreach ($rows as $row)
		{
			$cells = $xpath->query('td|th', $row);
			$rowData = array();
			foreach ($cells as $i => $cell)
			{
				if ($row->parentNode->tagName == 'thead')
				{
					$headers[] = $cell->nodeValue;
					continue;
				}

				$key = $headers[$i] ?? $i;
				$rowData[$key] = $cell->nodeValue;
			}

			if ($rowData)
				$productsArray[] = $rowData;
		}

		return $productsArray;
	}

	public static function dataExists(array $data)
	{
		foreach ($data as $d)
		{
			if ($d)
				return true;
		}

		return false;
	}
}
