@extends('themes.rexxsol-nova.layouts.app')

@php($pageContent = \App\Support\RichText::clean($page->content))

@section('content')
    <section class="nova-page">
        <div class="nova-container">
            <nav class="nova-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">{{ $page->title }}</span></nav>
            <article class="nova-content-panel nova-detail-panel">
                <p class="nova-page-kicker">{{ $page->navigation_label ?: 'Rexxsol' }}</p>
                <h1>{{ $page->title }}</h1>
                @if ($page->excerpt)<p class="nova-detail-lead">{{ $page->excerpt }}</p>@endif
                @if ($page->featured_image)<img class="nova-page-featured" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($page->featured_image, $page->featured_image_alt, $page->title) }}">@endif
                @if ($pageContent !== '')<div class="nova-prose">{!! $pageContent !!}</div>@endif
            </article>
        </div>
    </section>
@endsection