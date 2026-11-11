<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Support\Seo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RobotsTxtConfiguration
{
    public const SETTING_KEY = 'seo.robots';

    public function content(): string
    {
        $content = app(SiteSettings::class)->get(self::SETTING_KEY);

        return is_string($content) ? $content : $this->initialContent();
    }

    public function defaultContent(): string
    {
        $sitemap = app(Seo::class)->baseUrl().'/sitemap.xml';

        return "User-agent: *\nDisallow:\nSitemap: {$sitemap}\n";
    }

    private function initialContent(): string
    {
        $hasExistingPolicy = SiteSetting::query()
            ->whereIn('key', ['seo.indexing', 'site.maintenance_enabled'])
            ->exists();

        if (! $hasExistingPolicy) {
            return $this->defaultContent();
        }

        $settings = app(SiteSettings::class);
        $allow = $settings->get('seo.indexing', false) && ! $settings->get('site.maintenance_enabled', false);
        $sitemap = app(Seo::class)->baseUrl().'/sitemap.xml';

        return "User-agent: *\n".($allow
            ? "Disallow: /admin\nDisallow: /login\nDisallow: /settings\nSitemap: {$sitemap}\n"
            : "Disallow: /\n");
    }

    /**
     * @return array{errors: list<array{line: int, message: string}>, warnings: list<array{line: int, message: string}>, blocks_all: bool}
     */
    public function analyze(string $content): array
    {
        $errors = [];
        $warnings = [];
        $inUserAgentGroup = false;
        $wildcardGroups = [];
        $allowRootGroups = [];
        $group = -1;
        $groupHasRules = false;
        $recognized = ['user-agent', 'allow', 'disallow', 'sitemap'];
        $nonstandard = ['crawl-delay', 'host', 'clean-param', 'noindex'];

        foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $content)) as $index => $line) {
            $lineNumber = $index + 1;
            $trimmed = trim($line);
            if (str_contains($line, "\0") || preg_match('/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/', $line)) {
                $errors[] = ['line' => $lineNumber, 'message' => 'Binary and unsupported control characters are not allowed.'];

                continue;
            }
            if (preg_match('//u', $line) !== 1) {
                $errors[] = ['line' => $lineNumber, 'message' => 'This line contains invalid UTF-8 text.'];

                continue;
            }
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/<\s*\/?\s*(?:!doctype\b|html|head|body|script|style|div|p|a|img|h[1-6]|ul|ol|li|table|section|header|footer|main|span|form|input|button|title|meta|link)\b[^>]*>/i', $trimmed)) {
                $errors[] = ['line' => $lineNumber, 'message' => 'This looks like HTML, not robots.txt content.'];
                continue;
            }

            $directiveLine = trim(explode('#', $trimmed, 2)[0]);
            if (! preg_match('/\A([A-Za-z][A-Za-z0-9_-]*)\s*:\s*(.*?)\s*\z/', $directiveLine, $matches)) {
                $errors[] = ['line' => $lineNumber, 'message' => 'Expected a directive in the form Name: value.'];
                continue;
            }

            $directive = Str::lower($matches[1]);
            $value = trim($matches[2]);

            if ($directive === 'user-agent') {
                if ($value === '' || preg_match('/[\s#]/', $value)) {
                    $errors[] = ['line' => $lineNumber, 'message' => 'User-agent must contain one crawler name.'];
                    $inUserAgentGroup = false;
                    continue;
                }

                if (! $inUserAgentGroup || $groupHasRules) {
                    $group++;
                    $wildcardGroups[$group] = ['wildcard' => false, 'blocks_all' => false];
                    $groupHasRules = false;
                }
                $wildcardGroups[$group]['wildcard'] = $wildcardGroups[$group]['wildcard'] || $value === '*';
                $inUserAgentGroup = true;

                continue;
            }

            if ($directive === 'sitemap') {
                if (filter_var($value, FILTER_VALIDATE_URL) === false || ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true) || preg_match('/\s/', $value)) {
                    $errors[] = ['line' => $lineNumber, 'message' => 'Sitemap must be an absolute HTTP or HTTPS URL.'];
                }

                continue;
            }

            if (in_array($directive, ['allow', 'disallow'], true)) {
                if (! $inUserAgentGroup) {
                    $errors[] = ['line' => $lineNumber, 'message' => 'This rule must follow a User-agent directive.'];

                    continue;
                }
                if ($directive === 'allow' && $value === '') {
                    $errors[] = ['line' => $lineNumber, 'message' => 'Allow requires a path or URL pattern.'];

                    continue;
                }
                if ($directive === 'disallow' && $value === '/' && ($wildcardGroups[$group]['wildcard'] ?? false)) {
                    $wildcardGroups[$group]['blocks_all'] = true;
                }
                if ($directive === 'allow' && $value === '/') {
                    $allowRootGroups[$group] = true;
                }
                $groupHasRules = true;

                continue;
            }

            if (in_array($directive, $nonstandard, true) || ! in_array($directive, $recognized, true)) {
                $message = $directive === 'noindex'
                    ? 'Noindex is not a reliable robots.txt directive; use page meta robots or X-Robots-Tag instead.'
                    : "The {$matches[1]} directive is not consistently supported by crawlers.";
                $warnings[] = ['line' => $lineNumber, 'message' => $message];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'blocks_all' => collect($wildcardGroups)->contains(fn (array $rules, int $index): bool => $rules['wildcard'] && $rules['blocks_all'] && ! ($allowRootGroups[$index] ?? false)),
        ];
    }

    public function saveIfUnchanged(string $expectedHash, string $content, int $userId): bool
    {
        $saved = DB::transaction(function () use ($expectedHash, $content, $userId): bool {
            $row = DB::table('site_settings')->where('key', self::SETTING_KEY)->lockForUpdate()->first();
            $stored = $row ? json_decode((string) $row->value, true) : null;
            $current = is_string($stored) ? $stored : $this->initialContent();

            if (! hash_equals($expectedHash, hash('sha256', $current))) {
                return false;
            }

            $now = now();
            DB::table('site_settings')->updateOrInsert(
                ['key' => self::SETTING_KEY],
                ['value' => json_encode($content, JSON_THROW_ON_ERROR), 'media_id' => null, 'updated_at' => $now, 'created_at' => $row?->created_at ?? $now],
            );

            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'updated',
                'module' => 'SEO',
                'subject_type' => 'robots.txt',
                'subject_id' => 1,
                'subject_label' => 'robots.txt',
                'changes' => [
                    'content_sha256' => ['old' => hash('sha256', $current), 'new' => hash('sha256', $content)],
                    'content_bytes' => ['old' => strlen($current), 'new' => strlen($content)],
                ],
            ]);

            return true;
        }, 3);

        if ($saved) {
            Cache::forget(SiteSettings::CACHE_KEY);
        }

        return $saved;
    }

    public function savedAt(): mixed
    {
        return DB::table('site_settings')->where('key', self::SETTING_KEY)->value('updated_at');
    }

    public function savedBy(): mixed
    {
        return ActivityLog::query()
            ->with('user')
            ->where('subject_type', 'robots.txt')
            ->where('subject_id', 1)
            ->where('action', 'updated')
            ->latest('id')
            ->first()?->user;
    }
}
