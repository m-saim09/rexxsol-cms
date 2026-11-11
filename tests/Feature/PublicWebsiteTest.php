<?php

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\HomepageSection;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\FooterService;
use App\Support\GoogleMapsUrl;
use App\Support\HomepageContentService;
use App\Support\NavigationService;
use App\Support\PublicSiteSettings;
use App\Support\SettingsFields;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

afterEach(function () {
    Cache::forget(SiteSettings::CACHE_KEY);
    PublicSiteSettings::forget();
    NavigationService::forget();
});

it('loads the public homepage and renders enabled homepage sections in order', function () {
    HomepageSection::ensureDefaults();
    HomepageSection::where('key', 'hero')->update(['enabled' => true, 'sort_order' => 0, 'settings' => ['headline' => 'Built for modern operations', 'highlighted_text' => 'growth', 'description' => 'A smarter digital foundation for ambitious teams.']]);
    HomepageSection::where('key', 'services')->update(['enabled' => true, 'sort_order' => 1, 'settings' => ['heading' => 'What we solve', 'limit' => 3, 'selected_ids' => []]]);
    HomepageSection::where('key', 'team')->update(['enabled' => false]);

    Service::create(['name' => 'Cloud Advisory', 'slug' => 'cloud-advisory', 'status' => 'published', 'is_active' => true, 'short_description' => 'Cloud strategy.']);

    $response = $this->get(route('website.home'));

    $response->assertOk();
    $response->assertSee('<title>Rexxsol</title>', false);
    $response->assertSee('Built for modern operations');
    $response->assertSee('What we solve');
    $response->assertDontSee('Team');
});

it('renders CMS-managed process and why choose content in the Nova homepage sections', function () {
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-nova']);
    PublicSiteSettings::forget();
    HomepageSection::ensureDefaults();
    HomepageSection::where('key', 'process')->update([
        'enabled' => true,
        'settings' => [
            'eyebrow' => 'HOW WE WORK',
            'heading' => 'A practical process.',
            'description' => 'Four clear steps.',
            'steps' => [
                ['title' => 'Listen', 'description' => 'Understand the brief.'],
                ['title' => 'Plan', 'description' => 'Set the direction.'],
                ['title' => 'Build', 'description' => 'Create the solution.'],
                ['title' => 'Launch', 'description' => 'Ship with confidence.'],
            ],
        ],
    ]);
    HomepageSection::where('key', 'why_choose_us')->update([
        'enabled' => true,
        'settings' => [
            'eyebrow' => 'WHY THIS TEAM',
            'heading' => "Clear goals.\nSteady delivery.",
            'description' => 'A focused partnership.',
            'items' => [
                ['title' => 'Clear direction', 'description' => 'Make the next step obvious.'],
                ['title' => 'Practical delivery', 'description' => 'Keep progress visible.'],
            ],
        ],
    ]);
    HomepageContentService::forget();

    $this->get(route('website.home'))
        ->assertOk()
        ->assertSee('HOW WE WORK')
        ->assertSee('A practical process.')
        ->assertSee('Listen')
        ->assertSee('Launch')
        ->assertSee('<svg class="nova-process-icon"', false)
        ->assertSee('WHY THIS TEAM')
        ->assertSee('Clear goals.')
        ->assertSee('Clear direction')
        ->assertSee('Make the next step obvious.')
        ->assertDontSee('Strategic clarity');
});

it('mass assigns service publish metadata and keeps published services visible', function () {
    $service = Service::create(['name' => 'Published service', 'slug' => 'published-service', 'status' => 'published', 'published_at' => now()->subHour(), 'is_active' => true, 'short_description' => 'Live service']);

    expect($service->published_at)->not->toBeNull()
        ->and($service->status)->toBe('published');

    $this->get('/services')->assertOk()->assertSee('Published service');
});

it('shows published services and hides draft or inactive services from the public site', function () {
    Service::create(['name' => 'Published service', 'slug' => 'published-service', 'status' => 'published', 'published_at' => now()->subHour(), 'is_active' => true, 'short_description' => 'Live service']);
    Service::create(['name' => 'Draft service', 'slug' => 'draft-service', 'status' => 'draft', 'published_at' => now()->subHour(), 'is_active' => true, 'short_description' => 'Hidden']);
    Service::create(['name' => 'Inactive service', 'slug' => 'inactive-service', 'status' => 'published', 'published_at' => now()->subHour(), 'is_active' => false, 'short_description' => 'No access']);

    $this->get('/services')->assertOk()->assertSee('Published service')->assertDontSee('Draft service')->assertDontSee('Inactive service');
    $this->get('/services/published-service')
        ->assertOk()
        ->assertSee('Published service')
        ->assertSee('<title>Published service | Rexxsol</title>', false);
    $this->get('/services/draft-service')->assertNotFound();
});

it('renders the Nova services index with published cards and shared homepage sections', function () {
    HomepageSection::ensureDefaults();
    HomepageSection::whereIn('key', ['services', 'process', 'cta'])->update(['enabled' => true]);
    Service::create(['name' => 'First capability', 'slug' => 'first-capability', 'status' => 'published', 'is_active' => true, 'sort_order' => 1, 'short_description' => 'CMS-managed first description.']);
    Service::create(['name' => 'Second capability', 'slug' => 'second-capability', 'status' => 'published', 'is_active' => true, 'sort_order' => 2, 'short_description' => 'CMS-managed second description.']);
    HomepageContentService::forget();

    $this->actingAs(User::factory()->create(['role' => UserRole::SuperAdmin]))
        ->get('/services?theme=rexxsol-nova')
        ->assertOk()
        ->assertSee('Home', false)
        ->assertSee('Capabilities')
        ->assertSee('Built around')
        ->assertSee('your business.')
        ->assertSee('From strategy to software, we help you build what comes next.')
        ->assertSee('Explore our services')
        ->assertDontSee('Discuss your project')
        ->assertSeeInOrder(['First capability', 'Second capability'])
        ->assertSee('First capability')
        ->assertSee('Second capability')
        ->assertSee('CMS-managed first description.')
        ->assertSee('/services/first-capability?theme=rexxsol-nova', false)
        ->assertSee('Discover')
        ->assertSee('Design')
        ->assertSee('Develop')
        ->assertSee('Deliver')
        ->assertSee('Let’s talk.')
        ->assertSee('Tell us what you’re building.')
        ->assertSee('We’ll help you take it forward.')
        ->assertDontSee('Explore All Services');
});

it('shows published projects and blog posts while hiding unpublished content', function () {
    Project::create(['name' => 'Published project', 'slug' => 'published-project', 'status' => 'published', 'published_at' => now()->subDay(), 'short_description' => 'Results']);
    Project::create(['name' => 'Draft project', 'slug' => 'draft-project', 'status' => 'draft', 'short_description' => 'Hidden']);
    BlogPost::create(['title' => 'Published article', 'slug' => 'published-article', 'status' => 'published', 'published_at' => now()->subHour(), 'excerpt' => 'An insight']);
    BlogPost::create(['title' => 'Draft article', 'slug' => 'draft-article', 'status' => 'draft', 'excerpt' => 'Hidden']);

    $this->get('/projects')->assertOk()->assertSee('Published project')->assertDontSee('Draft project');
    $this->get('/projects/published-project')->assertOk()->assertSee('Published project');
    $this->get('/blog')->assertOk()->assertSee('Published article')->assertDontSee('Draft article');
    $this->get('/blog/published-article')->assertOk()->assertSee('Published article');
    $this->get('/blog/draft-article')->assertNotFound();
});

it('renders the public navigation and SEO metadata from configured settings', function () {
    SiteSetting::updateOrCreate(['key' => 'site.name'], ['value' => 'Rexxsol']);
    SiteSetting::updateOrCreate(['key' => 'seo.title'], ['value' => 'Rexxsol Digital Consulting']);
    SiteSetting::updateOrCreate(['key' => 'seo.description'], ['value' => 'Technology strategy and software delivery.']);
    SiteSetting::updateOrCreate(['key' => 'seo.indexing'], ['value' => true]);
    SiteSetting::updateOrCreate(['key' => 'seo.canonical_base'], ['value' => 'https://example.com']);
    NavigationItem::create(['menu' => 'primary', 'label' => 'Services', 'type' => 'service', 'target_id' => 1, 'target' => '_self', 'enabled' => true, 'sort_order' => 1]);

    $response = $this->get('/services');

    $response->assertOk();
    $response->assertSee('Services');
    $response->assertSee('<title>Services | Rexxsol</title>', false);
    $response->assertSee('Technology strategy and software delivery.');
});

it('validates and stores contact form submissions through the existing contact message module', function () {
    app(SiteSettings::class)->put([
        'appearance.active_theme' => 'rexxsol-nova',
        'site.name' => 'Rexxsol',
    ]);
    PublicSiteSettings::forget();
    Service::create([
        'name' => 'Custom Web Development',
        'slug' => 'contact-test-web-development',
        'status' => 'published',
        'is_active' => true,
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('Company <span>(Optional)</span>', false);

    $response = $this->from('/contact')->post('/contact', [
        'name' => '',
        'email' => 'not-an-email',
        'service' => 'Unlisted service',
        'message' => 'hi',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'service', 'message']);

    $this->from('/contact')->post('/contact', [
        'name' => 'Invalid Phone Example',
        'email' => 'phone@example.com',
        'phone' => 'call me at 1234567',
        'service' => 'Not Sure Yet',
        'message' => 'Please call me about this project.',
    ])->assertRedirect('/contact')->assertSessionHasErrors(['phone']);

    $this->post('/contact', [
        'name' => 'Client Example',
        'email' => 'client@example.com',
        'phone' => '+1 555 123 4567',
        'company' => 'Test Co',
        'subject' => 'Website modernization',
        'service' => 'Custom Web Development',
        'message' => 'We need help with our platform modernization.',
    ])->assertRedirect('/contact')->assertSessionHas('success');

    $storedInquiry = ContactMessage::where('email', 'client@example.com')->firstOrFail();
    expect($storedInquiry->subject)->toBe('Website modernization')
        ->and($storedInquiry->message)->toStartWith('Service interested in: Custom Web Development');

    $this->post('/contact', [
        'name' => 'Individual Client',
        'email' => 'individual@example.com',
        'service' => 'Not Sure Yet',
        'message' => 'I would like to discuss an individual project.',
    ])->assertRedirect('/contact')->assertSessionHas('success');

    expect(ContactMessage::where('email', 'individual@example.com')->firstOrFail()->company)->toBeNull();

    $this->post('/contact', [
        'name' => 'Spam Example',
        'email' => 'spam@example.com',
        'service' => 'Not Sure Yet',
        'message' => 'This hidden field should prevent storage.',
        'website_url' => 'https://spam.example',
    ])->assertRedirect('/contact')->assertSessionMissing('success');

    expect(ContactMessage::where('email', 'spam@example.com')->exists())->toBeFalse();
});

it('uses the contact page details in the footer instead of demo contact settings', function () {
    app(SiteSettings::class)->put([
        'appearance.active_theme' => 'rexxsol-nova',
        'site.name' => 'Rexxsol',
        'contact.business_email' => 'hello@rexxsol-demo.local',
        'contact.phone' => '+1 (415) 555-0189',
        'contact.address' => '1280 Market Street, San Francisco, CA',
    ]);
    PublicSiteSettings::forget();
    FooterService::forget();

    $this->get('/contact')
        ->assertOk()
        ->assertSee('contact@rexxsol.com')
        ->assertSee('+92 333 2465888')
        ->assertSee('Pearl City, Canal Rd, Rahim Yar Khan, 64200, Pakistan')
        ->assertDontSee('hello@rexxsol-demo.local')
        ->assertDontSee('1280 Market Street, San Francisco, CA');
});

it('renders the contact experience and only embeds explicitly configured Google Maps URLs', function () {
    $verifiedMapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3509.653683949486!2d70.32835107421269!3d28.399526194625874!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39375de324b503a5%3A0xcc942cc9d96036a1!2sRexxsol%20software%20solutions%20and%20trainings%20institute!5e0!3m2!1sen!2s!4v1791459311736!5m2!1sen!2s';
    app(SiteSettings::class)->put([
        'appearance.active_theme' => 'rexxsol-nova',
        'site.name' => 'Rexxsol',
        'contact.office_map_embed_url' => $verifiedMapUrl,
        'contact.office_map_directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=verified-office',
    ]);
    PublicSiteSettings::forget();

    $this->get('/contact')
        ->assertOk()
        ->assertSee('Tell us what you have in mind.')
        ->assertSee('Let’s start a conversation.')
        ->assertSee('Tell us about your project')
        ->assertSee('href="mailto:contact@rexxsol.com"', false)
        ->assertSee('href="tel:+923332465888"', false)
        ->assertSee('What can we help with?', false)
        ->assertSee('Please avoid sharing passwords or sensitive information.')
        ->assertSee('By submitting, you agree to our')
        ->assertDontSee('HAVE A PROJECT IN MIND?')
        ->assertSee($verifiedMapUrl, false)
        ->assertSee('title="Map showing Rexxsol’s verified office location"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=verified-office', false);

    app(SiteSettings::class)->put([
        'contact.office_map_embed_url' => 'https://maps.attacker.example/maps/embed?pb=wrong-pin',
        'contact.office_map_directions_url' => 'https://maps.attacker.example/maps/dir/?api=1&destination=wrong-pin',
    ]);
    PublicSiteSettings::forget();

    $this->get('/contact')
        ->assertOk()
        ->assertDontSee('<iframe', false)
        ->assertDontSee('wrong-pin')
        ->assertSee('Pearl City, Canal Rd, Rahim Yar Khan, 64200, Pakistan');
});

it('renders saved Contact page copy from the CMS', function () {
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-nova']);
    PublicSiteSettings::forget();
    $page = Page::query()->where('template', 'system-contact')->firstOrFail();
    $page->update([
        'hero_eyebrow' => 'A CUSTOM CONTACT EYEBROW',
        'hero_heading' => 'A custom contact heading',
        'hero_description' => 'A custom contact introduction.',
        'contact_info_heading' => 'A custom information heading',
        'contact_info_description' => 'A custom contact details introduction.',
        'contact_form_heading' => 'A custom enquiry heading',
        'contact_form_description' => 'A custom form introduction.',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('A CUSTOM CONTACT EYEBROW')
        ->assertSee('A custom contact heading')
        ->assertSee('A custom contact introduction.')
        ->assertSee('A custom information heading')
        ->assertSee('A custom contact details introduction.')
        ->assertSee('A custom enquiry heading')
        ->assertSee('A custom form introduction.');
});

it('uses the supplied verified office map unless an explicit CMS setting overrides it', function () {
    SiteSetting::where('key', 'contact.office_map_embed_url')->delete();
    Cache::forget(SiteSettings::CACHE_KEY);
    app(SiteSettings::class)->put([
        'appearance.active_theme' => 'rexxsol-nova',
        'site.name' => 'Rexxsol',
    ]);
    PublicSiteSettings::forget();

    $verifiedMapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3509.653683949486!2d70.32835107421269!3d28.399526194625874!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39375de324b503a5%3A0xcc942cc9d96036a1!2sRexxsol%20software%20solutions%20and%20trainings%20institute!5e0!3m2!1sen!2s!4v1791459311736!5m2!1sen!2s';

    $this->get('/contact')
        ->assertOk()
        ->assertSee($verifiedMapUrl, false)
        ->assertSee('referrerpolicy="strict-origin-when-cross-origin"', false);

    app(SiteSettings::class)->put(['contact.office_map_embed_url' => null]);
    PublicSiteSettings::forget();

    $this->get('/contact')
        ->assertOk()
        ->assertDontSee('<iframe', false);
});

it('validates configured office map URLs as Google Maps locations', function () {
    $verifiedMapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3509.653683949486!2d70.32835107421269!3d28.399526194625874!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39375de324b503a5%3A0xcc942cc9d96036a1!2sRexxsol%20software%20solutions%20and%20trainings%20institute!5e0!3m2!1sen!2s!4v1791459311736!5m2!1sen!2s';

    expect(GoogleMapsUrl::embed($verifiedMapUrl))->not->toBeNull()
        ->and(GoogleMapsUrl::embed('https://maps.attacker.example/maps/embed?pb=wrong-pin'))->toBeNull()
        ->and(GoogleMapsUrl::directions('https://www.google.com/maps/dir/?api=1&destination=verified-office'))->not->toBeNull()
        ->and(GoogleMapsUrl::directions('https://www.google.com/maps/dir/?api=1'))->toBeNull();

    $validator = Validator::make([
        'values' => [
            'site_name' => 'Rexxsol',
            'language' => 'en',
            'office_map_embed_url' => 'https://maps.attacker.example/maps/embed?pb=wrong-pin',
            'office_map_directions_url' => 'javascript:alert(1)',
        ],
    ], SettingsFields::rules('site'));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('values.office_map_embed_url')
        ->and($validator->errors()->keys())->toContain('values.office_map_directions_url');
});

it('keeps contact form values and renders inline errors after validation fails', function () {
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-nova']);

    $this->from('/contact')->followingRedirects()->post('/contact', [
        'name' => '',
        'email' => 'not-an-email',
        'company' => 'Acme Ltd',
        'phone' => '+92 333 1234567',
        'service' => 'Unknown service',
        'message' => 'short',
    ])
        ->assertOk()
        ->assertSee('id="contact-name-error"', false)
        ->assertSee('id="contact-email-error"', false)
        ->assertSee('id="contact-service-error"', false)
        ->assertSee('id="contact-message-error"', false)
        ->assertSee('value="not-an-email"', false)
        ->assertSee('value="Acme Ltd"', false)
        ->assertSee('value="+92 333 1234567"', false)
        ->assertSee('>short</textarea>', false);
});

it('requires a valid CMS page slug and keeps public routes unauthenticated', function () {
    Page::where('slug', 'about')->firstOrFail()->update([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'content' => 'About content',
    ]);

    $this->get('/about')->assertOk();
    $this->get('/pages/not-found')->assertNotFound();
    $this->get('/admin')->assertRedirect(route('login'));
});

it('renders the About reference sections from CMS page and homepage builder content', function () {
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-nova']);
    PublicSiteSettings::forget();
    HomepageSection::ensureDefaults();
    HomepageSection::where('key', 'about')->update([
        'enabled' => false,
        'settings' => [
            'story_heading' => 'A CMS story heading',
            'story_close' => 'A CMS story closing.',
            'mission_title' => 'CMS mission',
            'mission_description' => 'CMS mission description.',
            'vision_title' => 'CMS vision',
            'vision_description' => 'CMS vision description.',
            'thinking_eyebrow' => 'OUR APPROACH',
            'thinking_heading' => 'CMS thinking heading',
            'items' => [['title' => 'CMS feature', 'description' => 'CMS feature description.']],
        ],
    ]);
    HomepageSection::where('key', 'stats')->update([
        'enabled' => false,
        'settings' => ['items' => [['title' => '22+', 'description' => 'years of experience']]],
    ]);
    HomepageSection::where('key', 'why_choose_us')->update([
        'enabled' => false,
        'settings' => [
            'eyebrow' => 'OUR STRENGTHS',
            'heading' => 'CMS bring heading',
            'description' => 'CMS bring description.',
            'items' => [['title' => 'CMS benefit', 'description' => 'CMS benefit description.']],
        ],
    ]);
    Page::where('template', 'system-about')->firstOrFail()->update([
        'status' => 'published',
        'hero_eyebrow' => 'CMS about eyebrow',
        'hero_heading' => 'CMS about title',
        'hero_description' => 'CMS about description.',
        'content' => '',
    ]);
    HomepageContentService::forget();

    $this->get('/about')
        ->assertOk()
        ->assertSee('CMS about eyebrow')
        ->assertSee('CMS about title')
        ->assertSee('CMS about description.')
        ->assertSee('A CMS story heading')
        ->assertSee('22 plus years')
        ->assertSee('For over 22 years')
        ->assertSee('CMS mission')
        ->assertSee('CMS mission description.')
        ->assertSee('OUR APPROACH')
        ->assertSee('CMS feature description.')
        ->assertSee('OUR STRENGTHS')
        ->assertSee('CMS benefit')
        ->assertSee('nova-experience-curve')
        ->assertSee('nova-premium-footer');
});

it('builds a real public menu from cms navigation and renders the homepage from the existing homepage builder', function () {
    HomepageSection::ensureDefaults();
    HomepageSection::where('key', 'hero')->update(['enabled' => true, 'settings' => ['headline' => 'CMS-powered growth', 'highlighted_text' => 'Rexxsol']]);
    HomepageSection::where('key', 'services')->update(['enabled' => true]);
    NavigationItem::create(['menu' => 'primary', 'label' => 'Home', 'type' => 'custom', 'url' => '/', 'target' => '_self', 'enabled' => true, 'sort_order' => 1]);
    NavigationItem::create(['menu' => 'footer', 'label' => 'Contact', 'type' => 'custom', 'url' => '/contact', 'target' => '_self', 'enabled' => true, 'sort_order' => 1]);

    $response = $this->get(route('website.home'));

    $response->assertOk();
    $response->assertSee('Home');
    $response->assertSee('Contact');
    $response->assertSee('CMS-powered growth');
});
