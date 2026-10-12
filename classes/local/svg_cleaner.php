<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace qtype_simpledraw\local;

/**
 * Cleans SVG drawings sent from the browser before they are stored.
 *
 * Keeps only plain drawing elements and attributes: no scripts, event
 * handlers, embedded HTML or links to other pages or sites.
 *
 * @package    qtype_simpledraw
 * @copyright  2026 misko92
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class svg_cleaner {
    /** @var string The SVG namespace. */
    private const SVGNS = 'http://www.w3.org/2000/svg';

    /** @var string The XLink namespace. */
    private const XLINKNS = 'http://www.w3.org/1999/xlink';

    /** @var string[] Elements a drawing may contain. */
    private const ELEMENTS = [
        'svg', 'g', 'defs', 'title', 'desc', 'path', 'line', 'polyline', 'polygon', 'rect', 'circle', 'ellipse',
        'text', 'tspan', 'image', 'marker', 'use', 'clipPath', 'mask', 'pattern', 'linearGradient', 'radialGradient',
        'stop', 'filter', 'feGaussianBlur',
    ];

    /**
     * Clean an SVG document or fragment.
     *
     * @param string $svg
     * @return string the cleaned markup, or '' if it is not well-formed XML
     */
    public static function clean(string $svg): string {
        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
        $svg = preg_replace('/<!DOCTYPE[^>]*>/i', '', $svg);
        if (trim($svg) === '') {
            return '';
        }
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // No network access and no entity expansion (LIBXML_NOENT is deliberately not set).
        $ok = $doc->loadXML(
            '<svg xmlns="' . self::SVGNS . '" xmlns:xlink="' . self::XLINKNS . '">' . $svg . '</svg>',
            LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$ok || !$doc->documentElement) {
            return '';
        }
        self::clean_node($doc->documentElement);

        $result = '';
        foreach (iterator_to_array($doc->documentElement->childNodes) as $child) {
            $result .= $doc->saveXML($child);
        }
        return $result;
    }

    /**
     * Remove everything not allowed from an element's children and attributes.
     *
     * @param \DOMElement $element
     */
    private static function clean_node(\DOMElement $element): void {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (!self::attribute_allowed($attribute)) {
                $element->removeAttributeNode($attribute);
            }
        }
        foreach (iterator_to_array($element->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                if ($child->namespaceURI === self::SVGNS && in_array($child->localName, self::ELEMENTS, true)) {
                    self::clean_node($child);
                } else {
                    $element->removeChild($child);
                }
            } else if (!($child instanceof \DOMText) || $child instanceof \DOMCdataSection) {
                // Comments, processing instructions, CDATA (e.g. style sheets).
                $element->removeChild($child);
            }
        }
    }

    /**
     * Whether an attribute may stay.
     *
     * @param \DOMAttr $attribute
     * @return bool
     */
    private static function attribute_allowed(\DOMAttr $attribute): bool {
        $name = strtolower($attribute->localName);
        $value = trim($attribute->value);
        if (str_starts_with($name, 'on')) {
            return false;
        }
        if ($attribute->namespaceURI !== null && $attribute->namespaceURI !== self::XLINKNS) {
            // E.g. editor-specific attributes; harmless but not needed.
            return false;
        }
        if ($name === 'href') {
            // Same-document references, or embedded raster images.
            return str_starts_with($value, '#') || preg_match('~^data:image/(png|jpe?g|gif|webp);base64,~i', $value);
        }
        if (preg_match('/url\s*\(\s*[\'"]?\s*(?!#)/i', $value) || stripos($value, 'javascript:') !== false) {
            // Only same-document url(#id) references (markers, gradients, clip paths).
            return false;
        }
        return true;
    }
}
