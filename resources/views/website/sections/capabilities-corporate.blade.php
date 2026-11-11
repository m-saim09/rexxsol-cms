@php
    $capabilities = [
        [
            'title' => 'Strategy & Consulting',
            'description' => 'Turn complex priorities into a clear technology direction.',
            'path' => 'M12 3 3 7.5V12c0 4.8 5.3 8 9 9 3.7-1 9-4.2 9-9V7.5L12 3ZM8 13l3-3 2 2 3-4',
        ],
        [
            'title' => 'Custom Development',
            'description' => 'Build digital products around the way your business works.',
            'path' => 'm7 6-6 6 6 6m10-12 6 6-6 6M14 3l-4 18',
        ],
        [
            'title' => 'Cloud & Infrastructure',
            'description' => 'Create dependable foundations that scale with demand.',
            'path' => 'M6 19a5 5 0 0 1-1-9.9 7 7 0 0 1 13.7-1.3A5.7 5.7 0 0 1 18 19H6Z',
        ],
        [
            'title' => 'Ongoing Support',
            'description' => 'Keep your platforms healthy, secure, and moving forward.',
            'path' => 'M4 14v-3a8 8 0 0 1 16 0v3M4 12H2v7h4v-7H4Zm16 0h2v7h-4v-7h2Zm0 7v1a2 2 0 0 1-2 2h-5',
        ],
    ];
@endphp

<section
    class="capability-strip"
    aria-label="Our capabilities"
>
    <div class="site-container corporate-capabilities">
        @foreach ($capabilities as $capability)
            <div class="corporate-capability">
                <svg
                    class="corporate-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="{{ $capability['path'] }}" />
                </svg>

                <div>
                    <h2 class="corporate-capability-title">
                        {{ $capability['title'] }}
                    </h2>

                    <p class="corporate-capability-copy">
                        {{ $capability['description'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>