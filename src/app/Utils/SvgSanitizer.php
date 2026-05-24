<?php
namespace App\Utils;

class SvgSanitizer {
    /**
     * Sanitizes SVG string to prevent XSS.
     * Removes scripts, event handlers (on*), and dangerous attributes.
     */
    public static function sanitize(string $svg): string {
        if (empty($svg)) return '';

        // Create a new DOMDocument
        $dom = new \DOMDocument();
        
        // Disable error reporting for malformed XML
        libxml_use_internal_errors(true);
        
        // Load the SVG. Add a wrapper to ensure valid XML structure if only a fragment is provided
        $dom->loadXML($svg, LIBXML_NOENT | LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOCDATA);

        $xpath = new \DOMXPath($dom);
        // Register namespaces commonly used in SVGs to avoid query failures
        $xpath->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');

        // 1. Remove all <script> elements
        $scripts = $xpath->query('//script');
        if ($scripts !== false) {
            foreach ($scripts as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        // 2. Remove all event handlers (attributes starting with "on")
        $allAttributes = $xpath->query('//@*');
        if ($allAttributes !== false) {
            foreach ($allAttributes as $attribute) {
                if (strpos($attribute->nodeName, 'on') === 0) {
                    $attribute->parentNode->removeAttribute($attribute->nodeName);
                }
            }
        }

        // 3. Remove javascript: pseudo-protocols in href/src
        $dangerousAttributes = ['href', 'xlink:href', 'src'];
        foreach ($dangerousAttributes as $attrName) {
            $nodes = $xpath->query("//@$attrName");
            if ($nodes !== false) {
                foreach ($nodes as $attribute) {
                    if (preg_match('/^\s*javascript:/i', $attribute->nodeValue)) {
                        $attribute->parentNode->removeAttribute($attrName);
                    }
                }
            }
        }

        // Clean up errors
        libxml_clear_errors();

        // Save and return cleaned XML (stripping the XML declaration if needed)
        $cleanSvg = $dom->saveXML($dom->documentElement);
        return $cleanSvg ?: '';
    }
}