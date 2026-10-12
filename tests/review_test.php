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

namespace qtype_simpledraw;

use question_engine;
use question_display_options;

/**
 * Tests for how answers are shown on the review page.
 *
 * @package    qtype_simpledraw
 * @category   test
 * @copyright  2026 misko92
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \qtype_simpledraw_renderer
 */
final class review_test extends \advanced_testcase {
    /**
     * A tampered answer is shown as an image, never as markup on the page.
     */
    public function test_review_shows_drawings_as_images(): void {
        global $DB, $PAGE;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $quizgen = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgen->create_instance(['course' => $course->id]);
        $questiongen = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongen->create_question_category(['contextid' => \context_module::instance($quiz->cmid)->id]);
        $created = $questiongen->create_question('simpledraw', 'plain', ['category' => $category->id]);

        $this->setUser($student);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', \context_module::instance($quiz->cmid));
        $quba->set_preferred_behaviour('deferredfeedback');
        $slot = $quba->add_question(\question_bank::load_question($created->id));
        $quba->start_all_questions();
        $evil = '<svg xmlns="http://www.w3.org/2000/svg" width="580" height="400" onload="alert(1)">' .
            '<script>alert(2)</script><path d="M0 0L10 10" stroke="#000"/></svg>';
        $quba->process_all_actions(time(), $quba->prepare_simulated_post_data([$slot => ['answer' => $evil]]));
        $quba->finish_all_questions();
        question_engine::save_questions_usage_by_activity($quba);

        // An annotation saved before the cleaner existed.
        $DB->insert_record('qtype_simpledraw_annotations', [
            'questionid' => $created->id,
            'annotatedby' => get_admin()->id,
            'annotatedfor' => $student->id,
            'attemptid' => substr(md5($evil), 0, 14) . 'XX',
            'attemptcount' => 1,
            'annotation' => '<g><path d="M1 1" onclick="alert(3)"/></g>',
            'timecreated' => time(),
            'timemodified' => time(),
            'notes' => '',
        ]);

        $PAGE->set_url('/');
        $options = new question_display_options();
        $options->readonly = true;
        $html = $quba->render_question($slot, $options);

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onload=', $html);
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertMatchesRegularExpression('~<img[^>]+class="qtype_simpledraw_drawing"~', $html);

        // The drawing itself is still there, inside the image.
        preg_match_all('~src="data:image/svg\+xml;base64,([^"]+)"~', $html, $matches);
        $this->assertCount(2, $matches[1]);
        $answerimage = base64_decode($matches[1][0]);
        $this->assertStringContainsString('<path d="M0 0L10 10"', $answerimage);
        $annotatedimage = base64_decode($matches[1][1]);
        $this->assertStringContainsString('<path d="M1 1"', $annotatedimage);
    }
}
