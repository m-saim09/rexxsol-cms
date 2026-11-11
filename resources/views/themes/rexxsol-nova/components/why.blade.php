@php($settings = $section['settings'] ?? [])
@php($items = $settings['items'] ?? [])
@if (empty($items))
    @php($items = [
        ['title' => 'Strategic clarity', 'description' => 'Turn complex requirements into a clear roadmap and confident decisions.'],
        ['title' => 'Hands-on execution', 'description' => 'Stay involved through clear communication and steady progress.'],
        ['title' => 'Operational focus', 'description' => 'Solve everyday bottlenecks with tools built around your team.'],
    ])
@endif
<section class="nova-section nova-why" aria-labelledby="nova-why-title">
    <div class="nova-container nova-why-layout">
        <div class="nova-why-intro">
            <p class="nova-kicker">{{ filled($settings['eyebrow'] ?? null) ? $settings['eyebrow'] : 'Why Choose Rexxsol' }}</p>
            <h2 id="nova-why-title">{{ filled($settings['heading'] ?? null) ? $settings['heading'] : "Clear thinking.\nPractical\ndelivery." }}</h2>
            <p>{{ filled($settings['description'] ?? null) ? $settings['description'] : 'A focused approach to building what your business needs.' }}</p>
            <span class="nova-why-accent" aria-hidden="true"></span>
        </div>
        <ol class="nova-why-list">
            @foreach ($items as $index => $item)
                <li class="nova-why-row nova-why-row--{{ ['blue', 'teal', 'green'][$index % 3] }}">
                    <span class="nova-why-number" aria-hidden="true">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h3>{{ $item['title'] ?? '' }}</h3>
                        <p>{{ $item['description'] ?? '' }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
