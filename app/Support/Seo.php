<?php

namespace App\Support;

use App\Models\Media;
use App\Support\ContentImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class Seo
{
    public function baseUrl(): string
    {
        return rtrim(app(SiteSettings::class)->get('seo.canonical_base') ?: config('app.url'), '/');
    }

    public function contentUrl(string $resource, string $slug): string
    {
        // Public URL contract for the later frontend integration.
        return $this->baseUrl().'/'.($resource === 'pages' ? '' : $resource.'/').rawurlencode($slug);
    }

    public function metadata(?Model $content = null, ?string $url = null, ?string $pageTitle = null): array
    {
        $settings = app(SiteSettings::class);
        $image = Media::find($settings->get('branding.social_image'));

        $robots = $settings->get('seo.indexing', false) ? ['index', 'follow'] : ['noindex', 'nofollow'];
        if ($content?->noindex) {
            $robots[0] = 'noindex';
        }
        if ($content?->nofollow) {
            $robots[1] = 'nofollow';
        }

        $siteName = $settings->get('site.name', 'Rexxsol');
        $contentTitle = $content?->title ?: $content?->name ?: $pageTitle;
        $fallbackTitle = $settings->get('seo.title') ?: $siteName;
        if ($content?->seo_title) {
            $title = $content->seo_title;
        } elseif ($contentTitle === 'Home') {
            $title = $siteName;
        } else {
            $title = $contentTitle ? $contentTitle.' | '.$siteName : $fallbackTitle;
        }

        return [
            'title' => $title,
            'description' => $content?->seo_description ?: $settings->get('seo.description', ''),
            'keywords' => $content?->seo_keywords ?: $settings->get('seo.keywords', ''),
            'canonical' => $content?->canonical_url ?: ($url ?: $this->baseUrl()),
            'robots' => implode(',', $robots),
            'og_title' => $content?->seo_title ?: $settings->get('seo.og_title') ?: $title,
            'og_description' => $content?->seo_description ?: $settings->get('seo.og_description') ?: $settings->get('seo.description', ''),
            'image' => $content?->featured_image
                ? ContentImage::url($content->featured_image)
                : ($image ? ContentImage::url($image->path, $image->disk) : null),
            'twitter' => $settings->get('seo.twitter_handle'),
            'google' => $settings->get('seo.google_verification'),
            'bing' => $settings->get('seo.bing_verification'),
        ];
    }
}
