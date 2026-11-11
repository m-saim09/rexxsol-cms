# Theme Page Templates

Public CMS pages are stored once in `pages` and are rendered with the active theme. A theme owns its default page design at:

`resources/views/themes/{theme-slug}/templates/page.blade.php`

The template receives `$page`, `$settings`, and `$menus`. It should extend that theme's existing public layout, use the page's title, excerpt, featured image, and content without inventing sections, and render rich text with `App\Support\RichText::clean($page->content)`. The Corporate and BizConsult themes define this conventional view today.

A theme manifest may set `page_template` to another Blade view name relative to that theme's view namespace (for example, `templates.page` or `pages.page`). The registry checks that manifest view first, then the conventional `templates/page`, legacy `pages/page`, and `templates/default` views. If none exists, it safely uses `resources/views/website/pages/page.blade.php`.

Specialized routes such as Home, Services, Portfolio, and Contact continue to use their existing views. The single-segment public page route remains after the dedicated routes and rejects static route slugs during page validation.

New pages default to showing in the primary navbar and footer menu. Their navigation items target the page record, so URLs follow slug edits. Historical page records are not migrated into menus. Existing manual menu items and footer groups remain authoritative; unpublishing or scheduling a page for the future makes all page-target links unavailable until the page is public again.