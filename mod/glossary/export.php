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
 * This page is used to export a glossary
 *
 * @package    mod_glossary
 * @copyright  2003 onwards Williams Castillo (castillow@tutopia.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);      // Course Module ID.

$mode= optional_param('mode', '', PARAM_ALPHA);           // Term entry cat date letter search author approval.
$hook= optional_param('hook', '', PARAM_CLEAN);           // The term, entry, cat, etc... to look for based on mode.
$cat = optional_param('cat', 0, PARAM_ALPHANUM);

$url = new moodle_url('/mod/glossary/export.php', array('id'=>$id));
if ($cat !== 0) {
    $url->param('cat', $cat);
}
if ($mode !== '') {
    $url->param('mode', $mode);
}

$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('glossary', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $glossary = $DB->get_record("glossary", array("id"=>$cm->instance))) {
    print_error('invalidid', 'glossary');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/glossary:export', $context);

$strglossaries = get_string("modulenameplural", "glossary");
$strglossary = get_string("modulename", "glossary");
$strallcategories = get_string("allcategories", "glossary");
$straddentry = get_string("addentry", "glossary");
$strnoentries = get_string("noentries", "glossary");
$strsearchindefinition = get_string("searchindefinition", "glossary");
$strsearch = get_string("search");
$strexportfile = get_string("exportfile", "glossary");
$strexportentries = get_string('exportentriestoxml', 'glossary');

$PAGE->set_url('/mod/glossary/export.php', array('id'=>$cm->id));
$PAGE->navbar->add($strexportentries);
$PAGE->set_title(format_string($glossary->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($strexportentries);
echo $OUTPUT->box_start('glossarydisplay generalbox');
$exporturl = moodle_url::make_pluginfile_url($context->id, 'mod_glossary', 'export', 0, "/$cat/", 'export.xml', true);

?>
    <form action="<?php echo $exporturl->out(); ?>" method="post">
    <table border="0" cellpadding="6" cellspacing="6" width="100%">
    <tr><td align="center">
        <input type="submit" value="<?php p($strexportfile)?>" />
    </td></tr></table>
    <div>
    </div>
    </form>
<?php
// Don't need cap check here, we share with the general export.
if (!empty($CFG->enableportfolios) && $DB->count_records('glossary_entries', array('glossaryid' => $glossary->id))) {
    require_once($CFG->libdir . '/portfoliolib.php');
    $button = new portfolio_add_button();
    $button->set_callback_options('glossary_full_portfolio_caller', array('id' => $cm->id), 'mod_glossary');
    $button->render();
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();