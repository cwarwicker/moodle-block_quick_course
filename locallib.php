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
 * block_quick_course core class
 *
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function block_quick_course_can_user_see_result($course) {

    global $USER;

    print_object($course);

}

function block_quick_course_search($search, $exact) {

    global $DB;

    $results = false;
    $wildcard = "%{$search}%";

    // Exact results
    if ($exact) {

        $results = $DB->get_records_select(
            "course",
            "fullname = ? OR shortname = ?",
            array($search, $search),
            "fullname ASC, shortname ASC", "id, shortname, fullname, visible"
        );

    } else {

        // Similar results
        $results = $DB->get_records_select(
            "course",
            "(".$DB->sql_like('fullname', '?', false, false)." OR ".$DB->sql_like('shortname', '?', false, false).")
            AND
            (fullname != ? AND shortname != ?)",
            array($wildcard, $wildcard, $search, $search),
            "fullname ASC, shortname ASC", "id, shortname, fullname, visible");

    }

    return $results;

}


/**
* Function which displays the info for a result
*/
function block_quick_course_get_course_info($course) {

    global $CFG, $OUTPUT, $DB;


    // Loop through results.

    // Filter out any result the user should not be able to see.

    // Apply css classes depending on meta/child, visible/hidden, etc...

    // Build result bar.

    // Check capabilities to see if the user can see the extra links in the results.

    // Return results.




    $return = "";

    // Is it a child course?
    $children = $DB->get_records_sql("SELECT DISTINCT c.*
                                      FROM {course} c
                                      INNER JOIN {enrol} e ON e.customint1 = c.id
                                      WHERE e.enrol = 'meta'
                                      AND e.courseid = ?", array($course->id));

    // Bold for Meta courses, Italic for Child
    $fontstyle = ($children) ? 'font-weight:bold;' : 'font-style:italic;';

    // Hidden courses should be greyed out
    $class = ($course->visible) ? '' : 'dimmed';

    $return .= "<span><img src='{$CFG->wwwroot}/blocks/quick_course/pix/plus.png' style='width:16px;vertical-align:bottom;' class='quick_course_toggle' param='hidden_c{$course->id}' />  ";
    $return .= "<a href='{$CFG->wwwroot}/course/view.php?id={$course->id}' target='_blank' style='{$fontstyle}' class='{$class}'>".format_string($course->fullname)."</a></span><br>";
    $return .= "<div class='quick_course_expand' id='hidden_c{$course->id}' style='display: none;'>";

    $context = context_course::instance($course->id);

    // Edit course link (course settings)
    if (has_capability('moodle/course:update', $context)) {
        $return .= "<a href='{$CFG->wwwroot}/course/edit.php?id={$course->id}' target='_blank' title='".get_string('edit', 'block_quick_course')."'><img src='".$OUTPUT->image_url('t/edit')."' /></a> &nbsp; ";
    }

    // Participants link
    if (has_capability('moodle/course:viewparticipants', $context)) {
        $return .= "<a href='{$CFG->wwwroot}/user/index.php?id={$course->id}' target='_blank' title='".get_string('participants', 'block_quick_course')."'><img src='".$OUTPUT->image_url('t/groupv')."' /></a> &nbsp; ";
    }

    // Relationships link - Needs permission to configure meta links on this course in order to see the relationships
    if (has_capability('enrol/meta:config', $context)) {
        $return .= "<a href='{$CFG->wwwroot}/blocks/quick_course/relationships.php?id={$course->id}' target='_blank' title='".get_string('relationships', 'block_quick_course')."'><img src='".$CFG->wwwroot."/blocks/quick_course/pix/relationship.png' /></a> &nbsp; ";
    }

    $return .= "</div><br>";

    return $return;

}