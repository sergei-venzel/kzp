$('#add-section-button').bind('click', function(e) {

    var obj = $(this), src = $('input[name="section_name"]'), srtord = $('input[name="sort_order"]'), container = $('#sections-list');
    obj.attr('disabled', true);
    $.ajax(
        {
            url:'sections.php',
            data:{action:'createSection',section_name:src.val(), sort_order:srtord.val()},
            cache:false,
            dataType:'json',
            type:'post',
            success:function(response) {
                obj.attr('disabled', false);
                if(response.error) {
                    show_save_alert(response.error);
                }
                else {
                    src.val('');
                    srtord.val('0');
                    container.html(response.result);
                }
            }
        }
    );
});