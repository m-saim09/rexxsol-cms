<?php

namespace App\Livewire\Admin;

use App\Models\BlogTag;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BlogTags extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public string $slug = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    public ?int $deletingId = null;

    public function mount(?BlogTag $tag = null): void
    {
        Gate::authorize($tag?->exists ? 'blog.update' : (request()->routeIs('admin.blog.tags.create') ? 'blog.create' : 'blog.view'));
        if ($tag?->exists) {
            $this->edit($tag->id);
        } else {
            $this->showForm = request()->routeIs('admin.blog.tags.create');
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
        $this->reset(['name', 'slug', 'editingId']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('blog.update');
        $tag = BlogTag::query()->findOrFail($id);
        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->showForm = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'blog.update' : 'blog.create');
        $this->slug = Str::slug($this->slug ?: $this->name);
        $this->validate(['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('blog_tags', 'slug')->ignore($this->editingId)]]);
        BlogTag::query()->updateOrCreate(['id' => $this->editingId], ['name' => trim($this->name), 'slug' => $this->slug]);
        Flux::toast(variant: 'success', text: 'Blog tag saved successfully.');
        $this->reset(['name', 'slug', 'editingId']);
        $this->showForm = false;
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('blog.delete');
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        Gate::authorize('blog.delete');
        $tag = BlogTag::query()->withCount('posts')->findOrFail($this->deletingId);
        if ($tag->posts_count > 0) {
            $this->addError('delete', 'This tag is assigned to blog posts and cannot be deleted.');
            $this->deletingId = null;

            return;
        }
        $tag->delete();
        $this->deletingId = null;
        Flux::toast(variant: 'success', text: 'Blog tag deleted successfully.');
    }

    public function render()
    {
        Gate::authorize('blog.view');

        return view('livewire.admin.blog-tags', ['items' => BlogTag::query()->withCount('posts')->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))->orderBy('name')->paginate(15)])->layout('layouts.admin')->title('Blog Tags');
    }
}
