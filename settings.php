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
 * Settings for block_quick_course
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(
        new admin_setting_configtext('block_quick_course/limit',
            get_string('resultlimit', 'block_quick_course'),
            get_string('resultlimit:desc', 'block_quick_course'),
            50)
    );

    $settings->add(
        new admin_setting_configtext('block_quick_course/hidden_css_class',
            get_string('hiddencssclass', 'block_quick_course'),
            get_string('hiddencssclass:desc', 'block_quick_course'),
            'dimmed')
    );

    $settings->add(
        new admin_setting_configtext('block_quick_course/child_css_class',
            get_string('childcssclass', 'block_quick_course'),
            get_string('childcssclass:desc', 'block_quick_course'),
            'child-course')
    );

}