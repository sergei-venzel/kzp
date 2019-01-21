$('#add-ship-button').bind('click', function(e) {

    var obj = $(this), name = $('input[name="name"]'), cost = $('input[name="cost"]'), container = $('#shipp-list');
    var frm = $('form[name="add-shipping"]');
    console.log(frm.serialize());
    obj.attr('disabled', true);
    $.ajax(
        {
            url:'shippings.php?action=' + obj.attr('data-action'),
            data:frm.serialize(),
            cache:false,
            dataType:'json',
            type:'post',
            success:function(response) {
                obj.attr('disabled', false);
                if(response.error) {
                    show_save_alert(response.error);
                }
                else {
                    frm.trigger('reset');
                    container.html(response.result);
                }
            }
        }
    );
});