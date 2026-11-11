@extends('themes.rexxsol-nova.layouts.app')

@php
    $serviceSection = $homepage['services'] ?? [];
    $serviceCards = collect($serviceSection['items'] ?? []);
    if ($serviceCards->isEmpty()) {
        $serviceCards = collect($items ?? [])->map(fn ($item) => [
            'title' => $item->name,
            'slug' => $item->slug,
            'description' => $item->short_description,
            'icon' => $item->icon,
        ]);
    }
    $processSection = $homepage['process'] ?? null;
@endphp

@section('content')
    <div class="nova-services-page">
        <section class="nova-page nova-inner-hero nova-services-hero" aria-labelledby="nova-services-title">
            <div class="nova-container">
                <header class="nova-page-heading">
                    <p class="nova-page-kicker">{{ $page?->hero_eyebrow ?: 'Our services' }}</p>
                    <h1 id="nova-services-title">{{ $page?->hero_heading ?: 'Services built around your business.' }}</h1>
                    <p>{{ $page?->hero_description ?: 'From strategy to software, we help you build what comes next.' }}</p>
                </header>
            </div>
        </section>

        <section id="nova-services-grid" class="nova-section nova-expertise nova-services-index-grid" aria-labelledby="nova-services-grid-title">
            <div class="nova-container">
                <header class="nova-section-heading nova-expertise-heading">
                    <div>
                        <p class="nova-kicker">Capabilities</p>
                        <h2 id="nova-services-grid-title">Explore our services</h2>
                    </div>
                </header>
                @if ($serviceCards->isNotEmpty())
                    <div class="nova-service-grid">
                        @foreach ($serviceCards as $serviceCard)
                            @include('themes.rexxsol-nova.components.service-card', ['item' => $serviceCard, 'index' => $loop->iteration])
                        @endforeach
                    </div>
                @else
                    <p class="nova-empty-state">Services will appear here as they are published.</p>
                @endif
            </div>
        </section>

        @if ($processSection)
            @include('themes.rexxsol-nova.components.process', ['section' => $processSection])
        @endif
    </div>
@endsection
