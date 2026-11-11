<?php

use App\Http\Controllers\SeoController;
use App\Http\Controllers\WebsiteController;
use App\Livewire\Admin\ActivityLogs;
use App\Livewire\Admin\AppearanceManager;
use App\Livewire\Admin\BlogCategories;
use App\Livewire\Admin\BlogReports;
use App\Livewire\Admin\BlogTags;
use App\Livewire\Admin\ContactMessages;
use App\Livewire\Admin\DataTransfer;
use App\Livewire\Admin\HomepageBuilder;
use App\Livewire\Admin\HomepagePreview;
use App\Livewire\Admin\NavigationManager;
use App\Livewire\Admin\NovaFooterSettings;
use App\Livewire\Admin\PluginManager;
use App\Livewire\Admin\RoleForm;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\RobotsTxtEditor;
use App\Livewire\Admin\SearchResults;
use App\Livewire\Admin\SeoSettings;
use App\Livewire\Admin\SettingsEditor;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\Users;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/theme-preview/{theme}', [WebsiteController::class, 'previewTheme'])->name('theme.preview');
Route::get('/home', [WebsiteController::class, 'home'])->name('website.home');
Route::get('/about', [WebsiteController::class, 'about'])->name('website.about');
Route::get('/services', [WebsiteController::class, 'services'])->name('website.services');
Route::get('/services/{slug}', [WebsiteController::class, 'service'])->name('website.service');
Route::get('/projects', [WebsiteController::class, 'portfolio'])->name('website.projects');
Route::get('/projects/{slug}', [WebsiteController::class, 'project'])->name('website.project');
Route::get('/portfolio', [WebsiteController::class, 'portfolio'])->name('website.portfolio');
Route::get('/portfolio/{slug}', [WebsiteController::class, 'project'])->name('website.portfolio.project');
Route::get('/blog', [WebsiteController::class, 'blog'])->name('website.blog');
Route::get('/blog/{slug}', [WebsiteController::class, 'blogPost'])->name('website.blog.post');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('website.contact');
Route::post('/contact', [WebsiteController::class, 'storeContact'])->middleware('throttle:contact')->name('website.contact.store');

Route::get('/admin', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->middleware('admin')->group(function (): void {
        Route::livewire('dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
        Route::livewire('platform-administration', 'pages::admin.platform-administration')->middleware('can:manage-users')->name('admin.platform');
        Route::get('homepage', HomepageBuilder::class)->middleware('can:homepage_builder.view')->name('admin.homepage');
        Route::get('homepage/preview', HomepagePreview::class)->middleware('can:homepage_builder.view')->name('admin.homepage.preview');
        Route::get('pages/{page}/preview', [WebsiteController::class, 'previewPage'])->middleware('can:access-admin')->name('admin.pages.preview');
        Route::get('search', SearchResults::class)->name('admin.search');
        Route::get('navigation', NavigationManager::class)->middleware('can:navigation.view')->name('admin.navigation');
        Route::get('appearance/themes', AppearanceManager::class)->defaults('page', 'themes')->middleware('can:appearance.view')->name('admin.appearance.themes');
        Route::get('appearance/menus', NavigationManager::class)->middleware('can:navigation.view')->name('admin.appearance.menus');
        Route::get('appearance/footer', NavigationManager::class)->middleware('can:navigation.view')->defaults('menu', 'footer')->defaults('footerSubTab', 'settings')->name('admin.appearance.footer');
        Route::get('appearance/nova-footer', NovaFooterSettings::class)->middleware('can:appearance.view')->name('admin.appearance.nova-footer');
        Route::get('appearance/theme-options', AppearanceManager::class)->defaults('page', 'theme-options')->middleware('can:appearance.view')->name('admin.appearance.theme-options');
        Route::get('appearance/custom-css', AppearanceManager::class)->defaults('page', 'custom-css')->middleware('can:appearance.view')->name('admin.appearance.custom-css');
        Route::get('appearance/custom-html', AppearanceManager::class)->defaults('page', 'custom-html')->middleware('can:appearance.view')->name('admin.appearance.custom-html');
        Route::get('appearance/robots-txt', RobotsTxtEditor::class)->middleware('can:seo.view')->name('admin.appearance.robots-txt');
        Route::get('plugins', PluginManager::class)->defaults('page', 'index')->name('admin.plugins.index');
        Route::get('plugins/create', PluginManager::class)->defaults('page', 'create')->name('admin.plugins.create');
        Route::get('tools/data', DataTransfer::class)->name('admin.tools.data');
        Route::get('blog/categories', BlogCategories::class)->middleware('can:blog.view')->name('admin.blog.categories.index');
        Route::get('blog/categories/create', BlogCategories::class)->middleware('can:blog.create')->name('admin.blog.categories.create');
        Route::get('blog/categories/{category}/edit', BlogCategories::class)->middleware('can:blog.update')->name('admin.blog.categories.edit');
        Route::get('blog/tags', BlogTags::class)->middleware('can:blog.view')->name('admin.blog.tags.index');
        Route::get('blog/tags/create', BlogTags::class)->middleware('can:blog.create')->name('admin.blog.tags.create');
        Route::get('blog/tags/{tag}/edit', BlogTags::class)->middleware('can:blog.update')->name('admin.blog.tags.edit');
        Route::get('blog/reports', BlogReports::class)->middleware('can:blog.view')->name('admin.blog.reports.index');
        foreach (['pages', 'services', 'projects', 'blog', 'team', 'testimonials', 'faqs'] as $resource) {
            $view = in_array($resource, ['team', 'testimonials', 'faqs'], true) ? 'pages::admin.phase-three-list' : 'pages::admin.content-list';
            $form = match ($resource) {
                'team' => 'pages::admin.team-form',
                'testimonials' => 'pages::admin.testimonial-form',
                'faqs' => 'pages::admin.faq-form',
                default => 'pages::admin.content-form',
            };
            Route::livewire($resource, $view)->defaults('resource', $resource)->middleware("can:{$resource}.view")->name("admin.{$resource}.index");
            Route::livewire("{$resource}/create", $form)->defaults('resource', $resource)->middleware("can:{$resource}.create")->name("admin.{$resource}.create");
            Route::livewire("{$resource}/{model}/edit", $form)->defaults('resource', $resource)->middleware("can:{$resource}.update")->name("admin.{$resource}.edit");
        }
        Route::get('seo', SeoSettings::class)->middleware('can:seo.view')->name('admin.seo');
        Route::redirect('seo/robots', '/admin/appearance/robots-txt')->middleware('can:seo.view')->name('admin.seo.robots');
        Route::get('contact-messages', ContactMessages::class)->middleware('can:contact_messages.view')->name('admin.contact-messages');
        Route::get('activity-logs', ActivityLogs::class)->middleware('can:view-activity-logs')->name('admin.activity-logs');
        Route::get('users', Users::class)->middleware('can:users.view')->name('admin.users.index');
        Route::get('users/create', UserForm::class)->middleware('can:users.create')->name('admin.users.create');
        Route::get('users/{user}/edit', UserForm::class)->middleware('can:users.update')->name('admin.users.edit');
        Route::get('roles', RoleIndex::class)->middleware('can:roles.view')->name('admin.roles.index');
        Route::get('roles/create', RoleForm::class)->middleware('can:roles.create')->name('admin.roles.create');
        Route::get('roles/{role}/edit', RoleForm::class)->middleware('can:roles.update')->name('admin.roles.edit');
        Route::get('settings', SettingsEditor::class)->middleware('can:settings.view')->name('admin.site-settings');
        Route::livewire('media', 'pages::admin.media')->middleware('can:manage-media')->name('admin.media.index');
    });
});

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('custom.css', function (): \Illuminate\Http\Response {
    return response(app(\App\Support\CustomCssConfiguration::class)->publicStylesheet(), 200, [
        'Content-Type' => 'text/css; charset=UTF-8',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->name('custom-css');

require __DIR__.'/settings.php';

Route::get('/{slug}', [WebsiteController::class, 'page'])
    ->where('slug', '^(?!api|admin|settings|theme-preview|login|logout|register|password|sitemap\.xml|robots\.txt|custom\.css|home|about|services|service|projects|portfolio|blog|contact|dashboard|sanctum|storage|livewire)[A-Za-z0-9-]+$')
    ->name('website.page');
