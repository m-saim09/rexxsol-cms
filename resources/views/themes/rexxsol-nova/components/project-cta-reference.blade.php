@php($cta = app(\App\Support\NovaFooterService::class)->data()['cta'])
@if ($cta['enabled'])
    <section
        class="nova-final-cta"
        aria-labelledby="nova-project-cta-title"
        style="--nova-cta-start: {{ $cta['background_start'] }}; --nova-cta-end: {{ $cta['background_end'] }}; --nova-button-start: {{ $cta['button_start'] }}; --nova-button-end: {{ $cta['button_end'] }};"
    >
        <div class="nova-final-cta-inner nova-container">
            <div class="nova-final-cta-main">
                <div class="nova-final-cta-copy">
                    @if ($cta['eyebrow'] !== '')
                        <p class="nova-final-cta-eyebrow">{{ $cta['eyebrow'] }}</p>
                    @endif

                    @if ($cta['button_visible'])
                        <a class="nova-final-cta-title-link" href="{{ $cta['button_url'] }}" aria-label="{{ $cta['button_label'] ?: $cta['heading'] }}">
                            <h2 id="nova-project-cta-title">{{ $cta['heading'] }}</h2>
                            <span class="nova-final-cta-action" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M5 19 19 5M7 5h12v12" />
                                </svg>
                            </span>
                        </a>
                    @else
                        <h2 id="nova-project-cta-title">{{ $cta['heading'] }}</h2>
                    @endif
                </div>

                @if ($cta['description'] !== '')
                    <p class="nova-final-cta-description">{{ $cta['description'] }}</p>
                @endif
            </div>

            <div class="nova-final-cta-divider" aria-hidden="true"></div>

            <div class="nova-final-cta-meta">
                @if ($cta['email'] !== '')
                    <a class="nova-final-cta-email" href="mailto:{{ $cta['email'] }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <rect x="2.5" y="4.5" width="19" height="15" rx="1.5" />
                            <path d="m3.5 6 8.5 6.5L20.5 6" />
                        </svg>
                        <span>{{ $cta['email'] }}</span>
                    </a>
                @endif
                @if ($cta['secondary_visible'] && $cta['secondary_label'] !== '')
                    <a class="nova-final-cta-link" href="{{ $cta['secondary_url'] }}">
                        {{ $cta['secondary_label'] }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M5 19 19 5M7 5h12v12" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
