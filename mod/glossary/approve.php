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
 * Glossary module approve entries
 *
 * @package    mod_glossary
 * @copyright  2003 onwards Williams Castillo (castillow@tutopia.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$eid = required_param('eid', PARAM_INT);    // Entry ID.

$mode = optional_param('mode', 'approval', PARAM_ALPHA);
$hook = optional_param('hook', 'ALL', PARAM_CLEAN);

$url = new moodle_url('/mod/glossary/approve.php', array('eid'=>$eid, 'mode'=>$mode, 'hook'=>$hook));
$PAGE->set_url($url);

$entry = $DB->get_record('glossary_entries', array('id'=> $eid), '*', MUST_EXIST);
$glossary = $DB->get_record('glossary', array('id'=> $entry->glossaryid), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('glossary', $glossary->id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=> $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/glossary:approve', $context);

if (!$entry->approved and confirm_sesskey()) {
    $newentry = new stdClass();
    $newentry->id           = $entry->id;
    $newentry->approved     = 1;
    $newentry->timemodified = time(); // We need this date here to speed up recent activity.
    // TODO: (MDL-38265) use timestamp in approved field instead in 2.0.
    $DB->update_record("glossary_entries", $newentry);

    // Update completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $glossary->completionentries) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $entry->userid);
    }

    add_to_log($course->id, "glossary", "approve entry", "showentry.php?id=$cm->id&amp;eid=$eid", "$eid", $cm->id);
}

redirect("view.php?id=$cm->id&amp;mode=$mode&amp;hook=$hook");