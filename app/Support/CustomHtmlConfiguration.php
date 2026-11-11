<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\SiteSetting;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class CustomHtmlConfiguration
{
    private const MIGRATION_KEY = 'appearance.custom_html.sections_migrated';

    private const SECTIONS = [
        'header' => ['content' => 'appearance.custom_html.header', 'enabled' => 'appearance.custom_html.header_enabled', 'position' => 'head'],
        'body' => ['content' => 'appearance.custom_html.body', 'enabled' => 'appearance.custom_html.body_enabled', 'position' => 'body'],
        'footer' => ['content' => 'appearance.custom_html.footer', 'enabled' => 'appearance.custom_html.footer_enabled', 'position' => 'body'],
    ];

    private const LEGACY_KEYS = [
        'content' => 'appearance.custom_html',
        'location' => 'appearance.custom_html_location',
        'enabled' => 'appearance.custom_html_enabled',
    ];

    public function sections(): array
    {
        $this->migrateLegacySettings();
        $settings = app(SiteSettings::class);

        return collect(self::SECTIONS)->mapWithKeys(function (array $keys, string $section) use ($settings): array {
            $content = (string) $settings->get($keys['content'], '');
            $enabled = (bool) $settings->get($keys['enabled'], false);
            $valid = $this->validationError($section, $content) === null;

            return [$section => [
                'content' => $content,
                'enabled' => $enabled && $valid,
            ]];
        })->all();
    }

    public function editableSections(): array
    {
        $this->migrateLegacySettings();
        $settings = app(SiteSettings::class);

        return collect(self::SECTIONS)->mapWithKeys(fn (array $keys, string $section): array => [
            $section => [
                'content' => (string) $settings->get($keys['content'], ''),
                'enabled' => (bool) $settings->get($keys['enabled'], false),
            ],
        ])->all();
    }

    public function fingerprint(): string
    {
        return $this->hash($this->readSectionsFromDatabase());
    }

    public function save(array $sections, string $baselineFingerprint): bool
    {
        $saved = DB::transaction(function () use ($sections, $baselineFingerprint): bool {
            $stored = $this->readSectionsFromDatabase(lockForUpdate: true);
            if (! hash_equals($this->hash($stored), $baselineFingerprint)) {
                return false;
            }

            $changedSections = [];
            $enabledChanges = [];

            foreach (self::SECTIONS as $section => $keys) {
                $content = (string) $sections[$section]['content'];
                $enabled = (bool) $sections[$section]['enabled'];
                $old = $stored[$section];

                if ($old['content'] !== $content || $old['enabled'] !== $enabled) {
                    $changedSections[] = $section;
                }
                if ($old['enabled'] !== $enabled) {
                    $enabledChanges[$section] = ['old' => $old['enabled'], 'new' => $enabled];
                }

                SiteSetting::query()->updateOrCreate(['key' => $keys['content']], ['value' => $content]);
                SiteSetting::query()->updateOrCreate(['key' => $keys['enabled']], ['value' => $enabled]);
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'Appearance',
                'subject_type' => 'custom_html',
                'subject_id' => 0,
                'subject_label' => 'Custom HTML',
                'changes' => [
                    'sections' => ['old' => $this->sectionLabels($stored), 'new' => $changedSections],
                    'enabled' => $enabledChanges,
                    'content_sha256' => collect($sections)->map(fn (array $values): string => hash('sha256', (string) $values['content']))->all(),
                    'content_bytes' => collect($sections)->map(fn (array $values): int => strlen((string) $values['content']))->all(),
                ],
            ]);

            return true;
        });

        if ($saved) {
            $this->forgetSettingsCache();
        }

        return $saved;
    }

    public function lastSaved(): ?ActivityLog
    {
        return ActivityLog::query()
            ->with('user')
            ->where('subject_type', 'custom_html')
            ->where('subject_id', 0)
            ->where('module', 'Appearance')
            ->latest('id')
            ->first();
    }

    public function migrateLegacySettings(): void
    {
        $changed = DB::transaction(function (): bool {
            $existing = SiteSetting::query()->where('key', self::MIGRATION_KEY)->lockForUpdate()->first();
            if ($existing) {
                return false;
            }

            $legacy = SiteSetting::query()->whereIn('key', array_values(self::LEGACY_KEYS))->get()->keyBy('key');
            $content = (string) ($legacy[self::LEGACY_KEYS['content']]->value ?? '');
            $location = (string) ($legacy[self::LEGACY_KEYS['location']]->value ?? 'head');
            $enabled = (bool) ($legacy[self::LEGACY_KEYS['enabled']]->value ?? false);
            $target = match ($location) {
                'body_start' => 'body',
                'body_end' => 'footer',
                default => 'header',
            };

            foreach (self::SECTIONS as $section => $keys) {
                SiteSetting::query()->firstOrCreate(
                    ['key' => $keys['content']],
                    ['value' => $section === $target ? $content : ''],
                );
                SiteSetting::query()->firstOrCreate(
                    ['key' => $keys['enabled']],
                    ['value' => $section === $target && $enabled],
                );
            }
            SiteSetting::query()->create(['key' => self::MIGRATION_KEY, 'value' => true]);

            return true;
        });

        if ($changed) {
            $this->forgetSettingsCache();
        }
    }

    public function validationError(string $section, string $content): ?string
    {
        if ($content === '') {
            return null;
        }

        if (mb_strlen($content) > 50000) {
            return 'HTML must be 50,000 characters or fewer.';
        }
        if (preg_match('/<\?(?:php|=)?|@(?:php|endphp|if|elseif|else|endif|foreach|endforeach|for|endfor|while|endwhile|include|extends|section|yield|component|props)\b|\{\{|\{!!/i', $content)) {
            return 'PHP and Blade expressions are not allowed.';
        }
        if (str_contains($content, "\0")) {
            return 'Null bytes are not allowed.';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><!doctype html><html><body><div id="custom-html-root">'.$content.'</div></body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING,
            );
            if (! $loaded) {
                return 'Enter valid HTML markup.';
            }

            $root = $document->getElementById('custom-html-root');
            if (! $root) {
                return 'Enter valid HTML markup.';
            }

            foreach ($root->childNodes as $node) {
                $error = $this->validateNode($node, $section);
                if ($error !== null) {
                    return $error;
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return null;
    }

    private function validateNode(DOMNode $node, string $section): ?string
    {
        if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_TEXT_NODE) {
            return null;
        }
        if (! $node instanceof DOMElement) {
            return 'Unsupported HTML content is not allowed.';
        }

        $tag = strtolower($node->tagName);
        $headTags = ['title', 'meta', 'link'];
        $bodyTags = ['a', 'abbr', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li', 'ol', 'p', 'small', 'span', 'strong', 'sub', 'sup', 'ul'];
        $allowedTags = $section === 'header' ? $headTags : $bodyTags;

        if (! in_array($tag, $allowedTags, true)) {
            return $section === 'header'
                ? "The <{$tag}> element is not allowed in Header HTML. Use metadata such as <title>, <meta>, or <link>."
                : "The <{$tag}> element is not allowed. Scripts, forms, embeds, document wrappers, and executable markup are blocked.";
        }

        foreach ($node->attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            if (str_starts_with($name, 'on') || $name === 'style') {
                return "The {$name} attribute is not allowed.";
            }
            if (! $this->attributeAllowed($tag, $name, $value, $section)) {
                return "The {$name} attribute is not allowed on <{$tag}>.";
            }
        }

        if ($tag === 'meta' && strtolower($node->getAttribute('http-equiv')) === 'refresh') {
            return 'Meta refresh directives are not allowed.';
        }
        if ($tag === 'link' && ! in_array(strtolower($node->getAttribute('rel')), ['canonical', 'alternate', 'icon', 'preconnect', 'dns-prefetch'], true)) {
            return 'Only canonical, alternate, icon, preconnect, and dns-prefetch links are allowed in Header HTML.';
        }
        if ($tag === 'a' && strtolower($node->getAttribute('target')) === '_blank'
            && ! preg_match('/\bnoopener\b/i', $node->getAttribute('rel'))) {
            return 'Links opening a new tab must include rel="noopener".';
        }

        foreach ($node->childNodes as $child) {
            $error = $this->validateNode($child, $section);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    private function attributeAllowed(string $tag, string $name, string $value, string $section): bool
    {
        if (in_array($name, ['class', 'id', 'title', 'role', 'dir', 'lang'], true)
            || str_starts_with($name, 'aria-')
            || str_starts_with($name, 'data-')) {
            return true;
        }

        if ($tag === 'a' && in_array($name, ['href', 'target', 'rel'], true)) {
            return $name !== 'href' || $this->safeUrl($value, allowMailtoTel: true);
        }
        if ($tag === 'img' && in_array($name, ['src', 'alt', 'width', 'height', 'loading', 'decoding'], true)) {
            return $name !== 'src' || $this->safeUrl($value, allowMailtoTel: false);
        }
        if ($section === 'header' && $tag === 'meta' && in_array($name, ['name', 'content', 'property', 'charset', 'http-equiv'], true)) {
            return $name !== 'http-equiv' || in_array(strtolower($value), ['content-type', 'default-style'], true);
        }
        if ($section === 'header' && $tag === 'link' && in_array($name, ['rel', 'type', 'media', 'hreflang', 'href'], true)) {
            return $name !== 'href' || $this->safeUrl($value, allowMailtoTel: false);
        }

        return false;
    }

    private function safeUrl(string $url, bool $allowMailtoTel): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }

        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $match)) {
            $scheme = strtolower($match[1]);

            return in_array($scheme, $allowMailtoTel ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'], true);
        }

        return ! str_starts_with($url, '//');
    }

    private function readSectionsFromDatabase(bool $lockForUpdate = false): array
    {
        $keys = collect(self::SECTIONS)->flatMap(fn (array $item): array => [$item['content'], $item['enabled']])->all();
        $query = SiteSetting::query()->whereIn('key', $keys);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $stored = $query->get()->keyBy('key');

        return collect(self::SECTIONS)->mapWithKeys(fn (array $item, string $section): array => [
            $section => [
                'content' => (string) ($stored[$item['content']]->value ?? ''),
                'enabled' => (bool) ($stored[$item['enabled']]->value ?? false),
            ],
        ])->all();
    }

    private function hash(array $sections): string
    {
        return hash('sha256', json_encode($sections, JSON_THROW_ON_ERROR));
    }

    private function sectionLabels(array $sections): array
    {
        return collect($sections)->filter(fn (array $values): bool => $values['content'] !== '' || $values['enabled'])
            ->keys()->all();
    }

    private function forgetSettingsCache(): void
    {
        Cache::forget(SiteSettings::CACHE_KEY);
        PublicSiteSettings::forget();
    }
}
