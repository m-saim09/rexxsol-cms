<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class BlogReports extends Component
{
    public function mount(): void
    {
        Gate::authorize('blog.view');
    }

    public function render()
    {
        Gate::authorize('blog.view');

        return view('livewire.admin.blog-reports', [
            'published' => BlogPost::query()->where('status', 'published')->count(),
            'drafts' => BlogPost::query()->where('status', 'draft')->count(),
            'categories' => BlogCategory::query()->withCount('posts')->orderByDesc('posts_count')->orderBy('name')->get(),
            'authors' => BlogPost::query()->whereNotNull('author_id')->with('author')->selectRaw('author_id, count(*) as posts_count')->groupBy('author_id')->orderByDesc('posts_count')->get(),
        ])->layout('layouts.admin')->title('Blog Reports');
    }
}
