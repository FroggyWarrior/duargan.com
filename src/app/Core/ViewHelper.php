<?php
namespace App\Core;

/**
 * ViewHelper handles rendering reusable HTML snippets across the frontend.
 */
class ViewHelper {
    /**
     * Generates HTML for social media icons securely.
     * * @param array $platforms Array of social media data from the database
     * @param string $class CSS class for the container
     * @return string Generated HTML string
     */
    public static function renderSocialMedia($platforms, $class = 'social-links') {
        if (empty($platforms)) {
            return '<div class="' . htmlspecialchars($class) . '"></div>';
        }
        
        $html = '<div class="' . htmlspecialchars($class) . '">';
        foreach ($platforms as $platform) {
            $html .= '<a href="' . htmlspecialchars($platform['base_url']) . '" aria-label="' . htmlspecialchars($platform['name']) . '" target="_blank" rel="noopener">';
            // We decode the SVG icon since it's stored safely in the database
            $html .= html_entity_decode(stripslashes($platform['icon_svg']));
            $html .= '</a>';
        }
        $html .= '</div>';
        
        return $html;
    }
}