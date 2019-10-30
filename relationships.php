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
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

$courseid = optional_param('id', false, PARAM_INT);
$context = context_course::instance($courseid);

// Requires capabiity to configure the meta enrolments on the course, to see them.
require_capability('enrol/meta:config', $context);

$course = new block_quick_course\course($courseid);
if (!$course->exists()) {
    print_error('invalidcourse', 'block_quick_course');
}

$PAGE->set_context( $context );
$PAGE->set_url( new moodle_url('/blocks/quick_course/relationships.php', array('id' => $courseid)) );
$PAGE->set_title( get_string('relationships', 'block_quick_course') );
$PAGE->set_heading( get_string('relationships', 'block_quick_course') );
$PAGE->set_cacheable(true);
$PAGE->set_pagelayout( 'base' );

$parents = $course->get_parent_courses();
$children = $course->get_child_courses();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('relationshipsof', 'block_quick_course' ).' '.$course->get('fullname'));

// Parent courses.
echo $OUTPUT->heading( get_string('parentcourses', 'block_quick_course'), 4);

if ($parents) {

    foreach ($parents as $parent) {
        echo html_writer::tag('a', $parent->get('fullname'), array(
            'href' => new moodle_url('/course/view.php', array('id' => $parent->get('id')))
        ));
    }

} else {
    echo get_string('nocourses', 'block_quick_course' );
}

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('hr');
echo html_writer::empty_tag('br');

// Child Courses.
echo $OUTPUT->heading(get_string('childcourses', 'block_quick_course'), 4);

if ($children) {

    foreach ($children as $child) {
        echo html_writer::tag('a', $child->get('fullname'), array(
            'href' => new moodle_url('/course/view.php', array('id' => $child->get('id')))
        ));
    }

} else {
    echo get_string('nocourses', 'block_quick_course' );
}

echo $OUTPUT->footer();