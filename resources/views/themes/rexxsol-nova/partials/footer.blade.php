@php($novaFooter = app(\App\Support\NovaFooterService::class)->data())
@if ($novaFooter['visible'])
    <footer class="nova-premium-footer">
        <div class="nova-premium-footer-inner">
            <div class="nova-premium-footer-grid">
                <section class="nova-premium-footer-brand" aria-label="About {{ $novaFooter['brand']['name'] }}">
                    @php($brandLogo = $novaFooter['brand']['logo'] ?: asset('images/logo.png'))
                    @if ($brandLogo)
                        <img src="{{ $brandLogo }}" alt="{{ $novaFooter['brand']['name'] }}" width="240" height="72">
                    @else
                        <strong class="nova-premium-footer-wordmark">{{ $novaFooter['brand']['name'] }}</strong>
                    @endif
                    @if ($novaFooter['brand']['description'] !== '')<p>{{ $novaFooter['brand']['description'] }}</p>@endif
                    @if ($novaFooter['socials'])
                        <nav class="nova-premium-footer-socials" aria-label="Social media">
                            @foreach ($novaFooter['socials'] as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}">
                                    @switch($social['platform'])
                                        @case('linkedin')
                                            <svg viewBox="0 0 448 512" aria-hidden="true" focusable="false">
                                                <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 84 0 54.3 0 24.6 24.09.5 53.79.5c29.71 0 53.79 24.1 53.79 53.8 0 29.7-24.08 53.8-53.79 53.8zM447.9 448h-92.7V302.4c0-34.7-.7-79.2-48.3-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.8V148.9h89v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z" />
                                            </svg>
                                            @break
                                        @case('facebook')
                                            <svg viewBox="0 0 320 512" aria-hidden="true" focusable="false">
                                                <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06H297V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                                            </svg>
                                            @break
                                        @case('instagram')
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2.5" />
                                                <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2.5" />
                                                <circle cx="18" cy="6" r="1.25" />
                                            </svg>
                                            @break
                                        @default
                                            <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                                    @endswitch
                                </a>
                            @endforeach
                        </nav>
                    @endif
                </section>

                @foreach (['footer_company', 'footer_resources'] as $location)
                    @php($column = $novaFooter['locations'][$location])
                    <nav class="nova-premium-footer-nav" aria-label="{{ $column['heading'] }}">
                        <h2>{{ $column['heading'] }}</h2>
                        @foreach ($column['links'] as $link)
                            <a href="{{ $link['url'] }}" @if (($link['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                @endforeach

                <section class="nova-premium-footer-contact" aria-labelledby="nova-footer-contact-heading">
                    <h2 id="nova-footer-contact-heading">{{ $novaFooter['contact_heading'] }}</h2>
                    @if ($novaFooter['contact']['email'] !== '')
                        <a href="mailto:{{ $novaFooter['contact']['email'] }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i><span>{{ $novaFooter['contact']['email'] }}</span></a>
                    @endif
                    @if ($novaFooter['contact']['phone'] !== '')
                        <a href="tel:{{ preg_replace('/[^+\d]/', '', $novaFooter['contact']['phone']) }}"><i class="fa-solid fa-phone" aria-hidden="true"></i><span>{{ $novaFooter['contact']['phone'] }}</span></a>
                    @endif
                    @if ($novaFooter['contact']['whatsapp'] !== '')
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $novaFooter['contact']['whatsapp']) }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>{{ $novaFooter['contact']['whatsapp'] }}</span></a>
                    @endif
                    @if ($novaFooter['contact']['address'] !== '')
                        <a href="https://maps.google.com/?q={{ urlencode($novaFooter['contact']['address']) }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ $novaFooter['contact']['address'] }}</span></a>
                    @endif
                    @foreach ($novaFooter['additional_contacts'] as $contact)
                        <a href="{{ $contact['url'] }}" @if (in_array($contact['icon'], ['fa-brands fa-whatsapp', 'fa-solid fa-arrow-up-right-from-square'], true)) target="_blank" rel="noopener noreferrer" @endif><i class="{{ $contact['icon'] }}" aria-hidden="true"></i><span>{{ $contact['label'] }}: {{ $contact['value'] }}</span></a>
                    @endforeach
                </section>
            </div>

            <div class="nova-premium-footer-bottom">
                <p>{{ $novaFooter['copyright'] }}</p>
                <div class="nova-premium-footer-legal">
                    @if ($novaFooter['legal'])
                        <nav aria-label="Legal navigation">
                            @foreach ($novaFooter['legal'] as $link)
                                <a href="{{ $link['url'] }}" @if (($link['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
                            @endforeach
                        </nav>
                    @endif
                    @if ($novaFooter['appearance']['back_to_top'])
                        <button type="button" class="nova-premium-back-to-top" data-nova-back-to-top aria-label="Back to top">
                            <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </footer>
@endif
