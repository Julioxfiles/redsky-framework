<?php

declare(strict_types=1);

namespace RedSky\View;

final class Html extends Component
{
    /**
     * Render a generic HTML card.
     *
     * Supported configuration:
     * - title
     * - text
     * - image
     * - imageAlt
     * - footer
     * - buttons
     * - class
     *
     * @param array<string,mixed> $config
     */
    public static function card(array $config = []): string
    {
        $title    = $config['title'] ?? '';
        $text     = $config['text'] ?? '';
        $image    = $config['image'] ?? '';
        $imageAlt = $config['imageAlt'] ?? '';
        $footer   = $config['footer'] ?? '';
        $buttons  = $config['buttons'] ?? [];
        $class    = $config['class'] ?? '';

        $html = '<div class="card ' . self::e($class) . '">';

        if ($image !== '') {
            $html .= sprintf(
                '<img src="%s" class="card-img-top" alt="%s">',
                self::e($image),
                self::e($imageAlt)
            );
        }

        if ($title !== '' || $text !== '' || !empty($buttons)) {

            $html .= '<div class="card-body">';

            if ($title !== '') {
                $html .= '<h5 class="card-title">' . self::e($title) . '</h5>';
            }

            if ($text !== '') {
                $html .= '<p class="card-text">' . self::e($text) . '</p>';
            }

            foreach ($buttons as $button) {

                $html .= sprintf(
                    '<a href="%s" class="%s">%s</a> ',
                    self::e($button['href'] ?? '#'),
                    self::e($button['class'] ?? 'btn btn-primary'),
                    self::e($button['text'] ?? '')
                );
            }

            $html .= '</div>';
        }

        if ($footer !== '') {
            $html .= '<div class="card-footer">' . $footer . '</div>';
        }

        $html .= '</div>';

        return $html;
    }
    
}