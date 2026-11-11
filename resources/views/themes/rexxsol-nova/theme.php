<?php

return [
    'name' => 'Rexxsol Nova',
    'slug' => 'rexxsol-nova',
    'version' => '1.4.0',
    'description' => 'A bright, modern digital growth theme for Rexxsol.',
    'preview' => asset('themes/rexxsol-nova/preview.svg'),
    'view' => 'themes.rexxsol-nova',
    'layouts' => ['default', 'full-width'],
    'page_template' => 'templates.page',
    'menu_locations' => ['primary', 'footer_company', 'footer_resources', 'footer_legal'],
    'widget_areas' => ['footer_one', 'footer_two', 'footer_three'],
    'supports' => ['hero', 'rich-text', 'image-text', 'services', 'process', 'projects', 'blog', 'testimonials', 'team', 'faq', 'cta', 'contact'],
];
