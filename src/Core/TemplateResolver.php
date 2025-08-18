<?php
declare(strict_types=1);

namespace Piper\Core;

class TemplateResolver
{

    public static function resolve(string $template, null|array $data, bool $strict = false): string
    {
        return preg_replace_callback('/{{\s*([\w\.]+)\s*}}/', function ($matches) use ($data, $strict) {
            $path = explode('.', $matches[1]);
            $value = self::getValue($data, $path);

            if ($value === null) {
                if ($strict) {
                    throw new \RuntimeException("Missing key: {$matches[1]}");
                }
                // TODO: Replace with empty string or return the original match?
                return $matches[0];
            }

            return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $template);
    }

    // TODO simplify
    private static function getValue(array|object $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (is_array($current)) {
                if (array_key_exists($segment, $current)) {
                    $current = $current[$segment];
                } else {
                    return null;
                }
            } elseif (is_object($current)) {
                if (property_exists($current, $segment)) {
                    $current = $current->{$segment};
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $current;
    }
}
