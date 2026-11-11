<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BlogCategories extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public ?int $parentId = null;

    public string $icon = 'fa-solid fa-folder';

    public bool $isDefault = false;

    public bool $isFeatured = false;

    public string $status = 'published';

    public string $seoTitle = '';

    public string $seoDescription = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    public ?int $deletingId = null;

    public function mount(?BlogCategory $category = null): void
    {
        Gate::authorize($category?->exists ? 'blog.update' : (request()->routeIs('admin.blog.categories.create') ? 'blog.create' : 'blog.view'));
        if ($category?->exists) {
            $this->edit($category->id);
        } else {
            $this->showForm = request()->routeIs('admin.blog.categories.create');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        if (! $this->editingId || $this->slug === '') {
            $this->slug = Str::slug($this->name);
        }
    }

    public function create(): void
    {
        Gate::authorize('blog.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('blog.update');
        $category = BlogCategory::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->parentId = $category->parent_id;
        $this->icon = $category->icon ?: 'fa-solid fa-folder';
        $this->isDefault = (bool) $category->is_default;
        $this->isFeatured = (bool) $category->is_featured;
        $this->status = $category->status ?: 'published';
        $this->seoTitle = $category->seo_title ?? '';
        $this->seoDescription = $category->seo_description ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'blog.update' : 'blog.create');
        if ($this->status === 'published' && (! $this->editingId || BlogCategory::query()->whereKey($this->editingId)->value('status') !== 'published')) {
            Gate::authorize('blog.publish');
        }
        $this->slug = Str::slug($this->slug ?: $this->name);
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('blog_categories', 'slug')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'parentId' => ['nullable', 'integer', Rule::exists('blog_categories', 'id')->where('id', '!=', $this->editingId)],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/\Afa-(?:solid|regular|brands) fa-[a-z0-9-]+\z/'],
            'isDefault' => ['boolean'],
            'isFeatured' => ['boolean'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'seoTitle' => ['nullable', 'string', 'max:255'],
            'seoDescription' => ['nullable', 'string', 'max:255'],
        ]);
        if ($this->parentId !== null && $this->parentId === $this->editingId) {
            $this->addError('parentId', 'A category cannot be its own parent.');

            return;
        }
        BlogCategory::query()->updateOrCreate(['id' => $this->editingId], ['name' => trim($this->name), 'slug' => $this->slug, 'description' => $this->description ?: null, 'parent_id' => $this->parentId ?: null, 'icon' => $this->icon ?: null, 'is_default' => $this->isDefault, 'is_featured' => $this->isFeatured, 'status' => $this->status, 'seo_title' => $this->seoTitle ?: null, 'seo_description' => $this->seoDescription ?: null]);
        Flux::toast(variant: 'success', text: 'Blog category saved successfully.');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('blog.delete');
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        Gate::authorize('blog.delete');
        $category = BlogCategory::query()->withCount('posts')->findOrFail($this->deletingId);
        if ($category->posts_count > 0) {
            $this->addError('delete', 'This category is assigned to blog posts and cannot be deleted.');
            $this->deletingId = null;

            return;
        }
        $category->delete();
        $this->deletingId = null;
        Flux::toast(variant: 'success', text: 'Blog category deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'slug', 'description', 'parentId', 'icon', 'isDefault', 'isFeatured', 'status', 'seoTitle', 'seoDescription', 'editingId', 'deletingId']);
        $this->icon = 'fa-solid fa-folder';
        $this->status = 'published';
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('blog.view');

        return view('livewire.admin.blog-categories', ['items' => BlogCategory::query()->with(['children' => fn ($query) => $query->withCount('posts')])->withCount('posts')->whereNull('parent_id')->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))->orderBy('name')->paginate(15), 'parentOptions' => BlogCategory::query()->when($this->editingId, fn ($query) => $query->where('id', '!=', $this->editingId))->whereNull('parent_id')->orderBy('name')->get()])->layout('layouts.admin')->title('Blog Categories');
    }
}
