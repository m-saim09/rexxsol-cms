<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\HomepageSection;
use App\Models\Page;
use App\Models\PageSlugRedirect;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Support\GoogleMapsUrl;
use App\Support\HomepageContentService;
use App\Support\NavigationService;
use App\Support\NovaFooterService;
use App\Support\PublicSiteSettings;
use App\Support\Seo;
use App\Support\ThemeRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class WebsiteController extends Controller
{
    public function home()
    {
        $page = Page::query()->where('template', 'system-home')->first();
        $homepage = app(HomepageContentService::class)->data();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();

        return $this->renderPage('home', compact('page', 'homepage', 'settings', 'menus'));
    }

    public function previewTheme(string $theme, ThemeRegistry $themes)
    {
        abort_unless($themes->isRegistered($theme), 404);
        $homepage = app(HomepageContentService::class)->data();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();

        return view($themes->find($theme)['view'].'.pages.home', compact('homepage', 'settings', 'menus'))
            ->with('previewTheme', $theme)
            ->with('seoPageTitle', 'Home');
    }

    public function about()
    {
        $homepage = app(HomepageContentService::class)->data();
        HomepageSection::query()->whereIn('key', ['about', 'stats', 'why_choose_us'])->get()
            ->each(function (HomepageSection $section) use (&$homepage): void {
                $settings = $section->settings ?? [];
                $homepage[$section->key] = array_merge($homepage[$section->key] ?? [], [
                    'key' => $section->key,
                    'settings' => $settings,
                    'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
                ]);
            });
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $page = Page::query()
            ->where('template', 'system-about')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();
        $teamMembers = TeamMember::query()
            ->where('status', 'published')
            ->whereNotNull('profile_image')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->renderPage('about', compact('page', 'settings', 'menus', 'teamMembers', 'homepage'));
    }

    public function services()
    {
        $page = Page::query()->where('template', 'system-services')->first();
        $homepage = app(HomepageContentService::class)->data();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $items = Service::query()
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->renderPage('services', compact('page', 'items', 'homepage', 'settings', 'menus'));
    }

    public function service(string $slug)
    {
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $item = Service::query()->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->renderPage('service-detail', compact('item', 'settings', 'menus'));
    }

    public function portfolio()
    {
        $page = Page::query()->where('template', 'system-projects')->first();
        $homepage = app(HomepageContentService::class)->data();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $projects = Project::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return $this->renderPage('portfolio', compact('page', 'homepage', 'settings', 'menus', 'projects'));
    }

    public function project(string $slug)
    {
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $item = Project::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return $this->renderPage('project-detail', compact('item', 'settings', 'menus'));
    }

    public function blog()
    {
        $page = Page::query()->where('template', 'system-blog')->first();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $search = trim((string) request()->query('q', ''));
        $categorySlug = trim((string) request()->query('category', ''));
        $selectedCategory = $categorySlug !== ''
            ? BlogCategory::query()->where('slug', $categorySlug)->where('status', 'published')->first()
            : null;
        $query = BlogPost::query()->publiclyVisible()->with([
            'author',
            'categories' => fn ($query) => $query->where('status', 'published'),
        ]);

        if ($selectedCategory) {
            $query->where(function ($query) use ($selectedCategory): void {
                $query->whereHas('categories', fn ($categories) => $categories->whereKey($selectedCategory->id))
                    ->orWhere('category', $selectedCategory->name);
            });
        } elseif ($categorySlug !== '') {
            $query->whereRaw('1 = 0');
        }

        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('content', 'like', $like));
        }

        $items = $query->orderByDesc('published_at')->orderByDesc('id')->paginate(9)->withQueryString();
        $categories = BlogCategory::query()
            ->where('status', 'published')
            ->whereHas('posts', fn ($posts) => $posts->publiclyVisible())
            ->orderBy('name')
            ->get();

        return $this->renderPage('blog', compact('page', 'items', 'categories', 'selectedCategory', 'search', 'settings', 'menus'))
            ->with('seoUrl', app(Seo::class)->baseUrl().'/blog');
    }

    public function blogPost(string $slug)
    {
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $item = BlogPost::query()
            ->publiclyVisible()
            ->with(['author', 'categories' => fn ($query) => $query->where('status', 'published')])
            ->where('slug', $slug)
            ->firstOrFail();
        $relatedItems = collect();
        if ($item->categories->isNotEmpty()) {
            $relatedItems = BlogPost::query()
                ->publiclyVisible()
                ->whereKeyNot($item->id)
                ->whereHas('categories', fn ($query) => $query->whereIn('blog_categories.id', $item->categories->modelKeys())->where('status', 'published'))
                ->with(['author', 'categories' => fn ($query) => $query->where('status', 'published')])
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        return $this->renderPage('blog-detail', compact('item', 'relatedItems', 'settings', 'menus'))
            ->with('seoUrl', app(Seo::class)->contentUrl('blog', $item->slug));
    }

    public function contact()
    {
        $page = Page::query()->where('template', 'system-contact')->first();
        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $publicContact = app(PublicSiteSettings::class)->contactDetails();
        $contactEmail = $publicContact['email'];
        $contactPhone = $publicContact['phone'];
        $contactWhatsapp = $publicContact['whatsapp'];
        $contactAddress = $publicContact['address'];
        $contactDetails = [
            ['label' => 'Email Us', 'value' => $contactEmail, 'href' => 'mailto:'.$contactEmail, 'icon' => 'fa-envelope'],
            ['label' => 'Call Us', 'value' => $contactPhone, 'href' => 'tel:'.preg_replace('/\D+/', '', $contactPhone), 'icon' => 'fa-phone'],
        ];
        if ($contactWhatsapp) {
            $contactDetails[] = [
                'label' => 'WhatsApp',
                'value' => $contactWhatsapp,
                'href' => 'https://wa.me/'.preg_replace('/\D+/', '', $contactWhatsapp),
                'icon' => 'fa-comment',
            ];
        }
        if ($contactAddress) {
            $contactDetails[] = [
                'label' => 'Visit Us',
                'value' => $contactAddress,
                'href' => 'https://www.google.com/maps/search/?api=1&query='.urlencode($contactAddress),
                'target' => '_blank',
                'icon' => 'fa-map-marker-alt',
            ];
        }
        $officeAddress = $contactAddress;
        $officeMapEmbedUrl = GoogleMapsUrl::embed($settings['contact.office_map_embed_url'] ?? null);
        $directionsSetting = $settings['contact.office_map_directions_url'] ?? null;
        if (! $directionsSetting && $officeMapEmbedUrl && trim($contactAddress) !== '') {
            $directionsSetting = 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($contactAddress);
        }
        $officeMapDirectionsUrl = GoogleMapsUrl::directions($directionsSetting);
        $contactServices = $this->contactServiceNames();
        $footer = app(NovaFooterService::class)->data();
        $privacyPolicyLink = collect($footer['legal'] ?? [])
            ->first(fn (array $link): bool => str_contains(mb_strtolower((string) ($link['label'] ?? '')), 'privacy'));
        if (! $privacyPolicyLink) {
            $privacyPage = Page::query()
                ->where('slug', 'privacy-policy')
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->first();
            if ($privacyPage) {
                $privacyPolicyLink = ['url' => $privacyPage->publicUrl(), 'target' => '_self'];
            }
        }

        return $this->renderPage('contact', compact(
            'page',
            'settings',
            'menus',
            'contactDetails',
            'contactEmail',
            'contactPhone',
            'contactAddress',
            'contactServices',
            'officeAddress',
            'officeMapEmbedUrl',
            'officeMapDirectionsUrl',
            'privacyPolicyLink',
        ));
    }

    public function storeContact(Request $request)
    {
        if ($request->filled('website_url')) {
            return redirect()->route('website.contact');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/\A\+?(?=(?:\D*\d){7,})[0-9().\s-]{7,50}\z/'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'service' => ['required', 'string', Rule::in($this->contactServiceNames())],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'subject' => trim((string) ($validated['subject'] ?? '')) ?: $validated['service'],
            'message' => 'Service interested in: '.$validated['service']."\n\n".$validated['message'],
            'source' => 'website',
        ]);

        return redirect()->route('website.contact')->with('success', 'Your message has been received and the Rexxsol team will review your inquiry.');
    }

    private function contactServiceNames(): array
    {
        return Service::query()
            ->publiclyVisible()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name')
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->push('Not Sure Yet')
            ->unique()
            ->values()
            ->all();
    }

    public function page(string $slug)
    {
        $redirect = PageSlugRedirect::query()->where('old_slug', $slug)->first();
        if ($redirect) {
            $target = Page::query()
                ->where('id', $redirect->page_id)
                ->where('slug', $redirect->target_slug)
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->first();
            abort_unless($target, 404);

            return redirect()->route('website.page', ['slug' => $target->slug], 301);
        }

        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        $registry = app(ThemeRegistry::class);
        $previewTheme = request()->query('theme');
        if (is_string($previewTheme) && $previewTheme !== '') {
            Gate::authorize('manage-site-settings');
            abort_unless($registry->isRegistered($previewTheme), 404);
            $theme = $registry->find($previewTheme);
        } else {
            $theme = $registry->active();
        }

        return view($registry->resolveTemplate('page', $theme['slug']), compact('page', 'settings', 'menus'))
            ->with('previewTheme', $previewTheme)
            ->with('seoContent', $page)
            ->with('seoPageTitle', null);
    }

    public function previewPage(Page $page)
    {
        Gate::authorize('access-admin');
        abort_if($page->is_system || $page->template !== 'default', 404);

        $settings = app(PublicSiteSettings::class)->all();
        $menus = app(NavigationService::class)->menus();
        $theme = app(ThemeRegistry::class)->active();
        $view = app(ThemeRegistry::class)->resolveTemplate('page', $theme['slug']);

        return view($view, compact('page', 'settings', 'menus'))
            ->with('previewTheme', $theme['slug'])
            ->with('seoContent', $page)
            ->with('seoPageTitle', null);
    }

    private function renderPage(string $page, array $data)
    {
        $registry = app(ThemeRegistry::class);
        $previewTheme = request()->query('theme');
        if (is_string($previewTheme) && $previewTheme !== '') {
            Gate::authorize('manage-site-settings');
            abort_unless($registry->isRegistered($previewTheme), 404);
            $theme = $registry->find($previewTheme);
        } else {
            $theme = $registry->active();
        }

        $content = $data['page'] ?? $data['item'] ?? null;
        $pageTitles = [
            'home' => 'Home',
            'about' => 'About Us',
            'services' => 'Services',
            'service-detail' => null,
            'portfolio' => 'Projects',
            'project-detail' => null,
            'blog' => 'Blog',
            'blog-detail' => null,
            'contact' => 'Contact',
            'page' => null,
        ];

        $viewName = $page === 'portfolio' && view()->exists($theme['view'].'.pages.portfolio')
            ? 'portfolio'
            : ($page === 'portfolio' ? 'projects' : $page);

        return view($theme['view'].'.pages.'.$viewName, $data)
            ->with('previewTheme', $previewTheme)
            ->with('seoContent', $content)
            ->with('seoPageTitle', $pageTitles[$page] ?? null);
    }
}
