<?php

declare(strict_types=1);

namespace RedSky\Config;

class Repository
{
    /**
     * All configuration items.
     */
    protected array $items = [];

    /**
     * Create a new configuration repository.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Get all configuration items.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration key exists.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get a configuration value using dot notation.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set a configuration value using dot notation.
     */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);

        $items = &$this->items;

        foreach ($segments as $segment) {
            if (!isset($items[$segment]) || !is_array($items[$segment])) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        $items = $value;
    }
    
}