<?php

return [
    'name' => 'BizConsult',
    'slug' => 'bizconsult',
    'version' => '1.0.0',
    'description' => 'A bright, green consulting theme with illustrated sections and responsive Bootstrap interactions.',
    'preview' => asset('assets-bizconsult/img/hero.png'),
    'preview_note' => 'Temporary preview image from the supplied BizConsult presentation assets.',
    'view' => 'themes.bizconsult',
    'layouts' => ['default', 'full-width'],
    'menu_locations' => ['primary', 'footer_column_1', 'footer_column_2', 'footer_column_3', 'footer_legal', 'mobile'],
    'widget_areas' => ['footer_one', 'footer_two', 'footer_three'],
    'footer' => [
        'columns' => 3,
        'supports_logo' => true,
        'supports_description' => true,
        'supports_contact' => true,
        'supports_socials' => true,
        'supports_cta' => true,
        'locations' => ['footer_column_1' => 'Column One', 'footer_column_2' => 'Column Two', 'footer_column_3' => 'Column Three', 'footer_legal' => 'Legal'],
    ],
    'supports' => ['hero', 'rich-text', 'services', 'projects', 'blog', 'testimonials', 'team', 'faq', 'cta'],
];
