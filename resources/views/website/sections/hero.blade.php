@php($hero = $section['settings'] ?? [])
<section class="home-gradient relative overflow-hidden pb-14 pt-10 sm:pb-18 sm:pt-16 lg:pb-20 lg:pt-20">
    <div class="site-container text-center">
        <div class="site-reveal mx-auto max-w-4xl">
            <div class="site-pill mx-auto hero-badge">
                <span class="hero-badge-tag">NEW</span>
                <span>{{ $hero['eyebrow'] ?? 'Rexxsol Digital Solutions — Building what’s next' }}</span>
                <span class="hero-badge-arrow" aria-hidden="true">→</span>
            </div>

            <h1 class="hero-heading mx-auto mt-6 max-w-5xl text-[clamp(3rem,5vw,5rem)] font-black tracking-[-0.07em] leading-[0.96] text-[#0c163d]">
                {{ $hero['headline'] ?? 'Technology engineered to' }}
                @if (($hero['highlighted_text'] ?? null))
                    <span class="hero-gradient-text block">{{ $hero['highlighted_text'] }}</span>
                @else
                    <span class="hero-gradient-text hero-rotating-word" data-rotating-words="transform|scale|perform|grow">transform</span>
                @endif
            </h1>

            @if (($hero['description'] ?? null))
                <p class="site-copy hero-description mx-auto mt-6 max-w-2xl text-base sm:text-lg">{{ $hero['description'] }}</p>
            @else
                <p class="site-copy hero-description mx-auto mt-6 max-w-2xl text-base sm:text-lg">From websites and enterprise platforms to custom software and digital experiences, Rexxsol builds reliable technology that helps businesses work smarter and grow faster.</p>
            @endif

            <div class="mx-auto mt-8 flex max-w-xl flex-col justify-center gap-3 sm:flex-row">
                @if (($hero['primary_cta_label'] ?? null) && ($hero['primary_cta_url'] ?? null))
                    <a href="{{ $hero['primary_cta_url'] }}" class="site-button hero-primary-cta">{{ $hero['primary_cta_label'] }} <span aria-hidden="true">&#8594;</span></a>
                @else
                    <a href="{{ route('website.contact') }}" class="site-button hero-primary-cta">Start a Project <span aria-hidden="true">&#8594;</span></a>
                @endif
                @if (($hero['secondary_cta_label'] ?? null) && ($hero['secondary_cta_url'] ?? null))
                    <a href="{{ $hero['secondary_cta_url'] }}" class="site-button hero-secondary-cta">
                        <span class="site-button-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 5.5v9l7-4.5-7-4.5Z"/></svg>
                        </span>
                        {{ $hero['secondary_cta_label'] }}
                    </a>
                @else
                    <a href="{{ route('website.contact') }}" class="site-button hero-secondary-cta">
                        <span class="site-button-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M7 5.5v9l7-4.5-7-4.5Z"/></svg>
                        </span>
                        Explore Our Work
                    </a>
                @endif
            </div>

            @if (($hero['trust_text'] ?? null))
                <p class="mt-7 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[#7b88a8]">{{ $hero['trust_text'] }}</p>
            @else
                <p class="mt-7 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[#7b88a8]">Web Development · Software Solutions · UI/UX · Digital Transformation</p>
            @endif
        </div>

        <div class="site-reveal site-reveal-delay relative mx-auto mt-12 max-w-6xl sm:mt-16 lg:mt-20">
            <div class="hero-floating-card left-[-0.8rem] top-10 hidden md:flex"><span>◒</span>Scalable Solutions</div>
            <div class="hero-floating-card right-[-0.4rem] top-4 hidden md:flex"><span>✦</span>Secure Platform</div>
            <div class="hero-floating-card bottom-8 left-0 hidden md:flex"><span>↗</span>Smart Delivery</div>
            <div class="hero-floating-card bottom-20 right-0 hidden md:flex"><span>◆</span>Business Growth</div>

            <div class="hero-browser overflow-hidden p-2 sm:p-3">
                <div class="hero-browser-bar flex h-8 items-center gap-1.5 rounded-t-[1.2rem] px-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#ff9da5]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#f6c86f]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#8ae0b2]"></span>
                    <span class="mx-auto hidden h-4 w-24 rounded-full bg-[#f2f4fb] sm:block"></span>
                </div>

                @if (! empty($hero['hero_image']))
                    <div class="relative aspect-[16/9] bg-[#f6f7ff]">
                        <img src="{{ $hero['hero_image'] }}" alt="{{ $hero['hero_image_alt'] ?? ($hero['headline'] ?? 'Rexxsol product preview') }}" class="h-full w-full object-cover" />
                    </div>
                @else
                    <div class="hero-dashboard">
                        <aside class="product-sidebar">
                            <div class="product-brand" aria-label="Rexxsol product brand">
                                <span class="product-brand-mark">R</span>
                                <span class="product-brand-text">Rexxsol</span>
                            </div>

                            <nav class="product-nav" aria-label="Product navigation">
                                <button type="button" class="product-nav-item is-active">Overview</button>
                                <button type="button" class="product-nav-item">Projects</button>
                                <button type="button" class="product-nav-item">Insights</button>
                                <button type="button" class="product-nav-item">Analytics</button>
                                <button type="button" class="product-nav-item">Integrations</button>
                                <button type="button" class="product-nav-item">Settings</button>
                            </nav>

                            <div class="product-side-card">
                                <span class="product-side-label">Workspace</span>
                                <div class="product-side-progress">
                                    <span></span>
                                </div>
                                <strong>82%</strong>
                            </div>
                        </aside>

                        <div class="product-main">
                            <header class="product-toolbar">
                                <div class="product-search" aria-label="Search">
                                    <svg viewBox="0 0 20 20" aria-hidden="true">
                                        <circle cx="8.5" cy="8.5" r="5.25" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M12.5 12.5L17 17" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"/>
                                    </svg>
                                    <span>Search projects</span>
                                </div>

                                <div class="product-toolbar-actions">
                                    <button type="button" class="product-icon-button" aria-label="Notifications">
                                        <svg viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M10 3.25A4 4 0 0 0 6 7.25v2.1c0 .8-.24 1.58-.68 2.25L4.5 12.5h11l-1.02-1.1a3.9 3.9 0 0 1-.68-2.25v-2.1A4 4 0 0 0 10 3.25Zm0 13.5a2.22 2.22 0 0 1-2.1-1.5h4.2A2.22 2.22 0 0 1 10 16.75Z" fill="currentColor"/>
                                        </svg>
                                    </button>

                                    <div class="product-user-pill">
                                        <span class="product-user-avatar">RM</span>
                                        <div>
                                            <strong>Riley M.</strong>
                                            <small>Operations</small>
                                        </div>
                                    </div>
                                </div>
                            </header>

                            <div class="product-overview">
                                <p>Overview</p>
                                <h2>Good morning</h2>
                            </div>

                            <div class="product-kpis">
                                <div class="product-kpi">
                                    <span class="product-kpi-label">Active work</span>
                                    <strong>14</strong>
                                    <small>+18.2%</small>
                                </div>
                                <div class="product-kpi">
                                    <span class="product-kpi-label">Delivery</span>
                                    <strong>92%</strong>
                                    <small>On track</small>
                                </div>
                                <div class="product-kpi">
                                    <span class="product-kpi-label">Tasks</span>
                                    <strong>37</strong>
                                    <small>+5 this week</small>
                                </div>
                                <div class="product-kpi">
                                    <span class="product-kpi-label">Uptime</span>
                                    <strong>99.9%</strong>
                                    <small>Stable</small>
                                </div>
                            </div>

                            <div class="product-analytics">
                                <div class="product-chart-panel">
                                    <div class="panel-header">
                                        <div>
                                            <span>Delivery overview</span>
                                            <h3>Project growth</h3>
                                        </div>
                                        <div class="product-pill">+24.8%</div>
                                    </div>

                                    <div class="product-chart" aria-label="Project growth chart">
                                        <svg viewBox="0 0 520 210" preserveAspectRatio="none" role="img" aria-label="Growth chart">
                                            <defs>
                                                <linearGradient id="growthFill" x1="0" x2="0" y1="0" y2="1">
                                                    <stop offset="0%" stop-color="#7285ff" stop-opacity="0.30"/>
                                                    <stop offset="100%" stop-color="#7285ff" stop-opacity="0.02"/>
                                                </linearGradient>
                                            </defs>
                                            <g opacity="0.42" stroke="#dfe7ff" stroke-width="1">
                                                <line x1="0" y1="25" x2="520" y2="25"/>
                                                <line x1="0" y1="75" x2="520" y2="75"/>
                                                <line x1="0" y1="125" x2="520" y2="125"/>
                                                <line x1="0" y1="175" x2="520" y2="175"/>
                                            </g>
                                            <path d="M0 160 C 70 150, 110 120, 150 118 S 220 95, 270 105 S 340 50, 390 64 S 445 30, 520 48 L520 210 L0 210 Z" fill="url(#growthFill)"/>
                                            <path d="M0 160 C 70 150, 110 120, 150 118 S 220 95, 270 105 S 340 50, 390 64 S 445 30, 520 48" fill="none" stroke="#6078ff" stroke-width="3" stroke-linecap="round"/>
                                            <circle cx="390" cy="64" r="5.5" fill="#ffffff" stroke="#6078ff" stroke-width="3"/>
                                        </svg>
                                    </div>

                                    <div class="chart-labels">
                                        <span>Jan</span>
                                        <span>Feb</span>
                                        <span>Mar</span>
                                        <span>Apr</span>
                                        <span>May</span>
                                        <span>Jun</span>
                                    </div>
                                </div>

                                <aside class="product-activity-panel">
                                    <div class="panel-header panel-header--compact">
                                        <div>
                                            <span>Recent activity</span>
                                            <h3>Updates</h3>
                                        </div>
                                    </div>

                                    <div class="activity-list">
                                        <div class="activity-row">
                                            <span class="status-dot status-dot--green"></span>
                                            <div>
                                                <strong>Launch review cleared</strong>
                                                <small>12 min ago</small>
                                            </div>
                                        </div>
                                        <div class="activity-row">
                                            <span class="status-dot status-dot--violet"></span>
                                            <div>
                                                <strong>Design system updated</strong>
                                                <small>1 hour ago</small>
                                            </div>
                                        </div>
                                        <div class="activity-row">
                                            <span class="status-dot status-dot--blue"></span>
                                            <div>
                                                <strong>Client feedback received</strong>
                                                <small>3 hours ago</small>
                                            </div>
                                        </div>
                                        <div class="activity-row">
                                            <span class="status-dot status-dot--amber"></span>
                                            <div>
                                                <strong>Release checklist synced</strong>
                                                <small>Today</small>
                                            </div>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
