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
 * This file is used to dieplay 
 *
 * @package    mod_glossary
 * @copyright  2003 onwards Williams Castillo (castillow@tutopia.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$concept  = optional_param('concept', '', PARAM_CLEAN);
$courseid = optional_param('courseid', 0, PARAM_INT);
$eid      = optional_param('eid', 0, PARAM_INT);  // Glossary entry id.
$displayformat = optional_param('displayformat', -1, PARAM_SAFEDIR);

$url = new moodle_url('/mod/glossary/showentry.php');
$url->param('concept', $concept);
$url->param('courseid', $courseid);
$url->param('eid', $eid);
$url->param('displayformat', $displayformat);
$PAGE->set_url($url);

if ($CFG->forcelogin) {
    require_login();
}

if ($eid) {
    $entry = $DB->get_record('glossary_entries', array('id'=>$eid), '*', MUST_EXIST);
    $glossary = $DB->get_record('glossary', array('id'=>$entry->glossaryid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('glossary', $glossary->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    require_course_login($course, true, $cm);
    $entry->glossaryname = $glossary->name;
    $entry->cmid = $cm->id;
    $entry->courseid = $cm->course;
    $entries = array($entry);

} else if ($concept) {
    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    require_course_login($course);
    $entries = glossary_get_entries_search($concept, $courseid);

} else {
    print_error('invalidelementid');
}

$PAGE->set_pagelayout('course');

if ($entries) {
    foreach ($entries as $key => $entry) {
        // Need to get the course where the entry is,
        // in order to check for visibility/approve permissions there.
        $entrycourse = $DB->get_record('course', array('id' => $entry->courseid), '*', MUST_EXIST);
        $modinfo = get_fast_modinfo($entrycourse);
        // Make sure the entry is visible.
        if (empty($modinfo->cms[$entry->cmid]->uservisible)) {
            unset($entries[$key]);
            continue;
        }
        // Make sure the entry is approved (or approvable by current user).
        if (!$entry->approved and ($USER->id != $entry->userid)) {
            $context = context_module::instance($entry->cmid);
            if (!has_capability('mod/glossary:approve', $context)) {
                unset($entries[$key]);
                continue;
            }
        }
        $entries[$key]->footer = "<p style=\"text-align:right\">&raquo;&nbsp;<a href=\"$CFG->wwwroot/mod/glossary/view.php?g=$entry->glossaryid\">".
                                 format_string($entry->glossaryname, true)."</a></p>";
        add_to_log($entry->courseid, 'glossary', 'view entry', "showentry.php?eid=$entry->id", $entry->id, $entry->cmid);
    }
}

if (!empty($courseid)) {
    $strglossaries = get_string('modulenameplural', 'glossary');
    $strsearch = get_string('search');

    $PAGE->navbar->add($strglossaries);
    $PAGE->navbar->add($strsearch);
    $PAGE->set_title(strip_tags("$course->shortname: $strglossaries $strsearch"));
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
} else {
    echo $OUTPUT->header();    // Needs to be something here to allow linking back to the whole glossary.
}

if ($entries) {
    glossary_print_dynaentry($courseid, $entries, $displayformat);
}

// Show one reduced footer.
echo $OUTPUT->footer();