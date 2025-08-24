<?php

declare(strict_types=1);

namespace Piper\Factory;

use ReflectionClass;
use RuntimeException;
use function yaml_parse_file;

/**
 * Config reader and provider
 *
 * Reads configuration values from a YAML file located at `etc/config.yaml`.
 *
 * Usage:
 *   Cf::get('GoogleAiAdapter.apiKey')
 *   Cf::autoload($this)
 */
class Cf
{
    private const CONFIG_FILE = __DIR__ . '/../../etc/config.yaml';

    private static ?Cf $instance = null;
    private readonly array $config;

    private function __construct()
    {
        if (!file_exists(self::CONFIG_FILE)) {
            throw new RuntimeException(
                sprintf('Configuration file not found: %s', self::CONFIG_FILE)
            );
        }

        $parsed = yaml_parse_file(self::CONFIG_FILE);
        if ($parsed === false) {
            throw new RuntimeException(
                sprintf('Failed to parse configuration file: %s', self::CONFIG_FILE)
            );
        }

        $this->config = $parsed;
    }

    public static function get(string $key): array|string|null
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->resolve($key);
    }

    private function resolve(string $key): array|string|null
    {
        $parts = explode('.', $key);
        $value = $this->config;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    public static function autoload(object $instance): mixed
    {
        $ref = new ReflectionClass($instance);
        $configKey = $ref->getShortName();
        $config = self::get($configKey);

        if (!is_array($config)) {
            return null;
        }

        foreach ($config as $prop => $value) {
            $setter = 'set' . ucfirst($prop);
            if (method_exists($instance, $setter)) {
                $instance->$setter($value);
            }
        }

        return $config;
    }
}
