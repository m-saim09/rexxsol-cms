<?php

namespace App\Http\Controllers;

use App\Support\CmsContent;
use App\Support\Seo;
use App\Support\RobotsTxtConfiguration;
use App\Support\SiteSettings;

class SeoController extends Controller
{
    public function sitemap(SiteSettings $settings, Seo $seo)
    {
        return response()->stream(function () use ($settings, $seo) {
            echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            if ($settings->get('seo.indexing', false) && ! $settings->get('site.maintenance_enabled', false)) {
                foreach (['pages', 'services', 'projects', 'blog'] as $resource) {
                    $query = CmsContent::model($resource)::where('status', 'published');
                    if ($resource === 'services') {
                        $query->where('is_active', true);
                    } else {
                        $query->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
                    }
                    foreach ($query->lazyById(200) as $record) {
                        echo '<url><loc>'.htmlspecialchars($seo->contentUrl($resource, $record->slug), ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc><lastmod>'.$record->updated_at->toAtomString().'</lastmod></url>';
                    }
                }
            }
            echo '</urlset>';
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Robots-Tag' => 'noindex']);
    }

    public function robots(RobotsTxtConfiguration $robots)
    {
        return response($robots->content(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
