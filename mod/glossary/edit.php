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
 * Glossary module edit page
 *
 * @package    mod_glossary
 * @copyright  2003 onwards Williams Castillo (castillow@tutopia.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('edit_form.php');

$cmid = required_param('cmid', PARAM_INT);            // Course Module ID.
$id   = optional_param('id', 0, PARAM_INT);           // EntryID.

if (!$cm = get_coursemodule_from_id('glossary', $cmid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('coursemisconf');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

if (!$glossary = $DB->get_record('glossary', array('id'=>$cm->instance))) {
    print_error('invalidid', 'glossary');
}

$url = new moodle_url('/mod/glossary/edit.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);

if ($id) { // If entry is specified.
    if (isguestuser()) {
        print_error('guestnoedit', 'glossary', "$CFG->wwwroot/mod/glossary/view.php?id=$cmid");
    }

    if (!$entry = $DB->get_record('glossary_entries', array('id'=>$id, 'glossaryid'=>$glossary->id))) {
        print_error('invalidentry');
    }

    $ineditperiod = ((time() - $entry->timecreated <  $CFG->maxeditingtime) || $glossary->editalways);
    if (!has_capability('mod/glossary:manageentries', $context) and !($entry->userid == $USER->id and ($ineditperiod and has_capability('mod/glossary:write', $context)))) {
        if ($USER->id != $fromdb->userid) {
            print_error('errcannoteditothers', 'glossary', "view.php?id=$cm->id&amp;mode=entry&amp;hook=$id");
        } else if (!$ineditperiod) {
            print_error('erredittimeexpired', 'glossary', "view.php?id=$cm->id&amp;mode=entry&amp;hook=$id");
        }
    }

    // Prepare extra data.
    if ($aliases = $DB->get_records_menu("glossary_alias", array("entryid"=>$id), '', 'id, alias')) {
        $entry->aliases = implode("\n", $aliases) . "\n";
    }
    if ($categoriesarr = $DB->get_records_menu("glossary_entries_categories", array('entryid'=>$id), '', 'id, categoryid')) {
        // TODO: this fetches categories from both main and secondary glossary.
        $entry->categories = array_values($categoriesarr);
    }

} else { // New Entry.
    require_capability('mod/glossary:write', $context);
    // Note: guest user does not have any write capability.
    $entry = new stdClass();
    $entry->id = null;
}

$maxfiles = 99;                // TODO: add some setting (MDL-38264).
$maxbytes = $course->maxbytes; // TODO: add some setting (MDL-38264).

$definitionoptions = array('trusttext'=>true, 'subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'context'=>$context);
$attachmentoptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);

$entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_glossary', 'entry', $entry->id);
$entry = file_prepare_standard_filemanager($entry, 'attachment', $attachmentoptions, $context, 'mod_glossary', 'attachment', $entry->id);

$entry->cmid = $cm->id;

// Create form and set initial data.
$mform = new mod_glossary_entry_form(null, array('current'=>$entry, 'cm'=>$cm, 'glossary'=>$glossary,
                                                 'definitionoptions'=>$definitionoptions, 'attachmentoptions'=>$attachmentoptions));

if ($mform->is_cancelled()) {
    if ($id) {
        redirect("view.php?id=$cm->id&mode=entry&hook=$id");
    } else {
        redirect("view.php?id=$cm->id");
    }

} else if ($entry = $mform->get_data()) {
    $timenow = time();

    $categories = empty($entry->categories) ? array() : $entry->categories;
    unset($entry->categories);
    $aliases = trim($entry->aliases);
    unset($entry->aliases);

    if (empty($entry->id)) {
        $entry->glossaryid       = $glossary->id;
        $entry->timecreated      = $timenow;
        $entry->userid           = $USER->id;
        $entry->timecreated      = $timenow;
        $entry->sourceglossaryid = 0;
        $entry->teacherentry     = has_capability('mod/glossary:manageentries', $context);
    }

    $entry->concept          = trim($entry->concept);
    $entry->definition       = '';          // Updated later.
    $entry->definitionformat = FORMAT_HTML; // Updated later.
    $entry->definitiontrust  = 0;           // Updated later.
    $entry->timemodified     = $timenow;
    $entry->approved         = 0;
    $entry->usedynalink      = isset($entry->usedynalink) ?   $entry->usedynalink : 0;
    $entry->casesensitive    = isset($entry->casesensitive) ? $entry->casesensitive : 0;
    $entry->fullmatch        = isset($entry->fullmatch) ?     $entry->fullmatch : 0;

    if ($glossary->defaultapproval or has_capability('mod/glossary:approve', $context)) {
        $entry->approved = 1;
    }

    if (empty($entry->id)) {
        // New entry.
        $entry->id = $DB->insert_record('glossary_entries', $entry);

        // Update completion state.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $glossary->completionentries && $entry->approved) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        add_to_log($course->id, "glossary", "add entry",
                   "view.php?id=$cm->id&amp;mode=entry&amp;hook=$entry->id", $entry->id, $cm->id);

    } else {
        // Existing entry.
        $DB->update_record('glossary_entries', $entry);
        add_to_log($course->id, "glossary", "update entry",
                   "view.php?id=$cm->id&amp;mode=entry&amp;hook=$entry->id",
                   $entry->id, $cm->id);
    }

    // Save and relink embedded images and save attachments.
    $entry = file_postupdate_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_glossary', 'entry', $entry->id);
    $entry = file_postupdate_standard_filemanager($entry, 'attachment', $attachmentoptions, $context, 'mod_glossary', 'attachment', $entry->id);

    // Store the updated value values.
    $DB->update_record('glossary_entries', $entry);

    // Refetch complete entry.
    $entry = $DB->get_record('glossary_entries', array('id'=>$entry->id));

    // Update entry categories.
    $DB->delete_records('glossary_entries_categories', array('entryid'=>$entry->id));
    // TODO: this deletes cats from both both main and secondary glossary.
    if (!empty($categories) and array_search(0, $categories) === false) {
        foreach ($categories as $catid) {
            $newcategory = new stdClass();
            $newcategory->entryid    = $entry->id;
            $newcategory->categoryid = $catid;
            $DB->insert_record('glossary_entries_categories', $newcategory, false);
        }
    }

    // Update aliases.
    $DB->delete_records('glossary_alias', array('entryid'=>$entry->id));
    if ($aliases !== '') {
        $aliases = explode("\n", $aliases);
        foreach ($aliases as $alias) {
            $alias = trim($alias);
            if ($alias !== '') {
                $newalias = new stdClass();
                $newalias->entryid = $entry->id;
                $newalias->alias   = $alias;
                $DB->insert_record('glossary_alias', $newalias, false);
            }
        }
    }

    redirect("view.php?id=$cm->id&mode=entry&hook=$entry->id");
}

if (!empty($id)) {
    $PAGE->navbar->add(get_string('edit'));
}

$PAGE->set_title(format_string($glossary->name));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($glossary->name));

$mform->display();

echo $OUTPUT->footer();
