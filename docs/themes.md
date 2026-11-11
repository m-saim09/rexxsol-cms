# Global Theme Engine

The CMS treats content, menus, sections, and settings as global product data. Themes only provide presentation.

## Standard contract

Every theme follows the same structure under `resources/views/themes/{slug}` and optionally `public/themes/{slug}`.

```text
resources/views/themes/
├── rexxsol-corporate/
│   ├── theme.php
│   ├── layouts/
│   ├── templates/
│   ├── components/
│   └── partials/
├── bizconsult/
│   ├── theme.php
│   ├── layouts/
│   ├── templates/
│   ├── components/
│   └── partials/
└── ...future themes
```

A theme manifest may look like this:

```php
return [
    'name' => 'BizConsult',
    'slug' => 'bizconsult',
    'version' => '1.0.0',
    'description' => 'Business consulting theme',
    'layouts' => ['default', 'full-width'],
    'menu_locations' => ['primary', 'footer_column_1'],
    'widget_areas' => ['footer_one'],
    'supports' => ['hero', 'services', 'faq'],
];
```

## Theme registry

`App\Support\ThemeRegistry` owns active-theme resolution, preview handling, and template/component lookup. It uses a single central contract and never scatters `if ($theme === ...)` checks through the application.

Common methods:

- `all()` — list all registered themes.
- `find($slug)` — fetch a manifest by slug.
- `active()` — return the active theme manifest.
- `supports($slug, $feature)` — check whether a theme declares feature support.
- `resolveComponent($component, $themeSlug = null)` — get the best available component view with fallback.
- `resolveTemplate($template, $themeSlug = null)` — resolve a template or fallback view safely.

## Component override flow

The component resolver checks in this order:

1. `themes/{active-theme}/components/{component}`
2. `components/theme-blocks/{component}`
3. `components/{component}`
4. shared fallback component

This gives each theme full control over visual output while keeping the CMS content globally stored.

## Menu and settings separation

Global content and settings belong to the CMS. Theme manifests only provide theme metadata and allowed menu locations. The active theme changes the front-end presentation, not the underlying content model.

## Preview and activation

Theme previews keep the same CMS content but render using a selected theme preview. Switching the active theme updates presentation only; it does not duplicate or delete pages, content, menus, or sections.
