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
 * Admin settings for the drawing question type.
 *
 * @package qtype_simpledraw
 * @author Amr Hourani amr.hourani@id.ethz.ch
 * @copyright ETHz 2016 amr.hourani@id.ethz.ch
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Introductory explanation that all the settings are defaults for the edit_simpledraw_form.
    $settings->add(
        new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_simpledraw'))
    );

    // Default canvas width.
    $settings->add(
        $x = new admin_setting_configtext(
            'qtype_simpledraw/defaultcanvaswidth',
            get_string('defaultcanvaswidth', 'qtype_simpledraw'),
            get_string('defaultcanvaswidth_help', 'qtype_simpledraw'),
            580,
            PARAM_INT,
            4
        )
    );
    // Default canvas height.
    $settings->add(
        new admin_setting_configtext(
            'qtype_simpledraw/defaultcanvasheight',
            get_string('defaultcanvasheight', 'qtype_simpledraw'),
            get_string('defaultcanvasheight_help', 'qtype_simpledraw'),
            400,
            PARAM_INT,
            4
        )
    );

    // Add setting questionembed.
    $settings->add(
        new admin_setting_configcheckbox(
            'qtype_simpledraw/questionembed',
            get_string('questionembed', 'qtype_simpledraw'),
            get_string('questionembed_help', 'qtype_simpledraw'),
            1
        )
    );
}
