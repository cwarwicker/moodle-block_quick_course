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
 * AJAX search script
 *
 * @package    block_quick_course
 * @copyright  2016 Conn Warwicker <conn@cmrwarwicker.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once 'locallib.php';
require_login();

$search = required_param('search', PARAM_TEXT);
$courseID = required_param('course', PARAM_INT);

// Results limit
$limit = get_config('quick_course', 'limit');
if (!$limit){
    $limit = 100;
}

// If search ends with "-a" ignore the limit and display all
if (preg_match("/ \-a$/", $search)){
    $search = substr($search, 0, -3);
    $limit = false;
}

if ($search == ''){
    exit;
}

// Check valid course
$course = get_course($courseID);
if (!$course){
    exit;
}

$context = context_course::instance($COURSE->id);
if (!has_capability('block/quick_course:search', $context)){
    exit;
}

$PAGE->set_context($context);
        
$output = "";

// Exact Results
$output .= "<p class='quick_course_centre quick_course_bold'>".get_string('exactresults', 'block_quick_course')."</p>";

$results = $DB->get_records_select("course", "fullname = ? OR shortname = ?", array($search, $search), "fullname ASC, shortname ASC", "id, shortname, fullname, visible");

if (!$results){
    $output .= "<em>".get_string('noresults', 'block_quick_course')."...</em>";
} else {
    
    $n = 0;
    
    foreach($results as $result)
    {
        
        if ($limit > 0 && $n >= $limit){
            break;
        }
        
        $output .= block_quick_course_get_course_info($result);        
        $n++;
        
    }
    
    // if more
    if ($limit > 0){
        $cnt = count($results);
        if ($cnt > $limit){
            $more = $cnt - $limit;
            $output .= "<p class='quick_course_centre'><small>{$more} ".get_string('moreresults', 'block_quick_course')."</small></p>";
        }
    }
    
}


$output .= "<br><br>";


// Similar Results
$output .= "<p class='quick_course_centre quick_course_bold'>".get_string('similarresults', 'block_quick_course')."</p>";

$results = $DB->get_records_select("course", "(".$DB->sql_like('fullname', '?', false, false)." OR ".$DB->sql_like('shortname', '?', false, false).") AND (fullname != ? AND shortname != ?)", array('%'.$search.'%', '%'.$search.'%', $search, $search), "fullname ASC, shortname ASC", "id, shortname, fullname, visible");
if (!$results){
    $output .= "<em>".get_string('noresults', 'block_quick_course')."...</em>";
} else {
    
    $n = 0;
    
    foreach($results as $result)
    {
        
        if ($limit > 0 && $n >= $limit){
            break;
        }
       
        $output .= block_quick_course_get_course_info($result);        
        
        $n++;
        
    }
    
    // if more
    if ($limit > 0){
        $cnt = count($results);
        if ($cnt > $limit){
            $more = $cnt - $limit;
            $output .= "<p class='quick_course_centre'><small>{$more} ".get_string('moreresults', 'block_quick_course')."</small></p>";
        }    
    }
}

echo $output;
exit;