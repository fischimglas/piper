<?php

declare(strict_types=1);

namespace Piper\Core;

use RuntimeException;

final class TemplateEngine
{
    public static function render(string $template, array|object|null $vars, bool $strict = false): string
    {
        return preg_replace_callback('/{{\s*([\w\.]+)\s*}}/', function ($matches) use ($vars, $strict) {
            $path = explode('.', $matches[1]);
            $value = self::getValue($vars, $path);

            if ($value === null) {
                if ($strict) {
                    throw new RuntimeException("Missing key: {$matches[1]}");
                }
                return $matches[0];
            }

            return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $template);
    }

    private static function getValue(array|object|null $data, array $path): mixed
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
