<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealisticDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedNavigation();
        $this->seedHomepage();
        $this->seedPages();
        $this->seedServices();
        $this->seedProjects();
        $this->seedBlogPosts();
        $this->seedTeamMembers();
        $this->seedFaqs();
    }

    private function seedSettings(): void
    {
        $settings = [
            'site.name' => 'Rexxsol',
            'site.company' => 'Rexxsol Studio',
            'site.tagline' => 'Digital systems for modern teams',
            'site.description' => 'Placeholder demo content for local development and visual QA. This content is not a declaration of official company facts.',
            'contact.business_email' => 'hello@rexxsol-demo.local',
            'contact.support_email' => 'support@rexxsol-demo.local',
            'contact.phone' => '+1 (415) 555-0189',
            'contact.whatsapp' => '+1 (415) 555-0189',
            'contact.address' => '1280 Market Street, San Francisco, CA',
            'social.linkedin' => 'https://www.linkedin.com/company/rexxsol-software-solution-training-institute/',
            'social.facebook' => 'https://www.facebook.com/rexxsolPrivateLimited/',
            'social.instagram' => 'https://www.instagram.com/rexxsol/',
            'social.youtube' => 'https://www.youtube.com',
            'website.footer_text' => 'Practical digital support for teams that need reliable systems, smarter workflows, and a calmer path to delivery.',
            'website.copyright' => '© 2026 Rexxsol. All rights reserved.',
            'website.cta_label' => 'Book a consultation',
            'website.cta_url' => '/contact',
            'header.cta_label' => 'Start a project',
            'header.cta_url' => '/contact',
            'footer.company_description' => 'Practical digital solutions across web, mobile, software, and connected workflows.',
            'footer.cta_label' => 'Let’s talk',
            'footer.cta_url' => '/contact',
            'seo.title' => 'Rexxsol',
            'seo.description' => 'Local placeholder copy used for testing the public website experience.',
            'seo.indexing' => false,
            'seo.canonical_base' => 'http://127.0.0.1:8000',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedNavigation(): void
    {
        $primaryItems = [
            ['menu' => 'primary', 'label' => 'Home', 'type' => 'custom', 'url' => '/', 'target' => '_self', 'enabled' => true, 'sort_order' => 1],
            ['menu' => 'primary', 'label' => 'Services', 'type' => 'custom', 'url' => '/services', 'target' => '_self', 'enabled' => true, 'sort_order' => 2],
            ['menu' => 'primary', 'label' => 'Projects', 'type' => 'custom', 'url' => '/projects', 'target' => '_self', 'enabled' => true, 'sort_order' => 3],
            ['menu' => 'primary', 'label' => 'Insights', 'type' => 'custom', 'url' => '/blog', 'target' => '_self', 'enabled' => true, 'sort_order' => 4],
            ['menu' => 'primary', 'label' => 'About', 'type' => 'custom', 'url' => '/about', 'target' => '_self', 'enabled' => true, 'sort_order' => 5],
            ['menu' => 'primary', 'label' => 'Contact', 'type' => 'custom', 'url' => '/contact', 'target' => '_self', 'enabled' => true, 'sort_order' => 6],
        ];

        foreach ($primaryItems as $item) {
            NavigationItem::updateOrCreate(
                ['menu' => $item['menu'], 'label' => $item['label'], 'parent_id' => null],
                $item
            );
        }

        // Seed footer columns and their links
        $footerColumns = [
            [
                'label' => 'Explore',
                'sort_order' => 1,
                'links' => [
                    ['label' => 'Home', 'type' => 'system', 'url' => 'home', 'target' => '_self', 'enabled' => true, 'sort_order' => 1],
                    ['label' => 'About', 'type' => 'system', 'url' => 'about', 'target' => '_self', 'enabled' => true, 'sort_order' => 2],
                    ['label' => 'Services', 'type' => 'system', 'url' => 'services', 'target' => '_self', 'enabled' => true, 'sort_order' => 3],
                    ['label' => 'Portfolio', 'type' => 'system', 'url' => 'portfolio', 'target' => '_self', 'enabled' => true, 'sort_order' => 4],
                ],
            ],
            [
                'label' => 'Resources',
                'sort_order' => 2,
                'links' => [
                    ['label' => 'Blog', 'type' => 'system', 'url' => 'blog', 'target' => '_self', 'enabled' => true, 'sort_order' => 1],
                    ['label' => 'Contact', 'type' => 'system', 'url' => 'contact', 'target' => '_self', 'enabled' => true, 'sort_order' => 2],
                ],
            ],
            [
                'label' => 'Legal',
                'sort_order' => 3,
                'links' => [
                    ['label' => 'Privacy Policy', 'type' => 'custom', 'url' => '/privacy-policy', 'target' => '_self', 'enabled' => true, 'sort_order' => 1],
                    ['label' => 'Terms of Service', 'type' => 'custom', 'url' => '/privacy-policy', 'target' => '_self', 'enabled' => true, 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($footerColumns as $colDef) {
            $column = NavigationItem::updateOrCreate(
                ['menu' => 'footer', 'label' => $colDef['label'], 'parent_id' => null],
                [
                    'menu' => 'footer',
                    'label' => $colDef['label'],
                    'type' => 'column',
                    'enabled' => true,
                    'sort_order' => $colDef['sort_order'],
                    'parent_id' => null,
                ]
            );

            foreach ($colDef['links'] as $linkDef) {
                NavigationItem::updateOrCreate(
                    ['menu' => 'footer', 'parent_id' => $column->id, 'label' => $linkDef['label']],
                    array_merge($linkDef, [
                        'menu' => 'footer',
                        'parent_id' => $column->id,
                    ])
                );
            }
        }
    }

    private function seedHomepage(): void
    {
        HomepageSection::ensureDefaults();

        $updates = [
            'hero' => [
                'enabled' => true,
                'sort_order' => 0,
                'settings' => [
                    'eyebrow' => 'Local demo content',
                    'headline' => 'Technology systems that help teams move with clarity.',
                    'highlighted_text' => 'with confidence',
                    'description' => 'This is placeholder content for local UI testing and public-site QA. It demonstrates how the homepage reacts to real CMS records without claiming official business facts.',
                    'primary_cta_label' => 'Start a project',
                    'primary_cta_url' => '/contact',
                    'secondary_cta_label' => 'View services',
                    'secondary_cta_url' => '/services',
                    'trust_text' => 'Trusted by product, ops, and delivery teams',
                ],
            ],
            'about' => [
                'enabled' => true,
                'sort_order' => 1,
                'settings' => [
                    'eyebrow' => 'Why teams choose us',
                    'heading' => 'Built to simplify the work behind the work.',
                    'description' => 'We design practical digital systems that help teams align around delivery, reduce friction, and gain more confidence in the decisions they make every week.',
                ],
            ],
            'services' => [
                'enabled' => true,
                'sort_order' => 2,
                'settings' => ['eyebrow' => 'Capabilities', 'heading' => 'What we help teams do better', 'limit' => 3, 'selected_ids' => []],
            ],
            'projects' => [
                'enabled' => true,
                'sort_order' => 3,
                'settings' => ['eyebrow' => 'Selected work', 'heading' => 'Recent delivery stories', 'limit' => 3, 'selected_ids' => []],
            ],
            'stats' => [
                'enabled' => true,
                'sort_order' => 4,
                'settings' => [
                    'eyebrow' => 'Operational snapshot',
                    'heading' => 'Measured progress, not guesswork',
                    'items' => [
                        ['title' => '12 week launch cycles', 'description' => 'Faster validation with clearer priorities.', 'icon' => '↗'],
                        ['title' => '27% faster handoff', 'description' => 'Less friction from strategy into delivery.', 'icon' => '◎'],
                        ['title' => '4 teams aligned', 'description' => 'Shared product visibility across departments.', 'icon' => '✓'],
                    ],
                ],
            ],
            'why_choose_us' => [
                'enabled' => true,
                'sort_order' => 5,
                'settings' => [
                    'eyebrow' => 'Why choose Rexxsol',
                    'heading' => 'A calmer, more practical path to digital delivery.',
                    'items' => [
                        ['title' => 'Strategic clarity', 'description' => 'We turn complexity into a simpler roadmap and sharper decisions.', 'icon' => '01'],
                        ['title' => 'Hands-on execution', 'description' => 'Delivery is structured, transparent, and built around momentum.', 'icon' => '02'],
                        ['title' => 'Operational focus', 'description' => 'We solve the bottlenecks that slow teams down and create churn.', 'icon' => '03'],
                    ],
                ],
            ],
            'testimonials' => [
                'enabled' => true,
                'sort_order' => 6,
                'settings' => ['eyebrow' => 'Client feedback', 'heading' => 'What delivery teams say', 'limit' => 3, 'selected_ids' => []],
            ],
            'team' => [
                'enabled' => true,
                'sort_order' => 7,
                'settings' => ['eyebrow' => 'People', 'heading' => 'The specialists behind the work', 'limit' => 3, 'selected_ids' => []],
            ],
            'blog' => [
                'enabled' => true,
                'sort_order' => 8,
                'settings' => ['eyebrow' => 'Insights', 'heading' => 'Recent thinking', 'limit' => 3, 'selected_ids' => []],
            ],
            'faqs' => [
                'enabled' => true,
                'sort_order' => 9,
                'settings' => ['eyebrow' => 'FAQs', 'heading' => 'Common questions', 'limit' => 4, 'selected_ids' => []],
            ],
            'cta' => [
                'enabled' => true,
                'sort_order' => 10,
                'settings' => [
                    'eyebrow' => 'Let’s get started',
                    'heading' => 'Need a clearer path from idea to delivery?',
                    'description' => 'This demo call-to-action is included to validate the homepage flow and spacing in local QA.',
                    'primary_cta_label' => 'Book a conversation',
                    'primary_cta_url' => '/contact',
                    'secondary_cta_label' => 'Explore services',
                    'secondary_cta_url' => '/services',
                ],
            ],
        ];

        foreach ($updates as $key => $payload) {
            HomepageSection::where('key', $key)->update($payload);
        }
    }

    private function seedPages(): void
    {
        $pages = [
            ['title' => 'About Rexxsol', 'slug' => 'about', 'status' => 'published', 'published_at' => now()->subDay(), 'excerpt' => 'A practical, people-first digital partner for modern operations.', 'content' => '<p>Rexxsol is presented here as a local demo brand used only for public site QA. The content reflects a placeholder digital consultancy for testing layout, tone, and structure.</p><p>Use this page to validate spacing, responsiveness, and the presentation of CMS-driven copy across the public site.</p>'],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'status' => 'published', 'published_at' => now()->subDay(), 'excerpt' => 'Placeholder privacy policy for QA.', 'content' => '<p>Demo privacy stub.</p>'],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function seedServices(): void
    {
        $services = [
            ['name' => 'Digital Strategy', 'slug' => 'digital-strategy', 'status' => 'published', 'is_active' => true, 'short_description' => 'Roadmaps, discovery, and prioritization for teams moving into new digital capabilities.', 'description' => 'A placeholder service for local testing of the public services experience.', 'icon' => 'strategy', 'sort_order' => 1],
            ['name' => 'Product Design', 'slug' => 'product-design', 'status' => 'published', 'is_active' => true, 'short_description' => 'Human-centered design systems that reduce complexity and improve adoption.', 'description' => 'Desktop and mobile experiences that bring clarity to customer journeys and internal workflows.', 'icon' => 'design', 'sort_order' => 2],
            ['name' => 'Workflow Automation', 'slug' => 'workflow-automation', 'status' => 'published', 'is_active' => true, 'short_description' => 'Automations and operational tooling that reduce manual overhead and improve reliability.', 'description' => 'Practical automation built around real team processes, not generic templates.', 'icon' => 'automation', 'sort_order' => 3],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }

    private function seedProjects(): void
    {
        $projects = [
            ['name' => 'Enterprise CRM Modernization', 'slug' => 'northlane-crm-refresh', 'status' => 'published', 'published_at' => now()->subDays(14), 'short_description' => 'Modernized customer and sales workflows into a centralized platform with clearer reporting and streamlined operations.', 'description' => 'A centralized customer relationship platform supporting account workflows, reporting, and day-to-day sales operations.', 'client' => null, 'category' => 'Enterprise Systems', 'technologies' => ['Laravel', 'MySQL', 'API Integrations'], 'sort_order' => 1],
            ['name' => 'Corporate Digital Platform', 'slug' => 'harbor-platform-launch', 'status' => 'published', 'published_at' => now()->subDays(30), 'short_description' => 'Built a responsive corporate web platform designed around service discovery, content management and lead generation.', 'description' => 'A responsive corporate web platform bringing service discovery, content management, and lead generation into one maintainable experience.', 'client' => null, 'category' => 'Web Development', 'technologies' => ['Laravel', 'Livewire', 'Tailwind'], 'sort_order' => 2],
            ['name' => 'Operations Management System', 'slug' => 'summit-ops-dashboard', 'status' => 'published', 'published_at' => now()->subDays(45), 'short_description' => 'Developed a centralized system for managing internal processes, teams, records and day-to-day operational workflows.', 'description' => 'A centralized operational system supporting internal processes, team coordination, records, and recurring business workflows.', 'client' => null, 'category' => 'Business Operations', 'technologies' => ['Laravel', 'MySQL', 'Automation'], 'sort_order' => 3],
            ['name' => 'Customer Service Mobile App', 'slug' => 'customer-service-mobile-app', 'status' => 'published', 'published_at' => now()->subDays(60), 'short_description' => 'Created a mobile experience that gives customers faster access to services, account information and support.', 'description' => 'A customer-facing mobile experience for accessing services, account information, and support across devices.', 'client' => null, 'category' => 'Mobile Solutions', 'technologies' => ['React Native', 'REST API', 'Firebase'], 'sort_order' => 4],
            ['name' => 'Enterprise Systems Integration', 'slug' => 'enterprise-systems-integration', 'status' => 'published', 'published_at' => now()->subDays(75), 'short_description' => 'Connected multiple business applications and data sources to reduce manual work and improve information flow.', 'description' => 'An integration layer connecting business applications and data sources through coordinated workflows and reliable information exchange.', 'client' => null, 'category' => 'Integration', 'technologies' => ['API Integration', 'Webhooks', 'Automation'], 'sort_order' => 5],
            ['name' => 'Executive Analytics Dashboard', 'slug' => 'executive-analytics-dashboard', 'status' => 'published', 'published_at' => now()->subDays(90), 'short_description' => 'Delivered centralized reporting and operational dashboards to help teams monitor performance and make informed decisions.', 'description' => 'Centralized reporting and operational dashboards supporting performance review, visibility, and informed business decisions.', 'client' => null, 'category' => 'Analytics', 'technologies' => ['Power BI', 'Data Integration', 'Reporting'], 'sort_order' => 6],
            ['name' => 'Commerce & Order Management Platform', 'slug' => 'commerce-order-management-platform', 'status' => 'published', 'published_at' => now()->subDays(105), 'short_description' => 'Built a scalable digital commerce solution covering products, customer journeys, orders and administrative workflows.', 'description' => 'A digital commerce platform covering product management, customer journeys, orders, and supporting administrative workflows.', 'client' => null, 'category' => 'E-commerce', 'technologies' => ['Laravel', 'Payment Integration', 'MySQL'], 'sort_order' => 7],
            ['name' => 'Business Process Automation Platform', 'slug' => 'business-process-automation-platform', 'status' => 'published', 'published_at' => now()->subDays(120), 'short_description' => 'Digitized repetitive operational processes through custom workflows, approvals and automated notifications.', 'description' => 'A workflow platform digitizing repetitive processes through configurable approvals, notifications, and operational automation.', 'client' => null, 'category' => 'Custom Software', 'technologies' => ['Laravel', 'Workflow Automation', 'API'], 'sort_order' => 8],
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(['slug' => $project['slug']], $project);
        }
    }

    private function seedBlogPosts(): void
    {
        $author = User::firstOrCreate(
            ['email' => 'content@rexxsol-demo.local'],
            ['name' => 'Demo Editor', 'role' => 'super_admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        $posts = [
            ['title' => 'What good digital roadmaps actually look like', 'slug' => 'digital-roadmaps', 'status' => 'published', 'published_at' => now()->subDays(6), 'excerpt' => 'A practical look at building momentum without overcommitting too early.', 'content' => '<p>Placeholder article for local QA of the public blog and detail layouts.</p>', 'category' => 'Strategy', 'tags' => ['roadmaps', 'planning'], 'author_id' => $author->id],
            ['title' => 'Why operational clarity beats more tools', 'slug' => 'operational-clarity', 'status' => 'published', 'published_at' => now()->subDays(12), 'excerpt' => 'Fewer systems, cleaner workflows, and better decision-making.', 'content' => '<p>Placeholder article designed to test the public blog listing and article layout.</p>', 'category' => 'Operations', 'tags' => ['automation', 'teamwork'], 'author_id' => $author->id],
            ['title' => 'Designing a calmer release process', 'slug' => 'calmer-release-process', 'status' => 'published', 'published_at' => now()->subDays(18), 'excerpt' => 'A simpler way to coordinate release decisions and reduce churn.', 'content' => '<p>Demo article used to validate responsive blog content and callouts.</p>', 'category' => 'Delivery', 'tags' => ['launch', 'process'], 'author_id' => $author->id],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }

    private function seedTeamMembers(): void
    {
        $team = [
            ['name' => 'Alicia Morgan', 'slug' => 'alicia-morgan', 'designation' => 'Strategy Lead', 'short_bio' => 'Helps teams align digital investment with clear operational goals.', 'status' => 'published', 'published_at' => now()->subDays(20), 'sort_order' => 1],
            ['name' => 'Daniel Cross', 'slug' => 'daniel-cross', 'designation' => 'Product Designer', 'short_bio' => 'Designs clarity into customer journeys, internal systems, and team rituals.', 'status' => 'published', 'published_at' => now()->subDays(30), 'sort_order' => 2],
            ['name' => 'Meera Patel', 'slug' => 'meera-patel', 'designation' => 'Delivery Manager', 'short_bio' => 'Moves work from ambiguity into focused execution with less friction.', 'status' => 'published', 'published_at' => now()->subDays(40), 'sort_order' => 3],
        ];

        foreach ($team as $member) {
            TeamMember::updateOrCreate(['slug' => $member['slug']], $member);
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'How long does a typical engagement take?', 'answer' => 'Typical local demo engagements vary, but the public pages are designed to show a flexible workflow model and clear delivery timing.', 'status' => 'published', 'published_at' => now()->subDays(5), 'sort_order' => 1],
            ['question' => 'Do you support existing systems and teams?', 'answer' => 'Yes, the content model is designed to show systems work that complements current tools, teams, and operational constraints.', 'status' => 'published', 'published_at' => now()->subDays(10), 'sort_order' => 2],
            ['question' => 'Can this be used for internal automation?', 'answer' => 'This demo content demonstrates the kinds of process improvements and operational clarity we often support in real working environments.', 'status' => 'published', 'published_at' => now()->subDays(15), 'sort_order' => 3],
            ['question' => 'Is the site content production-ready?', 'answer' => 'This is local placeholder content for testing layout and experience quality, not official business messaging or production claims.', 'status' => 'published', 'published_at' => now()->subDays(20), 'sort_order' => 4],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }
    }
}
