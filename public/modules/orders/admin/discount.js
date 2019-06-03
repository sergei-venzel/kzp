$('#add-discount-button').bind('click', function(e) {

    let obj = $(this), container = $('#discount-list');
    let frm = $('form[name="add-discount"]');
    console.log(frm.serialize());
    obj.attr('disabled', true);
    $.ajax(
        {
            url:'discounts.php?action=' + obj.attr('data-action'),
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

$('#promo-gen', $('form[name="add-discount"]')).bind('click', function(e) {

    $('input[name="token"]', $(this).parent()).val(randString());

});

function randString() {

    let possible = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    var text = '', size = 8;
    for(var i=0; i < size; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}