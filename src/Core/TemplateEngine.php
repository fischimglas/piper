<?php

namespace Piper\Core;

final class TemplateEngine
{
    public static function render(string $template, array $vars): string
    {
        $replacements = [];
        foreach ($vars as $k => $v) {
            $replacements['{{' . $k . '}}'] = is_scalar($v) ? (string)$v : json_encode($v);
        }
        return strtr($template, $replacements);
    }
}
