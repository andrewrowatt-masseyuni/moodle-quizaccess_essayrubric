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

namespace quizaccess_essayrubric;

use qbehaviour_rubricgraded\display_settings;

/**
 * Stores the rubric display settings of each quiz.
 *
 * @package    quizaccess_essayrubric
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings {
    /** @var string the table the settings are stored in. */
    public const TABLE = 'quizaccess_essayrubric';

    /**
     * Get the rubric display settings of a quiz. A quiz with no settings has the defaults.
     *
     * @param int $quizid
     * @return display_settings
     */
    public static function for_quiz(int $quizid): display_settings {
        global $DB;
        $record = $DB->get_record(self::TABLE, ['quizid' => $quizid], implode(', ', display_settings::FIELDS));
        return $record ? display_settings::from_record($record) : new display_settings();
    }

    /**
     * Save the rubric display settings of a quiz.
     *
     * @param int $quizid
     * @param display_settings $settings
     */
    public static function save(int $quizid, display_settings $settings): void {
        global $DB;
        $record = (object) $settings->to_array();
        $record->quizid = $quizid;
        $existingid = $DB->get_field(self::TABLE, 'id', ['quizid' => $quizid]);
        if ($existingid) {
            $record->id = $existingid;
            $DB->update_record(self::TABLE, $record);
        } else {
            $DB->insert_record(self::TABLE, $record);
        }
    }

    /**
     * Delete the rubric display settings of a quiz.
     *
     * @param int $quizid
     */
    public static function delete(int $quizid): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['quizid' => $quizid]);
    }
}
