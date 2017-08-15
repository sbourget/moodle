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
 * Unit tests for events found in /grade/letter and /grade/scale.
 *
 * @package   core_grades
 * @category  test
 * @copyright 2017 Stephen Bourget
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Unit tests for grade/lib.php.
 *
 * @package   core_grades
 * @category  test
 * @copyright 2017 Stephen Bourget
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_grade_events_test extends advanced_testcase {

    /** @var stdClass the course used for testing */
    private $course;

    /**
     * Test set up.
     *
     * This is executed before running any test in this file.
     */
    public function setUp() {
        $this->resetAfterTest();

        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
    }

    /**
     * Test the grade_letter added event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_grade_letter_added() {

        // Create a scale aded event.
        $event = \core\event\grade_letter_added::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\grade_letter_added', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }

    /**
     * Test the grade_letter deleted event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_grade_letter_deleted() {

        // Create a grade_letter deleted event.
        $event = \core\event\grade_letter_deleted::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\grade_letter_deleted', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }

    /**
     * Test the grade_letter updated event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_grade_letter_updated() {

        // Create a scale updated event.
        $event = \core\event\grade_letter_updated::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\grade_letter_updated', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }


    /**
     * Test the scale added event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_scale_added() {

        // Create a scale aded event.
        $event = \core\event\scale_added::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\scale_added', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }

    /**
     * Test the scale deleted event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_scale_deleted() {

        // Create a scale deleted event.
        $event = \core\event\scale_deleted::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\scale_deleted', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }

    /**
     * Test the scale updated event.
     *
     * There is no external API for triggering this event, so the unit test will simply
     * create and trigger the event and ensure the data is returned as expected.
     */
    public function test_scale_updated() {

        // Create a scale updated event.
        $event = \core\event\scale_updated::create(array(
            'objectid' => 10,
            'context' => context_course::instance($this->course->id),
            'other' => array('courseid' => $this->course->id),
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\scale_updated', $event);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
    }
}
