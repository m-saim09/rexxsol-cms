@php($settings = $section['settings'] ?? [])
@php($items = collect($section['items'] ?? [])->take(6))
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)
@if ($items->isNotEmpty())
    <section id="nova-expertise" class="nova-section nova-expertise" aria-labelledby="nova-expertise-title">
        <div class="nova-container">
            <header class="nova-section-heading nova-expertise-heading">
                <div>
                    <p class="nova-kicker">Capabilities</p>
                    <h2 id="nova-expertise-title">Digital solutions.<br>Built for your business.</h2>
                </div>
                <p>From better websites to smarter workflows,<br>we help your business move forward.</p>
            </header>
            <div class="nova-service-grid">
                @foreach ($items as $item)
                    @include('themes.rexxsol-nova.components.service-card', ['item' => $item, 'index' => $loop->iteration])
                @endforeach
            </div>
            <div class="nova-services-action">
                <a class="nova-button nova-button-primary" href="{{ $withPreview(route('website.services')) }}">Explore All Services <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
@endif