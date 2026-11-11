<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class SearchResults extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    public function mount(): void
    {
        Gate::authorize('access-admin');
    }

    public function render()
    {
        $term = trim($this->query);
        $results = collect();
        if ($term !== '') {
            $like = "%{$term}%";
            $this->addResults($results, Page::query()->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('slug', 'like', $like))->limit(10)->get(), 'Page', fn ($item) => route('admin.pages.edit', $item));
            $this->addResults($results, BlogPost::query()->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('slug', 'like', $like))->limit(10)->get(), 'Blog post', fn ($item) => route('admin.blog.edit', $item));
            $this->addResults($results, Service::query()->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('slug', 'like', $like))->limit(10)->get(), 'Service', fn ($item) => route('admin.services.edit', $item));
            $this->addResults($results, Project::query()->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('slug', 'like', $like))->limit(10)->get(), 'Project', fn ($item) => route('admin.projects.edit', $item));
            $this->addResults($results, TeamMember::query()->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('slug', 'like', $like))->limit(10)->get(), 'Team member', fn ($item) => route('admin.team.edit', $item));
            $this->addResults($results, Testimonial::query()->where('name', 'like', $like)->limit(10)->get(), 'Testimonial', fn ($item) => route('admin.testimonials.edit', $item));
            $this->addResults($results, Faq::query()->where('question', 'like', $like)->limit(10)->get(), 'FAQ', fn ($item) => route('admin.faqs.edit', $item));
            $this->addResults($results, Media::query()->where(fn ($query) => $query->where('original_name', 'like', $like)->orWhere('title', 'like', $like))->limit(10)->get(), 'Media', fn () => route('admin.media.index'));
            if (auth()->user()->can('contact_messages.view')) {
                $this->addResults($results, ContactMessage::query()->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('subject', 'like', $like))->limit(10)->get(), 'Contact message', fn () => route('admin.contact-messages'));
            }
        }

        return view('livewire.admin.search-results', ['results' => $results->take(50)])->layout('layouts.admin')->title('Search');
    }

    protected function addResults($results, $items, string $type, callable $url): void
    {
        foreach ($items as $item) {
            $results->push(['type' => $type, 'title' => $item->title ?? $item->name ?? $item->question ?? $item->original_name ?? $item->subject, 'meta' => $item->slug ?? $item->email ?? null, 'url' => $url($item)]);
        }
    }
}
