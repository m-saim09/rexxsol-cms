# Theme Components and blocks

The CMS exposes a global component registry through `App\Support\ThemeComponentRegistry`.

## Built-in component keys

- `hero`
- `rich-text`
- `image-text`
- `services`
- `projects`
- `blog`
- `stats`
- `testimonials`
- `team`
- `faq`
- `cta`
- `contact`

Theme authors can override a block like this:

```text
resources/views/themes/bizconsult/components/hero.blade.php
```

If the theme does not ship a custom implementation, the shared fallback is used automatically.

## Custom component registration

Application developers can add their own component via the central registry or by a service provider. The same content model then renders across any theme with a matching visual override.

```php
app\Support\ThemeComponentRegistry
```

This keeps the CMS content, section schema, and theme layer separated.

## Safe rendering rules

- Do not store Blade or PHP in page content.
- Do not allow arbitrary executable code in database fields.
- Only registered components may be used for page-builder blocks.
- Layouts should consume structured data, not raw theme HTML.
