@php
    $pageTitle = trim((string) ($page?->title ?? '')) ?: 'About Rexxsol';
    $heroDescription = trim((string) ($page?->excerpt ?? '')) ?: 'A practical, people-first digital partner for modern operations.';
    $heroEyebrow = trim((string) ($page?->hero_eyebrow ?? '')) ?: 'About Rexxsol';
    $heroHeading = trim((string) ($page?->hero_heading ?? '')) ?: $pageTitle;
    $heroDescription = trim((string) ($page?->hero_description ?? '')) ?: $heroDescription;
    $storyContent = $page?->content ? \App\Support\RichText::clean($page->content) : '';
    $storyText = strtolower(strip_tags($storyContent));
    $isPlaceholderStory = str_contains($storyText, 'local demo brand used only for public site qa')
        || str_contains($storyText, 'placeholder digital consultancy for testing');
    $aboutSettings = is_array($homepage['about']['settings'] ?? null) ? $homepage['about']['settings'] : [];
    $thinkingPoints = is_array($aboutSettings['items'] ?? null) ? $aboutSettings['items'] : [];
    if ($thinkingPoints === []) {
        $thinkingPoints = [
            ['title' => 'Understand the business', 'description' => 'We listen, learn and take time to understand your goals and challenges.'],
            ['title' => 'Build with purpose', 'description' => 'We design and develop practical solutions that solve real needs.'],
            ['title' => 'Support what comes next', 'description' => 'We stay involved to help you adapt, improve and grow.'],
        ];
    }
    $whySettings = is_array($homepage['why_choose_us']['settings'] ?? null) ? $homepage['why_choose_us']['settings'] : [];
    $bringItems = is_array($whySettings['items'] ?? null) ? $whySettings['items'] : [];
    if ($bringItems === []) {
        $bringItems = [
            ['title' => 'Business understanding', 'description' => 'We take time to understand your goals, challenges and how your business works.'],
            ['title' => 'Hands-on technical expertise', 'description' => 'We bring practical experience across websites, applications and business software.'],
            ['title' => 'Clear collaboration', 'description' => 'We keep communication open and work closely at every stage.'],
            ['title' => 'Long-term perspective', 'description' => 'We build solutions that are designed to support your business as it grows.'],
        ];
    }
    $stats = $homepage['stats']['settings']['items'] ?? [];
    $experienceStat = collect(is_array($stats) ? $stats : [])->first(function ($stat): bool {
        if (! is_array($stat)) {
            return false;
        }

        $label = strtolower((string) ($stat['description'] ?? ''));

        return str_contains($label, 'year') || str_contains($label, 'experience');
    });
    $experienceRaw = trim((string) ($aboutSettings['experience_years'] ?? ($experienceStat['title'] ?? '15')));
    $experienceNumber = preg_replace('/[^\d]/', '', $experienceRaw) ?: '15';
    $storyHeading = trim((string) ($aboutSettings['story_heading'] ?? '')) ?: 'Experience that turns ideas into working solutions.';
    $storyClose = trim((string) ($aboutSettings['story_close'] ?? '')) ?: 'Built on understanding. Focused on delivery.';
    $storyFirstParagraph = 'For over '.$experienceNumber.' years, Rexxsol has helped businesses bring their digital ideas to life.';
    $storySecondParagraph = 'From websites and applications to business software, we combine practical thinking with hands-on delivery.';
    $missionTitle = trim((string) ($aboutSettings['mission_title'] ?? '')) ?: 'Our mission';
    $missionDescription = trim((string) ($aboutSettings['mission_description'] ?? '')) ?: 'Make technology useful, accessible and aligned with the way businesses work.';
    $visionTitle = trim((string) ($aboutSettings['vision_title'] ?? '')) ?: 'Our vision';
    $visionDescription = trim((string) ($aboutSettings['vision_description'] ?? '')) ?: 'Help businesses move forward with dependable digital solutions.';
    $whyEyebrow = trim((string) ($whySettings['eyebrow'] ?? '')) ?: 'What we bring';
    $whyHeading = trim((string) ($whySettings['heading'] ?? '')) ?: 'What we bring to your business.';
    $whyDescription = trim((string) ($whySettings['description'] ?? '')) ?: 'One team. A connected approach to digital work.';
@endphp

<div class="nova-about-page">
    <section class="nova-page nova-inner-hero nova-about-hero" aria-labelledby="nova-about-title">
        <div class="nova-container">
            <header class="nova-page-heading">
                <p class="nova-page-kicker nova-enter nova-enter-1">{{ $heroEyebrow }}</p>
                <h1 class="nova-enter nova-enter-2" id="nova-about-title">{{ $heroHeading }}</h1>
                <p class="nova-enter nova-enter-3">{{ $heroDescription }}</p>
            </header>
        </div>
    </section>

    <section class="nova-about-story nova-about-section" aria-labelledby="nova-about-story-title">
        <div class="nova-container">
            <div class="nova-about-story-grid">
                <div class="nova-experience-panel" data-about-reveal>
                    <div class="nova-experience-number" aria-label="{{ $experienceNumber }} plus years"><span data-about-counter data-counter-value="{{ $experienceNumber }}">{{ $experienceNumber }}</span><b>+</b></div>
                    <p class="nova-experience-label">Years of experience</p>
                    <svg class="nova-experience-curve" viewBox="0 0 520 112" role="img" aria-label="A connected curve representing our work across software, applications and websites">
                        <defs>
                            <linearGradient id="nova-experience-gradient" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0" stop-color="#168dd0"></stop>
                                <stop offset=".52" stop-color="#18afc5"></stop>
                                <stop offset="1" stop-color="#58cd78"></stop>
                            </linearGradient>
                        </defs>
                        <path pathLength="1" d="M14 82 C72 10 111 17 164 52 S258 112 325 92 S430 40 506 18"></path>
                        <circle class="nova-experience-node nova-experience-node--blue" cx="14" cy="82" r="5"></circle>
                        <circle class="nova-experience-node nova-experience-node--navy" cx="164" cy="52" r="5"></circle>
                        <circle class="nova-experience-node nova-experience-node--green" cx="325" cy="92" r="5"></circle>
                        <circle class="nova-experience-node nova-experience-node--cyan" cx="506" cy="18" r="5"></circle>
                    </svg>
                    <ul class="nova-experience-capabilities" aria-label="Our experience">
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="13" rx="1.5"></rect><path d="M8 21h8M12 17v4"></path></svg>
                            <span>Software</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"></path><path d="m3 12 9 5 9-5M3 16l9 5 9-5"></path></svg>
                            <span>Applications</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18M5.6 6.5h12.8M5.6 17.5h12.8"></path></svg>
                            <span>Websites</span>
                        </li>
                    </ul>
                </div>

                <div class="nova-about-story-copy" data-about-reveal>
                    <p class="nova-about-eyebrow">Our story</p>
                    <h2 id="nova-about-story-title">{{ $storyHeading }}</h2>
                    @if ($storyContent !== '' && ! $isPlaceholderStory)
                        <div class="nova-about-story-body nova-prose">{!! $storyContent !!}</div>
                    @else
                        <div class="nova-about-story-body">
                            <p>{{ $storyFirstParagraph }}</p>
                            <p>{{ $storySecondParagraph }}</p>
                        </div>
                    @endif
                    <div class="nova-about-accent" aria-hidden="true"></div>
                    <p class="nova-about-story-close">{{ $storyClose }}</p>
                </div>
            </div>

            <div class="nova-about-principles" aria-label="Our mission and vision">
                <article class="nova-about-principle" data-about-reveal>
                    <span class="nova-about-icon nova-about-icon--blue" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><circle cx="12" cy="12" r="5"></circle><circle cx="12" cy="12" r="1"></circle><path d="m12 12 7-7M16 5h3v3"></path></svg>
                    </span>
                    <div><h3>{{ $missionTitle }}</h3><p>{{ $missionDescription }}</p></div>
                </article>
                <article class="nova-about-principle nova-about-principle--vision" data-about-reveal>
                    <span class="nova-about-icon nova-about-icon--green" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                    <div><h3>{{ $visionTitle }}</h3><p>{{ $visionDescription }}</p></div>
                </article>
            </div>
        </div>
    </section>

    <section class="nova-about-thinking" aria-labelledby="nova-about-thinking-title">
        <div class="nova-container">
            <header class="nova-about-thinking-heading" data-about-reveal>
                <p class="nova-about-eyebrow">{{ filled($aboutSettings['thinking_eyebrow'] ?? null) ? $aboutSettings['thinking_eyebrow'] : 'How we think' }}</p>
                <h2 id="nova-about-thinking-title">{{ filled($aboutSettings['thinking_heading'] ?? null) ? $aboutSettings['thinking_heading'] : 'Clear thinking. Practical delivery.' }}</h2>
            </header>
            <div class="nova-thinking-grid">
                @foreach ($thinkingPoints as $point)
                    @php
                        $pointTitle = trim((string) ($point['title'] ?? ''));
                        $pointDescription = trim((string) ($point['description'] ?? ''));
                        $pointType = str_contains(strtolower($pointTitle), 'understand') ? 'people' : (str_contains(strtolower($pointTitle), 'purpose') ? 'code' : 'support');
                    @endphp
                    <article class="nova-thinking-item" data-about-reveal>
                        <span class="nova-about-icon nova-about-icon--{{ $pointType }}" aria-hidden="true">
                            @if ($pointType === 'people')
                                <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"></path></svg>
                            @elseif ($pointType === 'code')
                                <svg viewBox="0 0 24 24"><path d="m8 7-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M12 3a3 3 0 0 1 3 3v.4a7 7 0 0 1 1.5.9l.4-.2a3 3 0 1 1 3 5.2l-.4.2a7 7 0 0 1 0 1.8l.4.2a3 3 0 1 1-3 5.2l-.4-.2a7 7 0 0 1-1.5.9v.4a3 3 0 1 1-6 0v-.4a7 7 0 0 1-1.5-.9l-.4.2a3 3 0 1 1-3-5.2l.4-.2a7 7 0 0 1 0-1.8l-.4-.2a3 3 0 1 1 3-5.2l.4.2A7 7 0 0 1 9 6.4V6a3 3 0 0 1 3-3Z"></path><circle cx="12" cy="13" r="2.5"></circle></svg>
                            @endif
                        </span>
                        <h3>{{ $pointTitle }}</h3>
                        <p>{{ $pointDescription }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nova-about-bring nova-about-section" aria-labelledby="nova-about-bring-title">
        <div class="nova-container nova-about-bring-grid">
            <div class="nova-about-bring-intro" data-about-reveal>
                <p class="nova-about-eyebrow">{{ $whyEyebrow }}</p>
                <h2 id="nova-about-bring-title">{{ $whyHeading }}</h2>
                <p class="nova-about-bring-description">{{ $whyDescription }}</p>
                <div class="nova-about-accent" aria-hidden="true"></div>
            </div>
            <ol class="nova-about-bring-list">
                @foreach ($bringItems as $item)
                    <li data-about-reveal>
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div><h3>{{ $item['title'] ?? '' }}</h3><p>{{ $item['description'] ?? '' }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</div>
