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
 * Dashboard reset event.
 *
 * @package    core
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Dashboard reset event class.
 *
 * Class for event to be triggered when a dashboard is reset.
 *
 * @package    core
 * @since      Moodle 3.2
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_reset extends base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user';

    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has reset their dashboard";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventdashboardreset', 'core');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url|null
     */
    public function get_url() {
        return new \moodle_url('/my', array());
    }

    /**
     * Get mappings for backup / restore
     * @return boolean
     */
    public static function get_other_mapping() {
        // No mapping required.
        return false;
    }

    /**
     * Get mappings for backup / restore
     * @return array
     */
    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }
}
