<div class="rex-nav-editor-head">
    <div>
        <p class="rex-nav-editor-kicker">{{ $editingId ? 'Menu item' : 'New menu item' }}</p>
        <h2>{{ $editingId ? 'Edit navigation item' : 'Add navigation item' }}</h2>
        <p>Use published CMS content or a safe custom URL.</p>
    </div>
    <span class="rex-nav-editor-icon"><flux:icon name="link" class="size-5" /></span>
</div>

<form wire:submit="save" class="rex-nav-form" x-on:input="hasUnsavedChanges = true" x-on:change="hasUnsavedChanges = true">
    <div class="rex-nav-field">
        <label for="{{ $prefix }}-label">Label</label>
        <input id="{{ $prefix }}-label" wire:model="label" placeholder="Navigation label" />
        @error('label')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="rex-nav-field">
        <label for="{{ $prefix }}-type">Type</label>
        <select id="{{ $prefix }}-type" wire:model.live="type">
            <option value="page">Internal Page</option>
            <option value="service">Service</option>
            <option value="project">Portfolio</option>
            <option value="blog">Blog</option>
            <option value="system">System Link</option>
            <option value="custom">Custom URL</option>
        </select>
    </div>

    @if (in_array($type, ['custom', 'system'], true))
        <div class="rex-nav-field">
            <label for="{{ $prefix }}-url">{{ $type === 'system' ? 'System link' : 'URL' }}</label>
            @if ($type === 'system')
                <select id="{{ $prefix }}-url" wire:model="url">
                    <option value="">Choose system route</option>
                    @foreach ($options['system'] ?? [] as $route)
                        <option value="{{ $route->id }}">{{ $route->title }}</option>
                    @endforeach
                </select>
            @else
                <input id="{{ $prefix }}-url" wire:model="url" placeholder="https://example.com" />
            @endif
            @error('url')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
        </div>
    @else
        <div class="rex-nav-field">
            <label for="{{ $prefix }}-target">Published content</label>
            <select id="{{ $prefix }}-target" wire:model="targetId">
                <option value="">Choose content</option>
                @foreach ($options[$type] ?? [] as $option)
                    <option value="{{ $option->id }}">{{ in_array($type, ['service', 'project'], true) ? $option->name : $option->title }}</option>
                @endforeach
            </select>
            @error('targetId')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="rex-nav-field">
        <label for="{{ $prefix }}-parent">Parent item</label>
        <select id="{{ $prefix }}-parent" wire:model="parentId">
            <option value="">Top level</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}">{{ $parent->label }}</option>
            @endforeach
        </select>
        @error('parentId')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="rex-nav-two-up">
        <div class="rex-nav-field">
            <label for="{{ $prefix }}-window">Open in</label>
            <select id="{{ $prefix }}-window" wire:model="target">
                <option value="_self">Same tab</option>
                <option value="_blank">New tab</option>
            </select>
        </div>

        <div class="rex-nav-toggle-row">
            <div>
                <label for="{{ $prefix }}-enabled" class="text-xs font-semibold text-[#1e293b]">Enabled</label>
                <p class="rex-nav-toggle-help">Show on site</p>
            </div>
            <label class="rex-nav-toggle" for="{{ $prefix }}-enabled">
                <input id="{{ $prefix }}-enabled" type="checkbox" wire:model="enabled" />
                <span class="rex-nav-toggle-slider"></span>
            </label>
        </div>
    </div>

    <div class="rex-nav-actions-bar">
        <button type="button" wire:click="resetForm" class="rex-nav-action-button rex-nav-action-button--secondary">{{ $editingId ? 'Close editor' : 'Clear' }}</button>
        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update item' : 'Add item' }}</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>
</form>
