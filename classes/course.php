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
 * Core Course class
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quick_course;

defined('MOODLE_INTERNAL') || die();

/**
 * Core Course class
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {

    /**
     * Course ID
     * @var int
     */
    protected $id;

    /**
     * Course fullname
     * @var string
     */
    protected $fullname;

    /**
     * Course shortname
     * @var string
     */
    protected $shortname;

    /**
     * Course visible flag
     * @var int
     */
    protected $visible;

    /**
     * Course category ID
     * @var int
     */
    protected $category;

    /**
     * course constructor.
     * @param $id
     * @throws \dml_exception
     */
    public function __construct($id) {

        global $DB;

        $record = $DB->get_record('course', array('id' => $id));
        if ($record) {
            $this->id = $record->id;
            $this->fullname = $record->fullname;
            $this->shortname = $record->shortname;
            $this->visible = $record->visible;
            $this->category = $record->category;
        }

    }

    /**
     * Make sure a course with this id exists
     * @return bool
     */
    public function exists() {
        return ($this->id > 0);
    }

    /**
     * Get property of object
     * @param  string $prop
     * @return mixed
     */
    public function get($prop) {

        if (property_exists($this, $prop)) {
            return $this->$prop;
        } else {
            return null;
        }

    }

    /**
     * Get child courses of this course
     * @return array
     */
    public function get_child_courses() {

        global $DB;

        $results = $DB->get_records_sql("SELECT DISTINCT c.*
                                        FROM {course} c
                                        INNER JOIN {enrol} e ON e.customint1 = c.id
                                        WHERE e.enrol = 'meta'
                                        AND e.courseid = ?", array( $this->id ));

        $return = array();

        if ($results) {
            foreach ($results as $result) {
                $return[$result->id] = new course($result->id);
            }
        }

        return $return;

    }

    /**
     * Get parent courses of this course
     * @return array
     */
    public function get_parent_courses() {

        global $DB;

        $results = $DB->get_records_sql("SELECT DISTINCT c.id
                                        FROM {course} c
                                        INNER JOIN {enrol} e ON e.courseid = c.id
                                        WHERE e.enrol = 'meta'
                                        AND e.customint1 = ?", array( $this->id ));

        $return = array();

        if ($results) {
            foreach ($results as $result) {
                $return[$result->id] = new course($result->id);
            }
        }

        return $return;

    }

    /**
     * Check if the course is a child course (is attached as a meta link to a parent course)
     * @return boolean
     */
    public function is_child_course() {

        $parents = $this->get_parent_courses();
        return (!empty($parents));

    }

    /**
     * Get the full category path of a course
     * @return string
     */
    public function get_category_path() {

        global $DB;

        $path = array();

        // Get the category of this course.
        $category = $DB->get_record('course_categories', array('id' => $this->category));

        $catids = explode('/', $category->path);
        $catids = array_filter($catids);

        foreach ($catids as $catid) {
            $cat = $DB->get_record('course_categories', array('id' => $catid));
            $path[] = $cat->name;
        }

        $path[] = $this->fullname;

        return implode(' / ', $path);

    }

    /**
     * Get the extra css classes to apply, based on course type and visiblilty
     * @return string
     */
    public function get_css_class() {

        $class = '';

        // Parent or child course?
        $class .= ($this->is_child_course()) ? get_config('block_quick_course', 'child_css_class') : '';

        $class .= ' ';

        // Visible or hidden?
        $class .= ($this->visible == 0) ? get_config('block_quick_course', 'hidden_css_class') : '';

        return $class;

    }

    /**
     * Get the row to display for this course results
     * @param  int $id
     * @return mixed
     */
    public static function info($id) {

        global $CFG, $PAGE, $OUTPUT;

        $course = new course($id);
        if (!$course->exists()) {
            return null;
        }

        $context = \context_course::instance($id);
        $renderer = $PAGE->get_renderer('block_quick_course');

        $links = array();

        // Edit link.
        if (has_capability('moodle/course:update', $context)) {
            $links[] = array(
                'url' => new \moodle_url('/course/edit.php', array('id' => $course->get('id'))),
                'title' => get_string('edit'),
                'img' => $OUTPUT->image_url('t/edit')
            );
        }

        // Participants link.
        if (has_capability('moodle/course:viewparticipants', $context)) {
            $links[] = array(
                'url' => new \moodle_url('/user/index.php', array('id' => $course->get('id'))),
                'title' => get_string('participants', 'block_quick_course'),
                'img' => $OUTPUT->image_url('t/groupv')
            );
        }

        // Relationships link - Needs permission to configure meta links on this course in order to see the relationships.
        if (has_capability('enrol/meta:config', $context)) {
            $links[] = array(
                'url' => new \moodle_url('/blocks/quick_course/relationships.php', array('id' => $course->get('id'))),
                'title' => get_string('relationships', 'block_quick_course'),
                'img' => $CFG->wwwroot . '/blocks/quick_course/pix/relationship.png'
            );
        }

        $output = '';
        $output .= $renderer->render_from_template('block_quick_course/course_info', array(
            'config' => $CFG,
            'course' => array(
                'id' => $course->get('id'),
                'fullname'  => format_string( $course->get('fullname') ),
                'shortname' => $course->get('shortname'),
                'path'      => $course->get_category_path(),
                'cssclass'  => $course->get_css_class()
            ),
            'links' => $links
        ));

        return $output;

    }

}
