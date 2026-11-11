<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\CmsActivityObserver;
use App\Support\HomepageContentService;
use App\Support\NavigationService;
use App\Support\PublicSiteSettings;
use App\Support\SiteSettings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // SQLite and PHP need the user's writable Windows temp directory in artisan serve.
        if (PHP_OS_FAMILY === 'Windows') {
            ServeCommand::$passthroughVariables = array_unique(array_merge(
                ServeCommand::$passthroughVariables,
                ['TEMP', 'TMP', 'TMPDIR'],
            ));
        }

        Gate::define('access-admin', fn (User $user): bool => $user->isCmsUser());
        Gate::define('publish-cms-content', fn (User $user): bool => $user->canPublishCmsContent());
        Gate::define('delete-cms-content', fn (User $user): bool => $user->canPublishCmsContent());
        Gate::define('manage-media', fn (User $user): bool => $user->isCmsUser());
        Gate::define('manage-blog', fn (User $user): bool => $user->isCmsUser());
        Gate::before(fn (User $user): ?bool => ! $user->is_active ? false : ($user->isSuperAdmin() ? true : null));
        Gate::define('manage-users', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('manage-site-settings', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('view-activity-logs', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('manage-contact-messages', fn (User $user): bool => $user->canPublishCmsContent());
        Gate::define('manage-seo', fn (User $user): bool => $user->canPublishCmsContent());
        Gate::define('manage-navigation', fn (User $user): bool => $user->canPublishCmsContent());

        foreach ([Page::class, Service::class, Project::class, BlogPost::class, TeamMember::class, Testimonial::class, Faq::class, Media::class, ContactMessage::class, User::class, SiteSetting::class, HomepageSection::class, NavigationItem::class] as $model) {
            $model::observe(CmsActivityObserver::class);
        }
        SiteSetting::saved(fn () => Cache::forget(SiteSettings::CACHE_KEY));
        SiteSetting::deleted(fn () => Cache::forget(SiteSettings::CACHE_KEY));
        SiteSetting::saved(fn () => PublicSiteSettings::forget());
        SiteSetting::deleted(fn () => PublicSiteSettings::forget());
        HomepageSection::saved(fn () => HomepageContentService::forget());
        HomepageSection::deleted(fn () => HomepageContentService::forget());
        Service::saved(fn () => HomepageContentService::forget());
        Service::deleted(fn () => HomepageContentService::forget());
        Service::restored(fn () => HomepageContentService::forget());
        Testimonial::saved(fn () => HomepageContentService::forget());
        Testimonial::deleted(fn () => HomepageContentService::forget());
        Testimonial::restored(fn () => HomepageContentService::forget());
        NavigationItem::saved(fn () => NavigationService::forget());
        NavigationItem::deleted(function (NavigationItem $item): void {
            NavigationService::forget();
            $this->hidePageNavigation($item);
        });
        NavigationItem::updated(function (NavigationItem $item): void {
            NavigationService::forget();
            if ($item->wasChanged('enabled') && ! $item->enabled) {
                $this->hidePageNavigation($item);
            }
        });
        Page::saved(fn () => NavigationService::forget());
        Page::deleted(fn () => NavigationService::forget());
        Page::restored(fn () => NavigationService::forget());
        SiteSetting::saved(fn () => NavigationService::forget());
        SiteSetting::deleted(fn () => NavigationService::forget());
    }

    /**
     * Keep editor visibility settings aligned with menu changes.
     */
    private function hidePageNavigation(NavigationItem $item): void
    {
        if (! $item->is_automatic || $item->type !== 'page' || ! $item->target_id) {
            return;
        }

        $page = Page::query()->find($item->target_id);
        if (! $page) {
            return;
        }

        $visibilityField = $item->menu === 'primary' ? 'show_in_navbar' : ($item->menu === 'footer' ? 'show_in_footer' : null);
        if ($visibilityField && $page->{$visibilityField}) {
            $page->update([$visibilityField => false]);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
