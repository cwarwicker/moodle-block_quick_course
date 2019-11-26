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
 * Core Search class
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quick_course;

defined('MOODLE_INTERNAL') || die();

class search
{

    private $course, $context, $results = [];

    public function set_course(\stdClass $course) {
        $this->course = $course;
    }

    public function set_context(\context $context) {
        $this->context = $context;
    }

    public function results($search) {

        global $CFG, $DB, $USER;

        $results = array(
            'exact' => array(),
            'similar' => array()
        );

        // First find out what the search limit should be.
        $limit = get_config('block_quick_course', 'limit');
        if (!$limit) {
            $limit = 50;
        }

        // Build up the SQL to search the courses.
        // This searches the user's enrolled courses and any courses in any category they are enrolled onto.
        $sql = array();
        $sql['select'] = "SELECT id  ";
        $sql['from'] = "FROM (
                (
                    select c.id, c.shortname, c.fullname, c.visible
            		from {course} c
            		inner join {course_categories} cc on cc.id = c.category
            		inner join {context} x on x.instanceid = cc.id
            		inner join {role_assignments} r on r.contextid = x.id
            		where r.userid = ? and x.contextlevel = ?
                )
                UNION
                (
                    SELECT c.id, c.shortname, c.fullname, c.visible
            		FROM {course} c
            		INNER JOIN {enrol} e ON e.courseid = c.id
            		INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id
            		INNER JOIN {user} u ON u.id = ue.userid
            		WHERE ue.userid = ? AND ue.status = ? AND e.status = ?
                )

            ) courses  ";

        // TODO: QC-3: Search sub-categories of category enrolments.

        // First search for exact results.
        $sql['where'] = "WHERE (courses.fullname = ? OR courses.shortname = ?) AND courses.id <> ? ";
        $sql['order'] = "ORDER BY courses.fullname, courses.shortname  ";
        $sqlparams = array(
            $USER->id,
            CONTEXT_COURSECAT,
            $USER->id,
            ENROL_USER_ACTIVE,
            ENROL_INSTANCE_ENABLED,
            $search,
            $search,
            SITEID
        );

        // If we have the capability to searchall courses, remove the from section with all the JOINS and
        // literally just search in mdl_course.
        if (has_capability('block/quick_course:searchall', $this->context)) {
            $sql['from'] = "FROM {course} courses  ";
            $sqlparams = array($search, $search, SITEID);
        }

        $fullsql = implode(" ", $sql);
        $results['exact'] = $DB->get_records_sql($fullsql, $sqlparams, 0, $limit);

        // Then for similar ones.
        $sql['where'] = "WHERE (courses.fullname != ? AND courses.shortname != ?)
                        AND (
                            ".$DB->sql_like('courses.fullname', '?', false, false)."
                            OR
                            ".$DB->sql_like('courses.shortname', '?', false, false)."
                            )
                        AND courses.id <> ?";
        $sqlparams = array(
            $USER->id,
            CONTEXT_COURSECAT,
            $USER->id,
            ENROL_USER_ACTIVE,
            ENROL_INSTANCE_ENABLED,
            $search,
            $search,
            "%{$search}%",
            "%{$search}%",
            SITEID
        );

        // If we have the capability to searchall courses, remove the from section with all the JOINS and
        // literally just search in mdl_course.
        if (has_capability('block/quick_course:searchall', $this->context)) {
            $sql['from'] = "FROM {course} courses  ";
            $sqlparams = array($search, $search, "%{$search}%", "%{$search}%", SITEID);
        }

        $fullsql = implode(" ", $sql);

        $results['similar'] = $DB->get_records_sql($fullsql, $sqlparams, 0, $limit);

        return $results;

    }

}