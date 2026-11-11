@extends('themes.bizconsult.layouts.app')
@php($hero = $homepage['hero']['settings'] ?? [])
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)
@php($servicesSettings = $homepage['services']['settings'] ?? [])
@php($serviceIconMap = ['web' => 'fa fa-window-maximize', 'mobile' => 'fa fa-mobile-alt', 'integration' => 'fa fa-project-diagram'])
@section('content')
<section class="container-xxl bg-primary hero-header"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-6 text-center text-lg-start"><h1 class="text-white mb-4 animated zoomIn">{{ $hero['headline'] ?? 'We help move your business to the next level' }}</h1><p class="text-white pb-3 animated zoomIn">{{ $hero['description'] ?? ($site['site.description'] ?? 'Practical consulting and digital services for ambitious teams.') }}</p><a href="{{ $hero['primary_cta_url'] ?? route('website.services') }}" class="bizconsult-hero-cta animated slideInRight"><span>{{ $hero['primary_cta_label'] ?? 'Explore services' }}</span><i class="fa fa-arrow-right" aria-hidden="true"></i></a></div><div class="col-lg-6 text-center text-lg-start"><img class="img-fluid animated zoomIn" src="{{ $hero['hero_image'] ?? asset('assets-bizconsult/img/hero.png') }}" alt="{{ $hero['hero_image_alt'] ?? 'Business consulting illustration' }}"></div></div></div></section>
@if (!empty($homepage['about']))<section class="container-xxl py-6"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-6 wow zoomIn"><img class="img-fluid" src="{{ $homepage['about']['settings']['about_image'] ?? asset('assets-bizconsult/img/about.png') }}" alt="About {{ $site['site.name'] ?? 'our company' }}"></div><div class="col-lg-6 wow fadeInUp"><div class="d-inline-block border rounded-pill text-primary px-4 mb-3">About Us</div><h2 class="mb-4">Technology built around your business.</h2><p class="mb-4">At Rexxsol, we build websites, software, and digital tools around the way your business works. We take time to understand your goals, simplify everyday processes, and create solutions that support your next step.</p><a class="btn btn-primary rounded-pill py-3 px-5 mt-2 bizconsult-about-button" href="{{ route('website.about') }}" style="position: relative; display: inline-flex; align-items: center; gap: 8px; overflow: hidden; isolation: isolate; background: #00B98E; border-color: #00B98E; color: #ffffff; transition: transform 280ms ease-out, box-shadow 280ms ease-out, background-color 280ms ease-out;"><span style="position: relative; z-index: 1;">About Rexxsol</span><i class="fa fa-arrow-right ms-2" aria-hidden="true" style="position: relative; z-index: 1; transition: transform 280ms ease-out;"></i></a></div></div></div></section>@endif
@if (!empty($homepage['services']))<section class="bizconsult-services" style="background: #F3F7FA; padding: 88px 0;"><div class="container"><div class="bizconsult-services-header" style="display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(280px, .9fr); align-items: end; gap: 40px; margin-bottom: 42px;"><div><div style="display: inline-block; margin-bottom: 14px; color: #00A47C; font-size: .8rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">{{ $servicesSettings['eyebrow'] ?? 'Our Services' }}</div><h2 style="margin: 0; color: #103354; font-family: inherit; font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; line-height: 1.1;">{{ $servicesSettings['headline'] ?? 'Services that create momentum' }}</h2></div><div style="display: flex; flex-direction: column; align-items: flex-start; gap: 20px;"><p style="max-width: 510px; margin: 0; color: #607080; font-size: 1rem; line-height: 1.65;">{{ $servicesSettings['description'] ?? 'Practical digital services built around the way your business works.' }}</p><a href="{{ $withPreview(route('website.services')) }}" class="bizconsult-services-outline-link" style="display: inline-flex; align-items: center; gap: 9px; min-height: 44px; padding: 10px 18px; border: 1px solid #00A47C; border-radius: 999px; color: #007F69; font-weight: 700; text-decoration: none;">Explore all services <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div></div>@if (!empty($homepage['services']['items']))<div class="bizconsult-services-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px;">@foreach ($homepage['services']['items'] as $serviceIndex => $item)@php($serviceIcon = $serviceIconMap[$item['icon'] ?? ''] ?? 'fa fa-compass')<article class="bizconsult-service-card" style="display: flex; min-width: 0; flex-direction: column; min-height: 100%; padding: 30px 28px 26px; border: 1px solid #DCE6EC; border-radius: 12px; background: #ffffff; box-shadow: 0 8px 22px rgba(16, 51, 84, .05); transition: transform 220ms ease-out, border-color 220ms ease-out, box-shadow 220ms ease-out;"><div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 26px;"><span style="display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 10px; background: #E4F7F1; color: #008F78; font-size: 1.15rem;"><i class="{{ $serviceIcon }}" aria-hidden="true"></i></span><span style="color: #B6C5CF; font-size: .82rem; font-weight: 700; letter-spacing: .08em;">{{ str_pad((string) ($serviceIndex + 1), 2, '0', STR_PAD_LEFT) }}</span></div><h3 style="margin: 0 0 12px; color: #103354; font-family: inherit; font-size: 1.15rem; font-weight: 800; line-height: 1.3;">{{ $item['title'] }}</h3><p style="margin: 0; color: #607080; line-height: 1.6;">{{ $item['description'] ?? '' }}</p><div style="height: 1px; margin: 24px 0 18px; background: #E5EDF1;"></div><a href="{{ $withPreview(route('website.service', $item['slug'])) }}" class="bizconsult-service-link" style="display: inline-flex; align-items: center; align-self: flex-start; gap: 9px; margin-top: auto; color: #007F69; font-size: .9rem; font-weight: 700; text-decoration: none;">Explore service <span aria-hidden="true" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border: 1px solid #B9E8DC; border-radius: 50%; transition: transform 220ms ease-out;"><i class="fa fa-arrow-right" aria-hidden="true" style="font-size: .7rem;"></i></span></a></article>@endforeach</div>@else<div style="padding: 28px; border: 1px dashed #C8D7DF; border-radius: 12px; background: #ffffff; color: #607080;">Services will appear here when published.</div>@endif</div></section>@endif
@if (!empty($homepage['testimonials']['items']))
    @php($testimonialSettings = $homepage['testimonials']['settings'] ?? [])
    @php($testimonialEyebrow = $testimonialSettings['eyebrow'] ?? 'Client feedback')
    @php($testimonialHeading = $testimonialSettings['heading'] ?? 'What delivery teams say')
    @php($testimonialDescription = $testimonialSettings['description'] ?? 'A closer look at the experience of working with us.')
    @php($testimonialItems = collect($homepage['testimonials']['items'] ?? []))
    @php($fullItems = $testimonialItems->values()->all())
    @php($firstRow = [])
    @php($secondRow = [])
    @foreach ($fullItems as $index => $item)
        @if ($index % 2 === 0)
            @php($firstRow[] = $item)
        @else
            @php($secondRow[] = $item)
        @endif
    @endforeach
    @php($padRow = function (array $items, array $source): array {
        $minimum = 4;
        if (count($items) >= $minimum || $source === []) {
            return $items;
        }

        $padded = $items;
        $cursor = 0;
        while (count($padded) < $minimum) {
            $padded[] = $source[$cursor % count($source)];
            $cursor++;
        }

        return $padded;
    })
    @php($firstRow = $padRow($firstRow, $fullItems))
    @php($secondRow = $padRow($secondRow, $fullItems))
    @php($renderRow = function ($items): array { return array_merge($items, $items); })

    <section class="bizconsult-testimonials" style="background: #FAFAFA; padding: 88px 0; margin: 0; overflow: hidden;">
        <div class="container" style="max-width: 1200px; position: relative; z-index: 1;">
            <div class="bizconsult-testimonials-header" style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(280px, .9fr); align-items: end; gap: 18px 32px; margin-bottom: 42px;">
                <div>
                    <div style="display: inline-block; margin-bottom: 12px; color: #103354; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;">{{ mb_strtoupper($testimonialEyebrow) }}</div>
                    <h2 style="margin: 0; color: #103354; font-size: clamp(2.2rem, 4vw, 4rem); line-height: 1.02; font-weight: 800; letter-spacing: -0.04em;">{{ $testimonialHeading }}</h2>
                </div>
                <p style="margin: 0; max-width: 500px; color: #526477; font-size: 1.08rem; line-height: 1.6;">{{ $testimonialDescription }}</p>
            </div>

            @if (!empty($firstRow) || !empty($secondRow))
                <div class="bizconsult-marquee-wrap" style="display: grid; gap: 20px;">
                    @foreach ([[ 'items' => $firstRow, 'direction' => 'to-left' ], [ 'items' => $secondRow, 'direction' => 'to-right' ]] as $row)
                        @php($marqueeItems = $row['items'])
                        @if (!empty($marqueeItems))
                            @php($duplicated = $renderRow($marqueeItems))
                            <div class="bizconsult-marquee-row bizconsult-marquee-{{ $row['direction'] }}" aria-label="Client testimonials" style="position: relative; overflow: hidden; border-radius: 18px;">
                                <div class="bizconsult-marquee-fade bizconsult-marquee-fade-left" aria-hidden="true"></div>
                                <div class="bizconsult-marquee-fade bizconsult-marquee-fade-right" aria-hidden="true"></div>
                                <div class="bizconsult-marquee-track" style="display: flex; width: max-content; gap: 20px; padding: 4px 0;">
                                    @foreach ($duplicated as $item)
                                        @php($quoteText = trim((string) ($item['quote'] ?? $item['description'] ?? '')))
                                        @php($authorName = trim((string) ($item['title'] ?? '')))
                                        @php($companyName = trim((string) ($item['company'] ?? '')))
                                        @php($roleName = trim((string) ($item['designation'] ?? '')))
                                        @php($authorParts = preg_split('/\s+/', $authorName, -1, PREG_SPLIT_NO_EMPTY) ?: [])
                                        @php($authorInitials = collect($authorParts)->take(2)->map(fn (string $part): string => mb_substr(mb_strtoupper($part), 0, 1))->implode(''))
                                        @php($authorInitials = $authorInitials !== '' ? $authorInitials : 'NS')
                                        @php($authorDetail = trim(implode(' · ', array_filter([$roleName, $companyName], fn (string $value): bool => $value !== ''))))
                                        <article class="bizconsult-testimonial-card" style="flex: 0 0 auto; display: flex; flex-direction: column; justify-content: space-between; width: clamp(260px, 26vw, 360px); min-height: 220px; margin: 0; padding: 22px 22px 18px; border: 1px solid rgba(16, 51, 84, 0.12); border-radius: 16px; background: #ffffff; box-shadow: 0 10px 20px rgba(16, 51, 84, 0.05);">
                                            <div>
                                                <div aria-hidden="true" style="margin-bottom: 12px; color: #00B98E; font-size: 1.8rem; font-weight: 800; line-height: 1;">“</div>
                                                <p style="margin: 0; color: #103354; font-size: 1.05rem; line-height: 1.6; font-weight: 500;">{{ $quoteText }}</p>
                                            </div>
                                            <footer style="display: flex; align-items: center; gap: 12px; margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(16, 51, 84, 0.12);">
                                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%; background: #DDF5EE; color: #103354; font-size: 0.8rem; font-weight: 800; letter-spacing: 0.04em; flex-shrink: 0;">{{ $authorInitials }}</div>
                                                <div style="min-width: 0;">
                                                    <div style="color: #103354; font-weight: 700; font-size: 0.98rem; line-height: 1.3;">{{ $authorName }}</div>
                                                    @if ($authorDetail !== '')
                                                        <div style="color: #526477; font-size: 0.88rem; line-height: 1.45; margin-top: 2px;">{{ $authorDetail }}</div>
                                                    @endif
                                                </div>
                                            </footer>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.matchMedia('(hover: hover)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                document.querySelectorAll('.bizconsult-marquee-row').forEach(function (row) {
                    const track = row.querySelector('.bizconsult-marquee-track');
                    if (!track) {
                        return;
                    }

                    row.addEventListener('pointerenter', function () {
                        track.style.animationPlayState = 'paused';
                    }, { passive: true });

                    row.addEventListener('pointerleave', function () {
                        track.style.animationPlayState = '';
                    }, { passive: true });
                });
            });
        </script>

        <style>
            .bizconsult-marquee-row {
                position: relative;
                overflow: hidden;
            }

            .bizconsult-marquee-fade {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 80px;
                pointer-events: none;
                z-index: 2;
            }

            .bizconsult-marquee-fade-left {
                left: 0;
                background: linear-gradient(to right, #EFFAF6, rgba(239, 250, 246, 0));
            }

            .bizconsult-marquee-fade-right {
                right: 0;
                background: linear-gradient(to left, #EFFAF6, rgba(239, 250, 246, 0));
            }

            .bizconsult-marquee-track {
                display: flex;
                width: max-content;
                gap: 20px;
                will-change: transform;
                animation-timing-function: linear;
                animation-iteration-count: infinite;
                animation-duration: 42s;
            }

            .bizconsult-marquee-to-left .bizconsult-marquee-track {
                animation-name: bizconsult-marquee-left;
            }

            .bizconsult-marquee-to-right .bizconsult-marquee-track {
                animation-name: bizconsult-marquee-right;
            }

            @keyframes bizconsult-marquee-left {
                from { transform: translate3d(0, 0, 0); }
                to { transform: translate3d(calc(-50% - 10px), 0, 0); }
            }

            @keyframes bizconsult-marquee-right {
                from { transform: translate3d(calc(-50% - 10px), 0, 0); }
                to { transform: translate3d(0, 0, 0); }
            }

            @media (hover: hover) and (pointer: fine) {
                .bizconsult-marquee-row:hover .bizconsult-marquee-track {
                    animation-play-state: paused;
                }
            }

            @media (max-width: 991.98px) {
                .bizconsult-testimonials {
                    padding: 72px 0 !important;
                }

                .bizconsult-testimonials-header {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                    margin-bottom: 28px !important;
                }
            }

            @media (max-width: 767.98px) {
                .bizconsult-testimonials {
                    padding: 48px 0 !important;
                }

                .bizconsult-marquee-fade {
                    width: 40px;
                }

                .bizconsult-testimonial-card {
                    width: min(82vw, 320px) !important;
                    min-height: 200px !important;
                    padding: 18px 18px 16px !important;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .bizconsult-marquee-track {
                    animation: none !important;
                    transform: none !important;
                }
            }
        </style>
    </section>
@endif

@php($whyItems = [
    [
        'number' => '01',
        'title' => 'Practical Innovation',
        'description' => 'We use modern technology with a clear purpose, focusing on solutions that genuinely improve business operations and user experiences.',
    ],
    [
        'number' => '02',
        'title' => 'Reliable Delivery',
        'description' => 'Clear communication, structured processes, and careful execution keep projects focused from planning through launch.',
    ],
    [
        'number' => '03',
        'title' => 'Built for Growth',
        'description' => 'Our solutions are designed to remain flexible, scalable, and useful as your business continues to evolve.',
    ],
])

<section class="bizconsult-why" style="background: #ffffff; padding: 88px 0; border-top: 1px solid rgba(16, 51, 84, 0.08);">
    <div class="container" style="max-width: 1200px;">
        <div class="bizconsult-why-header" style="display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(220px, .7fr); align-items: end; gap: 20px 28px; margin-bottom: 26px;">
            <div>
                <div style="display: inline-block; margin-bottom: 12px; color: #103354; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;">WHY REXXSOL</div>
                <h2 style="margin: 0; max-width: 720px; color: #103354; font-size: clamp(2.1rem, 3vw, 3rem); line-height: 1.08; font-weight: 800; letter-spacing: -0.04em;">Built to innovate. Designed to grow.</h2>
            </div>
            <div style="display: flex; justify-content: flex-end; align-items: flex-end;">
                <a href="{{ route('website.about') }}" class="bizconsult-why-link" style="display: inline-flex; align-items: center; gap: 10px; color: #103354; font-weight: 700; text-decoration: none;">
                    <span>Learn more about Rexxsol</span>
                    <span class="bizconsult-why-link-arrow" aria-hidden="true" style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: rgba(0, 185, 142, 0.12); color: #00B98E; transition: transform 250ms ease-out, background 250ms ease-out;"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>

        <p style="margin: 0 0 28px; max-width: 760px; color: #526477; font-size: 1.05rem; line-height: 1.72;">
            We combine practical technology, thoughtful execution, and long-term partnerships to help businesses build better digital experiences and grow with confidence.
        </p>

        <div class="bizconsult-why-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; border-top: 1px solid rgba(16, 51, 84, 0.12); border-bottom: 1px solid rgba(16, 51, 84, 0.12); background: rgba(243, 251, 248, 0.2);">
            @foreach ($whyItems as $item)
                <article class="bizconsult-why-item" style="display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 18px; align-items: start; min-height: 100%; padding: 28px 22px 30px; border-left: 1px solid rgba(16, 51, 84, 0.1); background: transparent; transition: transform 250ms ease-out, border-color 250ms ease-out, box-shadow 250ms ease-out;">
                    <div class="bizconsult-why-number" style="padding-top: 2px; color: rgba(16, 51, 84, 0.38); font-size: .7rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase;">{{ $item['number'] }}</div>
                    <div class="bizconsult-why-copy" style="min-width: 0;">
                        <div class="bizconsult-why-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; margin-bottom: 16px; border: 1px solid rgba(0, 185, 142, 0.16); border-radius: 12px; background: #F3FBF8; color: #00B98E; transition: background-color 250ms ease-out, border-color 250ms ease-out, transform 250ms ease-out;">
                            <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 17.5H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M7 13.5L10.5 10L13 12.5L17 8.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 8.5H17V10.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 style="margin: 0 0 10px; color: #103354; font-size: 1.3rem; line-height: 1.3; font-weight: 700; letter-spacing: -0.02em;">{{ $item['title'] }}</h3>
                        <p style="margin: 0; color: #526477; font-size: 1rem; line-height: 1.7;">{{ $item['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <style>
        .bizconsult-why-item:first-child { border-left: none; }

        .bizconsult-why-link {
            transition: opacity 250ms ease-out;
        }

        .bizconsult-why-link:hover {
            opacity: 0.88;
        }

        .bizconsult-why-item:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 185, 142, 0.28);
        }

        .bizconsult-why-item:hover .bizconsult-why-icon {
            background: #eafaf4;
            border-color: rgba(0, 185, 142, 0.3);
        }

        .bizconsult-why-item:hover .bizconsult-why-link-arrow,
        .bizconsult-why-link:hover .bizconsult-why-link-arrow {
            transform: translateX(4px);
            background: rgba(0, 185, 142, 0.18);
        }

        @media (max-width: 991.98px) {
            .bizconsult-why {
                padding: 72px 0 !important;
            }

            .bizconsult-why-header {
                grid-template-columns: 1fr !important;
                gap: 18px !important;
            }

            .bizconsult-why-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                border-top: 1px solid rgba(16, 51, 84, 0.12);
            }

            .bizconsult-why-item {
                border-left: 1px solid rgba(16, 51, 84, 0.1);
                border-top: 1px solid rgba(16, 51, 84, 0.1);
            }
        }

        @media (max-width: 767.98px) {
            .bizconsult-why {
                padding: 60px 0 !important;
            }

            .bizconsult-why-grid {
                grid-template-columns: 1fr !important;
                border-top: 1px solid rgba(16, 51, 84, 0.12);
            }

            .bizconsult-why-header {
                margin-bottom: 20px !important;
            }

            .bizconsult-why-item {
                padding: 22px 18px 20px !important;
                border-left: none !important;
                border-top: 1px solid rgba(16, 51, 84, 0.1);
            }
        }
    </style>
</section>
@endsection