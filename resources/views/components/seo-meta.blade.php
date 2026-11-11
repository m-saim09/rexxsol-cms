@props(['content' => null, 'url' => null, 'pageTitle' => null])
@php($meta = app(App\Support\Seo::class)->metadata($content, $url, $pageTitle))
<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}" />
<meta name="keywords" content="{{ $meta['keywords'] }}" />
<meta name="robots" content="{{ $meta['robots'] }}" />
<link rel="canonical" href="{{ $meta['canonical'] }}" />
<meta property="og:title" content="{{ $meta['og_title'] ?: $meta['title'] }}" />
<meta property="og:description" content="{{ $meta['og_description'] ?: $meta['description'] }}" />
<meta property="og:url" content="{{ $meta['canonical'] }}" />
<meta name="twitter:card" content="{{ $meta['image'] ? 'summary_large_image' : 'summary' }}" />
@if ($meta['image'])<meta property="og:image" content="{{ $meta['image'] }}" />@endif
@if ($meta['twitter'])<meta name="twitter:site" content="{{ $meta['twitter'] }}" />@endif
@if ($meta['google'])<meta name="google-site-verification" content="{{ $meta['google'] }}" />@endif
@if ($meta['bing'])<meta name="msvalidate.01" content="{{ $meta['bing'] }}" />@endif
