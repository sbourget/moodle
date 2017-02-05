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
 * This file contains the overall badge criteria award class
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Overall badge award criteria
 *
 */
class badgecriteria_overall_award extends badgecriteria_award {

    /* @var string Criteria ['overall'] */
    public $criteriatype = 'overall';
    /* @var array Supported badge types */
    public static $supportedtypes = array(BADGE_TYPE_COURSE, BADGE_TYPE_SITE);

    /**
     * Add appropriate form elements to the criteria form
     *
     * @param badge $badge Badge being edited
     */
    public function config_form_criteria(badge $badge) {
        global $OUTPUT;
        $prefix = 'criteria-' . $this->id;
        if ($badge->has_criteria() && !$badge->has_one_criterion()) {
            echo $OUTPUT->box_start();
            if (!empty($this->description)) {
                $badge = new badge($this->badgeid);
                echo $OUTPUT->box(
                    format_text($this->description, $this->descriptionformat, array('context' => $badge->get_context())),
                    'criteria-description');
            }
            echo $OUTPUT->heading($this->get_title(), 2);

            $agg = $badge->get_aggregation_methods();
            if (!$badge->is_locked() && !$badge->is_active()) {
                $editurl = new moodle_url('/badges/criteria_settings.php',
                               array('badgeid' => $this->badgeid,
                                   'edit' => true,
                                   'type' => $this->criteriatype,
                                   'crit' => $this->id
                               )
                        );
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')), null,
                              array('class' => 'criteria-action'));
                echo $OUTPUT->box($editaction, array('criteria-header'));

                $url = new moodle_url('criteria.php', array('id' => $badge->id, 'sesskey' => sesskey()));
                echo $OUTPUT->single_select($url, 'update', $agg, $badge->get_aggregation_method($this->criteriatype),
                    null, null, array('aria-describedby' => 'overall'));
                echo html_writer::span(get_string('overallcrit', 'badges'), '', array('id' => 'overall'));
            } else {
                echo $OUTPUT->box(get_string('description', 'badgecriteria_' . $this->criteriatype,
                        core_text::strtoupper($agg[$badge->get_aggregation_method()])), 'clearfix');
            }
            echo $OUTPUT->box_end();
        }
    }

    /**
     * Add appropriate parameter elements to the criteria form
     *
     */
    public function config_options(&$mform, $param) {
    }

    /**
     * Get criteria details for displaying to users
     * @param short boolean
     * @return string
     */
    public function get_details($short = false) {
    }

    /**
     * Review this criteria and decide if it has been completed
     * Overall criteria review should be called only from other criteria handlers.
     *
     * @param int $userid User whose criteria completion needs to be reviewed.
     * @param bool $filtered An additional parameter indicating that user list
     *        has been reduced and some expensive checks can be skipped.
     *
     * @return bool Whether criteria is complete
     */
    public function review($userid, $filtered = false) {
        global $DB;

        $sql = "SELECT bc.*, bcm.critid, bcm.userid, bcm.datemet
                FROM {badge_criteria} bc
                LEFT JOIN {badge_criteria_met} bcm
                    ON bc.id = bcm.critid AND bcm.userid = :userid
                WHERE bc.badgeid = :badgeid
                    AND bc.criteriatype != :criteriatype ";

        $params = array(
                    'userid' => $userid,
                    'badgeid' => $this->badgeid,
                    'criteriatype' => 'overall'
                );

        $criteria = $DB->get_records_sql($sql, $params);
        $overall = false;
        foreach ($criteria as $crit) {
            // Check the award criteria exists.
            $critobj = badgecriteria_award::build((array)$crit);
            if ($this->method == BADGE_CRITERIA_AGGREGATION_ALL) {
                if (!$critobj || $crit->datemet === null) {
                    return false;
                } else {
                    $overall = true;
                    continue;
                }
            } else {
                if (!$critobj || $crit->datemet === null) {
                    $overall = false;
                    continue;
                } else {
                    return true;
                }
            }
        }

        return $overall;
    }

    /**
     * Returns array with sql code and parameters returning all ids
     * of users who meet this particular criterion.
     *
     * @return array list($join, $where, $params)
     */
    public function get_completed_criteria_sql() {
        return array('', '', array());
    }

    /**
     * Add appropriate criteria elements to the form
     *
     */
    public function get_options(&$mform) {
    }

    /**
     * Return criteria parameters
     *
     * @param int $critid Criterion ID
     * @return array
     */
    public function get_params($cid) {
    }

    /**
     * Saves overall badge criteria description.
     *
     * @param array $params Values from the form or any other array.
     */
    public function save($params = array()) {
        global $DB;

        // Sort out criteria description.
        // If it is coming from the form editor, it is an array of (text, format).
        $description = '';
        $descriptionformat = FORMAT_HTML;
        if (isset($params['description']['text'])) {
            $description = $params['description']['text'];
            $descriptionformat = $params['description']['format'];
        } else if (isset($params['description'])) {
            $description = $params['description'];
        }

        $fordb = new stdClass();
        $fordb->criteriatype = $this->criteriatype;
        $fordb->badgeid = $this->badgeid;
        $fordb->description = $description;
        $fordb->descriptionformat = $descriptionformat;
        if ($this->id !== 0) {
            $fordb->id = $this->id;
            $DB->update_record('badge_criteria', $fordb);
        } else {
            // New record in DB, set aggregation to ALL by default.
            $fordb->method = BADGE_CRITERIA_AGGREGATION_ALL;
            $DB->insert_record('badge_criteria', $fordb);
        }
    }
}
