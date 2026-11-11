@php($itemUrl = (string) ($item['url'] ?? ''))
@php($itemLabel = (string) ($item['label'] ?? ''))
@php($itemPath = trim((string) parse_url($itemUrl, PHP_URL_PATH), '/'))
@php($itemHost = parse_url($itemUrl, PHP_URL_HOST))
@php($itemIsActive = ($itemHost === null || strcasecmp($itemHost, request()->getHost()) === 0) && ($isHomePage ? $itemPath === '' : ($itemPath !== '' && ($itemPath === $currentPath || str_starts_with($currentPath, $itemPath.'/')))))
@php($hasChildren = ! empty($item['children']))
@php($panelId = $idPrefix.'-'.$index)
@php($normalizedLabel = \Illuminate\Support\Str::lower($itemLabel))
@php($itemIcon = str_contains($normalizedLabel, 'automation') || str_contains($normalizedLabel, 'workflow') ? 'fa-gears' : (str_contains($normalizedLabel, 'web') ? 'fa-laptop-code' : (str_contains($normalizedLabel, 'software') || str_contains($normalizedLabel, 'code') ? 'fa-code' : (str_contains($normalizedLabel, 'service') ? 'fa-layer-group' : 'fa-arrow-right'))))
<li class="nova-navigation-item {{ $hasChildren ? 'has-children' : '' }}">
    @if ($hasChildren)
        <div @class(['nova-nav-group-row', 'has-parent-link' => $itemUrl !== ''])>
            @if ($itemUrl !== '')
                <a class="nova-navigation-link nova-nav-parent" href="{{ $itemUrl }}" @if ($itemIsActive) aria-current="page" @endif @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $itemLabel }}</a>
            @endif
            <details class="nova-nav-disclosure" data-nova-dropdown>
                <summary aria-controls="{{ $panelId }}" aria-expanded="false" aria-label="{{ $itemUrl === '' ? $itemLabel : 'More '.$itemLabel.' links' }}">
                    @if ($itemUrl === '')<span>{{ $itemLabel }}</span>@endif
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="nova-nav-dropdown" id="{{ $panelId }}">
                    <ul class="nova-submenu-list">
                        @foreach ($item['children'] as $childIndex => $child)
                            @include('themes.rexxsol-nova.partials.navigation-item', ['item' => $child, 'index' => $childIndex, 'idPrefix' => $panelId, 'level' => $level + 1, 'currentPath' => $currentPath, 'isHomePage' => $isHomePage])
                        @endforeach
                    </ul>
                </div>
            </details>
        </div>
    @else
        <a class="nova-navigation-link" href="{{ $itemUrl }}" @if ($itemIsActive) aria-current="page" @endif @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>@if ($level > 0)<i class="fa-solid {{ $itemIcon }} nova-submenu-icon" aria-hidden="true"></i>@endif<span>{{ $itemLabel }}</span></a>
    @endif
</li>