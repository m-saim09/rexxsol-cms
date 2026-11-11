@php($settings = $section['settings'] ?? [])
@php($steps = $settings['steps'] ?? [
    ['title' => 'Discover', 'description' => "Your goals.\nOur starting point."],
    ['title' => 'Design', 'description' => "Thoughtful experiences,\nshaped around you."],
    ['title' => 'Develop', 'description' => "Your vision,\nbuilt with precision."],
    ['title' => 'Deliver', 'description' => "Launch confidently.\nGrow with support."],
])
<section class="nova-section nova-process" aria-labelledby="nova-process-title">
    <div class="nova-container">
        <header class="nova-section-heading">
            <p class="nova-kicker">{{ filled($settings['eyebrow'] ?? null) ? $settings['eyebrow'] : 'Our process' }}</p>
            <h2 id="nova-process-title">{{ filled($settings['heading'] ?? null) ? $settings['heading'] : 'From your first idea to launch.' }}</h2>
            <p>{{ filled($settings['description'] ?? null) ? $settings['description'] : 'A clear, collaborative process. Every step, together.' }}</p>
        </header>
        <div class="nova-process-journey">
            <svg class="nova-process-connector nova-process-connector--desktop nova-process-connector--one" viewBox="0 0 100 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M 0 15 C 34 15 66 100 100 100"></path>
            </svg>
            <svg class="nova-process-connector nova-process-connector--desktop nova-process-connector--two" viewBox="0 0 100 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M 0 100 C 34 100 66 15 100 15"></path>
            </svg>
            <svg class="nova-process-connector nova-process-connector--desktop nova-process-connector--three" viewBox="0 0 100 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M 0 15 C 34 15 66 100 100 100"></path>
            </svg>
            <svg class="nova-process-connector nova-process-connector--mobile" viewBox="0 0 40 760" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M 19 0 C 30 90 8 120 19 190 S 30 330 19 380 S 8 520 19 570 S 30 690 19 760"></path>
            </svg>
            <ol class="nova-process-list">
                @foreach ($steps as $index => $step)
                    <li class="nova-process-step nova-process-step--{{ $index + 1 }}">
                        <div class="nova-process-marker">
                            <span class="nova-step-number" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <svg class="nova-process-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                @switch($index)
                                    @case(0)
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="m15.5 8.5-2.2 4.8-4.8 2.2 2.2-4.8 4.8-2.2Z"></path>
                                        <path d="M12 3v1.5M21 12h-1.5M12 21v-1.5M3 12h1.5"></path>
                                        @break
                                    @case(1)
                                        <path d="m14.5 5.5 4 4M4 20l4.3-1 10.9-10.9a2.1 2.1 0 0 0-3-3L5.3 16 4 20Z"></path>
                                        <path d="M3 4v5M1 6h5M14 18h7M17.5 14.5V22"></path>
                                        @break
                                    @case(2)
                                        <path d="m8 7-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"></path>
                                        @break
                                    @default
                                        <circle cx="7.5" cy="15.5" r="4.5"></circle>
                                        <path d="M10.7 12.3 18 5a2.1 2.1 0 0 1 3 3l-1 1h-3v3h-3l-2 2"></path>
                                        @break
                                @endswitch
                            </svg>
                        </div>
                        <div class="nova-process-copy">
                            <h3>{{ $step['title'] ?? '' }}</h3>
                            <p>{{ $step['description'] ?? '' }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>