<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Models\ActivityLog;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Support\FooterService;
use App\Support\NavigationService;
use App\Support\SiteSettings;
use App\Support\ThemeRegistry;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NavigationManager extends Component
{
    public string $menu = 'primary';

    public ?int $editingId = null;

    public ?string $editingVersion = null;

    public array $menuDraft = [];

    #[Locked]
    public array $menuDraftOriginal = [];

    #[Locked]
    public string $menuDraftVersion = '';

    #[Locked]
    public int $nextDraftId = -1;

    #[Locked]
    public bool $menuDraftDirty = false;

    public string $label = '';

    public string $type = 'page';

    public string $targetId = '';

    public string $url = '';

    public string $target = '_self';

    public bool $enabled = true;

    public ?int $parentId = null;

    public string $newMenuName = '';

    public string $renameMenuName = '';

    public bool $showCreateMenuModal = false;

    public bool $showRenameMenuModal = false;

    public bool $showDeleteMenuModal = false;

    public array $selectedContentIds = [];

    public string $selectedContentType = 'page';

    public string $contentSearch = '';

    public bool $showLocations = false;

    public array $locationAssignments = [];

    // Footer-specific workspace properties
    public string $footerSubTab = 'structure'; // 'structure', 'settings', 'locations'

    public bool $showPreview = false;

    public string $pageSearch = '';

    public ?int $linkColumnId = null;

    public array $collapsedColumns = [];

    // Column Management Modals / Inputs
    public bool $showAddColumnModal = false;

    public string $newColumnName = '';

    public ?int $editingColumnId = null;

    public string $editingColumnName = '';

    public ?int $deletingColumnId = null;

    public ?int $reassignColumnId = null;

    public bool $showDeleteColumnModal = false;

    // Footer Settings Properties
    public string $footerLogoUrl = '';

    public string $siteName = '';

    public string $footerDescription = '';

    public string $contactEmail = '';

    public string $contactPhone = '';

    public string $contactWhatsapp = '';

    public string $contactAddress = '';

    public string $socialFacebook = '';

    public string $socialLinkedin = '';

    public string $socialInstagram = '';

    public string $socialYoutube = '';

    public string $socialTwitter = '';

    public bool $ctaEnabled = true;

    public string $ctaEyebrow = '';

    public string $ctaHeading = '';

    public string $ctaDescription = '';

    public string $ctaButtonLabel = '';

    public string $ctaButtonUrl = '';

    public string $ctaImage = '';

    public function mount(?string $menu = null, ?string $footerSubTab = null): void
    {
        Gate::authorize('navigation.view');

        $requestedMenu = request()->query('menu', $menu);
        if (is_string($requestedMenu) && in_array($requestedMenu, $this->menuOptions(), true)) {
            $this->menu = $requestedMenu;
        }

        $requestedSubTab = request()->query('tab', $footerSubTab);
        if (is_string($requestedSubTab) && in_array($requestedSubTab, ['structure', 'settings', 'locations'], true)) {
            $this->footerSubTab = $requestedSubTab;
        }

        $this->syncLocationAssignments();
        $this->loadFooterSettings();
        $this->loadMenuDraft();
    }

    public function updatedMenu(): void
    {
        $this->resetForm();
    }

    public function selectMenu(string $menu): void
    {
        Gate::authorize('navigation.view');

        if (! in_array($menu, $this->menuOptions(), true)) {
            return;
        }

        if ($this->menuDraftIsDirty()) {
            $this->addError('menuItems', 'Save or discard the current menu changes before switching menus.');

            return;
        }

        $this->menu = $menu;
        $this->resetForm();
        $this->loadMenuDraft();
    }

    public function discardDraftAndSelectMenu(string $menu): void
    {
        Gate::authorize('navigation.view');
        if (! in_array($menu, $this->menuOptions(), true)) {
            abort(404);
        }

        $this->menu = $menu;
        $this->resetForm();
        $this->loadMenuDraft();
        $this->dispatch('navigation-saved');
    }

    public function discardDraft(): void
    {
        Gate::authorize('navigation.view');
        $this->loadMenuDraft();
        $this->resetForm();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Unsaved menu changes discarded.');
    }

    public function setFooterSubTab(string $tab): void
    {
        Gate::authorize('navigation.view');

        if (! in_array($tab, ['structure', 'settings', 'locations'], true)) {
            return;
        }

        $this->footerSubTab = $tab;
        if ($tab === 'settings') {
            $this->loadFooterSettings();
        } elseif ($tab === 'locations') {
            $this->syncLocationAssignments();
        }
    }

    public function openLocations(): void
    {
        Gate::authorize('navigation.view');
        if ($this->menuDraftIsDirty()) {
            $this->addError('menuItems', 'Save or discard the current menu changes before opening locations.');

            return;
        }
        $this->menu = 'footer';
        $this->footerSubTab = 'locations';
        $this->syncLocationAssignments();
        $this->resetForm();
        $this->loadMenuDraft();
    }

    public function createMenu(): void
    {
        Gate::authorize('manage-navigation');
        if ($this->menuDraftIsDirty()) {
            $this->addError('menuItems', 'Save or discard the current menu changes before creating another menu.');

            return;
        }

        $slug = trim((string) $this->newMenuName);
        if ($slug === '') {
            $this->addError('newMenuName', 'Menu name is required.');

            return;
        }

        $slug = Str::slug($slug, '-');
        if ($slug === '') {
            $this->addError('newMenuName', 'Please enter a valid menu name.');

            return;
        }

        if (in_array($slug, $this->menuOptions(), true)) {
            $this->addError('newMenuName', 'A menu with this name already exists.');

            return;
        }

        DB::transaction(function () use ($slug): void {
            $this->registerMenu($slug);
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'Navigation',
                'subject_type' => NavigationItem::class,
                'subject_id' => 0,
                'subject_label' => $slug,
                'changes' => [],
            ]);
        });
        $this->menu = $slug;
        $this->newMenuName = '';
        $this->showCreateMenuModal = false;
        $this->resetForm();
        $this->loadMenuDraft();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Menu created successfully.');
    }

    public function startRenameMenu(): void
    {
        Gate::authorize('manage-navigation');
        if (in_array($this->menu, NavigationItem::MENUS, true)) {
            $this->addError('renameMenuName', 'Built-in menus cannot be renamed.');

            return;
        }

        $this->renameMenuName = Str::headline($this->menu);
        $this->showRenameMenuModal = true;
    }

    public function renameMenu(): void
    {
        Gate::authorize('manage-navigation');
        if ($this->menuDraftIsDirty()) {
            $this->addError('menuItems', 'Save or discard the current menu changes before renaming this menu.');

            return;
        }

        if (in_array($this->menu, NavigationItem::MENUS, true)) {
            $this->addError('renameMenuName', 'Built-in menus cannot be renamed.');

            return;
        }

        $oldMenu = $this->menu;
        $newMenu = Str::slug(trim($this->renameMenuName), '-');
        $this->validate(['renameMenuName' => ['required', 'string', 'max:40']]);
        if ($newMenu === '' || preg_match('/\A[a-z0-9_-]+\z/', $newMenu) !== 1) {
            $this->addError('renameMenuName', 'Enter a valid menu name.');

            return;
        }
        if ($newMenu !== $oldMenu && in_array($newMenu, $this->menuOptions(), true)) {
            $this->addError('renameMenuName', 'A menu with this name already exists.');

            return;
        }
        if ($newMenu === $oldMenu) {
            $this->renameMenuName = '';

            return;
        }

        DB::transaction(function () use ($oldMenu, $newMenu): void {
            NavigationItem::query()->where('menu', $oldMenu)->update(['menu' => $newMenu]);

            $settings = app(SiteSettings::class);
            $menus = $settings->get('cms.navigation.menus', []);
            $menus = is_array($menus) ? array_map(fn ($menu) => $menu === $oldMenu ? $newMenu : $menu, $menus) : [];

            $assignments = $settings->get('theme.menu_locations', []);
            if (is_array($assignments)) {
                foreach ($assignments as $theme => $themeAssignments) {
                    if (! is_array($themeAssignments)) {
                        continue;
                    }
                    foreach ($themeAssignments as $location => $menu) {
                        if ($menu === $oldMenu) {
                            $assignments[$theme][$location] = $newMenu;
                        }
                    }
                }
            }

            $settings->put([
                'cms.navigation.menus' => array_values(array_unique($menus)),
                'theme.menu_locations' => $assignments,
            ]);

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'renamed',
                'module' => 'Navigation',
                'subject_type' => NavigationItem::class,
                'subject_id' => 0,
                'subject_label' => $newMenu,
                'changes' => ['slug' => ['old' => $oldMenu, 'new' => $newMenu]],
            ]);
        });

        $this->menu = $newMenu;
        $this->renameMenuName = '';
        $this->showRenameMenuModal = false;
        $this->forgetCaches();
        $this->loadMenuDraft();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Menu renamed successfully.');
    }

    public function deleteMenu(string $menu): void
    {
        Gate::authorize('manage-navigation');
        if ($menu === $this->menu && $this->menuDraftIsDirty()) {
            $this->addError('menuItems', 'Save or discard the current menu changes before deleting this menu.');

            return;
        }
        abort_if(in_array($menu, NavigationItem::MENUS, true), 422, 'Built-in menus cannot be deleted.');
        abort_unless(in_array($menu, $this->menuOptions(), true), 404);

        DB::transaction(function () use ($menu): void {
            $settings = app(SiteSettings::class);
            $assignments = $settings->get('theme.menu_locations', []);
            if (is_array($assignments)) {
                foreach ($assignments as $theme => $themeAssignments) {
                    if (! is_array($themeAssignments)) {
                        continue;
                    }
                    foreach ($themeAssignments as $location => $assignedMenu) {
                        if ($assignedMenu === $menu) {
                            $assignments[$theme][$location] = '';
                        }
                    }
                }
            }

            $registeredMenus = $settings->get('cms.navigation.menus', []);
            $registeredMenus = is_array($registeredMenus) ? array_values(array_diff($registeredMenus, [$menu])) : [];
            $settings->put([
                'theme.menu_locations' => $assignments,
                'cms.navigation.menus' => $registeredMenus,
            ]);

            NavigationItem::query()->where('menu', $menu)->orderBy('id')->get()->each->delete();
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'module' => 'Navigation',
                'subject_type' => NavigationItem::class,
                'subject_id' => 0,
                'subject_label' => $menu,
                'changes' => [],
            ]);
        });

        if ($this->menu === $menu) {
            $this->menu = 'primary';
        }
        $this->resetForm();
        $this->loadMenuDraft();
        $this->forgetCaches();
        $this->showDeleteMenuModal = false;
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Menu and its location assignments were removed.');
    }

    public function addSelectedContent(): void
    {
        Gate::authorize('manage-navigation');

        $validParent = $this->parentId === null || $this->draftItemIsRoot($this->parentId);
        $this->validate([
            'selectedContentType' => ['required', Rule::in(['page', 'service', 'project', 'blog'])],
            'selectedContentIds' => ['required', 'array', 'min:1'],
            'selectedContentIds.*' => ['required', 'integer', 'distinct'],
            'parentId' => ['nullable', 'integer'],
        ]);
        if (! $validParent) {
            $this->addError('parentId', 'Choose a top-level item from this menu as the parent.');

            return;
        }

        $model = match ($this->selectedContentType) {
            'page' => Page::class,
            'service' => Service::class,
            'project' => Project::class,
            'blog' => BlogPost::class,
        };
        $query = $model::query()->whereIn('id', $this->selectedContentIds)->where('status', 'published');
        if ($this->selectedContentType === 'service') {
            $query->where('is_active', true);
        } else {
            $query->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }
        $records = $query->get();
        if ($records->count() !== count($this->selectedContentIds)) {
            $this->addError('selectedContentIds', 'One or more selected items are no longer published.');

            return;
        }

        $added = 0;
        foreach ($records as $record) {
            $type = $this->selectedContentType;
            $existing = collect($this->menuDraft)->contains(fn (array $item): bool => $item['type'] === $type && (int) $item['target_id'] === (int) $record->getKey());
            if ($existing) {
                continue;
            }

            $sortOrder = $this->nextDraftOrder($this->parentId);
            $this->menuDraft[(string) $this->nextDraftId] = [
                'id' => $this->nextDraftId--,
                'menu' => $this->menu,
                'label' => $type === 'service' || $type === 'project' ? $record->name : $record->title,
                'type' => $type,
                'target_type' => $model,
                'target_id' => (int) $record->getKey(),
                'url' => null,
                'target' => '_self',
                'enabled' => true,
                'parent_id' => $this->parentId,
                'sort_order' => $sortOrder,
                'is_automatic' => false,
                'automatic_label' => null,
            ];
            $added++;
        }

        $this->selectedContentIds = [];
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
        Flux::toast(variant: 'success', text: $added === 0 ? 'All selected content is already in this menu.' : $added.' '.str('item')->plural($added).' staged. Save menu to publish these changes.');
    }

    public function reorderItems(array $itemIds): void
    {
        Gate::authorize('manage-navigation');

        $this->validate(['menu' => ['required', 'string', 'max:40', 'regex:/\A[a-z0-9_-]+\z/']]);
        $ids = array_map('intval', $itemIds);
        $existingIds = collect($this->menuDraft)->filter(fn (array $item): bool => $item['parent_id'] === null)->sortBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($ids) !== count(array_unique($ids)) || count($ids) !== count($existingIds) || array_diff($ids, $existingIds) !== []) {
            $this->addError('menuItems', 'The menu changed while ordering. Reload the menu and try again.');

            return;
        }

        foreach ($ids as $index => $id) {
            $this->menuDraft[(string) $id]['sort_order'] = $index + 1;
        }
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    // ==========================================
    // FOOTER COLUMN ACTIONS
    // ==========================================

    public function openAddColumn(): void
    {
        Gate::authorize('manage-navigation');
        $this->newColumnName = '';
        $this->showAddColumnModal = true;
    }

    public function createColumn(): void
    {
        Gate::authorize('manage-navigation');

        $name = trim($this->newColumnName);
        if ($name === '') {
            $this->addError('newColumnName', 'Column name is required.');

            return;
        }

        if (mb_strlen($name) > 255) {
            $this->addError('newColumnName', 'Column name cannot exceed 255 characters.');

            return;
        }

        $id = $this->nextDraftId--;
        $this->menuDraft[(string) $id] = [
            'id' => $id,
            'menu' => 'footer',
            'label' => $name,
            'type' => 'column',
            'target_type' => null,
            'target_id' => null,
            'url' => null,
            'target' => '_self',
            'enabled' => true,
            'parent_id' => null,
            'sort_order' => $this->nextDraftOrder(null),
            'is_automatic' => false,
            'automatic_label' => null,
        ];
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);

        $this->newColumnName = '';
        $this->showAddColumnModal = false;
        $this->linkColumnId = $id;

        Flux::toast(variant: 'success', text: 'Footer column staged. Save menu to publish these changes.');
    }

    public function startEditColumn(int $id): void
    {
        Gate::authorize('manage-navigation');
        $column = $this->draftItem($id);
        abort_if(! $column || $column['parent_id'] !== null || $column['type'] !== 'column', 404);
        $this->editingColumnId = (int) $column['id'];
        $this->editingColumnName = $column['label'];
    }

    public function cancelEditColumn(): void
    {
        $this->editingColumnId = null;
        $this->editingColumnName = '';
    }

    public function updateColumn(): void
    {
        Gate::authorize('manage-navigation');

        if (! $this->editingColumnId) {
            return;
        }

        $name = trim($this->editingColumnName);
        if ($name === '') {
            $this->addError('editingColumnName', 'Column name is required.');

            return;
        }

        $column = $this->draftItem($this->editingColumnId);
        abort_if(! $column || $column['type'] !== 'column', 404);
        $this->menuDraft[(string) $this->editingColumnId]['label'] = $name;
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);

        $this->editingColumnId = null;
        $this->editingColumnName = '';

        Flux::toast(variant: 'success', text: 'Column rename staged. Save menu to publish these changes.');
    }

    public function toggleColumn(int $id): void
    {
        Gate::authorize('manage-navigation');
        $column = $this->draftItem($id);
        abort_if(! $column || $column['parent_id'] !== null || $column['type'] !== 'column', 404);
        $this->menuDraft[(string) $id]['enabled'] = ! $column['enabled'];
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function moveColumn(int $id, string $direction): void
    {
        Gate::authorize('manage-navigation');
        if (! in_array($direction, ['up', 'down'], true)) {
            $this->addError('footerLinks', 'Choose a valid column direction.');

            return;
        }
        $column = $this->draftItem($id);
        abort_if(! $column || $column['parent_id'] !== null || $column['type'] !== 'column', 404);

        $columns = collect($this->menuDraft)
            ->filter(fn (array $row): bool => $row['parent_id'] === null && $row['type'] === 'column')
            ->sortBy('sort_order')
            ->values();

        $index = $columns->search(fn (array $row): bool => (int) $row['id'] === $id);
        if ($index === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if (! $columns->has($targetIndex)) {
            return;
        }

        $temp = $columns[$index];
        $columns[$index] = $columns[$targetIndex];
        $columns[$targetIndex] = $temp;

        foreach ($columns as $order => $col) {
            $this->menuDraft[(string) $col['id']]['sort_order'] = $order + 1;
        }

        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function reorderColumns(array $columnIds): void
    {
        Gate::authorize('manage-navigation');

        $ids = array_map('intval', $columnIds);
        $expected = collect($this->menuDraft)->filter(fn (array $row): bool => $row['parent_id'] === null && $row['type'] === 'column')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($ids) !== count(array_unique($ids)) || count($ids) !== count($expected) || array_diff($ids, $expected) !== []) {
            $this->addError('footerLinks', 'The footer columns changed while ordering.');

            return;
        }
        foreach ($ids as $index => $id) {
            $this->menuDraft[(string) $id]['sort_order'] = $index + 1;
        }
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function confirmDeleteColumn(int $id): void
    {
        Gate::authorize('manage-navigation');
        $column = $this->draftItem($id);
        abort_if(! $column || $column['parent_id'] !== null || $column['type'] !== 'column', 404);

        $this->deletingColumnId = (int) $column['id'];
        $this->reassignColumnId = null;
        $this->showDeleteColumnModal = true;
    }

    public function deleteColumn(): void
    {
        Gate::authorize('manage-navigation');

        if (! $this->deletingColumnId) {
            return;
        }

        $column = $this->draftItem($this->deletingColumnId);
        abort_if(! $column || $column['parent_id'] !== null || $column['type'] !== 'column', 404);

        if ($this->reassignColumnId && $this->reassignColumnId !== (int) $column['id']) {
            $targetCol = $this->draftItem($this->reassignColumnId);
            if ($targetCol && ($targetCol['parent_id'] !== null || $targetCol['type'] !== 'column')) {
                abort(404);
            }
            if ($targetCol) {
                $maxOrder = $this->nextDraftOrder((int) $targetCol['id']) - 1;
                foreach ($this->menuDraft as $child) {
                    if ((int) ($child['parent_id'] ?? 0) !== (int) $column['id']) {
                        continue;
                    }
                    $maxOrder++;
                    $this->menuDraft[(string) $child['id']]['parent_id'] = (int) $targetCol['id'];
                    $this->menuDraft[(string) $child['id']]['sort_order'] = $maxOrder;
                }
            }
        } else {
            foreach ($this->menuDraft as $child) {
                if ((int) ($child['parent_id'] ?? 0) === (int) $column['id']) {
                    unset($this->menuDraft[(string) $child['id']]);
                }
            }
        }

        unset($this->menuDraft[(string) $column['id']]);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);

        $this->deletingColumnId = null;
        $this->reassignColumnId = null;
        $this->showDeleteColumnModal = false;

        Flux::toast(variant: 'success', text: 'Column changes staged. Save menu to publish them.');
    }

    public function toggleCollapseColumn(int $id): void
    {
        if (in_array($id, $this->collapsedColumns, true)) {
            $this->collapsedColumns = array_values(array_diff($this->collapsedColumns, [$id]));
        } else {
            $this->collapsedColumns[] = $id;
        }
    }

    // ==========================================
    // FOOTER LINK ACTIONS
    // ==========================================

    public function prepareAddLink(?int $columnId = null): void
    {
        $this->resetLinkForm();
        if ($columnId) {
            $this->linkColumnId = $columnId;
        } else {
            $firstCol = collect($this->menuDraft)->filter(fn (array $row): bool => $row['parent_id'] === null && $row['type'] === 'column')->sortBy('sort_order')->first();
            $this->linkColumnId = $firstCol ? (int) $firstCol['id'] : null;
        }
    }

    public function editLink(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] === null, 404);
        $this->editingId = (int) $item['id'];
        $this->editingVersion = $this->draftItemVersion($item);
        $this->linkColumnId = (int) $item['parent_id'];
        $this->label = $item['label'];
        $this->type = $item['type'];
        $this->targetId = (string) ($item['target_id'] ?? '');
        $this->url = $item['url'] ?? '';
        $this->target = $item['target'];
        $this->enabled = (bool) $item['enabled'];
    }

    public function updatedTargetId(): void
    {
        if (trim($this->label) === '' && $this->targetId !== '') {
            $name = match ($this->type) {
                'page' => Page::find($this->targetId)?->title,
                'service' => Service::find($this->targetId)?->name,
                'project' => Project::find($this->targetId)?->name,
                'blog' => BlogPost::find($this->targetId)?->title,
                default => null,
            };

            if ($name) {
                $this->label = $name;
            }
        }
    }

    public function updatedUrl(): void
    {
        if ($this->type === 'system' && trim($this->label) === '' && $this->url !== '') {
            $sys = $this->systemOptions();
            if (isset($sys[$this->url])) {
                $this->label = $sys[$this->url];
            }
        }
    }

    public function updatedType(): void
    {
        $this->targetId = '';
        $this->url = '';

        if ($this->type === 'contact') {
            if (trim($this->label) === '') {
                $this->label = 'Contact';
            }
        }
    }

    public function saveLink(): void
    {
        Gate::authorize('manage-navigation');

        $rules = [
            'linkColumnId' => ['required', 'integer'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['page', 'service', 'project', 'blog', 'contact', 'system', 'custom'])],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'enabled' => ['boolean'],
        ];

        if ($this->type === 'custom') {
            $rules['url'] = ['required', 'string', 'max:500', 'regex:/\A(?:https?:\/\/[^\s]+|\/(?!\/)[^\s]*|mailto:[^\s]+|tel:[^\s]+)\z/i'];
        } elseif ($this->type === 'system') {
            $rules['url'] = ['required', 'string', 'max:255', Rule::in(array_keys($this->systemOptions()))];
        } elseif ($this->type === 'contact') {
            // No extra input needed
        } else {
            $rules['targetId'] = ['required', 'integer', Rule::exists($this->tableForType(), 'id')->where('status', 'published')];
        }

        $this->validate($rules);

        $column = $this->draftItem((int) $this->linkColumnId);
        if (! $column || $column['parent_id'] !== null || $column['type'] !== 'column') {
            $this->addError('linkColumnId', 'Choose a footer column from this menu.');

            return;
        }
        $item = $this->editingId ? $this->draftItem($this->editingId) : null;
        if ($this->editingId && (! $item || $this->editingVersion === null || ! hash_equals($this->editingVersion, $this->draftItemVersion($item)))) {
            $this->addError('menuItems', 'This item changed while you were editing it. Reload it and reapply your changes.');

            return;
        }

        $targetType = match ($this->type) {
            'page' => Page::class,
            'service' => Service::class,
            'project' => Project::class,
            'blog' => BlogPost::class,
            default => null,
        };

        $resolvedUrl = match ($this->type) {
            'custom' => $this->url,
            'system' => $this->url,
            'contact' => 'contact',
            default => null,
        };

        $id = $item ? (int) $item['id'] : $this->nextDraftId--;
        $this->menuDraft[(string) $id] = array_merge($item ?? [], [
            'id' => $id,
            'menu' => 'footer',
            'label' => $this->label,
            'type' => $this->type,
            'target_id' => in_array($this->type, ['custom', 'system', 'contact'], true) ? null : (int) $this->targetId,
            'target_type' => $targetType,
            'url' => $resolvedUrl,
            'target' => $this->target,
            'enabled' => $this->enabled,
            'parent_id' => $this->linkColumnId,
            'sort_order' => $item['sort_order'] ?? $this->nextDraftOrder((int) $this->linkColumnId),
            'is_automatic' => $item['is_automatic'] ?? false,
            'automatic_label' => $item['automatic_label'] ?? null,
        ]);

        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
        Flux::toast(variant: 'success', text: 'Footer link staged. Save menu to publish these changes.');
        $this->resetLinkForm();
    }

    public function moveLink(int $id, string $direction): void
    {
        Gate::authorize('manage-navigation');
        if (! in_array($direction, ['up', 'down'], true)) {
            $this->addError('footerLinks', 'Choose a valid link direction.');

            return;
        }
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] === null, 404);
        $siblings = collect($this->menuDraft)
            ->filter(fn (array $row): bool => $row['parent_id'] === $item['parent_id'])
            ->sortBy('sort_order')
            ->values();

        $index = $siblings->search(fn (array $row): bool => (int) $row['id'] === $id);
        if ($index === false) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if (! $siblings->has($swapIndex)) {
            return;
        }

        $temp = $siblings[$index];
        $siblings[$index] = $siblings[$swapIndex];
        $siblings[$swapIndex] = $temp;

        foreach ($siblings as $order => $sibling) {
            $this->menuDraft[(string) $sibling['id']]['sort_order'] = $order + 1;
        }

        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function moveLinkToColumn(int $linkId, int $newColumnId): void
    {
        Gate::authorize('manage-navigation');

        $item = $this->draftItem($linkId);
        $newColumn = $this->draftItem($newColumnId);
        abort_if(! $item || $item['parent_id'] === null || ! $newColumn || $newColumn['parent_id'] !== null || $newColumn['type'] !== 'column', 404);
        $oldColumnId = (int) $item['parent_id'];
        $this->menuDraft[(string) $linkId]['parent_id'] = $newColumnId;
        $this->menuDraft[(string) $linkId]['sort_order'] = $this->nextDraftOrder($newColumnId);
        $this->renumberDraftSiblings($oldColumnId);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function reorderLinks(int $columnId, array $linkIds): void
    {
        Gate::authorize('manage-navigation');

        $ids = array_map('intval', $linkIds);
        $column = $this->draftItem($columnId);
        $expected = collect($this->menuDraft)
            ->filter(fn (array $row): bool => (int) ($row['parent_id'] ?? 0) === $columnId)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        if (! $column || $column['type'] !== 'column' || count($ids) !== count(array_unique($ids)) || count($ids) !== count($expected) || array_diff($ids, $expected) !== []) {
            $this->addError('footerLinks', 'The footer column changed while ordering. Reload it and try again.');

            return;
        }

        foreach ($ids as $index => $id) {
            $this->menuDraft[(string) $id]['sort_order'] = $index + 1;
        }
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function toggleLink(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] === null, 404);
        $this->menuDraft[(string) $id]['enabled'] = ! $item['enabled'];
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function removeLink(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] === null, 404);
        unset($this->menuDraft[(string) $id]);
        $this->renumberDraftSiblings((int) $item['parent_id']);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
        Flux::toast(variant: 'success', text: 'Footer link removal staged. Save menu to publish these changes.');
        $this->resetLinkForm();
    }

    public function resetLinkForm(): void
    {
        $this->reset(['editingId', 'editingVersion', 'label', 'targetId', 'url', 'pageSearch']);
        $this->type = 'page';
        $this->target = '_self';
        $this->enabled = true;
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftIsDirty());
    }

    // ==========================================
    // FOOTER SETTINGS ACTIONS
    // ==========================================

    public function loadFooterSettings(): void
    {
        $settings = app(SiteSettings::class);

        $this->footerLogoUrl = (string) ($settings->get('branding.footer_logo') ?: $settings->get('branding.logo') ?: '');
        $this->siteName = (string) ($settings->get('site.name') ?: $settings->get('site.company') ?: 'Rexxsol');
        $this->footerDescription = (string) ($settings->get('footer.company_description') ?: $settings->get('site.description') ?: '');

        $this->contactEmail = (string) ($settings->get('contact.business_email') ?: $settings->get('contact.support_email') ?: '');
        $this->contactPhone = (string) ($settings->get('contact.phone') ?: '');
        $this->contactWhatsapp = (string) ($settings->get('contact.whatsapp') ?: '');
        $this->contactAddress = (string) ($settings->get('contact.address') ?: '');

        $this->socialFacebook = (string) ($settings->get('social.facebook') ?: '');
        $this->socialLinkedin = (string) ($settings->get('social.linkedin') ?: '');
        $this->socialInstagram = (string) ($settings->get('social.instagram') ?: '');
        $this->socialYoutube = (string) ($settings->get('social.youtube') ?: '');
        $this->socialTwitter = (string) ($settings->get('social.twitter') ?: '');

        $this->ctaEnabled = (bool) $settings->get('footer.cta_enabled', true);
        $this->ctaEyebrow = (string) ($settings->get('footer.cta_eyebrow') ?: 'Get in touch');
        $this->ctaHeading = (string) ($settings->get('footer.cta_heading') ?: 'Let’s build something great together.');
        $this->ctaDescription = (string) ($settings->get('footer.cta_description') ?: 'Smart digital solutions for your next chapter.');
        $this->ctaButtonLabel = (string) ($settings->get('footer.cta_button_label') ?: 'Start a project');
        $this->ctaButtonUrl = (string) ($settings->get('footer.cta_button_url') ?: '/contact');
        $this->ctaImage = (string) ($settings->get('footer.cta_image') ?: asset('assets-bizconsult/img/hero.png'));
    }

    public function saveFooterSettings(): void
    {
        Gate::authorize('manage-navigation');

        $this->validate([
            'siteName' => ['required', 'string', 'max:255'],
            'footerDescription' => ['nullable', 'string', 'max:2000'],
            'footerLogoUrl' => ['nullable', 'string', 'max:500'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:60'],
            'contactWhatsapp' => ['nullable', 'string', 'max:60'],
            'contactAddress' => ['nullable', 'string', 'max:1000'],
            'socialFacebook' => ['nullable', 'url:http,https', 'max:500'],
            'socialLinkedin' => ['nullable', 'url:http,https', 'max:500'],
            'socialInstagram' => ['nullable', 'url:http,https', 'max:500'],
            'socialYoutube' => ['nullable', 'url:http,https', 'max:500'],
            'socialTwitter' => ['nullable', 'url:http,https', 'max:500'],
            'ctaEnabled' => ['boolean'],
            'ctaEyebrow' => ['nullable', 'string', 'max:255'],
            'ctaHeading' => ['nullable', 'string', 'max:255'],
            'ctaDescription' => ['nullable', 'string', 'max:2000'],
            'ctaButtonLabel' => ['nullable', 'string', 'max:255'],
            'ctaButtonUrl' => ['nullable', 'string', 'max:500'],
            'ctaImage' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = app(SiteSettings::class);
        $settings->put([
            'site.name' => $this->siteName,
            'footer.company_description' => $this->footerDescription,
            'branding.footer_logo' => $this->footerLogoUrl ?: null,
            'contact.business_email' => $this->contactEmail ?: null,
            'contact.phone' => $this->contactPhone ?: null,
            'contact.whatsapp' => $this->contactWhatsapp ?: null,
            'contact.address' => $this->contactAddress ?: null,
            'social.facebook' => $this->socialFacebook ?: null,
            'social.linkedin' => $this->socialLinkedin ?: null,
            'social.instagram' => $this->socialInstagram ?: null,
            'social.youtube' => $this->socialYoutube ?: null,
            'social.twitter' => $this->socialTwitter ?: null,
            'footer.cta_enabled' => $this->ctaEnabled,
            'footer.cta_eyebrow' => $this->ctaEyebrow,
            'footer.cta_heading' => $this->ctaHeading,
            'footer.cta_description' => $this->ctaDescription,
            'footer.cta_button_label' => $this->ctaButtonLabel,
            'footer.cta_button_url' => $this->ctaButtonUrl,
            'footer.cta_image' => $this->ctaImage,
        ]);

        $this->forgetCaches();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Footer settings saved.');
    }

    // ==========================================
    // PRIMARY / CUSTOM MENU ACTIONS (COMPATIBILITY)
    // ==========================================

    public function edit(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item, 404);
        $this->editingId = (int) $item['id'];
        $this->editingVersion = $this->draftItemVersion($item);
        $this->label = $item['label'];
        $this->type = $item['type'];
        $this->targetId = (string) ($item['target_id'] ?? '');
        $this->url = $item['url'] ?? '';
        $this->target = $item['target'];
        $this->enabled = (bool) $item['enabled'];
        $this->parentId = $item['parent_id'];
    }

    public function startCustomLink(): void
    {
        Gate::authorize('manage-navigation');
        $this->resetForm();
        $this->type = 'custom';
    }

    public function save(): void
    {
        Gate::authorize('manage-navigation');

        // If in footer menu, route to saveLink if linkColumnId or parentId is set
        if ($this->menu === 'footer') {
            if ($this->parentId && ! $this->linkColumnId) {
                $this->linkColumnId = $this->parentId;
            }
            if ($this->linkColumnId) {
                $this->saveLink();

                return;
            }
        }

        $this->validate($this->rules());
        $item = $this->editingId ? $this->draftItem($this->editingId) : null;
        if ($this->editingId && (! $item || $this->editingVersion === null || ! hash_equals($this->editingVersion, $this->draftItemVersion($item)))) {
            $this->addError('menuItems', 'This item changed in your draft while you were editing it. Reload it and reapply your changes.');

            return;
        }
        if ($this->parentId && (($item && (int) $item['id'] === $this->parentId) || ! $this->draftItemIsRoot($this->parentId))) {
            $this->addError('parentId', 'Only one child level is supported.');

            return;
        }

        $id = $item ? (int) $item['id'] : $this->nextDraftId--;
        $this->menuDraft[(string) $id] = array_merge($item ?? [], [
            'id' => $id,
            'menu' => $this->menu,
            'label' => $this->label,
            'type' => $this->type,
            'target_id' => in_array($this->type, ['custom', 'system', 'contact'], true) ? null : (int) $this->targetId,
            'target_type' => $this->targetType(),
            'url' => in_array($this->type, ['custom', 'system', 'contact'], true) ? $this->url : null,
            'target' => $this->target,
            'enabled' => $this->enabled,
            'parent_id' => $this->parentId,
            'sort_order' => $item['sort_order'] ?? $this->nextDraftOrder($this->parentId),
            'is_automatic' => $item['is_automatic'] ?? false,
            'automatic_label' => $item['automatic_label'] ?? null,
        ]);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
        Flux::toast(variant: 'success', text: 'Menu item staged. Save menu to publish these changes.');
        $this->resetForm();
    }

    public function saveMenu(): void
    {
        Gate::authorize('manage-navigation');
        if (! $this->menuDraftIsDirty()) {
            Flux::toast(variant: 'success', text: 'There are no unsaved menu changes.');

            return;
        }

        if (! $this->validateDraft()) {
            return;
        }
        $saved = DB::transaction(function (): bool {
            $currentVersion = $this->databaseMenuVersion($this->menu);
            if (! hash_equals($this->menuDraftVersion, $currentVersion)) {
                $this->addError('menuItems', 'This menu changed after you opened it. Reload before saving to avoid overwriting another administrator.');

                return false;
            }
            if (! $this->validateDraft()) {
                return false;
            }

            $existingItems = NavigationItem::query()
                ->where('menu', $this->menu)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $draftPersistedIds = collect($this->menuDraft)
                ->pluck('id')
                ->filter(fn ($id): bool => (int) $id > 0)
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($existingItems as $id => $existingItem) {
                if (! in_array((int) $id, $draftPersistedIds, true)) {
                    $existingItem->delete();
                }
            }

            $savedIds = [];
            $pending = collect($this->menuDraft)->sortBy('sort_order')->values();
            while ($pending->isNotEmpty()) {
                $progress = false;
                foreach ($pending->values() as $index => $row) {
                    $parentId = $row['parent_id'] === null ? null : (int) $row['parent_id'];
                    if ($parentId !== null && $parentId < 0 && ! isset($savedIds[$parentId])) {
                        continue;
                    }
                    if ($parentId !== null && $parentId < 0) {
                        $parentId = $savedIds[$parentId];
                    }

                    $attributes = $this->draftAttributes($row);
                    $attributes['parent_id'] = $parentId;
                    $item = (int) $row['id'] > 0
                        ? $existingItems->get((int) $row['id'])
                        : new NavigationItem;
                    if (! $item) {
                        throw new \RuntimeException('A menu item no longer belongs to this menu.');
                    }
                    $item->fill($attributes);
                    $item->save();
                    $savedIds[(int) $row['id']] = (int) $item->id;
                    $pending->forget($index);
                    $progress = true;
                }

                if (! $progress) {
                    throw new \RuntimeException('The menu draft contains an invalid parent relationship.');
                }
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'Navigation',
                'subject_type' => NavigationItem::class,
                'subject_id' => 0,
                'subject_label' => $this->menu.' menu',
                'changes' => ['item_count' => count($this->menuDraft)],
            ]);

            return true;
        });

        if (! $saved) {
            return;
        }

        $this->loadMenuDraft();
        $this->resetForm();
        $this->forgetCaches();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Menu changes saved.');
    }

    public function saveLocations(): void
    {
        Gate::authorize('manage-navigation');

        $registry = app(ThemeRegistry::class);
        $activeTheme = $registry->active();
        $theme = $activeTheme['slug'];
        $registeredLocations = array_fill_keys($activeTheme['menu_locations'] ?? [], true);
        $validLocations = array_keys($registeredLocations);
        $footerLocations = array_keys($registry->footerCapabilities($theme)['locations'] ?? []);
        $this->validate(['locationAssignments' => ['array']]);

        foreach ($this->locationAssignments as $location => $assignment) {
            if (! in_array((string) $location, $validLocations, true)) {
                $this->addError('locationAssignments', 'This location is not registered by the active theme.');

                return;
            }
            if ($assignment === null || $assignment === '') {
                continue;
            }

            if (str_starts_with((string) $assignment, 'column:')) {
                $columnId = (int) substr((string) $assignment, 7);
                if (! in_array((string) $location, $footerLocations, true)
                    || ! NavigationItem::query()->where('menu', 'footer')->whereNull('parent_id')->where('type', 'column')->whereKey($columnId)->exists()) {
                    $this->addError("locationAssignments.{$location}", 'Choose a footer column registered for this theme.');

                    return;
                }
            } elseif (! in_array((string) $assignment, $this->menuOptions(), true)) {
                $this->addError("locationAssignments.{$location}", 'Choose a registered menu.');

                return;
            }
        }

        $mapping = app(SiteSettings::class)->get('theme.menu_locations', []);
        if (! is_array($mapping)) {
            $mapping = [];
        }

        $previousAssignments = is_array($mapping[$theme] ?? null) ? $mapping[$theme] : [];
        $previousAutomaticMenus = $this->automaticPageMenus($mapping);
        $newAssignments = array_intersect_key($this->locationAssignments, $registeredLocations);
        $mapping[$theme] = $newAssignments;
        $currentAutomaticMenus = $this->automaticPageMenus($mapping);
        DB::transaction(function () use ($mapping, $activeTheme, $theme, $previousAssignments, $newAssignments): void {
            app(SiteSettings::class)->put(['theme.menu_locations' => $mapping]);
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'locations-updated',
                'module' => 'Navigation',
                'subject_type' => NavigationItem::class,
                'subject_id' => 0,
                'subject_label' => $activeTheme['name'].' menu locations',
                'changes' => [
                    'theme' => $theme,
                    'assignments' => ['old' => $previousAssignments, 'new' => $newAssignments],
                ],
            ]);
        });
        foreach (array_diff($previousAutomaticMenus, $currentAutomaticMenus) as $removedMenu) {
            NavigationItem::query()
                ->where('menu', $removedMenu)
                ->where('type', 'page')
                ->where('is_automatic', true)
                ->update(['enabled' => false]);
        }
        Page::query()->orderBy('id')->chunkById(100, function ($pages): void {
            foreach ($pages as $page) {
                app(\App\Support\PageNavigationSync::class)->sync($page);
            }
        });
        $this->forgetCaches();
        $this->dispatch('navigation-saved');
        Flux::toast(variant: 'success', text: 'Theme menu locations saved.');
    }

    public function remove(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item, 404);
        $removeIds = [(int) $item['id']];
        if ($item['parent_id'] === null) {
            foreach ($this->menuDraft as $row) {
                if ((int) ($row['parent_id'] ?? 0) === (int) $item['id']) {
                    $removeIds[] = (int) $row['id'];
                }
            }
        }
        foreach ($removeIds as $removeId) {
            unset($this->menuDraft[(string) $removeId]);
        }
        $this->renumberDraftSiblings($item['parent_id']);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
        $this->resetForm();
        Flux::toast(variant: 'success', text: 'Item removal staged. Save menu to publish these changes.');
    }

    public function move(int $id, string $direction): void
    {
        Gate::authorize('manage-navigation');
        if (! in_array($direction, ['up', 'down'], true)) {
            $this->addError('menuItems', 'Choose a valid item direction.');

            return;
        }
        $item = $this->draftItem($id);
        abort_if(! $item, 404);
        $siblings = collect($this->menuDraft)
            ->filter(fn (array $row): bool => ($row['parent_id'] ?? null) === $item['parent_id'])
            ->sortBy('sort_order')
            ->values();
        $index = $siblings->search(fn (array $sibling): bool => (int) $sibling['id'] === $id);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if ($index === false || ! $siblings->has($swap)) {
            return;
        }
        [$siblings[$index], $siblings[$swap]] = [$siblings[$swap], $siblings[$index]];
        foreach ($siblings as $order => $sibling) {
            $this->menuDraft[(string) $sibling['id']]['sort_order'] = $order + 1;
        }
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function indent(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] !== null, 404);
        if (collect($this->menuDraft)->contains(fn (array $row): bool => (int) ($row['parent_id'] ?? 0) === $id)) {
            $this->addError('menuItems', 'An item with children cannot be indented further.');

            return;
        }

        $siblings = collect($this->menuDraft)->filter(fn (array $row): bool => $row['parent_id'] === null)->sortBy('sort_order')->values();
        $index = $siblings->search(fn (array $sibling): bool => (int) $sibling['id'] === $id);
        if ($index === false || $index === 0) {
            $this->addError('menuItems', 'Move an item below a top-level item before indenting it.');

            return;
        }

        $parent = $siblings[$index - 1];
        $this->menuDraft[(string) $id]['parent_id'] = (int) $parent['id'];
        $this->menuDraft[(string) $id]['sort_order'] = $this->nextDraftOrder((int) $parent['id']);
        $this->renumberDraftSiblings(null);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function outdent(int $id): void
    {
        Gate::authorize('manage-navigation');
        $item = $this->draftItem($id);
        abort_if(! $item || $item['parent_id'] === null, 404);
        $oldParentId = $item['parent_id'];
        $this->menuDraft[(string) $id]['parent_id'] = null;
        $this->menuDraft[(string) $id]['sort_order'] = $this->nextDraftOrder(null);
        $this->renumberDraftSiblings($oldParentId);
        $this->menuDraftDirty = $this->menuDraftIsDirty();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftDirty);
    }

    public function render()
    {
        Gate::authorize('navigation.view');

        $activeTheme = app(ThemeRegistry::class)->active();
        $footerCapabilities = app(ThemeRegistry::class)->footerCapabilities();

        $contentOptions = $this->contentOptions();
        $menuOptions = $this->menuOptions();

        // Footer-specific data
        $footerColumns = $this->draftTreeModels()
            ->filter(fn (NavigationItem $item): bool => $item->type === 'column')
            ->values();

        // If no linkColumnId currently set, default to first column
        if (! $this->linkColumnId && $footerColumns->isNotEmpty()) {
            $this->linkColumnId = $footerColumns->first()->id;
        }

        $footerLinkCount = collect($this->menuDraft)->filter(fn (array $item): bool => $item['parent_id'] !== null)->count();

        // Filter published pages if search term is provided
        $publishedPages = Page::query()
            ->where('status', 'published')
            ->when($this->pageSearch !== '', fn ($q) => $q->where('title', 'like', '%'.$this->pageSearch.'%'))
            ->orderBy('title')
            ->get();

        $publishedServices = Service::query()
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $previewData = $this->showPreview
            ? app(FooterService::class)->data()
            : null;

        // Standard menu items for non-footer menus
        $items = $this->draftTreeModels();

        return view('livewire.admin.navigation-manager', [
            'items' => $items,
            'options' => $contentOptions,
            'parents' => collect($this->menuDraft)
                ->filter(fn (array $row): bool => $row['parent_id'] === null && (int) $row['id'] !== $this->editingId)
                ->sortBy('sort_order')
                ->values()
                ->map(fn (array $row): NavigationItem => $this->draftModel($row)),
            'menuOptions' => $menuOptions,
            'themeLocations' => $activeTheme['menu_locations'] ?? [],
            'activeTheme' => $activeTheme,
            'footerCapabilities' => $footerCapabilities,
            'footerColumns' => $footerColumns,
            'footerLinkCount' => $footerLinkCount,
            'publishedPages' => $publishedPages,
            'publishedServices' => $publishedServices,
            'previewData' => $previewData,
        ])->layout('layouts.admin')->title('Menus');
    }

    private function rules(): array
    {
        $rules = [
            'menu' => ['required', 'string', 'max:40', 'regex:/\A[a-z0-9_-]+\z/'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($this->menu === 'footer' ? NavigationItem::TYPES : array_diff(NavigationItem::TYPES, ['column']))],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'enabled' => ['boolean'],
            'parentId' => ['nullable', 'integer'],
        ];

        if ($this->type === 'custom') {
            $rules['url'] = ['required', 'string', 'max:500', 'regex:/\A(?:https?:\/\/[^\s]+|\/(?!\/)[^\s]*|mailto:[^\s]+|tel:[^\s]+)\z/i'];
        } elseif ($this->type === 'system') {
            $rules['url'] = ['required', 'string', 'max:255', Rule::in(array_keys($this->systemOptions()))];
        } elseif ($this->type === 'contact') {
            // No URL validation required
        } elseif ($this->type === 'column') {
            // Columns only need label
        } else {
            $rules['targetId'] = ['required', 'integer', Rule::exists($this->tableForType(), 'id')->where('status', 'published')];
        }

        return $rules;
    }

    private function targetType(): ?string
    {
        if (in_array($this->type, ['custom', 'system', 'column', 'contact'], true)) {
            return null;
        }

        return ['page' => Page::class, 'service' => Service::class, 'project' => Project::class, 'blog' => BlogPost::class][$this->type] ?? null;
    }

    private function loadMenuDraft(): void
    {
        $rows = NavigationItem::query()
            ->where('menu', $this->menu)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $this->menuDraft = $rows->mapWithKeys(fn (NavigationItem $item): array => [(string) $item->id => [
            'id' => (int) $item->id,
            'menu' => $item->menu,
            'label' => $item->label,
            'type' => $item->type,
            'target_type' => $item->target_type,
            'target_id' => $item->target_id === null ? null : (int) $item->target_id,
            'url' => $item->url,
            'target' => $item->target,
            'enabled' => (bool) $item->enabled,
            'parent_id' => $item->parent_id === null ? null : (int) $item->parent_id,
            'sort_order' => (int) $item->sort_order,
            'is_automatic' => (bool) $item->is_automatic,
            'automatic_label' => $item->automatic_label,
            'updated_at' => $item->updated_at?->toISOString(),
        ]])->all();
        $this->menuDraftOriginal = $this->menuDraft;
        $this->menuDraftVersion = $this->draftVersion($this->menuDraft);
        $this->nextDraftId = -1;
        $this->menuDraftDirty = false;
        if ($this->menu === 'footer' && (! $this->linkColumnId || ! $this->draftItemIsRoot($this->linkColumnId) || $this->draftItem($this->linkColumnId)['type'] !== 'column')) {
            $firstColumn = collect($this->menuDraft)
                ->filter(fn (array $item): bool => $item['parent_id'] === null && $item['type'] === 'column')
                ->sortBy('sort_order')
                ->first();
            $this->linkColumnId = $firstColumn ? (int) $firstColumn['id'] : null;
        }
    }

    private function databaseMenuVersion(string $menu): string
    {
        $rows = NavigationItem::query()->where('menu', $menu)->orderBy('id')->lockForUpdate()->get();
        $draft = $rows->mapWithKeys(fn (NavigationItem $item): array => [(string) $item->id => [
            'id' => (int) $item->id,
            'menu' => $item->menu,
            'label' => $item->label,
            'type' => $item->type,
            'target_type' => $item->target_type,
            'target_id' => $item->target_id === null ? null : (int) $item->target_id,
            'url' => $item->url,
            'target' => $item->target,
            'enabled' => (bool) $item->enabled,
            'parent_id' => $item->parent_id === null ? null : (int) $item->parent_id,
            'sort_order' => (int) $item->sort_order,
            'is_automatic' => (bool) $item->is_automatic,
            'automatic_label' => $item->automatic_label,
            'updated_at' => $item->updated_at?->toISOString(),
        ]])->all();

        return $this->draftVersion($draft);
    }

    private function draftVersion(array $draft): string
    {
        ksort($draft);

        return hash('sha256', json_encode($draft, JSON_THROW_ON_ERROR));
    }

    private function menuDraftIsDirty(): bool
    {
        return $this->draftVersion($this->menuDraft) !== $this->draftVersion($this->menuDraftOriginal);
    }

    private function draftItem(int $id): ?array
    {
        return $this->menuDraft[(string) $id] ?? null;
    }

    private function draftItemVersion(array $item): string
    {
        unset($item['id']);

        return hash('sha256', json_encode($item, JSON_THROW_ON_ERROR));
    }

    private function draftItemIsRoot(int $id): bool
    {
        $item = $this->draftItem($id);

        return $item !== null && $item['parent_id'] === null;
    }

    private function nextDraftOrder(?int $parentId): int
    {
        $max = collect($this->menuDraft)
            ->filter(fn (array $item): bool => ($item['parent_id'] ?? null) === $parentId)
            ->max('sort_order');

        return (int) $max + 1;
    }

    private function renumberDraftSiblings(?int $parentId): void
    {
        $siblings = collect($this->menuDraft)
            ->filter(fn (array $item): bool => ($item['parent_id'] ?? null) === $parentId)
            ->sortBy('sort_order')
            ->values();
        foreach ($siblings as $index => $sibling) {
            $this->menuDraft[(string) $sibling['id']]['sort_order'] = $index + 1;
        }
    }

    private function draftTreeModels(): \Illuminate\Support\Collection
    {
        $rows = collect($this->menuDraft)->sortBy('sort_order')->values();

        return $rows->filter(fn (array $row): bool => $row['parent_id'] === null)
            ->map(function (array $row) use ($rows): NavigationItem {
                $model = $this->draftModel($row);
                $children = $rows->filter(fn (array $child): bool => (int) ($child['parent_id'] ?? 0) === (int) $row['id'])
                    ->sortBy('sort_order')
                    ->map(fn (array $child): NavigationItem => $this->draftModel($child))
                    ->values();
                $model->setRelation('children', $children);

                return $model;
            })
            ->values();
    }

    private function draftModel(array $row): NavigationItem
    {
        $model = new NavigationItem;
        foreach ($row as $key => $value) {
            if ($key !== 'updated_at') {
                $model->setAttribute($key, $value);
            }
        }

        return $model;
    }

    private function draftAttributes(array $row): array
    {
        return [
            'menu' => $this->menu,
            'label' => trim((string) $row['label']),
            'type' => $row['type'],
            'target_type' => $row['target_type'] ?? null,
            'target_id' => $row['target_id'] === null ? null : (int) $row['target_id'],
            'url' => $row['url'] ?? null,
            'target' => $row['target'],
            'enabled' => (bool) $row['enabled'],
            'sort_order' => (int) $row['sort_order'],
            'parent_id' => $row['parent_id'] === null ? null : (int) $row['parent_id'],
            'is_automatic' => (bool) ($row['is_automatic'] ?? false),
            'automatic_label' => $row['automatic_label'] ?? null,
        ];
    }

    private function validateDraft(): bool
    {
        if (count($this->menuDraft) > 500) {
            $this->addError('menuItems', 'A menu cannot contain more than 500 items.');

            return false;
        }

        $ids = [];
        foreach ($this->menuDraft as $key => $row) {
            $validator = Validator::make($row, [
                'id' => ['required', 'integer'],
                'menu' => ['required', 'string', Rule::in([$this->menu])],
                'label' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::in($this->menu === 'footer' ? NavigationItem::TYPES : array_diff(NavigationItem::TYPES, ['column']))],
                'target_type' => ['nullable', 'string', Rule::in([Page::class, Service::class, Project::class, BlogPost::class])],
                'target_id' => ['nullable', 'integer', 'min:1'],
                'url' => ['nullable', 'string', 'max:500'],
                'target' => ['required', Rule::in(['_self', '_blank'])],
                'enabled' => ['required', 'boolean'],
                'parent_id' => ['nullable', 'integer'],
                'sort_order' => ['required', 'integer', 'min:1'],
                'is_automatic' => ['boolean'],
                'automatic_label' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                $this->addError('menuItems', 'One or more menu items have invalid data. Review the editor fields and try again.');

                return false;
            }

            $id = (int) $row['id'];
            if (isset($ids[$id]) || (string) $id !== (string) $key) {
                $this->addError('menuItems', 'The menu draft contains duplicate or invalid item identifiers.');

                return false;
            }
            $ids[$id] = true;

            if ($id > 0 && ! NavigationItem::query()->where('menu', $this->menu)->whereKey($id)->exists()) {
                $this->addError('menuItems', 'A menu item no longer belongs to this menu. Reload and try again.');

                return false;
            }

            if ($row['parent_id'] !== null) {
                $parent = $this->draftItem((int) $row['parent_id']);
                if (! $parent || $parent['parent_id'] !== null || (int) $parent['id'] === $id
                    || ($this->menu === 'footer' && $parent['type'] !== 'column')
                    || ($this->menu !== 'footer' && $parent['type'] === 'column')) {
                    $this->addError('menuItems', 'Menu items may only be nested one level below a valid top-level item.');

                    return false;
                }
            }
            if ($row['type'] === 'column' && ($this->menu !== 'footer' || $row['parent_id'] !== null)) {
                $this->addError('menuItems', 'Footer columns may only be top-level items in the footer menu.');

                return false;
            }

            if ($row['type'] === 'column') {
                if ($this->menu !== 'footer' || $row['parent_id'] !== null) {
                    $this->addError('menuItems', 'Footer columns must be top-level items in the footer menu.');

                    return false;
                }
                continue;
            }

            if (in_array($row['type'], ['custom', 'system', 'contact'], true)) {
                if ($row['type'] === 'custom' && ! preg_match('/\A(?:https?:\/\/[^\s]+|\/(?!\/)[^\s]*|mailto:[^\s]+|tel:[^\s]+)\z/i', (string) $row['url'])) {
                    $this->addError('menuItems', 'A custom menu URL is missing or unsafe.');

                    return false;
                }
                if ($row['type'] === 'system' && ! array_key_exists((string) $row['url'], $this->systemOptions())) {
                    $this->addError('menuItems', 'Choose a registered system destination.');

                    return false;
                }
                continue;
            }

            $model = match ($row['type']) {
                'page' => Page::class,
                'service' => Service::class,
                'project' => Project::class,
                'blog' => BlogPost::class,
            };
            $record = $model::query()->whereKey($row['target_id'])->where('status', 'published')->first();
            if (! $record
                || ($row['type'] === 'service' && ! $record->is_active)
                || ($row['type'] !== 'service' && $record->published_at?->isFuture())) {
                $this->addError('menuItems', 'A linked content item is no longer published or available.');

                return false;
            }
        }

        foreach ($this->menuDraft as $row) {
            if ($row['parent_id'] !== null && ! isset($ids[(int) $row['parent_id']])) {
                $this->addError('menuItems', 'A menu item references a parent that is not part of this menu.');

                return false;
            }
        }

        return true;
    }

    private function automaticPageMenus(array $mapping): array
    {
        $menus = [];
        foreach ($mapping as $themeAssignments) {
            if (! is_array($themeAssignments)) {
                continue;
            }

            foreach ($themeAssignments as $location => $menu) {
                $isManagedLocation = in_array($location, ['primary', 'mobile'], true)
                    || (str_starts_with((string) $location, 'footer') && ! str_contains((string) $location, 'legal'));
                if ($isManagedLocation && is_string($menu) && $menu !== '' && ! str_starts_with($menu, 'column:')) {
                    $menus[] = $menu;
                }
            }
        }

        return array_values(array_unique($menus));
    }

    private function tableForType(): string
    {
        return ['page' => 'pages', 'service' => 'services', 'project' => 'projects', 'blog' => 'blog_posts'][$this->type] ?? 'pages';
    }

    private function contentOptions(): array
    {
        $content = collect([
            'page' => [Page::class, 'pages'],
            'service' => [Service::class, 'services'],
            'project' => [Project::class, 'projects'],
            'blog' => [BlogPost::class, 'blog'],
        ])->mapWithKeys(function (array $definition, string $type): array {
            $query = $definition[0]::query()->where('status', 'published');
            if ($type === 'service') {
                $query->where('is_active', true);
            } else {
                $query->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
            }
            if (trim($this->contentSearch) !== '') {
                $query->where($type === 'service' || $type === 'project' ? 'name' : 'title', 'like', '%'.trim($this->contentSearch).'%');
            }

            return [$type => $query->orderBy($type === 'service' || $type === 'project' ? 'name' : 'title')->get()];
        })->all();

        $content['system'] = collect($this->systemOptions())->map(fn (string $label, string $key): object => (object) ['id' => $key, 'title' => $label, 'name' => $label]);

        return $content;
    }

    public function systemOptions(): array
    {
        return [
            'home' => 'Home',
            'about' => 'About',
            'services' => 'Services',
            'portfolio' => 'Portfolio',
            'blog' => 'Blog',
            'contact' => 'Contact',
        ];
    }

    public function menuOptions(): array
    {
        $menus = array_merge(['primary', 'footer'], NavigationItem::allMenuKeys());

        return array_values(array_unique(array_filter(array_map('strval', $menus), fn (string $menu): bool => $menu !== '')));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'editingVersion', 'label', 'targetId', 'url', 'parentId', 'newMenuName', 'pageSearch', 'selectedContentIds', 'contentSearch']);
        $this->type = 'page';
        $this->target = '_self';
        $this->enabled = true;
        $this->syncLocationAssignments();
        $this->dispatch('navigation-draft-changed', dirty: $this->menuDraftIsDirty());
    }

    private function registerMenu(string $menu): void
    {
        $menu = trim((string) $menu);
        if ($menu === '') {
            return;
        }

        $existing = app(SiteSettings::class)->get('cms.navigation.menus', []);
        $menus = is_array($existing) ? $existing : [];
        $menus[] = $menu;

        app(SiteSettings::class)->put([
            'cms.navigation.menus' => array_values(array_unique(array_filter(array_map('strval', $menus), fn (string $value): bool => $value !== ''))),
        ]);
    }

    public function syncLocationAssignments(): void
    {
        $theme = app(ThemeRegistry::class)->active()['slug'];
        $mapping = app(SiteSettings::class)->get('theme.menu_locations', []);
        $this->locationAssignments = is_array($mapping) && isset($mapping[$theme]) && is_array($mapping[$theme])
            ? $mapping[$theme]
            : [];
    }

    private function forgetCaches(): void
    {
        NavigationService::forget();
        FooterService::forget();
    }
}
