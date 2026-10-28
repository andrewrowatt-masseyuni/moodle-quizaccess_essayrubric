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
 * Restore code for the quizaccess_essayrubric plugin.
 *
 * @package    quizaccess_essayrubric
 * @category   backup
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qbehaviour_rubricgraded\display_settings;
use quizaccess_essayrubric\settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');

/**
 * Restores the rubric display settings of a quiz.
 *
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_essayrubric_subplugin extends restore_mod_quiz_access_subplugin {
    #[\Override]
    protected function define_quiz_subplugin_structure() {
        return [
            new restore_path_element('quizaccess_essayrubric', $this->get_pathfor('/quizaccess_essayrubric')),
        ];
    }

    /**
     * Process the restored settings.
     *
     * @param array $data
     */
    public function process_quizaccess_essayrubric($data) {
        settings::save($this->get_new_parentid('quiz'), display_settings::from_record($data));
    }
}
