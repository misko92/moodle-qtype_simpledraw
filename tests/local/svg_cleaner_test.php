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
 * Tests for the SVG cleaner.
 *
 * @package    qtype_simpledraw
 * @category   test
 * @copyright  2026 misko92
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \qtype_simpledraw\local\svg_cleaner
 */
final class svg_cleaner_test extends \basic_testcase {
    /**
     * Cleaning examples.
     *
     * @return array[] [input, expected output]
     */
    public static function clean_provider(): array {
        return [
            'drawing kept' => [
                '<g><path d="M0 0L10 10" stroke="red" stroke-width="4" fill="none"/></g>',
                '<g><path d="M0 0L10 10" stroke="red" stroke-width="4" fill="none"/></g>',
            ],
            'xml declaration removed' => [
                '<?xml version="1.0"?><svg width="5"><rect width="5" height="5"/></svg>',
                '<svg width="5"><rect width="5" height="5"/></svg>',
            ],
            'event handler removed' => [
                '<g><path d="M0 0" onclick="alert(1)"/></g>',
                '<g><path d="M0 0"/></g>',
            ],
            'script removed' => [
                '<svg><script>alert(1)</script><circle r="2"/></svg>',
                '<svg><circle r="2"/></svg>',
            ],
            'embedded html removed' => [
                '<g><foreignObject><div xmlns="http://www.w3.org/1999/xhtml">x</div></foreignObject></g>',
                '<g/>',
            ],
            'links removed' => [
                '<a href="javascript:alert(1)"><text>hi</text></a><text>ok</text>',
                '<text>ok</text>',
            ],
            'outside image removed, embedded kept' => [
                '<image xlink:href="https://example.com/x.png"/><image xlink:href="data:image/png;base64,AAAA"/>',
                '<image/><image xlink:href="data:image/png;base64,AAAA"/>',
            ],
            'same-document references kept' => [
                '<use href="#a"/><rect style="fill:url(#g)"/>',
                '<use href="#a"/><rect style="fill:url(#g)"/>',
            ],
            'outside url in style removed' => [
                '<rect style="fill:url(https://example.com/x)"/>',
                '<rect/>',
            ],
            'animation removed' => [
                '<rect><set attributeName="onload" to="alert(1)"/><animate/></rect>',
                '<rect/>',
            ],
            'cdata and comments removed' => [
                '<g><![CDATA[ <script>alert(1)</script> ]]><!-- note --></g>',
                '<g/>',
            ],
            'entities refused' => [
                '<!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><text>&x;</text>',
                '',
            ],
            'not xml refused' => [
                'not xml <<',
                '',
            ],
            'empty' => [
                '',
                '',
            ],
        ];
    }

    /**
     * Test clean().
     *
     * @dataProvider clean_provider
     * @param string $input
     * @param string $expected
     */
    public function test_clean(string $input, string $expected): void {
        $this->assertSame($expected, svg_cleaner::clean($input));
    }
}
