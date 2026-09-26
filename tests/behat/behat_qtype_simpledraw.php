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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Steps for driving the Simple drawing canvas, which lives in a same-origin iframe.
 *
 * @package    qtype_simpledraw
 * @copyright  2026 misko92
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qtype_simpledraw extends behat_base {
    /** @var string JS expression for the drawing iframe's window. */
    const FRAME = "document.querySelector('.que.simpledraw iframe').contentWindow";

    /**
     * Waits until the drawing editor inside the iframe has finished loading.
     */
    protected function wait_for_canvas(): void {
        $this->spin(function() {
            return $this->evaluate_script("return (function() {
                try {
                    const w = " . self::FRAME . ";
                    const overlay = w.document.getElementById('qtype-simpledraw-editor-loading');
                    return !!(w.svgCanvas && w.document.getElementById('svgcontent'))
                        && (!overlay || overlay.classList.contains('is-hidden'));
                } catch (e) {
                    return false;
                }
            })();");
        }, [], behat_base::get_extended_timeout());
    }

    /**
     * Draws a short diagonal freehand stroke with the current tool.
     *
     * @When I draw a stroke on the simple drawing canvas
     */
    public function i_draw_a_stroke_on_the_simple_drawing_canvas(): void {
        $this->wait_for_canvas();
        // WebDriver offsets are from the centre of the element's visible part, so bring the whole
        // iframe into view first.
        $this->execute_script("document.querySelector('.que.simpledraw iframe').scrollIntoView({block: 'start'});");
        // Start 30% into the white page (<svg id="svgcontent"> itself reports a 0x0 box).
        $geom = $this->evaluate_script("return (function() {
            const w = " . self::FRAME . ";
            const r = w.document.getElementById('canvasBackground').getBoundingClientRect();
            const f = w.frameElement.getBoundingClientRect();
            return {x: r.left + r.width * 0.3, y: r.top + r.height * 0.3, fw: f.width, fh: f.height,
                w: r.width, h: r.height};
        })();");
        // Real pointer input (not synthetic JS events) so the editor sees trusted mouse events.
        // Offsets are from the centre of the iframe element.
        $webdriver = $this->getSession()->getDriver()->getWebDriver();
        $frame = $webdriver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.que.simpledraw iframe'));
        $actions = $webdriver->action()
            ->moveToElement($frame, (int) ($geom['x'] - $geom['fw'] / 2), (int) ($geom['y'] - $geom['fh'] / 2))
            ->clickAndHold();
        for ($i = 0; $i < 8; $i++) {
            $actions = $actions->moveByOffset((int) ($geom['w'] * 0.04), (int) ($geom['h'] * 0.03));
        }
        $actions->release()->perform();
    }

    /**
     * Checks the canvas holds a freehand stroke with the given colour and width.
     *
     * @Then the simple drawing canvas should contain a stroke of colour :colour and width :width
     * @param string $colour expected stroke colour, e.g. #000000
     * @param string $width expected stroke width in px
     */
    public function the_canvas_should_contain_a_stroke(string $colour, string $width): void {
        $this->wait_for_canvas();
        $found = $this->evaluate_script("return (function() {
            const w = " . self::FRAME . ";
            // Ignore degenerate (zero-length) paths.
            return Array.from(w.document.querySelectorAll('#svgcontent path, #svgcontent polyline')).filter(
                function(p) {
                    const box = p.getBBox();
                    return box.width + box.height > 10;
                }
            ).map(function(p) {
                return (p.getAttribute('stroke') || '') + ' ' + (p.getAttribute('stroke-width') || '');
            });
        })();");
        if (!in_array(strtolower($colour) . ' ' . $width, array_map('strtolower', $found))) {
            throw new ExpectationException(
                "No stroke '$colour $width' on the canvas; found: [" . implode(', ', $found) . ']',
                $this->getSession()
            );
        }
    }

    /**
     * Checks whether a toolbar tool is shown in the drawing editor.
     *
     * @Then the simple drawing tool :toolid should be :visibility
     * @param string $toolid DOM id of the tool button, e.g. tool_fhpath
     * @param string $visibility "visible" or "hidden"
     */
    public function the_tool_should_be(string $toolid, string $visibility): void {
        $this->wait_for_canvas();
        $shown = $this->evaluate_script("return (function() {
            const el = " . self::FRAME . ".document.getElementById(" . json_encode($toolid) . ");
            return !!el && el.getClientRects().length > 0;
        })();");
        if ($shown !== ($visibility === 'visible')) {
            throw new ExpectationException("Tool '$toolid' is not $visibility", $this->getSession());
        }
    }

    /**
     * Checks the teacher's review canvas shows the student's saved drawing.
     *
     * The teacher annotates on top of the student's drawing, which is shown as a locked
     * background image, so check the answer behind that image rather than editable paths.
     *
     * @Then the simple drawing review should show a student stroke of colour :colour and width :width
     * @param string $colour expected stroke colour, e.g. #000000
     * @param string $width expected stroke width in px
     */
    public function the_review_should_show_a_student_stroke(string $colour, string $width): void {
        $this->wait_for_canvas();
        $result = $this->evaluate_script("return (function() {
            const w = " . self::FRAME . ";
            const answer = document.querySelector('.que.simpledraw [id^=qtype_simpledraw_original_stdanswer_id_]');
            return {
                answer: answer ? answer.value : '',
                images: w.document.querySelectorAll('#svgroot image').length,
            };
        })();");
        $stroke = 'stroke-width="' . $width . '" stroke="' . strtolower($colour) . '"';
        if (strpos(strtolower($result['answer']), $stroke) === false || $result['images'] < 1) {
            throw new ExpectationException(
                "Student drawing with $stroke not shown in review (background images: {$result['images']})",
                $this->getSession()
            );
        }
    }
}
