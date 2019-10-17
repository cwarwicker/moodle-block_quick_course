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

class block_quick_course extends block_base
{

    public function init() {
        $this->title = get_string('pluginname', 'block_quick_course');
    }

    public function get_content() {

        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';

        $context = context_course::instance($COURSE->id);

        // If they don't have the search capability, then don't display the block.
        if (!has_capability('block/quick_course:search', $context)) {
            return $this->content;
        }

        // Search bar.

        // Clear results link.
        $this->content->text .= html_writer::start_tag('p', array('class' => 'quick_course_centre'));

            $this->content->text .= html_writer::tag(
                'small',
                html_writer::link('#', get_string('clear', 'block_quick_course'),
                array('id' => 'quick_course_clear'))
            );

        $this->content->text .= html_writer::end_tag('p');

        // Form input.
        $this->content->text .= html_writer::start_tag('div', array('id' => 'quick_course'));

            $this->content->text .= html_writer::tag(
                'form',
                html_writer::tag('input', null, array('id' => 'quick_course_search', 'type' => 'text')),
                array('id' => 'quick_course_form', 'method' => 'post', 'action' => '')
            );

        $this->content->text .= html_writer::end_tag('div');

        // Results.
        $this->content->text .= html_writer::tag('br', null);
        $this->content->text .= html_writer::tag('div', null, array('id' => 'quick_course_results'));

        $this->page->requires->js_call_amd('block_quick_course/module', 'init', array($COURSE->id));

        return $this->content;

    }

    public function has_config() {
        return true;
    }


}