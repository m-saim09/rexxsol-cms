<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class CmsContent
{
    public static function model(string $resource): string
    {
        return match ($resource) {
            'pages' => Page::class,
            'services' => Service::class,
            'projects' => Project::class,
            'blog' => BlogPost::class,
            'team' => TeamMember::class,
            'testimonials' => Testimonial::class,
            'faqs' => Faq::class,
            default => throw new InvalidArgumentException('Unknown CMS resource.'),
        };
    }

    public static function singular(string $resource): string
    {
        return match ($resource) {
            'pages' => 'Page',
            'services' => 'Service',
            'projects' => 'Project',
            'blog' => 'Blog post',
            'team' => 'Team member',
            'testimonials' => 'Testimonial',
            'faqs' => 'FAQ',
            default => throw new InvalidArgumentException('Unknown CMS resource.'),
        };
    }

    public static function titleField(string $resource): string
    {
        return in_array($resource, ['services', 'projects', 'team', 'testimonials'], true) ? 'name' : ($resource === 'faqs' ? 'question' : 'title');
    }

    public static function query(string $resource): string
    {
        return self::model($resource);
    }

    public static function label(Model $model, string $resource): string
    {
        return (string) $model->{self::titleField($resource)};
    }
}
