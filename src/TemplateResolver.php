<?php

namespace Piper;

class TemplateResolver
{
    /**
     * Ersetzt {{placeholders}} im Template mit Werten aus $data.
     *
     * @param bool $strict Wenn true: unbekannte Keys → Exception, sonst leerer String
     */
    public static function resolve(string $template, null|array $data, bool $strict = false): string
    {
        return preg_replace_callback('/{{\s*([\w\.]+)\s*}}/', function ($matches) use ($data, $strict) {
            $path = explode('.', $matches[1]);
            $value = self::getValue($data, $path);

            if ($value === null) {
                if ($strict) {
                    throw new \RuntimeException("Missing key: {$matches[1]}");
                }
                return $matches[0]; // oder '' wenn du lieber leer ersetzt
            }

            return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $template);
    }

    /**
     * Traversiert Arrays/Objekte anhand eines Pfads.
     */
    private static function getValue(array|object $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (is_array($current)) {
                // Prüft auch numerische Indizes und null-Values korrekt
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
