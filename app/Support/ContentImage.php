<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

final class ContentImage
{
    public static function url(?string $path, string $disk = 'public'): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = Media::normalizePath($path);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        if (config("filesystems.disks.{$disk}.driver") === 'local' && ! $storage->exists($path)) {
            return null;
        }

        return $storage->url($path);
    }

    public static function alt(?string $path, ?string $customAlt = null, ?string $fallbackTitle = null): string
    {
        $customAlt = trim((string) ($customAlt ?? ''));
        if ($customAlt !== '') {
            return $customAlt;
        }

        $fallbackTitle = trim((string) ($fallbackTitle ?? ''));
        if ($fallbackTitle !== '') {
            return $fallbackTitle;
        }

        if (! is_string($path) || trim($path) === '') {
            return '';
        }

        $filename = basename($path);
        $stem = pathinfo($filename, PATHINFO_FILENAME);
        if ($stem === '') {
            return '';
        }

        return trim((string) preg_replace('/[-_]+/', ' ', $stem)) ?: '';
    }
}