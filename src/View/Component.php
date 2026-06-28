<?php

declare(strict_types=1);

namespace RedSky\View;

use RedSky\View\Support\HtmlAttributes;

abstract class Component
{
    /**
     * Render HTML attributes.
     *
     * @param array<string,mixed> $attributes
     */
    protected static function attributes(array $attributes): string
    {
        return HtmlAttributes::render($attributes);
    }

    /**
     * Escape HTML.
     */
    protected static function e(
        string|int|float|null $value
    ): string
    {
        return htmlspecialchars(
            (string)$value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}