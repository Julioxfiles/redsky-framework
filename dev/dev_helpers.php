<?php
declare(strict_types=1);

if (!function_exists('dd')) {

    function dd(mixed ...$vars): never
    {
        echo debug_styles();

        echo '<div class="dd-container">';

        foreach ($vars as $var) {
            render_debug($var);
        }

        echo '</div>';
        exit(1);
    }
}

if (!function_exists('dump')) {

    function dump(mixed ...$vars): void
    {
        static $printed = false;

        if (!$printed) {
            echo debug_styles();
            $printed = true;
        }

        echo '<div class="dd-container">';

        foreach ($vars as $var) {
            render_debug($var);
        }

        echo '</div>';
    }
}

/**
 * Shared renderer (DRY)
 */
if (!function_exists('render_debug')) {

    function render_debug(mixed $value): void
    {
        if (is_array($value)) {
            echo '<details open><summary>array (' . count($value) . ')</summary>';
            foreach ($value as $key => $val) {
                echo '<div>';
                echo '<span class="dd-key">' . htmlspecialchars((string)$key) . '</span> => ';
                render_debug($val);
                echo '</div>';
            }
            echo '</details>';
            return;
        }

        if (is_object($value)) {
            echo '<details open><summary>object ' . get_class($value) . '</summary>';
            foreach (get_object_vars($value) as $key => $val) {
                echo '<div>';
                echo '<span class="dd-key">' . htmlspecialchars((string)$key) . '</span> => ';
                render_debug($val);
                echo '</div>';
            }
            echo '</details>';
            return;
        }

        if (is_string($value)) {
            echo '<span class="dd-string">"' . htmlspecialchars($value) . '"</span><br>';
            return;
        }

        if (is_int($value) || is_float($value)) {
            echo '<span class="dd-number">' . $value . '</span><br>';
            return;
        }

        if (is_bool($value)) {
            echo '<span class="dd-bool">' . ($value ? 'true' : 'false') . '</span><br>';
            return;
        }

        if ($value === null) {
            echo '<span class="dd-null">null</span><br>';
            return;
        }

        echo htmlspecialchars((string)$value) . '<br>';
    }
}

/**
 * Shared CSS (only printed once)
 */
if (!function_exists('debug_styles')) {

    function debug_styles(): string
    {
        static $printed = false;

        if ($printed) {
            return '';
        }

        $printed = true;

        return <<<HTML
<style>
    .dd-container {
        font-family: monospace;
        background: #212121;
        color: #e0e0e0;
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 16px;
    }
    .dd-key { color: #b0b0b0; }
    .dd-string { color: #c3e88d; }
    .dd-number { color: #82aaff; }
    .dd-null { color: #ff6b6b; }
    .dd-bool { color: #ffcb6b; }
    details { margin-left: 20px; }
    summary { cursor: pointer; }
</style>
HTML;
    }
}