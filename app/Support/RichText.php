<?php

namespace App\Support;

use App\Models\Media;
use DOMDocument;
use DOMElement;
use DOMNode;

final class RichText
{
    private const ELEMENTS = [
        'a', 'blockquote', 'br', 'code', 'del', 'div', 'em', 'figcaption', 'figure',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'li', 'ol',
        'p', 'pre', 's', 'span', 'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    public static function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML('<!doctype html><html><body><div id="rich-content">'.$html.'</div></body></html>', LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);
        $container = $document->getElementById('rich-content');
        if (! $container) {
            return '';
        }

        self::cleanChildren($container);
        $result = '';
        foreach ($container->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'], true)) {
                $node->removeChild($child);

                continue;
            }

            self::cleanChildren($child);
            if (! in_array($tag, self::ELEMENTS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $allowed = in_array($name, ['alt', 'class', 'colspan', 'height', 'href', 'id', 'rel', 'rowspan', 'src', 'target', 'title', 'width'], true);
                if (! $allowed || (($name === 'href' || $name === 'src') && ! self::safeUrl($attribute->value))) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'img' && ! $child->hasAttribute('alt')) {
                $path = Media::normalizePath($child->getAttribute('src'));
                $media = Media::query()
                    ->where('disk', 'public')
                    ->where('path', $path)
                    ->first(['alt_text']);
                $child->setAttribute('alt', $media?->alt_text ?? '');
            }

            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private static function safeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return ! str_starts_with($url, '//');
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto', 'tel'], true);
    }
}