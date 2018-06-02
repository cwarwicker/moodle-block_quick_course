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
 * List of meta relationships for a course
 *
 * @package    block_quick_course
 * @copyright  2016 Conn Warwicker <conn@cmrwarwicker.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_login();

$courseID = optional_param('id', SITEID, PARAM_INT);
$context = context_course::instance($courseID);

// Requires capabiity to configure the meta enrolments on the course, to see them
require_capability('enrol/meta:config', $context);

$PAGE->set_context( context_course::instance(SITEID) );
$PAGE->set_url($CFG->wwwroot . '/blocks/quick_course/relationships.php?id='.$courseID);
$PAGE->set_title(get_string('relationships', 'block_quick_course') );
$PAGE->set_heading( get_string('relationships', 'block_quick_course') );
$PAGE->set_cacheable(true);
$PAGE->set_pagelayout( 'base' );

$course = $DB->get_record("course", array("id" => $courseID));

$meta = $DB->get_records_sql("SELECT DISTINCT c.*
                              FROM {course} c
                              INNER JOIN {enrol} e ON e.courseid = c.id
                              WHERE e.enrol = 'meta'
                              AND e.customint1 = ?", array($courseID));

$child = $DB->get_records_sql("SELECT DISTINCT c.*
                              FROM {course} c
                              INNER JOIN {enrol} e ON e.customint1 = c.id
                              WHERE e.enrol = 'meta'
                              AND e.courseid = ?", array($courseID));



echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('relationshipsof', 'block_quick_course' ).' '.$course->fullname);

// Parent courses
echo $OUTPUT->heading(get_string('parentcourses', 'block_quick_course'), 4);

if ($meta)
{
    
    foreach($meta as $m)
    {
        echo "<a href='{$CFG->wwwroot}/course/view.php?id={$m->id}'>". $m->fullname."</a><br>";
    }       
    
}
else
{
    echo get_string('nocourses', 'block_quick_course' );
}


echo "<br><br><hr><br>";

// Child Courses
echo $OUTPUT->heading(get_string('childcourses', 'block_quick_course'), 4);

if ($child)
{
    
    foreach($child as $m)
    {
        echo "<a href='{$CFG->wwwroot}/course/view.php?id={$m->id}'>". $m->fullname."</a><br>";
    }       
    
}
else 
{
    echo get_string('nocourses', 'block_quick_course' );
}


echo $OUTPUT->footer();