<div class="rex-content-form-root">
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.blog.categories.index') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Blog Categories</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $showForm ? ($editingId ? 'Edit Category' : 'Create Category') : 'Categories' }}</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-lg bg-[#eef4ff] text-[#000080]"><i class="fa-solid fa-folder-tree" aria-hidden="true"></i></span>
                <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Editorial</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $showForm ? ($editingId ? 'Edit Category' : 'Create Category') : 'Blog Categories' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Organize posts into clear editorial topics.</p>
                </div>
            </div>
        </div>

        @if ($showForm)
            <form wire:submit.prevent="save" class="rex-page-edit-grid">
                <main class="space-y-4">
                    <section class="rex-page-edit-card">
                        <div class="rex-page-edit-card-heading">
                            <div>
                                <h2><i class="fa-solid fa-folder-open mr-2 text-[#000080]" aria-hidden="true"></i>Category details</h2>
                                <p>Keep the name and slug consistent for clean blog organization.</p>
                            </div>
                        </div>

                        <div class="rex-page-edit-fields">
                            <div>
                                <label for="blog-category-name">Name <span>*</span></label>
                                <input id="blog-category-name" wire:model.live="name" type="text" value="{{ $name }}" placeholder="Strategy, Product, Support" />
                                @error('name')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="blog-category-slug">Slug <span>*</span></label>
                                <div class="rex-page-edit-slug">
                                    <span>{{ url('/') }}/</span>
                                    <input id="blog-category-slug" wire:model="slug" type="text" value="{{ $slug }}" placeholder="strategy" />
                                </div>
                                @error('slug')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="blog-category-description">Description</label>
                                <textarea id="blog-category-description" wire:model="description" rows="6" placeholder="Summarize how this category is used.">{{ $description }}</textarea>
                                @error('description')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="blog-category-parent">Parent</label>
                                <select id="blog-category-parent" wire:model="parentId">
                                    <option value="">None</option>
                                    @foreach ($parentOptions as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                @error('parentId')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="blog-category-icon">Font Awesome icon</label>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#eef4ff] text-[#000080]"><i class="{{ $icon ?: 'fa-solid fa-folder' }}" aria-hidden="true"></i></span>
                                    <input id="blog-category-icon" wire:model="icon" type="text" placeholder="fa-solid fa-folder" />
                                </div>
                                @error('icon')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="rex-page-edit-check"><input type="checkbox" wire:model="isDefault" /> Is default?</label>
                                <label class="rex-page-edit-check"><input type="checkbox" wire:model="isFeatured" /> Is featured?</label>
                            </div>

                            <div>
                                <label for="blog-category-status">Status <span>*</span></label>
                                <select id="blog-category-status" wire:model="status"><option value="published">Published</option><option value="draft">Draft</option></select>
                                @error('status')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <section class="border-t border-[#eef1f6] pt-4">
                                <h3 class="text-sm font-bold text-[#172033]"><i class="fa-solid fa-magnifying-glass mr-2 text-[#000080]" aria-hidden="true"></i>Search Engine Optimize</h3>
                                <p class="mt-1 text-xs text-[#64748b]">Optional metadata for category archive pages.</p>
                                <div class="mt-4 space-y-4">
                                    <div><label for="blog-category-seo-title">SEO title</label><input id="blog-category-seo-title" wire:model="seoTitle" type="text" placeholder="Category title for search engines" />@error('seoTitle')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                                    <div><label for="blog-category-seo-description">SEO description</label><textarea id="blog-category-seo-description" wire:model="seoDescription" rows="4" placeholder="Short search result description."></textarea>@error('seoDescription')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                                </div>
                            </section>
                        </div>
                    </section>
                </main>

                <aside class="rex-page-edit-sidebar">
                    <section class="rex-page-edit-card rex-page-edit-publish">
                        <div class="rex-page-edit-card-heading">
                            <h2><i class="fa-solid fa-floppy-disk mr-2 text-[#000080]" aria-hidden="true"></i>Publish</h2>
                        </div>
                        <div class="space-y-3">
                            <button type="submit" wire:loading.attr="disabled" class="rex-page-edit-button rex-page-edit-button--primary">
                                <span wire:loading.remove><i class="fa-solid fa-check mr-2" aria-hidden="true"></i>Save category</span>
                                <span wire:loading>Saving...</span>
                            </button>
                            <a href="{{ route('admin.blog.categories.index') }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                        </div>
                    </section>
                </aside>
            </form>
        @endif

        @error('delete')<p class="border-b border-red-100 bg-red-50 px-5 py-3 text-sm text-red-700">{{ $message }}</p>@enderror

        <section class="overflow-hidden rounded-[12px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
            <div class="flex flex-col gap-4 border-b border-[#eef1f6] p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2><i class="fa-solid fa-layer-group mr-2 text-[#000080]" aria-hidden="true"></i>Categories</h2>
                    <p>Review every blog category in the CMS.</p>
                </div>
                @can('blog.create')<button type="button" wire:click="create" class="inline-flex items-center justify-center rounded-[5px] bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"><i class="fa-solid fa-plus mr-2" aria-hidden="true"></i>Create category</button>@endcan
            </div>

            <div class="flex items-center gap-3 border-b border-[#eef1f6] px-5 py-4">
                <i class="fa-solid fa-magnifying-glass text-[#94a3b8]" aria-hidden="true"></i>
                <label for="category-search" class="sr-only">Search categories</label>
                <input id="category-search" wire:model.live.debounce.300ms="search" type="search" placeholder="Search categories..." class="w-full border-0 bg-transparent text-sm text-[#172033] outline-none focus:ring-0">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left">
                    <thead class="bg-[#f7f9fc] text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                        <tr>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Slug</th>
                            <th class="px-5 py-3">Posts</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef1f6]">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-[#172033]">
                                    <i class="{{ $item->icon ?: 'fa-solid fa-folder' }} mr-2 text-[#000080]" aria-hidden="true"></i>{{ $item->name }}
                                    <p class="mt-1 text-xs font-normal text-[#64748b]">{{ $item->description }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-[#64748b]">{{ $item->slug }}</td>
                                <td class="px-5 py-4 text-sm text-[#64748b]">{{ $item->posts_count }}</td>
                                <td class="px-5 py-4 text-right">
                                    @can('blog.update')<a href="{{ route('admin.blog.categories.edit', $item) }}" wire:navigate class="mr-3 text-sm font-semibold text-[#2563eb]">Edit</a>@endcan
                                    @can('blog.delete')<button type="button" wire:click="confirmDelete({{ $item->id }})" class="text-sm font-semibold text-red-600">Delete</button>@endcan
                                </td>
                            </tr>
                            @foreach ($item->children as $child)
                                <tr class="bg-[#fbfcfe]">
                                    <td class="px-5 py-3 pl-10 text-sm font-medium text-[#334155]"><i class="fa-solid fa-turn-up mr-2 rotate-90 text-[#94a3b8]" aria-hidden="true"></i><i class="{{ $child->icon ?: 'fa-solid fa-folder' }} mr-2 text-[#64748b]" aria-hidden="true"></i>{{ $child->name }}</td>
                                    <td class="px-5 py-3 text-sm text-[#64748b]">{{ $child->slug }}</td>
                                    <td class="px-5 py-3 text-sm text-[#64748b]">{{ $child->posts_count }}</td>
                                    <td class="px-5 py-3 text-right">@can('blog.update')<a href="{{ route('admin.blog.categories.edit', $child) }}" wire:navigate class="mr-3 text-sm font-semibold text-[#2563eb]">Edit</a>@endcan @can('blog.delete')<button type="button" wire:click="confirmDelete({{ $child->id }})" class="text-sm font-semibold text-red-600">Delete</button>@endcan</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center text-sm text-[#64748b]"><i class="fa-regular fa-folder-open mb-3 block text-2xl text-[#94a3b8]" aria-hidden="true"></i>No blog categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#eef1f6] px-5 py-4">{{ $items->links() }}</div>
        </section>
    </div>

    @if ($deletingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#172033]/40 px-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-md border border-[#e5eaf2] bg-white p-6 shadow-xl">
                <h2 class="text-lg font-bold text-[#172033]"><i class="fa-solid fa-triangle-exclamation mr-2 text-red-600" aria-hidden="true"></i>Delete this category?</h2>
                <p class="mt-2 text-sm text-[#64748b]">Categories assigned to posts cannot be deleted.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('deletingId', null)" class="border border-[#dbe2ed] px-4 py-2 text-sm">Cancel</button>
                    <button type="button" wire:click="delete" class="bg-red-600 px-4 py-2 text-sm font-semibold text-white">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
