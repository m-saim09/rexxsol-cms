@php
    $hero = is_array($section['settings'] ?? null)
        ? $section['settings']
        : [];

    $text = static function (
        string $key,
        string $fallback
    ) use ($hero): string {
        $value = $hero[$key] ?? null;

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : $fallback;
    };

    $safeUrl = static function (
        $value,
        string $fallback
    ): string {
        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $value = trim($value);

        if (preg_match('/[\x00-\x20\x7f\\\\]/', $value)) {
            return $fallback;
        }

        if (
            str_starts_with($value, '/')
            && ! str_starts_with($value, '//')
        ) {
            return $value;
        }

        if (
            str_starts_with($value, '#')
            && strlen($value) > 1
        ) {
            return $value;
        }

        if (
            filter_var($value, FILTER_VALIDATE_URL)
            && in_array(
                strtolower((string) parse_url($value, PHP_URL_SCHEME)),
                ['http', 'https'],
                true
            )
        ) {
            return $value;
        }

        return $fallback;
    };

    $headline = $text(
        'headline',
        'Building digital solutions that move business forward.'
    );

    $servicesUrl = \Illuminate\Support\Facades\Route::has('website.services')
        ? route('website.services')
        : '';

    $contactUrl = \Illuminate\Support\Facades\Route::has('website.contact')
        ? route('website.contact')
        : '';

    $primaryUrl = $safeUrl(
        $hero['primary_cta_url'] ?? null,
        $servicesUrl
    );

    $secondaryUrl = $safeUrl(
        $hero['secondary_cta_url'] ?? null,
        $contactUrl
    );

    $image = $safeUrl($hero['hero_image'] ?? null, '');

    if (str_starts_with($image, '#')) {
        $image = '';
    }

    $imageAlt = is_string($hero['hero_image_alt'] ?? null)
        ? $hero['hero_image_alt']
        : 'Rexxsol team at work';
@endphp

<section
    class="home-gradient corporate-hero"
    aria-labelledby="corporate-hero-heading"
>
    <div class="site-container">
        <div class="hero-corporate-grid">
            <div>
                <p class="site-eyebrow">
                    {{ $text('eyebrow', 'TECHNOLOGY. BUILT FOR BUSINESS.') }}
                </p>

                <h1
                    id="corporate-hero-heading"
                    class="hero-heading"
                >
                    {{ $headline }}
                </h1>

                <p class="hero-description">
                    {{
                        $text(
                            'description',
                            'From custom software to reliable IT infrastructure, we help you build what comes next.'
                        )
                    }}
                </p>

                @if ($primaryUrl !== '' || $secondaryUrl !== '')
                    <div class="corporate-hero-actions">
                        @if ($primaryUrl !== '')
                            <a
                                href="{{ $primaryUrl }}"
                                class="site-button"
                            >
                                {{ $text('primary_cta_label', 'Explore Our Services') }}

                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        @endif

                        @if ($secondaryUrl !== '')
                            <a
                                href="{{ $secondaryUrl }}"
                                class="site-button site-button--outline"
                            >
                                {{ $text('secondary_cta_label', 'Start a Project') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="hero-corporate-media">
                <span
                    class="hero-corporate-accent hero-corporate-accent--top"
                    aria-hidden="true"
                ></span>

                <span
                    class="hero-corporate-accent hero-corporate-accent--bottom"
                    aria-hidden="true"
                ></span>

                @if ($image !== '')
                    <img
                        class="corporate-hero-image"
                        src="{{ $image }}"
                        alt="{{ $imageAlt }}"
                        width="720"
                        height="600"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <div
                        class="corporate-hero-placeholder"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 720 600"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M0 480H720M0 360H720M0 240H720M120 0V600M240 0V600M360 0V600M480 0V600M600 0V600"
                                stroke="#dce3ef"
                            />

                            <path
                                d="M150 480V210L330 120V480H150Z"
                                fill="#fff"
                                stroke="#c3ccdd"
                                stroke-width="2"
                            />

                            <path
                                d="M330 120L530 210V480H330V120Z"
                                fill="#e1e7f1"
                                stroke="#c3ccdd"
                                stroke-width="2"
                            />

                            <path
                                d="M390 245H480M390 305H480M390 365H480M205 260L275 225M205 320L275 285M205 380L275 345"
                                stroke="currentColor"
                                stroke-width="8"
                            />

                            <path
                                d="M100 480H580"
                                stroke="currentColor"
                                stroke-width="3"
                            />
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>