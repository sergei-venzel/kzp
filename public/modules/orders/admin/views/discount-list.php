<div id="discounts-set">
<?php
/**
 * kz.loc
 * add-ship.php
 * Date: 1/21/19
 */
/**
 * @var array $items
 */
foreach($items as $row) {

    ?>
    <div class="boxed clearf section-scope corner-all" data-id="<?php echo $row['id'];?>" style="margin:4px 0 8px 0;padding:4px 2px;background-color:#efe;border:1px solid #dedede;">
        <div class="section-data">
            <input type="hidden" name="action" value="modifySection" />
            <input type="hidden" name="id" value="<?php echo $row['id'];?>" />
            <input disabled readonly type="text" name="token" placeholder="Промо-код" value="<?php echo htmlspecialchars($row['token']);?>" style="width:140px;" />
            <input type="text" name="discount" placeholder="Скидка" value="<?php echo (float) $row['discount'];?>" style="width:40px;" />
            <input type="text" class="frm-date" name="expired" value="<?php echo $row['expired'];?>" placeholder="Действует до..." />
            <button class="action fa fa-lg fa-btn fa-save" title="Сохранить"></button>
            <button class="delete fa fa-lg fa-btn fa-minus-circle attention" title="Удалить промо-код"></button>
        </div>
    </div>
    <?php
}
?>
</div>
<script>
    jQuery(document).ready(function($) {

        let DSCC = $('#discounts-set');
        $('input.frm-date', DSCC).datepicker(
            {
                buttonImageOnly:false,
                // buttonImage:'../images/calendar.png',
                // showOn:'button',
                dateFormat: "dd-mm-yy",
                buttonText:'Set Date'
            }
        );

        $('.delete', DSCC).bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope'), id = parseInt(prnt.attr('data-id'));

            if(!isNaN(id) && confirm('Удалить промо-код "'+ $('input[name="token"]', prnt).val() +'"')) {

                obj.attr('disabled', true);

                $.ajax(
                    {
                        url:'discounts.php',
                        data:{action:'removeSection',id:id},
                        cache:false,
                        dataType:'json',
                        type:'post',
                        success:function(response) {
                            obj.attr('disabled', false);
                            if(response.error) {
                                show_save_alert(response.error);
                            }
                            else {
                                $('#discount-list').html(response.result);
                            }
                        }
                    }
                );
            }
        });

        $('.action', DSCC).bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope');

            obj.attr('disabled', true);

            obj.simpleWaiter({prnt:obj});

            $.ajax(
                {
                    url:'discounts.php',
                    data:$('input', prnt).serialize(),
                    cache:false,
                    dataType:'json',
                    type:'post',
                    success:function(response) {

                        obj.attr('disabled', false);
                        obj.trigger('stop');
                        if(response.error) {
                            show_save_alert(response.error);
                        }
                    }
                }
            );
        });
    });
</script>
