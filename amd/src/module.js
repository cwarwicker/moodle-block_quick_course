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
 * @package    block_quick_course
 * @copyright  2019 Conn Warwicker <conn@cmrwarwicker.com>
 * @link       https://github.com/cwarwicker/moodle-block_quick_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    var module = {};

    module.bind = function(courseID){

        // Result links toggle.
        $('.quick_course_toggle').each( function(){

            $(this).off('click');
            $(this).on('click', function(){
                var id = $(this).data('courseid');
                $('#hidden_course_' + id).toggle();
            });

        });

        // Submit search.
        $('#quick_course_form').off('submit').on('submit', function(e){

            var search = $('#quick_course_search').val();
            search.trim();

            var results = $('#quick_course_results');
            results.html('');

            // If the search term was empty, just stop.
            if (search == ''){
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Display the loading gif while the results are fetched.
            results.html('<div class="quick_course_centre"><img id="quick_course_loading" src="' + M.cfg.wwwroot + '/blocks/quick_course/pix/load.gif" /></div>');

            // Ajax call to get the results.
            $.post(M.cfg.wwwroot + '/blocks/quick_course/ajax/search.php', {
                course: courseID,
                search: search
            }, function(data){
                results.html(data);
                module.bind(courseID);
            });

            e.preventDefault();
            e.stopPropagation();
            return true;

        });

        // Clear results.
        $('#quick_course_clear').off('click').on('click', function(e){

            $('#quick_course_search').val('');
            $('#quick_course_results').html('');

            e.preventDefault();
            e.stopPropagation();
            return true;

        });

    };

    module.init = function(courseID){
        module.bind(courseID);
    };

    return module;

});