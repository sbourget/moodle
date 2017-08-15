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
 * Scale added event.
 *
 * @package    core
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Scale added event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int itemid: scale item id.
 *      - int courseid: id of course if a course scale
 * }
 *
 * @package    core
 * @since      Moodle 3.4
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scale_added extends base {


    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'scale';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventscaleadded', 'core_grades');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        if ($this->other['courseid'] > 0) {
            return "The user with id '$this->userid' created the custom scale with id '$this->objectid'".
                    " from the course in the id '".$this->other['courseid']."'.";
        } else {
            return "The user with id '$this->userid' created the standard scale with id '$this->objectid'.";
        }
    }

    /**
     * Returns relevant URL.
     * @return \moodle_url
     */
    public function get_url() {
        if ($this->other['courseid'] > 0) {
            return new \moodle_url('/grade/edit/scale/index.php', array('id' => $this->courseid));
        } else {
            return new \moodle_url('/grade/edit/scale/index.php', array());
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
        return array('db' => 'scale', 'restore' => 'scale');
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
