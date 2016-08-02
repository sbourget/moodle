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
 * Question previewed event.
 *
 * @package    core
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Question previewed event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int categoryid: The ID of the category where the question resides
 * }
 *
 * @package    core
 * @since      Moodle 3.2
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_previewed extends base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'question';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventquestionpreviewed', 'question');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' previewed the question with the id of '$this->objectid'.";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        if ($this->courseid) {
            if ($this->contextlevel == CONTEXT_MODULE) {
                return new \moodle_url('/question/preview.php', array('cmid' => $this->contextinstanceid, 'id' => $this->objectid));
            }
            return new \moodle_url('/question/preview.php', array('courseid' => $this->courseid, 'id' => $this->objectid));
        }

        // Bad luck, there does not seem to be any simple intelligent way
        // to go to specific question category in context above course,
        // let's try to edit it from frontpage which may surprisingly work.
        return new \moodle_url('/question/preview.php', array('courseid' => SITEID, 'id' => $this->objectid));
    }

    /**
     * Custom validations.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['categoryid'])) {
            throw new \coding_exception('The \'categoryid\' must be set in \'other\'.');
        }
    }

    /**
     * Returns DB mappings used with backup / restore.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return array('db' => 'question', 'restore' => 'question');
    }

    /**
     * Used for maping events on restore
     *
     * @return array
     */
    public static function get_other_mapping() {

        $othermapped = array();
        $othermapped['categoryid'] = array('db' => 'question_categories', 'restore' => 'question_categories');
        return $othermapped;
    }
}
