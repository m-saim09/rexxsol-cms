@extends('website.layouts.app')

@section('content')
    @php
        $sectionViews = [
            'hero' => 'website.sections.hero-corporate',
            'about' => 'website.sections.about',
            'services' => 'website.sections.services',
            'projects' => 'website.sections.projects',
            'stats' => 'website.sections.stats',
            'why_choose_us' => 'website.sections.why-choose-us',
            'testimonials' => 'website.sections.testimonials',
            'team' => 'website.sections.team',
            'blog' => 'website.sections.blog',
            'faqs' => 'website.sections.faqs',
            'cta' => 'website.sections.cta',
        ];
    @endphp

    <div class="home-page">
        @foreach (($homepage ?? []) as $key => $section)
            @continue(! is_array($section))
            @continue(! ($section['enabled'] ?? true))
            @continue(! isset($sectionViews[$key]))

            <div
                id="{{ str_replace('_', '-', $key) }}"
                data-home-section="{{ $key }}"
            >
                @include($sectionViews[$key], [
                    'section' => $section,
                    'settings' => $settings ?? [],
                ])
            </div>

            @if ($key === 'hero')
                @include('website.sections.capabilities-corporate')
            @endif
        @endforeach
    </div>
@endsection