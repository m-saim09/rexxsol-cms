@props(['active' => false, 'flyout' => false])

<a {{ $attributes->class(['rex-submenu-link block rounded-lg px-2.5', 'py-1.5 text-[13px]' => ! $flyout, 'py-2 text-sm' => $flyout]) }}
    wire:navigate
    @if ($active) aria-current="page" @endif
>{{ $slot }}</a>
