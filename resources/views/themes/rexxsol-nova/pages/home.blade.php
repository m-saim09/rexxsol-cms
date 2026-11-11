@extends('themes.rexxsol-nova.layouts.app')

@section('content')
    @php($sections = $homepage ?? [])
    <div class="nova-home">
        @if (isset($sections['hero']))
            @include('themes.rexxsol-nova.components.hero', ['section' => $sections['hero']])
            @include('themes.rexxsol-nova.components.impact-strip')
        @endif
        @if (isset($sections['services']))
            @include('themes.rexxsol-nova.components.expertise', ['section' => $sections['services']])
        @endif
        @if (isset($sections['about']))
            @include('themes.rexxsol-nova.components.about', ['section' => $sections['about']])
        @endif
        @if (isset($sections['testimonials']))
            @include('themes.rexxsol-nova.components.testimonials', ['section' => $sections['testimonials']])
        @endif
        @if (isset($sections['process']))
            @include('themes.rexxsol-nova.components.process', ['section' => $sections['process']])
        @endif
        @if (isset($sections['why_choose_us']))
            @include('themes.rexxsol-nova.components.why', ['section' => $sections['why_choose_us']])
        @endif
        @if (isset($sections['faqs']))
            @include('themes.rexxsol-nova.components.faq', ['section' => $sections['faqs']])
        @endif
    </div>
@endsection