<?php

namespace App\Support;

final class ThemeComponentRegistry
{
    /**
     * Central registry of CMS blocks that are available across themes.
     * Each theme may override the presentation while keeping the same content shape.
     */
    public function all(): array
    {
        return [
            'hero' => [
                'key' => 'hero',
                'name' => 'Hero',
                'description' => 'Primary call-to-action banner area',
                'icon' => 'sparkles',
                'supports' => ['hero'],
            ],
            'rich-text' => [
                'key' => 'rich-text',
                'name' => 'Rich Text',
                'description' => 'Freeform prose content block',
                'icon' => 'document-text',
                'supports' => ['rich-text'],
            ],
            'image-text' => [
                'key' => 'image-text',
                'name' => 'Image + Text',
                'description' => 'Side-by-side media and content section',
                'icon' => 'photo',
                'supports' => ['image-text'],
            ],
            'services' => [
                'key' => 'services',
                'name' => 'Services',
                'description' => 'Featured service listing',
                'icon' => 'briefcase',
                'supports' => ['services'],
            ],
            'projects' => [
                'key' => 'projects',
                'name' => 'Projects',
                'description' => 'Recent project showcase',
                'icon' => 'folder-open',
                'supports' => ['projects'],
            ],
            'blog' => [
                'key' => 'blog',
                'name' => 'Blog Posts',
                'description' => 'Latest articles',
                'icon' => 'newspaper',
                'supports' => ['blog'],
            ],
            'stats' => [
                'key' => 'stats',
                'name' => 'Stats',
                'description' => 'Key metrics and proof points',
                'icon' => 'chart-bar',
                'supports' => ['stats'],
            ],
            'testimonials' => [
                'key' => 'testimonials',
                'name' => 'Testimonials',
                'description' => 'Customer and client feedback',
                'icon' => 'chat-bubble-left-right',
                'supports' => ['testimonials'],
            ],
            'team' => [
                'key' => 'team',
                'name' => 'Team',
                'description' => 'People profile showcase',
                'icon' => 'users',
                'supports' => ['team'],
            ],
            'faq' => [
                'key' => 'faq',
                'name' => 'FAQ',
                'description' => 'Common questions and answers',
                'icon' => 'question-mark-circle',
                'supports' => ['faq'],
            ],
            'cta' => [
                'key' => 'cta',
                'name' => 'CTA',
                'description' => 'Call-to-action banner',
                'icon' => 'arrow-right-circle',
                'supports' => ['cta'],
            ],
            'contact' => [
                'key' => 'contact',
                'name' => 'Contact',
                'description' => 'Contact card or lead capture section',
                'icon' => 'phone',
                'supports' => ['contact'],
            ],
        ];
    }

    public function find(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    public function keys(): array
    {
        return array_keys($this->all());
    }
}
