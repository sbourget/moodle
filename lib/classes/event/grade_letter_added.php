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
 * Grade letter added event.
 *
 * @package    core
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Grade letter added event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int itemid: grade letter item id.
 *      - int courseid: id of the course (or site)
 * }
 *
 * @package    core
 * @since      Moodle 3.4
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_letter_added extends base {


    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'grade_letters';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventgradeletteradded', 'core_grades');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        if ($this->other['courseid'] > 0) {
            return "The user with id '$this->userid' created the letter grade with id '$this->objectid'".
                    " in the course in the id '".$this->other['courseid']."'.";
        } else {
            return "The user with id '$this->userid' created the letter grade with id '$this->objectid'.";
        }
    }

    /**
     * Returns relevant URL.
     * @return \moodle_url
     */
    public function get_url() {
        if ($this->other['courseid'] > 0) {
            return new \moodle_url('/grade/edit/letter/index.php', array('id' => $this->context->id));
        } else {
            return new \moodle_url('/grade/edit/letter/index.php', array());
        }
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception when validation does not pass.
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set.');
        }
        if (!isset($this->other['courseid'])) {
            throw new \coding_exception('The \'courseid\' value must be set in other.');
        }
    }

    /**
     * Used for mapping events on restore
     * @return array
     */
    public static function get_objectid_mapping() {
        return array('db' => 'grade_letters', 'restore' => 'grade_letters');
    }

    /**
     * Used for mapping events on restore
     *
     * @return bool
     */
    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['courseid'] = array('db' => 'course', 'restore' => 'course');
        return $othermapped;
    }
}
