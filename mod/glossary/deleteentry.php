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
 * Glossary module delete entries
 *
 * @package    mod_glossary
 * @copyright  2003 onwards Williams Castillo (castillow@tutopia.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

$id       = required_param('id', PARAM_INT);          // Course module ID.
$confirm  = optional_param('confirm', 0, PARAM_INT);  // Commit the operation?
$entry    = optional_param('entry', 0, PARAM_INT);    // Entry id.
$prevmode = required_param('prevmode', PARAM_ALPHA);
$hook     = optional_param('hook', '', PARAM_CLEAN);

$url = new moodle_url('/mod/glossary/deleteentry.php', array('id'=>$id, 'prevmode'=>$prevmode));
if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($entry !== 0) {
    $url->param('entry', $entry);
}
if ($hook !== '') {
    $url->param('hook', $hook);
}
$PAGE->set_url($url);

$strglossary   = get_string("modulename", "glossary");
$strglossaries = get_string("modulenameplural", "glossary");
$stredit       = get_string("edit");
$entrydeleted  = get_string("entrydeleted", "glossary");


if (! $cm = get_coursemodule_from_id('glossary', $id)) {
    print_error("invalidcoursemodule");
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $entry = $DB->get_record("glossary_entries", array("id"=>$entry))) {
    print_error('invalidentry');
}

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
$manageentries = has_capability('mod/glossary:manageentries', $context);

if (! $glossary = $DB->get_record("glossary", array("id"=>$cm->instance))) {
    print_error('invalidid', 'glossary');
}


$strareyousuredelete = get_string("areyousuredelete", "glossary");

if (($entry->userid != $USER->id) and !$manageentries) { // Guest id is never matched, no need for special check here.
    print_error('nopermissiontodelentry');
}
$ineditperiod = ((time() - $entry->timecreated <  $CFG->maxeditingtime) || $glossary->editalways);
if (!$ineditperiod and !$manageentries) {
    print_error('errdeltimeexpired', 'glossary');
}

// If data is submitted, then process and store.

if ($confirm and confirm_sesskey()) { // The operation was confirmed.
    // If it is an imported entry, just delete the relation.

    if ($entry->sourceglossaryid) {
        if (!$newcm = get_coursemodule_from_instance('glossary', $entry->sourceglossaryid)) {
            print_error('invalidcoursemodule');
        }
        $newcontext = context_module::instance($newcm->id);

        $entry->glossaryid       = $entry->sourceglossaryid;
        $entry->sourceglossaryid = 0;
        $DB->update_record('glossary_entries', $entry);

        // Move the attachments too.
        $fs = get_file_storage();

        if ($oldfiles = $fs->get_area_files($context->id, 'mod_glossary', 'attachment', $entry->id)) {
            foreach ($oldfiles as $oldfile) {
                $file_record = new stdClass();
                $file_record->contextid = $newcontext->id;
                $fs->create_file_from_storedfile($file_record, $oldfile);
            }
            $fs->delete_area_files($context->id, 'mod_glossary', 'attachment', $entry->id);
            $entry->attachment = '1';
        } else {
            $entry->attachment = '0';
        }
        $DB->update_record('glossary_entries', $entry);

    } else {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_glossary', 'attachment', $entry->id);
        $DB->delete_records("comments", array('itemid'=>$entry->id, 'commentarea'=>'glossary_entry', 'contextid'=>$context->id));
        $DB->delete_records("glossary_alias", array("entryid"=>$entry->id));
        $DB->delete_records("glossary_entries", array("id"=>$entry->id));

        // Update completion state.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $glossary->completionentries) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $entry->userid);
        }

        // Delete glossary entry ratings.
        require_once($CFG->dirroot.'/rating/lib.php');
        $delopt = new stdClass();
        $delopt->contextid = $context->id;
        $delopt->component = 'mod_glossary';
        $delopt->ratingarea = 'entry';
        $delopt->itemid = $entry->id;
        $rm = new rating_manager();
        $rm->delete_ratings($delopt);
    }

    add_to_log($course->id, "glossary", "delete entry", "view.php?id=$cm->id&amp;mode=$prevmode&amp;hook=$hook", $entry->id, $cm->id);
    redirect("view.php?id=$cm->id&amp;mode=$prevmode&amp;hook=$hook");

} else {
    // The operation has not been confirmed yet so ask the user to do so.
    $PAGE->navbar->add(get_string('delete'));
    $PAGE->set_title(format_string($glossary->name));
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $areyousure = "<b>".format_string($entry->concept)."</b><p>$strareyousuredelete</p>";
    $linkyes    = 'deleteentry.php';
    $linkno     = 'view.php';
    $optionsyes = array('id'=>$cm->id, 'entry'=>$entry->id, 'confirm'=>1, 'sesskey'=>sesskey(), 'prevmode'=>$prevmode, 'hook'=>$hook);
    $optionsno  = array('id'=>$cm->id, 'mode'=>$prevmode, 'hook'=>$hook);

    echo $OUTPUT->confirm($areyousure, new moodle_url($linkyes, $optionsyes), new moodle_url($linkno, $optionsno));

    echo $OUTPUT->footer();
}
