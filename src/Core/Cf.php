<?php

/**
 * Config reader and provider
 *
 * Reads configuration values from a YAML file located at etc/config.yaml.
 *
 * Usage:
 *   Cf::get('GoogleAiAdapter.apiKey')
 *   Cf::autoload($this)
 */

declare(strict_types=1);

namespace Piper\Core;

use ReflectionClass;
use RuntimeException;

use function yaml_parse_file;

class Cf
{
    private const string CONFIG_FILE = __DIR__ . '/../../etc/config.yaml';
    private static ?Cf $instance = null;
    private readonly array $cf;

    private function __construct()
    {
        if (!file_exists(self::CONFIG_FILE)) {
            throw new RuntimeException(sprintf("Configuration file not found: %s", self::CONFIG_FILE));
        }
        $config = yaml_parse_file(self::CONFIG_FILE);
        if ($config === false) {
            throw new RuntimeException(sprintf("Failed to parse configuration file: %s", self::CONFIG_FILE));
        }

        $this->cf = $config;
    }

    public static function get(string $key): array|string|null
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->getConfigValue($key);
    }

    private function getConfigValue(string $key): array|string|null
    {

        $parts = explode('.', $key);
        $value = $this->cf;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return null;
            }
        }
        return $value;
    }

    public static function autoload(object $instance): void
    {
        $ref = new ReflectionClass($instance);
        $configKey = $ref->getShortName();
        $config = Cf::get($configKey);
        if (!is_array($config)) {
            return;
        }

        foreach ($config as $prop => $value) {
            $fnName = 'set' . ucfirst($prop);
            if (method_exists($instance, $fnName)) {
                $instance->$fnName($value);
            }
        }
    }
}
