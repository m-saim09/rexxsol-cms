<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class CustomCssConfiguration
{
    private const CONTENT_KEY = 'appearance.custom_css';

    private const ENABLED_KEY = 'appearance.custom_css_enabled';

    public function state(): array
    {
        $settings = app(SiteSettings::class);
        $content = (string) $settings->get(self::CONTENT_KEY, '');

        return [
            'content' => $content,
            'enabled' => (bool) $settings->get(self::ENABLED_KEY, $content !== ''),
        ];
    }

    public function fingerprint(): string
    {
        return hash('sha256', json_encode($this->databaseState(), JSON_THROW_ON_ERROR));
    }

    public function stylesheetVersion(): string
    {
        return substr(hash('sha256', json_encode($this->state(), JSON_THROW_ON_ERROR)), 0, 16);
    }

    public function publicStylesheet(): string
    {
        $state = $this->state();

        return $state['enabled'] ? $state['content'] : '';
    }

    public function lastSaved(): ?ActivityLog
    {
        return ActivityLog::query()
            ->with('user')
            ->where('subject_type', 'custom_css')
            ->where('subject_id', 0)
            ->where('module', 'Appearance')
            ->latest('id')
            ->first();
    }

    public function validate(string $css): ?string
    {
        if (strlen($css) > 200000) {
            return 'CSS must be 200,000 bytes or fewer.';
        }
        if (str_contains($css, "\0")) {
            return 'Null bytes are not allowed.';
        }
        if (preg_match('/<\s*\/?\s*(?:style|script|html|head|body)\b/i', $css)) {
            return 'Enter CSS only, without HTML, script, or style tags.';
        }
        if (preg_match('/<\?(?:php|=)?|@(?:php|endphp|if|foreach)\b|\{\{|\{!!/i', $css)) {
            return 'PHP and Blade expressions are not allowed.';
        }
        if (($escapedTokenError = $this->escapedSecurityTokenError($css)) !== null) {
            return $escapedTokenError;
        }

        $scan = $this->scan($css);
        if ($scan !== null) {
            return $scan;
        }

        foreach ($this->urlValues($css) as [$url, $offset]) {
            if (! $this->trustedUrl($url)) {
                [$line, $column] = $this->lineColumn($css, $offset);

                return "Line {$line}, column {$column}: URL is not allowed. Use a relative site/media URL or an HTTPS URL from this site's configured media origin.";
            }
        }

        return null;
    }

    public function save(string $css, bool $enabled, string $baselineFingerprint): bool
    {
        $saved = DB::transaction(function () use ($css, $enabled, $baselineFingerprint): bool {
            $old = $this->databaseState(lockForUpdate: true);
            if (! hash_equals($this->hash($old), $baselineFingerprint)) {
                return false;
            }

            $new = ['content' => $css, 'enabled' => $enabled];
            SiteSetting::query()->updateOrCreate(['key' => self::CONTENT_KEY], ['value' => $css]);
            SiteSetting::query()->updateOrCreate(['key' => self::ENABLED_KEY], ['value' => $enabled]);

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'Appearance',
                'subject_type' => 'custom_css',
                'subject_id' => 0,
                'subject_label' => 'Custom CSS (Global)',
                'changes' => [
                    'scope' => ['old' => 'global', 'new' => 'global'],
                    'enabled' => ['old' => $old['enabled'], 'new' => $enabled],
                    'content_sha256' => ['old' => hash('sha256', $old['content']), 'new' => hash('sha256', $css)],
                    'content_bytes' => ['old' => strlen($old['content']), 'new' => strlen($css)],
                    'revision' => ['old' => $this->hash($old), 'new' => $this->hash($new)],
                ],
            ]);

            return true;
        });

        if ($saved) {
            Cache::forget(SiteSettings::CACHE_KEY);
            PublicSiteSettings::forget();
        }

        return $saved;
    }

    private function scan(string $css): ?string
    {
        $length = strlen($css);
        $stack = [];
        $quote = null;
        $comment = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $css[$i];
            $next = $css[$i + 1] ?? '';

            if ($comment) {
                if ($char === '*' && $next === '/') {
                    $comment = false;
                    $i++;
                }
                continue;
            }
            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === '/' && $next === '*') {
                $comment = true;
                $i++;
                continue;
            }
            if ($char === '"' || $char === "'") {
                $quote = $char;
                continue;
            }

            if ($char === '@' && preg_match('/\G@([a-z-]+)/i', $css, $match, 0, $i)) {
                $directive = strtolower($match[1]);
                if ($directive === 'import') {
                    [$line, $column] = $this->lineColumn($css, $i);

                    return "Line {$line}, column {$column}: @import is not allowed.";
                }
                $i += strlen($match[0]) - 1;
                continue;
            }

            if (preg_match('/\G(?:expression\s*\(|-moz-binding\b|behavior\s*:)/i', $css, $match, 0, $i)) {
                [$line, $column] = $this->lineColumn($css, $i);

                return "Line {$line}, column {$column}: executable CSS constructs are not allowed.";
            }

            if ($char === '{' || $char === '(' || $char === '[') {
                $stack[] = $i;
            } elseif (in_array($char, ['}', ')', ']'], true)) {
                if ($stack === []) {
                    [$line, $column] = $this->lineColumn($css, $i);

                    return "Line {$line}, column {$column}: unexpected closing brace.";
                }
                $opening = $css[$stack[array_key_last($stack)]];
                if (($opening === '{' && $char !== '}') || ($opening === '(' && $char !== ')') || ($opening === '[' && $char !== ']')) {
                    [$line, $column] = $this->lineColumn($css, $i);

                    return "Line {$line}, column {$column}: mismatched closing delimiter.";
                }
                array_pop($stack);
            }
        }

        if ($quote !== null) {
            [$line, $column] = $this->lineColumn($css, $length);

            return "Line {$line}, column {$column}: unclosed string.";
        }
        if ($comment) {
            [$line, $column] = $this->lineColumn($css, $length);

            return "Line {$line}, column {$column}: unclosed comment.";
        }
        if ($stack !== []) {
            [$line, $column] = $this->lineColumn($css, $stack[array_key_last($stack)]);
            $opening = $css[$stack[array_key_last($stack)]];
            $message = match ($opening) {
                '{' => 'unclosed block',
                '(' => 'unclosed parenthesis',
                default => 'unclosed bracket',
            };

            return "Line {$line}, column {$column}: {$message}.";
        }

        return null;
    }

    private function urlValues(string $css): array
    {
        $values = [];
        $length = strlen($css);

        for ($index = 0; $index < $length; $index++) {
            if (substr($css, $index, 2) === '/*') {
                $commentEnd = strpos($css, '*/', $index + 2);
                $index = $commentEnd === false ? $length : $commentEnd + 1;
                continue;
            }
            if ($css[$index] === '"' || $css[$index] === "'") {
                $quote = $css[$index++];
                while ($index < $length) {
                    if ($css[$index] === '\\') {
                        $index += 2;
                        continue;
                    }
                    if ($css[$index++] === $quote) {
                        break;
                    }
                }
                continue;
            }
            if (($index > 0 && preg_match('/[\w-]/', $css[$index - 1])) || ! preg_match('/\Gurl\s*\(/i', $css, $match, 0, $index)) {
                continue;
            }

            $open = $index + strlen($match[0]) - 1;
            $start = $open + 1;
            $quote = null;
            $escaped = false;
            $depth = 1;
            for ($cursor = $start; $cursor < $length; $cursor++) {
                $character = $css[$cursor];
                if ($quote !== null) {
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($character === '\\') {
                        $escaped = true;
                    } elseif ($character === $quote) {
                        $quote = null;
                    }
                    continue;
                }
                if ($character === '"' || $character === "'") {
                    $quote = $character;
                } elseif ($character === '(') {
                    $depth++;
                } elseif ($character === ')' && --$depth === 0) {
                    $raw = trim(substr($css, $start, $cursor - $start));
                    if (strlen($raw) > 1 && ($raw[0] === '"' || $raw[0] === "'") && $raw[0] === substr($raw, -1)) {
                        $raw = substr($raw, 1, -1);
                    }
                    $values[] = [$raw, $start];
                    $index = $cursor;
                    break;
                }
            }
        }

        return $values;
    }

    private function trustedUrl(string $url): bool
    {
        $url = preg_replace_callback('/\\\\([0-9a-fA-F]{1,6})(?:[\t\n\r\f ]|\\\\)?|\\\\([^\r\n\f])/u', function (array $match): string {
            if (($match[1] ?? '') !== '') {
                $codepoint = hexdec($match[1]);

                return $codepoint > 0 && $codepoint <= 0x10FFFF
                    ? html_entity_decode('&#x'.dechex($codepoint).';', ENT_QUOTES | ENT_HTML5, 'UTF-8')
                    : '';
            }

            return $match[2] ?? '';
        }, $url) ?? $url;
        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url) || str_starts_with($url, '//')) {
            return false;
        }
        if (! preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            return ! str_starts_with($url, '\\');
        }

        $parts = parse_url($url);
        if (! is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
            return false;
        }

        $trustedHosts = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            parse_url((string) config('filesystems.disks.public.url'), PHP_URL_HOST),
        ]);

        return in_array(strtolower($parts['host']), array_map('strtolower', $trustedHosts), true);
    }

    private function escapedSecurityTokenError(string $css): ?string
    {
        $masked = preg_replace_callback(
            '/\/\*[\s\S]*?\*\/|"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/u',
            fn (array $match): string => preg_replace('/[^\r\n]/', ' ', $match[0]) ?? $match[0],
            $css,
        ) ?? $css;
        $decoded = preg_replace_callback('/\\\\([0-9a-fA-F]{1,6})(?:[\t\n\r\f ]|\\\\)?|\\\\([^\r\n\f])/u', function (array $match): string {
            if (($match[1] ?? '') !== '') {
                $codepoint = hexdec($match[1]);

                return $codepoint > 0 && $codepoint <= 0x10FFFF
                    ? html_entity_decode('&#x'.dechex($codepoint).';', ENT_QUOTES | ENT_HTML5, 'UTF-8')
                    : '';
            }

            return $match[2] ?? '';
        }, $masked) ?? $masked;

        foreach (['@import' => '/@import\b/i', 'url()' => '/\burl\s*\(/i'] as $name => $pattern) {
            if (preg_match($pattern, $decoded) && ! preg_match($pattern, $masked)) {
                return $name === '@import'
                    ? 'Escaped @import directives are not allowed.'
                    : 'Escaped url() functions are not allowed.';
            }
        }

        return null;
    }

    private function lineColumn(string $css, int $offset): array
    {
        $before = substr($css, 0, $offset);
        $line = substr_count($before, "\n") + 1;
        $column = $offset - (strrpos($before, "\n") === false ? -1 : strrpos($before, "\n"));

        return [$line, $column];
    }

    private function databaseState(bool $lockForUpdate = false): array
    {
        $query = SiteSetting::query()->whereIn('key', [self::CONTENT_KEY, self::ENABLED_KEY]);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $values = $query->get()->keyBy('key');
        $content = (string) ($values[self::CONTENT_KEY]->value ?? '');

        return [
            'content' => $content,
            'enabled' => (bool) ($values[self::ENABLED_KEY]->value ?? $content !== ''),
        ];
    }

    private function hash(array $state): string
    {
        return hash('sha256', json_encode($state, JSON_THROW_ON_ERROR));
    }
}
