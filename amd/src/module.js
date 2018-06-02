define(['jquery'], function($) {
 
    return {
        
        init: function(courseID) {
 
 
            function apply_quick_course(){

                $('.quick_course_toggle').each( function(){

                    $(this).off('click');
                    $(this).on('click', function(){
                        var param = $(this).attr('param');
                        $('#'+param).toggle();
                    });

                });

            }
 
 
 
            $('#quick_course_form').off('submit');
            $('#quick_course_form').on('submit', function(e){

                var search = $('#quick_course_search').val();
                search.trim();

                var results = $('#quick_course_results');
                results.html('');

                if (search == ''){
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                results.html('<div class="quick_course_centre"><img id="quick_course_loading" src="'+M.cfg.wwwroot+'/blocks/quick_course/pix/load.gif" /></div>');

                $.post(M.cfg.wwwroot + '/blocks/quick_course/search.php', {
                    course: courseID,
                    search: search
                }, function(data){
                    results.html(data);
                    apply_quick_course();
                });

                e.preventDefault();
                e.stopPropagation();
                return true;

            });


            // Clear results
            $('#quick_course_clear').off('click');
            $('#quick_course_clear').on('click', function(e){

                $('#quick_course_search').val('');
                $('#quick_course_results').html('');

                e.preventDefault();
                e.stopPropagation();
                return true;

            });
 
        }
        
    }
 
});

