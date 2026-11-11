@php($settings = $section['settings'] ?? [])
@php($points = $settings['items'] ?? $settings['points'] ?? [])
@if (empty($points))
    @php($points = [
    ['title' => 'Understand your business', 'description' => 'We listen, learn and align with your goals.', 'icon' => 'people'],
    ['title' => 'Build with purpose', 'description' => 'Thoughtful solutions designed for real impact.', 'icon' => 'target'],
    ['title' => 'Support your next step', 'description' => 'Ongoing support as your business evolves.', 'icon' => 'heart'],
    ])
@endif
<section class="nova-section nova-about" aria-labelledby="nova-about-title">
    <div class="nova-container nova-about-grid">
        <div class="nova-dashboard" aria-hidden="true">
            @if (! empty($settings['about_image']))
                <img src="{{ $settings['about_image'] }}" alt="" loading="lazy">
            @else
                <div class="nova-dashboard-window">
                    <div class="nova-window-bar"><span></span><span></span><span></span><i>workspace</i></div>
                    <div class="nova-dashboard-layout">
                        <nav aria-hidden="true"><i class="fa-solid fa-house"></i><i class="fa-solid fa-table-cells-large"></i><i class="fa-solid fa-folder"></i><i class="fa-solid fa-chart-simple"></i><i class="fa-solid fa-user"></i></nav>
                        <div class="nova-dashboard-main">
                            <div class="nova-dashboard-title"><strong>Workspace overview</strong><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></div>
                            <div class="nova-dashboard-panels">
                                <div><i class="fa-regular fa-folder-open" aria-hidden="true"></i><span>Projects</span><b></b></div>
                                <div><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Tasks</span><b></b></div>
                                <div><i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Activity</span><b></b></div>
                            </div>
                            <div class="nova-dashboard-chart">
                                <strong>Progress over time</strong>
                                <div class="nova-dashboard-chart-plot" aria-hidden="true">
                                    <svg viewBox="0 0 320 100" preserveAspectRatio="none">
                                        <polyline points="0,78 34,68 62,72 92,48 122,52 151,37 182,44 214,19 245,28 278,5 320,11"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nova-dashboard-workflows">
                    <strong>Connected workflows</strong>
                    <span><i class="fa-solid fa-globe" aria-hidden="true"></i>Website <b></b></span>
                    <span><i class="fa-solid fa-database" aria-hidden="true"></i>CRM <b></b></span>
                    <span><i class="fa-solid fa-people-arrows-left-right" aria-hidden="true"></i>Team tools <b></b></span>
                </div>
                <div class="nova-dashboard-integrations"><i class="fa-solid fa-share-nodes" aria-hidden="true"></i><span>Integrations</span></div>
            @endif
        </div>
        <div class="nova-about-copy">
            <p class="nova-kicker">{{ $settings['eyebrow'] ?? 'Why Teams Choose Us' }}</p>
            <h2 id="nova-about-title">{{ $settings['heading'] ?? 'Built to simplify the work behind the work.' }}</h2>
            <p class="nova-about-description">{{ $settings['description'] ?? 'We turn complex business needs into practical digital solutions, with clarity at every step.' }}</p>
            <ul class="nova-about-points">
                @foreach ($points as $point)
                    @php($pointIcon = match ($point['icon'] ?? '') { 'target' => 'fa-solid fa-bullseye', 'heart' => 'fa-solid fa-heart', default => 'fa-solid fa-users' })
                    <li>
                        <span class="nova-icon-tile" aria-hidden="true"><i class="{{ $pointIcon }}"></i></span>
                        <span><strong>{{ $point['title'] ?? '' }}</strong><small>{{ $point['description'] ?? '' }}</small></span>
                    </li>
                @endforeach
            </ul>
            <a class="nova-text-link" href="{{ route('website.about') }}">{{ $settings['cta_label'] ?? 'About Rexxsol' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>