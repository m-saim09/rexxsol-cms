<div class="rex-content-form-root">
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.blog.tags.index') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Blog Tags</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $showForm ? ($editingId ? 'Edit Tag' : 'Create Tag') : 'Tags' }}</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-lg bg-[#eef4ff] text-[#000080]"><i class="fa-solid fa-tags" aria-hidden="true"></i></span>
                <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Editorial</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $showForm ? ($editingId ? 'Edit Tag' : 'Create Tag') : 'Blog Tags' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Use reusable labels to improve editorial organization.</p>
                </div>
            </div>
        </div>

        @if ($showForm)
            <form wire:submit.prevent="save" class="rex-page-edit-grid">
                <main class="space-y-4">
                    <section class="rex-page-edit-card">
                        <div class="rex-page-edit-card-heading">
                            <div>
                                <h2><i class="fa-solid fa-tag mr-2 text-[#000080]" aria-hidden="true"></i>Tag details</h2>
                                <p>Give each tag a stable name and slug that matches your content taxonomy.</p>
                            </div>
                        </div>

                        <div class="rex-page-edit-fields">
                            <div>
                                <label for="blog-tag-name">Name <span>*</span></label>
                                <input id="blog-tag-name" wire:model.live="name" type="text" value="{{ $name }}" placeholder="Insight, Update, Launch" />
                                @error('name')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="blog-tag-slug">Slug <span>*</span></label>
                                <div class="rex-page-edit-slug">
                                    <span>{{ url('/') }}/</span>
                                    <input id="blog-tag-slug" wire:model="slug" type="text" value="{{ $slug }}" placeholder="insight" />
                                </div>
                                @error('slug')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>
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
                                <span wire:loading.remove><i class="fa-solid fa-check mr-2" aria-hidden="true"></i>Save tag</span>
                                <span wire:loading>Saving...</span>
                            </button>
                            <a href="{{ route('admin.blog.tags.index') }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                        </div>
                    </section>
                </aside>
            </form>
        @endif

        @error('delete')<p class="border-b border-red-100 bg-red-50 px-5 py-3 text-sm text-red-700">{{ $message }}</p>@enderror

        <section class="overflow-hidden rounded-[12px] border border-[#e5eaf2] bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
            <div class="flex flex-col gap-4 border-b border-[#eef1f6] p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2><i class="fa-solid fa-tags mr-2 text-[#000080]" aria-hidden="true"></i>Tags</h2>
                    <p>Review every blog tag in the CMS.</p>
                </div>
                @can('blog.create')<button type="button" wire:click="create" class="inline-flex items-center justify-center rounded-[5px] bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]"><i class="fa-solid fa-plus mr-2" aria-hidden="true"></i>Create tag</button>@endcan
            </div>

            <div class="flex items-center gap-3 border-b border-[#eef1f6] px-5 py-4">
                <i class="fa-solid fa-magnifying-glass text-[#94a3b8]" aria-hidden="true"></i>
                <label for="tag-search" class="sr-only">Search tags</label>
                <input id="tag-search" wire:model.live.debounce.300ms="search" type="search" placeholder="Search tags..." class="w-full border-0 bg-transparent text-sm text-[#172033] outline-none focus:ring-0">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left">
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
                                <td class="px-5 py-4 text-sm font-semibold text-[#172033]">{{ $item->name }}</td>
                                <td class="px-5 py-4 text-sm text-[#64748b]">{{ $item->slug }}</td>
                                <td class="px-5 py-4 text-sm text-[#64748b]">{{ $item->posts_count }}</td>
                                <td class="px-5 py-4 text-right">
                                    @can('blog.update')<a href="{{ route('admin.blog.tags.edit', $item) }}" wire:navigate class="mr-3 text-sm font-semibold text-[#2563eb]">Edit</a>@endcan
                                    @can('blog.delete')<button type="button" wire:click="confirmDelete({{ $item->id }})" class="text-sm font-semibold text-red-600">Delete</button>@endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center text-sm text-[#64748b]"><i class="fa-regular fa-bookmark mb-3 block text-2xl text-[#94a3b8]" aria-hidden="true"></i>No blog tags found.</td>
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
                <h2 class="text-lg font-bold text-[#172033]"><i class="fa-solid fa-triangle-exclamation mr-2 text-red-600" aria-hidden="true"></i>Delete this tag?</h2>
                <p class="mt-2 text-sm text-[#64748b]">Tags assigned to posts cannot be deleted.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('deletingId', null)" class="border border-[#dbe2ed] px-4 py-2 text-sm">Cancel</button>
                    <button type="button" wire:click="delete" class="bg-red-600 px-4 py-2 text-sm font-semibold text-white">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
