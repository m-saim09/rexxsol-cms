@php($withPreview = static function (string $url): string { return ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url; })
<section @class(['nova-page', 'nova-inner-hero' => in_array($viewType, ['projects', 'service-detail', 'project-detail', 'blog-detail'], true)])>
    <div class="nova-container">
        @switch($viewType)
            @case('about')
                @php($body = $page?->content ? \App\Support\RichText::clean($page->content) : '')
                <header class="nova-page-heading"><p class="nova-page-kicker">About Rexxsol</p><h1>{{ $page?->title ?? 'About Rexxsol' }}</h1>@if ($page?->excerpt)<p>{{ $page->excerpt }}</p>@endif</header>
                <article class="nova-content-panel">
                    @if ($page?->featured_image)<img class="nova-page-featured" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image) }}" alt="{{ $page->title }}">@endif
                    @if ($body !== '')<div class="nova-prose">{!! $body !!}</div>@else<p>We help organizations move from complexity to clarity with thoughtful digital strategy, product delivery, and operations enablement.</p>@endif
                    @if (($teamMembers ?? collect())->isNotEmpty())
                        <div class="nova-team-grid">
                            @foreach ($teamMembers as $member)
                                <article><h2>{{ $member->name }}</h2>@if ($member->designation)<p>{{ $member->designation }}</p>@endif</article>
                            @endforeach
                        </div>
                    @endif
                </article>
                @break

            @case('services')
                <header class="nova-page-heading"><p class="nova-page-kicker">What we do</p><h1>Digital capabilities, built around your business.</h1><p>Thoughtful technology services for teams ready to move forward.</p></header>
                <div class="nova-record-grid">
                    @forelse ($items as $item)
                        <article class="nova-record-card"><span class="nova-record-icon" aria-hidden="true"><i class="fa-solid fa-laptop-code"></i></span><h2>{{ $item->name }}</h2>@if ($item->short_description)<p>{{ $item->short_description }}</p>@endif<a href="{{ $withPreview(route('website.service', $item->slug)) }}">Explore service <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></article>
                    @empty
                        <p class="nova-empty-state">Services will appear here as they are published.</p>
                    @endforelse
                </div>
                @break

            @case('service-detail')
                <nav class="nova-breadcrumb" aria-label="Breadcrumb"><a href="{{ $withPreview(route('website.services')) }}">Services</a><span aria-hidden="true">/</span><span aria-current="page">{{ $item->name }}</span></nav>
                <article class="nova-content-panel nova-detail-panel"><p class="nova-page-kicker">Service</p><h1>{{ $item->name }}</h1>@if ($item->featured_image)<img class="nova-page-featured" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->name }}">@endif @if ($item->short_description)<p class="nova-detail-lead">{{ $item->short_description }}</p>@endif @if ($item->description)<div class="nova-prose">{!! \App\Support\RichText::clean($item->description) !!}</div>@endif<a class="nova-button nova-button-primary" href="{{ $withPreview(route('website.contact')) }}">Talk to our team <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></article>
                @break

            @case('projects')
                <header class="nova-page-heading"><p class="nova-page-kicker">Selected work</p><h1>Projects made for what comes next.</h1><p>Explore digital products and platforms shaped with our clients.</p></header>
                <div class="nova-record-grid nova-project-grid">
                    @forelse (($projects ?? $items ?? []) as $item)
                        <article class="nova-record-card nova-project-card">@if ($item->featured_image)<img class="nova-record-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->name }}">@else<div class="nova-record-image nova-record-image-empty" aria-hidden="true"><span>R</span></div>@endif<p class="nova-page-kicker">{{ $item->category ?? 'Project' }}</p><h2>{{ $item->name }}</h2>@if ($item->short_description)<p>{{ $item->short_description }}</p>@endif<a href="{{ $withPreview(route('website.project', $item->slug)) }}">View project <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></article>
                    @empty
                        <p class="nova-empty-state">Published projects will appear here.</p>
                    @endforelse
                </div>
                @break

            @case('project-detail')
                <nav class="nova-breadcrumb" aria-label="Breadcrumb"><a href="{{ $withPreview(route('website.portfolio')) }}">Portfolio</a><span aria-hidden="true">/</span><span aria-current="page">{{ $item->name }}</span></nav>
                <article class="nova-content-panel nova-detail-panel"><p class="nova-page-kicker">{{ $item->category ?? 'Project' }}</p><h1>{{ $item->name }}</h1>@if ($item->featured_image)<img class="nova-page-featured" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->name }}">@endif @if ($item->short_description)<p class="nova-detail-lead">{{ $item->short_description }}</p>@endif @if ($item->description)<div class="nova-prose">{!! \App\Support\RichText::clean($item->description) !!}</div>@endif @if ($item->project_url)<a class="nova-button nova-button-primary" href="{{ $item->project_url }}" target="_blank" rel="noopener noreferrer">Visit project <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>@endif</article>
                @break

            @case('blog')
                <header class="nova-page-heading"><p class="nova-page-kicker">Ideas and insights</p><h1>Perspectives for digital growth.</h1><p>Practical thinking on technology, products, and better ways of working.</p></header>
                <form class="nova-blog-filters" method="GET" action="{{ route('website.blog') }}">
                    @if ($previewTheme ?? false)<input type="hidden" name="theme" value="{{ $previewTheme }}">@endif
                    <label><span class="sr-only">Search articles</span><input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Search articles"></label>
                    @if (($categories ?? collect())->isNotEmpty())<label><span class="sr-only">Filter by category</span><select name="category"><option value="">All topics</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected(($selectedCategory?->slug ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>@endif
                    <button class="nova-button nova-button-secondary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i>Filter</button>
                </form>
                <div class="nova-record-grid nova-blog-grid">
                    @forelse ($items as $item)
                        <article class="nova-record-card">@if ($item->featured_image)<img class="nova-record-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->title }}">@else<div class="nova-record-image nova-record-image-empty" aria-hidden="true"><span>R</span></div>@endif<p class="nova-page-kicker">{{ $item->published_at ? $item->published_at->format('M d, Y') : 'Published' }}</p><h2>{{ $item->title }}</h2>@if ($item->excerpt)<p>{{ $item->excerpt }}</p>@endif<a href="{{ $withPreview(route('website.blog.post', $item->slug)) }}">Read article <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></article>
                    @empty
                        <p class="nova-empty-state">No published articles match this selection.</p>
                    @endforelse
                </div>
                @if (method_exists($items, 'links'))<div class="nova-pagination">{{ $items->links() }}</div>@endif
                @break

            @case('blog-detail')
                <nav class="nova-breadcrumb" aria-label="Breadcrumb"><a href="{{ $withPreview(route('website.blog')) }}">Insights</a><span aria-hidden="true">/</span><span aria-current="page">{{ $item->title }}</span></nav>
                <article class="nova-content-panel nova-detail-panel">@if ($item->featured_image)<img class="nova-page-featured" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->title }}">@endif<p class="nova-page-kicker">{{ $item->published_at ? $item->published_at->format('M d, Y') : 'Published' }}</p><h1>{{ $item->title }}</h1>@if ($item->excerpt)<p class="nova-detail-lead">{{ $item->excerpt }}</p>@endif @if ($item->content)<div class="nova-prose">{!! \App\Support\RichText::clean($item->content) !!}</div>@endif</article>
                @break

            @case('contact')
                <header class="nova-page-heading"><p class="nova-page-kicker">Let's build something meaningful</p><h1>Tell us what you have in mind.</h1><p>Share a little about your project and our team will be in touch.</p></header>
                <div class="nova-contact-layout">
                    <form class="nova-contact-form" method="POST" action="{{ route('website.contact.store') }}">
                        @csrf
                        @if (session('success'))<p class="nova-form-success" role="status">{{ session('success') }}</p>@endif
                        <div class="nova-form-row"><label>Name<input name="name" value="{{ old('name') }}" autocomplete="name" required>@error('name')<span>{{ $message }}</span>@enderror</label><label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<span>{{ $message }}</span>@enderror</label></div>
                        <div class="nova-form-row"><label>Company<input name="company" value="{{ old('company') }}" autocomplete="organization"></label><label>Phone<input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label></div>
                        <label>What can we help with?<select name="service" required><option value="">Choose a service</option>@foreach (($contactServices ?? []) as $service)<option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>@endforeach</select>@error('service')<span>{{ $message }}</span>@enderror</label>
                        <label>Project details<textarea name="message" rows="6" minlength="10" required>{{ old('message') }}</textarea>@error('message')<span>{{ $message }}</span>@enderror</label>
                        <label class="nova-honeypot" aria-hidden="true">Website URL<input name="website_url" tabindex="-1" autocomplete="off"></label>
                        <button class="nova-button nova-button-primary" type="submit">Send enquiry <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
                    </form>
                    <aside class="nova-contact-details">
                        @foreach (($contactDetails ?? []) as $detail)<a href="{{ $detail['href'] }}" @if ($detail['target'] ?? false) target="{{ $detail['target'] }}" rel="noopener noreferrer" @endif><span>{{ $detail['label'] }}</span><strong>{{ $detail['value'] }}</strong></a>@endforeach
                    </aside>
                </div>
                @break
        @endswitch
    </div>
</section>