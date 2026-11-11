@extends('website.layouts.app')

@php($seoContent = $item)
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="breadcrumb mb-8 flex items-center gap-2"><a href="{{ $withPreview(route('website.services')) }}">Services</a><span>/</span><span>{{ $item->name }}</span></div>
            <article class="content-card mx-auto max-w-5xl p-5 sm:p-8 lg:p-12">
                <div class="mb-8 flex items-center gap-4">
                    <span class="corporate-icon inline-flex h-12 w-12 items-center justify-center border border-[#dbe2ed] text-lg font-bold">{{ Str::upper(Str::substr($item->name, 0, 1)) }}</span>
                <div>
                    <p class="site-eyebrow">Service</p>
                    <h1 class="mt-1 text-3xl font-bold text-[#172033] sm:text-5xl">{{ $item->name }}</h1>
                </div>
                </div>
                @if ($item->featured_image)
                    <div class="page-image mb-8"><img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($item->featured_image, $item->featured_image_alt, $item->name) }}" class="h-72 sm:h-96" /></div>
                @endif
                @if ($item->short_description)
                    <p class="page-lead">{{ $item->short_description }}</p>
                @endif
                @if ($item->description)
                    <div class="prose prose-slate mt-8 max-w-3xl">{!! $item->description !!}</div>
                @endif
                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('website.contact') }}" class="site-button">Talk to our team <span aria-hidden="true">&#8594;</span></a>
                    <a href="{{ $withPreview(route('website.services')) }}" class="site-button site-button--light">Back to services</a>
                </div>
            </article>
        </div>
    </div>
@endsection
