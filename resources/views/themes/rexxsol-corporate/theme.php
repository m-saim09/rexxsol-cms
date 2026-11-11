<?php

return [
    'name' => 'Rexxsol Corporate',
    'slug' => 'rexxsol-corporate',
    'version' => '1.0.0',
    'description' => 'A refined corporate theme for technology, software, and consulting businesses.',
    'preview' => asset('images/logo.png'),
    'view' => 'website',
    'layouts' => ['default', 'full-width'],
    'menu_locations' => ['primary', 'footer_company', 'footer_resources', 'footer_legal'],
    'widget_areas' => ['footer_one', 'footer_two', 'footer_three'],
    'footer' => [
        'columns' => 2,
        'supports_logo' => true,
        'supports_description' => true,
        'supports_contact' => true,
        'supports_socials' => true,
        'supports_cta' => false,
        'locations' => ['footer_company' => 'Company', 'footer_resources' => 'Resources', 'footer_legal' => 'Legal'],
    ],
    'supports' => ['hero', 'rich-text', 'image-text', 'services', 'projects', 'blog', 'testimonials', 'team', 'faq', 'cta', 'contact'],
];
