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

use mod_quiz\quiz_settings;
use qbehaviour_rubricgraded\display_settings;
use qtype_essayrubric\local\display_settings_resolver;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/essayrubric/rule.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/mod_form.php');

/**
 * Unit tests for the quiz rubric display settings.
 *
 * @package    quizaccess_essayrubric
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \quizaccess_essayrubric
 * @covers     \quizaccess_essayrubric\settings
 * @covers     \qtype_essayrubric\local\display_settings_resolver
 */
final class rule_test extends \advanced_testcase {
    /** @var array settings that differ from every default. */
    public const NON_DEFAULT_SETTINGS = [
        'essayrubric_alwaysshowdefinition' => 0,
        'essayrubric_showscorestudent' => 0,
        'essayrubric_enableremarks' => 0,
        'essayrubric_showremarksstudent' => 0,
        'essayrubric_showscoreteacher' => 0,
        'essayrubric_sortlevelsasc' => 0,
    ];

    /**
     * Create a quiz.
     *
     * @param array $settings extra quiz settings.
     * @return \stdClass the quiz.
     */
    protected function create_quiz(array $settings = []): \stdClass {
        $course = $this->getDataGenerator()->create_course();
        return $this->getDataGenerator()->create_module('quiz', ['course' => $course->id] + $settings);
    }

    public function test_quiz_without_settings_has_defaults(): void {
        $this->resetAfterTest();
        $quiz = $this->create_quiz();

        $this->assertEquals(new display_settings(), settings::for_quiz($quiz->id));

        // The settings form gets the defaults too.
        $quizrecord = quiz_settings::create($quiz->id)->get_quiz();
        $this->assertEquals(1, $quizrecord->essayrubric_alwaysshowdefinition);
        $this->assertEquals(1, $quizrecord->essayrubric_sortlevelsasc);
    }

    public function test_settings_are_saved_from_the_form(): void {
        global $DB;
        $this->resetAfterTest();
        $quiz = $this->create_quiz(self::NON_DEFAULT_SETTINGS);

        $this->assertEquals(new display_settings(false, false, false, false, false, false), settings::for_quiz($quiz->id));
        $quizrecord = quiz_settings::create($quiz->id)->get_quiz();
        $this->assertEquals(0, $quizrecord->essayrubric_alwaysshowdefinition);

        // Saving again updates the same record.
        \quizaccess_essayrubric::save_settings((object) (['id' => $quiz->id, 'essayrubric_alwaysshowdefinition' => 1] +
                self::NON_DEFAULT_SETTINGS));
        $this->assertTrue(settings::for_quiz($quiz->id)->alwaysshowdefinition);
        $this->assertEquals(1, $DB->count_records(settings::TABLE, ['quizid' => $quiz->id]));
    }

    public function test_settings_are_not_saved_without_the_form(): void {
        global $DB;
        $this->resetAfterTest();
        $quiz = $this->create_quiz();

        \quizaccess_essayrubric::save_settings((object) ['id' => $quiz->id, 'name' => 'Changed']);
        $this->assertFalse($DB->record_exists(settings::TABLE, ['quizid' => $quiz->id]));
    }

    public function test_settings_are_deleted_with_the_quiz(): void {
        global $DB;
        $this->resetAfterTest();
        $quiz = $this->create_quiz(self::NON_DEFAULT_SETTINGS);
        $this->assertTrue($DB->record_exists(settings::TABLE, ['quizid' => $quiz->id]));

        course_delete_module($quiz->cmid);
        $this->assertFalse($DB->record_exists(settings::TABLE, ['quizid' => $quiz->id]));
    }

    public function test_settings_form_fields(): void {
        $mform = new \MoodleQuickForm('test', 'post', '');
        $mform->addElement('text', 'name', 'Name');
        $mform->addElement('header', 'security', 'Extra restrictions on attempts');
        $quizform = $this->getMockBuilder(\mod_quiz_mod_form::class)->disableOriginalConstructor()->getMock();

        \quizaccess_essayrubric::add_settings_form_fields($quizform, $mform);

        // Each setting is its own element, in order, in a section before the security section.
        $expected = ['name' => 'text', 'essayrubric' => 'header'];
        foreach (display_settings::FIELDS as $field) {
            $expected['essayrubric_' . $field] = $field === 'sortlevelsasc' ? 'select' : 'advcheckbox';
        }
        $expected['security'] = 'header';
        $actual = [];
        foreach ($mform->_elements as $index => $element) {
            $actual[$element->getName()] = $element->getType();
            $this->assertSame($index, $mform->_elementIndex[$element->getName()]);
        }
        $this->assertSame($expected, $actual);
        $this->assertEquals(1, $mform->getElement('essayrubric_alwaysshowdefinition')->getValue());
    }

    public function test_never_restricts_access(): void {
        $this->resetAfterTest();
        $quiz = $this->create_quiz(self::NON_DEFAULT_SETTINGS);

        $this->assertNull(\quizaccess_essayrubric::make(quiz_settings::create($quiz->id), time(), false));
    }

    public function test_questions_use_the_quiz_settings(): void {
        $this->resetAfterTest();
        $quiz = $this->create_quiz(self::NON_DEFAULT_SETTINGS);
        $otherquiz = $this->create_quiz();
        $page = $this->getDataGenerator()->create_module('page', ['course' => $quiz->course]);

        $this->assertEquals(
            new display_settings(false, false, false, false, false, false),
            display_settings_resolver::for_context(\context_module::instance($quiz->cmid))
        );
        $this->assertEquals(
            new display_settings(),
            display_settings_resolver::for_context(\context_module::instance($otherquiz->cmid))
        );
        $this->assertEquals(
            new display_settings(),
            display_settings_resolver::for_context(\context_module::instance($page->cmid))
        );
    }

    public function test_settings_are_backed_up_and_restored(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $quiz = $this->create_quiz(['essayrubric_showscoreteacher' => 1] + self::NON_DEFAULT_SETTINGS);

        // Duplicating a quiz backs it up and restores it.
        $newcm = duplicate_module(get_course($quiz->course), get_fast_modinfo($quiz->course)->get_cm($quiz->cmid));

        $this->assertEquals(
            new display_settings(false, false, false, false, true, false),
            settings::for_quiz($newcm->instance)
        );
    }
}
