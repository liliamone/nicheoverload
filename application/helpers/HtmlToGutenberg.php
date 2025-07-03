<?php

namespace TooMuchNiche\application\helpers;

use function TooMuchNiche\prn;
use function TooMuchNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * HtmlToGutenberg class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */

class HtmlToGutenberg
{
    /**
     * Convert HTML to Gutenberg blocks, preserving any existing Gutenberg
     * comment blocks (e.g., <!-- wp:... -->).
     *
     * @param  string $html
     * @return string
     */
    public static function convert($html)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $encoding_hint = '<?xml encoding="UTF-8" ?>';
        $dom->loadHTML($encoding_hint . '<div>' . $html . '</div>');

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $wrapper = $dom->getElementsByTagName('div')->item(0);
        if (!$wrapper)
        {
            return $html;
        }

        $gutenberg = '';
        foreach ($wrapper->childNodes as $node)
        {
            $gutenberg .= self::traverse($node);
        }

        return trim($gutenberg);
    }

    /**
     * Recursively traverse DOM nodes, converting to Gutenberg block syntax.
     *
     * @param  \DOMNode $node
     * @return string
     */
    public static function traverse($node)
    {
        switch ($node->nodeType)
        {
            case XML_TEXT_NODE:
                return $node->textContent;

            case XML_ELEMENT_NODE:
                $tag = strtolower($node->nodeName);
                switch ($tag)
                {
                    case 'p':
                        return static::buildParagraphBlock(self::getInnerHTML($node));
                    case 'h1':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 1);
                    case 'h2':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 2);
                    case 'h3':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 3);
                    case 'h4':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 4);
                    case 'h5':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 5);
                    case 'h6':
                        return static::buildHeadingBlock(self::getInnerHTML($node), 6);
                    case 'hr':
                        return static::buildHrBlock();
                    case 'blockquote':
                        return static::buildBlockquoteBlock($node);
                    case 'pre':
                        // Look for code inside <pre>
                        if (stripos(static::getInnerHTML($node), '<code') !== false)
                        {
                            return static::buildCodeBlock(self::getTextContent($node));
                        }
                        else
                        {
                            return static::buildCodeBlock(self::getTextContent($node));
                        }
                    case 'code':
                        return static::buildCodeBlock(self::getTextContent($node));
                    case 'ul':
                        return static::buildListBlock($node, false);
                    case 'ol':
                        return static::buildListBlock($node, true);
                    case 'table':
                        return static::buildTableBlock($node);
                    default:
                        // For any other tag, recursively process children
                        $content = '';
                        foreach ($node->childNodes as $child)
                        {
                            $content .= self::traverse($child);
                        }
                        return $content;
                }

            case XML_COMMENT_NODE:
                // Preserve Gutenberg comment blocks (or any HTML comment).
                return "<!--" . $node->textContent . "-->";

            default:
                return '';
        }
    }

    public static function buildParagraphBlock($content)
    {
        return "\n<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->\n";
    }

    public static function buildHeadingBlock($content, $level = 2)
    {
        $safeLevel = max(1, min(6, $level));
        $slug = self::slugify($content);
        $id = $slug ?: 'heading-' . $safeLevel;
        return "\n<!-- wp:heading {\"level\":{$safeLevel}} -->\n<h{$safeLevel} class=\"wp-block-heading\" id=\"{$id}\">{$content}</h{$safeLevel}>\n<!-- /wp:heading -->\n";
    }

    public static function slugify($text)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', strtolower($text));
        return trim($text, '-');
    }

    public static function buildHrBlock()
    {
        return "\n<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->\n";
    }

    public static function buildBlockquoteBlock($blockquoteNode)
    {
        $inner = '';
        foreach ($blockquoteNode->childNodes as $child)
        {
            $inner .= self::wrapIfTextNode($child);
        }
        // Ensure there's a <p> if needed
        if (stripos($inner, '<p') === false)
        {
            $inner = "<p>{$inner}</p>";
        }
        return "\n<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">{$inner}</blockquote>\n<!-- /wp:quote -->\n";
    }

    public static function buildCodeBlock($content)
    {
        $encoded = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE);
        return "\n<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>{$encoded}</code></pre>\n<!-- /wp:code -->\n";
    }

    public static function buildListBlock($listNode, $ordered = false)
    {
        $listTag = $ordered ? 'ol' : 'ul';
        $orderedAttr = $ordered ? '{"ordered":true}' : '';
        $listItems = '';

        foreach ($listNode->childNodes as $child)
        {
            if (strtolower($child->nodeName) === 'li')
            {
                $liContent = self::getInnerHTML($child);
                $listItems .= "\n<!-- wp:list-item -->\n<li>{$liContent}</li>\n<!-- /wp:list-item -->";
            }
        }

        return "\n<!-- wp:list {$orderedAttr} -->\n<{$listTag} class=\"wp-block-list\">{$listItems}\n</{$listTag}>\n<!-- /wp:list -->\n";
    }

    public static function buildTableBlock($tableNode)
    {
        $innerHtml = self::getInnerHTML($tableNode);
        return "\n<!-- wp:table -->\n<figure class=\"wp-block-table\"><table class=\"has-fixed-layout\">{$innerHtml}</table></figure>\n<!-- /wp:table -->\n";
    }

    public static function getInnerHTML($node)
    {
        $innerHTML = '';
        foreach ($node->childNodes as $child)
        {
            $innerHTML .= $node->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    public static function getTextContent($node)
    {
        return $node->textContent;
    }

    public static function wrapIfTextNode($node)
    {
        if ($node->nodeType === XML_TEXT_NODE)
        {
            return htmlspecialchars($node->textContent, ENT_QUOTES | ENT_SUBSTITUTE);
        }
        elseif ($node->nodeType === XML_ELEMENT_NODE)
        {
            return $node->ownerDocument->saveHTML($node);
        }
        return '';
    }
}
