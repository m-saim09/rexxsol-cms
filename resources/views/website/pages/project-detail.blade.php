@extends('website.layouts.app')

@php($seoContent = $item)

@section('content')
    <div class="page-shell">
        <div class="site-container">
            <div class="breadcrumb mb-8 flex items-center gap-2"><a href="{{ route('website.projects') }}">Projects</a><span>/</span><span>{{ $item->name }}</span></div>
            <article class="content-card mx-auto max-w-5xl p-5 sm:p-8 lg:p-12">
                <p class="site-eyebrow">{{ $item->category ?? 'Case study' }}</p>
                <h1 class="mt-3 text-3xl font-bold text-[#172033] sm:text-5xl">{{ $item->name }}</h1>
            @if ($item->featured_image)
                <div class="page-image mt-8"><img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ \App\Support\ContentImage::alt($item->featured_image, $item->featured_image_alt, $item->name) }}" class="h-80 sm:h-[28rem]" /></div>
            @endif
            @if ($item->short_description)
                <p class="page-lead mt-8">{{ $item->short_description }}</p>
            @endif
            @if ($item->description)
                <div class="prose prose-slate mt-8 max-w-3xl">{!! $item->description !!}</div>
            @endif
            @if ($item->gallery)
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach ($item->gallery as $imagePath)
                        @if ($galleryUrl = \App\Support\ContentImage::url($imagePath))
                            <img src="{{ $galleryUrl }}" alt="{{ \App\Support\ContentImage::alt($imagePath) }}" class="max-h-[32rem] w-full rounded-lg object-contain" loading="lazy" />
                        @endif
                    @endforeach
                </div>
            @endif
            @if ($item->project_url)
                <a href="{{ $item->project_url }}" class="site-button mt-10" target="_blank" rel="noreferrer">View project <span aria-hidden="true">&#8599;</span></a>
            @endif
            </article>
        </div>
    </div>
@endsection
