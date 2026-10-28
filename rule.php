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
 * Implementation of the quizaccess_essayrubric plugin.
 *
 * @package    quizaccess_essayrubric
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\local\access_rule_base;
use mod_quiz\quiz_settings;
use qbehaviour_rubricgraded\display_settings;
use quizaccess_essayrubric\settings;

/**
 * Quiz settings for how the rubrics of essay with rubric questions are displayed.
 *
 * This never restricts access to the quiz. It is an access rule because that is how
 * a plugin adds settings to the quiz settings form, and has them backed up with the quiz.
 *
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_essayrubric extends access_rule_base {
    #[\Override]
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        return null;
    }

    #[\Override]
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $defaults = new display_settings();
        $elements = [];

        $elements[] = $mform->createElement('header', 'essayrubric', get_string('rubricsettings', 'quizaccess_essayrubric'));
        foreach (display_settings::FIELDS as $field) {
            $name = 'essayrubric_' . $field;
            if ($field === 'sortlevelsasc') {
                $elements[] = $mform->createElement('select', $name, get_string($field, 'quizaccess_essayrubric'), [
                    1 => get_string('sortlevelsasc1', 'quizaccess_essayrubric'),
                    0 => get_string('sortlevelsasc0', 'quizaccess_essayrubric'),
                ]);
            } else {
                $elements[] = $mform->createElement('advcheckbox', $name, get_string($field, 'quizaccess_essayrubric'));
            }
        }

        // Insert the section before "Extra restrictions on attempts", so that fields added by
        // access rules after this one don't end up in it. insertElementBefore() keeps a reference
        // to the variable it is given, so pass each array element rather than a reused loop variable.
        foreach (array_keys($elements) as $key) {
            $mform->insertElementBefore($elements[$key], 'security');
        }
        foreach (display_settings::FIELDS as $field) {
            $name = 'essayrubric_' . $field;
            $mform->setDefault($name, (int) $defaults->$field);
            $mform->addHelpButton($name, $field, 'quizaccess_essayrubric');
        }
        $mform->hideIf('essayrubric_showremarksstudent', 'essayrubric_enableremarks');
    }

    #[\Override]
    public static function save_settings($quiz) {
        if (!isset($quiz->essayrubric_alwaysshowdefinition)) {
            // Not saved from the settings form, for example when a quiz is created by code.
            return;
        }

        $values = [];
        foreach (display_settings::FIELDS as $field) {
            $values[$field] = $quiz->{'essayrubric_' . $field} ?? null;
        }
        settings::save($quiz->id, display_settings::from_record($values));
    }

    #[\Override]
    public static function delete_settings($quiz) {
        settings::delete($quiz->id);
    }

    #[\Override]
    public static function get_settings_sql($quizid) {
        $defaults = (new display_settings())->to_array();
        $fields = [];
        foreach (display_settings::FIELDS as $field) {
            // A quiz with no settings has the defaults.
            $fields[] = "COALESCE(essayrubric.$field, {$defaults[$field]}) AS essayrubric_$field";
        }
        return [
            implode(', ', $fields),
            'LEFT JOIN {' . settings::TABLE . '} essayrubric ON essayrubric.quizid = quiz.id',
            [],
        ];
    }
}
