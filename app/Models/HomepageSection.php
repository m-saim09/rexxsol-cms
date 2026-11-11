<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'enabled', 'sort_order', 'settings'])]
class HomepageSection extends Model
{
    public const DEFINITIONS = [
        'hero' => 'Hero',
        'about' => 'Company / About',
        'services' => 'Services',
        'process' => 'Our Process',
        'projects' => 'Projects',
        'stats' => 'Stats / Counters',
        'why_choose_us' => 'Why Choose Rexxsol',
        'testimonials' => 'Testimonials',
        'team' => 'Team',
        'blog' => 'Blog / Insights',
        'faqs' => 'FAQs',
        'cta' => 'Call to action',
    ];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'settings' => 'array'];
    }

    public static function ensureDefaults(): void
    {
        foreach (array_keys(self::DEFINITIONS) as $order => $key) {
            self::firstOrCreate(['key' => $key], [
                'name' => self::DEFINITIONS[$key],
                'sort_order' => $order,
                'settings' => [],
            ]);
        }
    }
}
