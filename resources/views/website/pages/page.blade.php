@extends('website.layouts.app')

@php($seoContent = $page)
@php($pageContent = \App\Support\RichText::clean($page->content))
@php($heroHeading = trim((string) $page->hero_heading) ?: $page->title)
@php($heroDescription = trim((string) ($page->hero_description ?: $page->excerpt)))

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <article class="content-card mx-auto max-w-5xl overflow-hidden p-5 sm:p-8 lg:p-12">
                <nav aria-label="Breadcrumb" class="mb-6 text-sm text-slate-500"><a href="{{ route('home') }}" class="transition hover:text-[#000080]">Home</a><span class="mx-2" aria-hidden="true">/</span><span aria-current="page">{{ $page->title }}</span></nav>
                <header class="max-w-3xl">
                    @if ($page->hero_eyebrow)<p class="site-eyebrow">{{ $page->hero_eyebrow }}</p>@endif
                    <h1 class="text-3xl font-bold leading-tight text-[#172033] sm:text-5xl">{{ $heroHeading }}</h1>
                    @if ($heroDescription)<p class="page-lead mt-5">{{ $heroDescription }}</p>@endif
                </header>
                @if ($page->featured_image)
                    <figure class="page-image mt-8"><img src="{{ Storage::disk('public')->url($page->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($page->featured_image, $page->featured_image_alt, $page->title) }}" class="h-auto max-h-[32rem] w-full object-cover" /></figure>
                @endif
                @if ($pageContent !== '')
                    <div class="prose prose-slate mt-8 max-w-none break-words [&_img]:h-auto [&_img]:max-w-full [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto">{!! $pageContent !!}</div>
                @endif
            </article>
        </div>
    </div>
@endsection
