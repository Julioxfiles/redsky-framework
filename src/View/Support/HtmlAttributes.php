<?php

declare(strict_types=1);

namespace RedSky\View\Support;

final class HtmlAttributes
{
    /**
     * Convert an attribute array into HTML.
     *
     * @param array<string,mixed> $attributes
     */
    public static function render(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $key => $value) {

            if ($value === false || $value === null) {
                continue;
            }

            if ($value === true) {
                $html .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
                continue;
            }

            $html .= sprintf(
                ' %s="%s"',
                htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8')
            );
        }

        return $html;
    }

    private function __construct()
    {
    }
    
}