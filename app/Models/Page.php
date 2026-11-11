<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'featured_image', 'featured_image_alt', 'status', 'published_at', 'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'noindex', 'nofollow', 'show_in_navbar', 'show_in_footer', 'navigation_label', 'menu_order', 'template', 'is_system', 'hero_eyebrow', 'hero_heading', 'hero_description', 'contact_info_heading', 'contact_info_description', 'contact_form_heading', 'contact_form_description'])]
class Page extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'show_in_navbar' => true,
        'show_in_footer' => true,
        'menu_order' => 0,
        'template' => 'default',
        'is_system' => false,
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'noindex' => 'boolean', 'nofollow' => 'boolean', 'show_in_navbar' => 'boolean', 'show_in_footer' => 'boolean', 'is_system' => 'boolean'];
    }

    public function publicUrl(): string
    {
        return match ($this->template) {
            'system-home' => route('home'),
            'system-about' => route('website.about'),
            'system-services' => route('website.services'),
            'system-contact' => route('website.contact'),
            'system-projects' => route('website.projects'),
            'system-blog' => route('website.blog'),
            default => route('website.page', ['slug' => $this->slug]),
        };
    }
}
