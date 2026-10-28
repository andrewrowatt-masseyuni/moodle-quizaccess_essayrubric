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
 * Backup code for the quizaccess_essayrubric plugin.
 *
 * @package    quizaccess_essayrubric
 * @category   backup
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qbehaviour_rubricgraded\display_settings;
use quizaccess_essayrubric\settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_mod_quiz_access_subplugin.class.php');

/**
 * Provides the information to backup the rubric display settings of a quiz.
 *
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_quizaccess_essayrubric_subplugin extends backup_mod_quiz_access_subplugin {
    #[\Override]
    protected function define_quiz_subplugin_structure() {
        parent::define_quiz_subplugin_structure();

        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginsettings = new backup_nested_element('quizaccess_essayrubric', null, display_settings::FIELDS);

        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginsettings);

        $subpluginsettings->set_source_table(settings::TABLE, ['quizid' => backup::VAR_ACTIVITYID]);

        return $subplugin;
    }
}
