@extends('themes.bizconsult.layouts.app')

@php($pageContent = \App\Support\RichText::clean($page->content))

@section('content')
    <section class="content-wrap py-5">
        <div class="container">
            <article class="mx-auto" style="max-width: 850px; min-width: 0;">
                <nav aria-label="Breadcrumb" class="small text-muted mb-3"><a href="{{ route('home') }}">Home</a><span class="mx-2" aria-hidden="true">/</span><span aria-current="page">{{ $page->title }}</span></nav>
                <header class="mb-4">
                    <h1 class="mb-3">{{ $page->title }}</h1>
                    @if ($page->excerpt)<p class="lead mb-0">{{ $page->excerpt }}</p>@endif
                </header>
                @if ($page->featured_image)<figure class="mb-4"><img src="{{ Storage::disk('public')->url($page->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($page->featured_image, $page->featured_image_alt, $page->title) }}" class="img-fluid rounded w-100" style="max-height: 480px; object-fit: cover;"></figure>@endif
                @if ($pageContent !== '')<div class="bizconsult-page-content">{!! $pageContent !!}</div>@endif
            </article>
        </div>
    </section>
    @push('styles')
        <style>
            .bizconsult-page-content { overflow-wrap: anywhere; line-height: 1.8; }
            .bizconsult-page-content img { max-width: 100%; height: auto; }
            .bizconsult-page-content table { display: block; max-width: 100%; overflow-x: auto; border-collapse: collapse; }
            .bizconsult-page-content th, .bizconsult-page-content td { padding: .65rem .8rem; border: 1px solid #dce5e8; text-align: left; }
            .bizconsult-page-content h2, .bizconsult-page-content h3 { margin-top: 1.75rem; }
        </style>
    @endpush
@endsection
