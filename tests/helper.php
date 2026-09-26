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

/**
 * Test helpers for the Simple drawing question type.
 *
 * @package    qtype_simpledraw
 * @copyright  ETH Zurich <moodle@id.ethz.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Test helper class for the Simple drawing question type.
 *
 * @copyright  ETH Zurich <moodle@id.ethz.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simpledraw_test_helper extends question_test_helper {
    #[\Override]
    public function get_test_questions() {
        return ['plain'];
    }

    /**
     * Makes a Simple drawing question with no background image.
     *
     * @return qtype_simpledraw_question
     */
    public function make_simpledraw_question_plain() {
        question_bank::load_question_definition_classes('simpledraw');
        $q = new qtype_simpledraw_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Simple drawing question';
        $q->questiontext = 'Draw a water molecule.';
        $q->qtype = question_bank::get_qtype('simpledraw');
        $q->backgrounduploaded = 0;
        $q->backgroundwidth = 580;
        $q->backgroundheight = 400;
        $q->preservear = 1;
        $q->questionembed = 0;
        return $q;
    }

    /**
     * Form data for a Simple drawing question with no background image.
     *
     * @return stdClass the data that would be returned by $form->get_data().
     */
    public function get_simpledraw_question_form_data_plain() {
        $fromform = new stdClass();
        $fromform->name = 'Simple drawing question';
        $fromform->questiontext = ['text' => 'Draw a water molecule.', 'format' => FORMAT_HTML];
        $fromform->generalfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->defaultmark = 1;
        $fromform->backgrounduploaded = 0;
        $fromform->backgroundwidth = 580;
        $fromform->backgroundheight = 400;
        $fromform->preservear = 1;
        $fromform->questionembed = 0;
        return $fromform;
    }
}
