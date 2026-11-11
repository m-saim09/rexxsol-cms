@extends('themes.bizconsult.layouts.app')

@php
    $withPreview = fn (string $url): string => ($previewTheme ?? null)
        ? $url . (str_contains($url, '?') ? '&' : '?') . 'theme=' . urlencode($previewTheme)
        : $url;

    $portfolioFilters = [
        'all' => 'All',
        'web-development' => 'Web Development',
        'mobile-apps' => 'Mobile Apps',
        'software-solutions' => 'Software Solutions',
    ];

    $portfolioTestimonials = collect(($homepage ?? [])['testimonials']['items'] ?? []);
    $portfolioTestimonialRows = [
        $portfolioTestimonials->values()->filter(fn (array $item, int $index): bool => $index % 2 === 0)->values(),
        $portfolioTestimonials->values()->filter(fn (array $item, int $index): bool => $index % 2 !== 0)->values(),
    ];
    $portfolioItems = $projects->map(function ($project): array {
        $category = trim((string) ($project->category ?? 'Software Solutions'));
        $categoryKey = strtolower($category);
        $filter = match (true) {
            str_contains($categoryKey, 'web') || str_contains($categoryKey, 'commerce') => 'web-development',
            str_contains($categoryKey, 'mobile') || str_contains($categoryKey, 'app') => 'mobile-apps',
            str_contains($categoryKey, 'enterprise') || str_contains($categoryKey, 'integration') || str_contains($categoryKey, 'automation') => 'software-solutions',
            default => 'software-solutions',
        };

        $tags = is_array($project->technologies ?? null)
            ? array_values(array_filter(array_map('trim', $project->technologies)))
            : [];

        return [
            'category' => $category,
            'title' => $project->name,
            'description' => $project->short_description ?: $project->description,
            'filter' => $filter,
            'icon' => match ($filter) {
                'web-development' => 'fa-laptop-code',
                'mobile-apps' => 'fa-mobile-alt',
                default => 'fa-layer-group',
            },
            'tags' => $tags,
        ];
    });
@endphp

@push('styles')
<style>
    .rx-portfolio-page{overflow:hidden;color:#526477;background:#fff}
    .rx-portfolio-wrap{width:min(1180px,calc(100% - 40px));margin:0 auto}
    .rx-portfolio-hero{position:relative;padding:8.5rem 0 5.8rem;background:#f3f7fa}
    .rx-portfolio-hero:after{position:absolute;right:-10%;bottom:-1px;left:-10%;height:78px;content:'';background:#fff;clip-path:ellipse(62% 54% at 50% 100%)}
    .rx-portfolio-hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1.04fr .96fr;align-items:center;gap:56px}
    .rx-portfolio-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:18px;color:#00a47c;font-size:.74rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
    .rx-portfolio-eyebrow:before{width:28px;height:2px;content:'';background:#00b98e}
    .rx-portfolio-eyebrow--light{color:#fff}.rx-portfolio-eyebrow--light:before{background:#fff}
    .rx-portfolio-hero h1,.rx-portfolio-section-head h2,.rx-portfolio-testimonials-head h2{color:#103354;letter-spacing:-.06em}
    .rx-portfolio-hero h1{max-width:670px;margin:0 0 22px;font-size:clamp(2.7rem,4vw,4.1rem);line-height:1.03}
    .rx-portfolio-hero p{max-width:560px;margin:0 0 30px;color:#526477;font-size:1.05rem;line-height:1.72}
    .rx-portfolio-button,.rx-portfolio-link{display:inline-flex;align-items:center;gap:10px;font-weight:700;text-decoration:none}
    .rx-portfolio-button{min-height:50px;padding:13px 24px;border:1px solid #00b98e;border-radius:999px;background:#00b98e;color:#fff;transition:transform 250ms ease,background-color 250ms ease,border-color 250ms ease}
    .rx-portfolio-button:hover,.rx-portfolio-button:focus-visible{background:#0b2f3a;border-color:#0b2f3a;color:#fff;transform:translateY(-2px)}
    .rx-portfolio-arrow{transition:transform 250ms ease}.rx-portfolio-button:hover .rx-portfolio-arrow,.rx-portfolio-button:focus-visible .rx-portfolio-arrow{transform:translateX(4px)}
    .rx-portfolio-hero-art{position:relative;display:flex;align-items:center;justify-content:center;min-height:430px}
    .rx-portfolio-hero-art:before{position:absolute;top:7%;right:4%;width:76%;height:76%;content:'';border-radius:50%;background:#d9f3e9}
    .rx-portfolio-hero-art img{position:relative;z-index:1;display:block;width:100%;max-height:470px;object-fit:contain;filter:drop-shadow(0 20px 28px rgba(16,51,84,.12))}
    .rx-portfolio-section{padding:96px 0}.rx-portfolio-section--soft{background:#f5f8fb}
    .rx-portfolio-section-head{display:grid;grid-template-columns:1.15fr .85fr;gap:28px 32px;align-items:end;margin-bottom:36px}
    .rx-portfolio-section-head h2,.rx-portfolio-testimonials-head h2{margin:0;font-size:clamp(2.15rem,3.2vw,3.15rem);line-height:1.06}
    .rx-portfolio-section-head p,.rx-portfolio-testimonials-head p{max-width:520px;margin:0;color:#526477;line-height:1.7}
    .rx-portfolio-filters{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:32px}
    .rx-portfolio-filter{appearance:none;border:1px solid #dfe7ec;border-radius:999px;background:#f7f9fa;color:#1c3552;cursor:pointer;font-size:.86rem;font-weight:600;line-height:1;padding:9px 14px;transition:background-color 250ms ease,border-color 250ms ease,color 250ms ease,transform 250ms ease}
    .rx-portfolio-filter:hover{border-color:#c5e2d8;background:#effaf6;color:#103354}
    .rx-portfolio-filter:focus-visible{border-color:#00a47c;outline:3px solid rgba(0,185,142,.2);outline-offset:2px}
    .rx-portfolio-filter.is-active{border-color:#00b98e;background:#00b98e;color:#fff}
    .rx-portfolio-filter.is-active:hover{border-color:#00a47c;background:#00a47c;color:#fff}
    .rx-portfolio-item.is-filter-hidden{opacity:0;transform:translateY(6px);pointer-events:none}
    .rx-portfolio-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:28px}
    .rx-portfolio-item{position:relative;display:flex;flex-direction:column;min-height:330px;padding:28px 28px 24px;border:1px solid rgba(16,51,84,.08);border-radius:20px;background:#fff;box-shadow:0 10px 22px rgba(16,51,84,.03);transition:transform 250ms ease,border-color 250ms ease,box-shadow 250ms ease}
    .rx-portfolio-item:hover,.rx-portfolio-item:focus-within{border-color:rgba(0,185,142,.35);box-shadow:0 18px 26px rgba(16,51,84,.06);transform:translateY(-3px)}
    .rx-portfolio-project-number{position:absolute;top:18px;right:22px;color:rgba(16,51,84,.06);font-size:clamp(3.2rem,5vw,5.6rem);font-weight:800;line-height:1;letter-spacing:-.08em;pointer-events:none;transition:color 250ms ease,opacity 250ms ease}
    .rx-portfolio-item:hover .rx-portfolio-project-number,.rx-portfolio-item:focus-within .rx-portfolio-project-number{color:rgba(0,185,142,.12)}
    .rx-portfolio-project-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;padding-right:62px}
    .rx-portfolio-project-icon{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border:1px solid rgba(0,185,142,.18);border-radius:12px;background:#edf9f5;color:#00a47c;transition:transform 250ms ease}
    .rx-portfolio-item:hover .rx-portfolio-project-icon,.rx-portfolio-item:focus-within .rx-portfolio-project-icon{transform:translateY(-2px)}
    .rx-portfolio-copy{display:flex;flex:1;flex-direction:column;position:relative;z-index:1}
    .rx-portfolio-category{display:block;margin-bottom:12px;color:#00a47c;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
    .rx-portfolio-copy h3{margin:0 0 14px;color:#103354;font-size:clamp(1.8rem,2.2vw,2.6rem);line-height:1.1;letter-spacing:-.06em}
    .rx-portfolio-copy p{margin:0;color:#526477;line-height:1.72;max-width:44ch}
    .rx-portfolio-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:auto;padding-top:18px}
    .rx-portfolio-tag{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#f4f7fa;color:#445d73;font-size:.74rem;font-weight:600;letter-spacing:.02em}
    .rx-portfolio-service-columns{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0}
    .rx-portfolio-service{position:relative;padding:8px 32px 8px 0}
    .rx-portfolio-service + .rx-portfolio-service{padding-left:32px}
    .rx-portfolio-service + .rx-portfolio-service{border-left:1px solid rgba(16,51,84,.08)}
    .rx-portfolio-service i{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;margin-bottom:18px;border:1px solid rgba(0,185,142,.16);border-radius:12px;background:#edf9f5;color:#00a47c;font-size:1.2rem}
    .rx-portfolio-service h3{margin:0 0 12px;color:#103354;font-size:1.4rem;line-height:1.3;letter-spacing:-.04em}
    .rx-portfolio-service p{margin:0;color:#526477;line-height:1.72}
    .rx-portfolio-testimonials{padding:28px 0 96px;background:#fafafa}
    .rx-portfolio-testimonials-head{display:grid;grid-template-columns:1.2fr .8fr;gap:18px 32px;align-items:end;margin-bottom:36px}
    .rx-portfolio-marquee{display:grid;gap:20px}
    .rx-portfolio-row{position:relative;overflow:hidden;border-radius:18px}
    .rx-portfolio-track{display:flex;gap:18px;width:max-content;padding:4px 0;animation-duration:42s;animation-timing-function:linear;animation-iteration-count:infinite}
    .rx-portfolio-row:hover .rx-portfolio-track{animation-play-state:paused}
    .rx-portfolio-row:not(.is-right) .rx-portfolio-track{animation-name:rx-portfolio-marquee-left}.rx-portfolio-row.is-right .rx-portfolio-track{animation-name:rx-portfolio-marquee-right}
    .rx-portfolio-fade{position:absolute;z-index:2;top:0;bottom:0;width:90px;pointer-events:none}
    .rx-portfolio-fade.left{left:0;background:linear-gradient(to right,#fafafa,rgba(250,250,250,0))}
    .rx-portfolio-fade.right{right:0;background:linear-gradient(to left,#fafafa,rgba(250,250,250,0))}
    .rx-portfolio-card{display:flex;flex:0 0 auto;width:clamp(300px,28vw,360px);min-height:220px;flex-direction:column;justify-content:space-between;padding:22px 22px 20px;border:1px solid rgba(16,51,84,.08);border-radius:18px;background:#fff;box-shadow:0 10px 20px rgba(16,51,84,.04)}
    .rx-portfolio-quote{margin-bottom:12px;color:#00b98e;font-size:2rem;font-weight:800;line-height:1}
    .rx-portfolio-card blockquote{margin:0;color:#103354;font-size:1.05rem;line-height:1.6;font-weight:500}
    .rx-portfolio-meta{display:flex;align-items:center;gap:12px;margin-top:18px;padding-top:14px;border-top:1px solid rgba(16,51,84,.08)}
    .rx-portfolio-avatar{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;background:#eafaf5;color:#007a5d;font-size:.78rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
    .rx-portfolio-meta div{display:flex;flex-direction:column;min-width:0}
    .rx-portfolio-meta strong{color:#103354;font-size:.96rem}.rx-portfolio-meta span{color:#607489;font-size:.8rem}
    @keyframes rx-portfolio-marquee-left{from{transform:translateX(0)}to{transform:translateX(-50%)}}
    @keyframes rx-portfolio-marquee-right{from{transform:translateX(-50%)}to{transform:translateX(0)}}
    @media (max-width:991.98px){.rx-portfolio-hero{padding:7.8rem 0 5.5rem}.rx-portfolio-hero-grid,.rx-portfolio-section-head,.rx-portfolio-testimonials-head{grid-template-columns:1fr;gap:36px}.rx-portfolio-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rx-portfolio-service-columns{grid-template-columns:1fr;gap:0}.rx-portfolio-service,.rx-portfolio-service + .rx-portfolio-service{padding:24px 0}.rx-portfolio-service + .rx-portfolio-service{border-left:0;border-top:1px solid rgba(16,51,84,.08)}}
    @media (max-width:767.98px){.rx-portfolio-wrap{width:min(calc(100% - 32px),560px)}.rx-portfolio-hero{padding:7rem 0 4.5rem}.rx-portfolio-hero h1{font-size:clamp(2.35rem,11vw,3.25rem)}.rx-portfolio-hero-art{min-height:280px}.rx-portfolio-section{padding:68px 0}.rx-portfolio-section-head,.rx-portfolio-testimonials-head{margin-bottom:28px}.rx-portfolio-filters{gap:8px;margin-bottom:28px}.rx-portfolio-filter{padding:8px 13px;font-size:.84rem}.rx-portfolio-grid{grid-template-columns:1fr;gap:20px}.rx-portfolio-card{width:min(82vw,320px);min-height:200px;padding:18px}.rx-portfolio-fade{width:54px}}
    @media (prefers-reduced-motion: reduce){.rx-portfolio-item,.rx-portfolio-button,.rx-portfolio-filter,.rx-portfolio-track{transition:none!important;animation:none!important}}
</style>
@endpush

@section('content')
<div class="rx-portfolio-page">
    <section class="rx-portfolio-hero">
        <div class="rx-portfolio-wrap rx-portfolio-hero-grid">
            <div>
                <div class="rx-portfolio-eyebrow">{{ $page?->hero_eyebrow ?: 'Our portfolio' }}</div>
                <h1>{{ $page?->hero_heading ?: 'Empowering Your Digital Future' }}</h1>
                <p>{{ $page?->hero_description ?: 'Explore our portfolio to see how Rexxsol transforms ideas into practical digital solutions that help businesses work smarter and grow with confidence.' }}</p>
                <a class="rx-portfolio-button" href="{{ $withPreview(route('website.contact')) }}">
                    Start a project
                    <i class="fa fa-arrow-right rx-portfolio-arrow" aria-hidden="true"></i>
                </a>
            </div>

            <div class="rx-portfolio-hero-art">
                <img src="{{ asset('assets-bizconsult/img/portfolio-hero.png') }}" alt="Rexxsol digital product and software portfolio preview">
            </div>
        </div>
    </section>

    <section class="rx-portfolio-section" id="portfolio-showcase">
        <div class="rx-portfolio-wrap">
            <div class="rx-portfolio-section-head">
                <div>
                    <div class="rx-portfolio-eyebrow">Selected work</div>
                    <h2>Solutions shaped by years of practical experience.</h2>
                </div>
                <p>A selection of digital platforms, business systems and applications delivered across evolving technologies and business needs.</p>
            </div>

            <div class="rx-portfolio-filters" role="tablist" aria-label="Portfolio categories">
                @foreach ($portfolioFilters as $filter => $label)
                    <button type="button" class="rx-portfolio-filter {{ $loop->first ? 'is-active' : '' }}" data-filter="{{ $filter }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if ($portfolioItems->isNotEmpty())
                <div class="rx-portfolio-grid" data-portfolio-grid>
                    @foreach ($portfolioItems as $index => $item)
                        <article class="rx-portfolio-item" data-portfolio-filter="{{ $item['filter'] }}">
                            <div class="rx-portfolio-project-number" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="rx-portfolio-project-head">
                                <span class="rx-portfolio-project-icon"><i class="fa {{ $item['icon'] }}" aria-hidden="true"></i></span>
                            </div>
                            <div class="rx-portfolio-copy">
                                <span class="rx-portfolio-category">{{ $item['category'] }}</span>
                                <h3>{{ $item['title'] }}</h3>
                                @if (! empty($item['description']))
                                    <p>{{ Str::limit(strip_tags($item['description']), 160) }}</p>
                                @endif

                                @if (! empty($item['tags']))
                                    <div class="rx-portfolio-tags">
                                        @foreach (array_slice($item['tags'], 0, 4) as $tag)
                                            <span class="rx-portfolio-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="rx-portfolio-section rx-portfolio-section--soft">
        <div class="rx-portfolio-wrap">
            <div class="rx-portfolio-section-head">
                <div>
                    <div class="rx-portfolio-eyebrow">Service overview</div>
                    <h2>Services behind our work.</h2>
                </div>
                <p>Our core capabilities support practical digital delivery, from responsive web platforms to connected software and business workflows.</p>
            </div>

            <div class="rx-portfolio-service-columns">
                @foreach ([
                    ['fa-laptop-code', 'Custom Web Development', 'Responsive websites and digital experiences built around business goals, user needs, and long-term growth.'],
                    ['fa-mobile-alt', 'Mobile App Development', 'Intuitive mobile applications and connected digital experiences designed around the needs of modern users and teams.'],
                    ['fa-project-diagram', 'Custom Software & Integration', 'Tailored business systems, automation, API integrations, and connected workflows that support dependable operations.'],
                ] as $service)
                    <article class="rx-portfolio-service">
                        <i class="fa {{ $service[0] }}" aria-hidden="true"></i>
                        <h3>{{ $service[1] }}</h3>
                        <p>{{ $service[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @if ($portfolioTestimonials->isNotEmpty())
        <section class="rx-portfolio-testimonials">
            <div class="rx-portfolio-wrap">
                <div class="rx-portfolio-testimonials-head">
                    <div>
                        <div class="rx-portfolio-eyebrow">Client feedback</div>
                        <h2>What delivery teams say</h2>
                    </div>
                    <p>Real feedback from teams we have supported through product delivery, system modernization, and business-critical digital work.</p>
                </div>

                <div class="rx-portfolio-marquee">
                    @foreach ($portfolioTestimonialRows as $rowIndex => $rowItems)
                        <div class="rx-portfolio-row {{ $rowIndex ? 'is-right' : '' }}">
                            <div class="rx-portfolio-fade left"></div>
                            <div class="rx-portfolio-fade right"></div>
                            <div class="rx-portfolio-track">
                                @foreach ($rowItems->concat($rowItems) as $item)
                                    @php
                                        $name = trim((string) ($item['title'] ?? ''));
                                        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                        $initials = collect($parts)->take(2)->map(fn (string $part): string => mb_substr(mb_strtoupper($part), 0, 1))->implode('');
                                    @endphp
                                    <article class="rx-portfolio-card">
                                        <div>
                                            <div class="rx-portfolio-quote" aria-hidden="true">“</div>
                                            <blockquote>{{ $item['quote'] ?? $item['description'] ?? '' }}</blockquote>
                                        </div>
                                        <footer class="rx-portfolio-meta">
                                            <span class="rx-portfolio-avatar">{{ $initials ?: 'R' }}</span>
                                            <div>
                                                <strong>{{ $name ?: 'Rexxsol client' }}</strong>
                                                <span>{{ $item['designation'] ?? 'Business partner' }}{{ ! empty($item['company']) ? ' · ' . $item['company'] : '' }}</span>
                                            </div>
                                        </footer>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filters = document.querySelectorAll('.rx-portfolio-filter');
        const items = document.querySelectorAll('[data-portfolio-filter]');
        const filterTimers = new WeakMap();

        filters.forEach(function (filter) {
            filter.addEventListener('click', function () {
                const value = this.dataset.filter;

                filters.forEach(function (item) {
                    const isActive = item === filter;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                items.forEach(function (item) {
                    const show = value === 'all' || item.dataset.portfolioFilter === value;

                    clearTimeout(filterTimers.get(item));

                    if (show) {
                        item.hidden = false;
                        requestAnimationFrame(function () {
                            item.classList.remove('is-filter-hidden');
                        });
                        return;
                    }

                    item.classList.add('is-filter-hidden');
                    filterTimers.set(item, setTimeout(function () {
                        item.hidden = true;
                    }, 250));
                });
            });
        });
    });
</script>
@endsection
