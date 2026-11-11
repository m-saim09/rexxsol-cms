<?php

namespace App\Support;

final class GoogleMapsUrl
{
    public static function embed(mixed $url): ?string
    {
        if (! self::isHttpsGoogleUrl($url, $parts)) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);
        $validPath = $parts['path'] === '/maps/embed'
            || ($parts['host'] === 'maps.google.com' && $parts['path'] === '/maps');
        $validQuery = ! empty($query['pb'])
            || (! empty($query['q']) && ($query['output'] ?? null) === 'embed');

        return $validPath && $validQuery ? $url : null;
    }

    public static function directions(mixed $url): ?string
    {
        if (! self::isHttpsGoogleUrl($url, $parts)) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);
        $validPath = in_array($parts['path'], ['/maps/dir', '/maps/dir/'], true)
            || ($parts['host'] === 'maps.google.com' && $parts['path'] === '/maps');
        $hasDestination = ! empty($query['destination']);
        $isDirectionsRequest = ($query['api'] ?? null) === '1' && $hasDestination;

        return $validPath && $isDirectionsRequest ? $url : null;
    }

    private static function isHttpsGoogleUrl(mixed $url, ?array &$parts): bool
    {
        $parts = null;
        if (! is_string($url) || $url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        return ($parts['scheme'] ?? null) === 'https'
            && in_array(strtolower($parts['host'] ?? ''), ['www.google.com', 'maps.google.com'], true)
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['fragment'])
            && (! isset($parts['port']) || $parts['port'] === 443)
            && isset($parts['path'], $parts['query']);
    }
}
