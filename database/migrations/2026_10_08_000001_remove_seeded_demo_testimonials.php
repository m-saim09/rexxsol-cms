<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEMO_TESTIMONIALS = [
        [
            'name' => 'Erin Hall',
            'designation' => 'Operations Director',
            'company' => 'Northlane',
            'quote' => 'The project gave us a better system and a calmer delivery rhythm. It felt practical from day one.',
        ],
        [
            'name' => 'Samir Khan',
            'designation' => 'Founder',
            'company' => 'Harbor Labs',
            'quote' => 'We needed alignment, not more complexity. The process made our next steps much clearer.',
        ],
        [
            'name' => 'Sofia Chen',
            'designation' => 'VP Product',
            'company' => 'Summit Group',
            'quote' => 'The experience was structured, transparent, and focused on real operational outcomes.',
        ],
    ];

    public function up(): void
    {
        foreach (self::DEMO_TESTIMONIALS as $testimonial) {
            DB::table('testimonials')
                ->where('name', $testimonial['name'])
                ->where('designation', $testimonial['designation'])
                ->where('company', $testimonial['company'])
                ->where('quote', $testimonial['quote'])
                ->whereNull('deleted_at')
                ->delete();
        }
    }

    public function down(): void
    {
        foreach (self::DEMO_TESTIMONIALS as $testimonial) {
            $exists = DB::table('testimonials')
                ->where('name', $testimonial['name'])
                ->where('designation', $testimonial['designation'])
                ->where('company', $testimonial['company'])
                ->where('quote', $testimonial['quote'])
                ->exists();

            if (! $exists) {
                DB::table('testimonials')->insert([
                    ...$testimonial,
                    'avatar' => null,
                    'rating' => 5,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]);
            }
        }
    }
};
