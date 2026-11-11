@extends('website.layouts.app')

@php($seoContent = $page)

@section('content')
    @php($pageContent = \App\Support\RichText::clean((string) ($page?->content ?? '')))
    <div class="page-shell">
        <div class="site-container">
            <div class="page-hero page-hero--center mb-12">
                <span class="site-pill">{{ $page?->hero_eyebrow ?: 'About Rexxsol' }}</span>
                <h1 class="site-heading mt-6">{{ $page?->hero_heading ?: ($page?->title ?? 'About Rexxsol') }}</h1>
                @if ($page?->hero_description || $page?->excerpt)<p class="page-lead mt-5">{{ $page->hero_description ?: $page->excerpt }}</p>@endif
            </div>
            <article class="content-card mx-auto max-w-5xl p-5 sm:p-8 lg:p-12">
                @if ($page?->featured_image)
                    <div class="page-image mb-9"><img src="{{ Storage::disk('public')->url($page->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($page->featured_image, $page->featured_image_alt, $page->title) }}" class="h-72 sm:h-96" /></div>
                @endif
                @if ($pageContent !== '')
                    <div class="prose prose-slate mx-auto max-w-3xl text-slate-700">{!! $pageContent !!}</div>
                @else
                    <p class="page-lead mx-auto">We help organizations move from complexity to clarity with thoughtful digital strategy, product delivery, and operations enablement.</p>
                @endif
            </article>
        </div>
    </div>
@endsection
