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
 * @package mod_glossary
 * @copyright 2004 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function glossary_show_entry_continuous($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1, $aliases=false) {

    global $USER;

    echo '<table class="glossarypost continuous" cellspacing="0">';
    echo '<tr valign="top">';
    echo '<td class="entry">';
    glossary_print_entry_approval($cm, $entry, $mode);
    glossary_print_entry_attachment($entry, $cm, 'html', 'right');
    echo '<div class="concept">';
    glossary_print_entry_concept($entry);
    echo '</div> ';
    glossary_print_entry_definition($entry, $glossary, $cm);
    $entry->alias = '';
    echo '</td></tr>';

    echo '<tr valign="top"><td class="entrylowersection">';
    glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases);
    echo '</td>';
    echo '</tr>';
    echo "</table>\n";
}

function glossary_print_entry_continuous($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {

    // The print view for this format is exactly the normal view, so we use it.

    // Take out autolinking in definitions un print view.
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    // Call to view function (without icons, ratings and aliases) and return its result.
    glossary_show_entry_continuous($course, $cm, $glossary, $entry, $mode, $hook, false, false, false);

}


