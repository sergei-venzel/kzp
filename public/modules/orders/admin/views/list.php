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
            <input type="text" name="name" placeholder="Способ доставки" value="<?php echo htmlspecialchars($row['name']);?>" style="width:140px;" />
            <input type="text" name="cost" placeholder="Стоимость" value="<?php echo (float) $row['cost'];?>" style="width:40px;" />
            <button class="action fa fa-lg fa-btn fa-save" title="Сохранить"></button>
            <button class="delete fa fa-lg fa-btn fa-minus-circle attention" title="Удалить способ доставки"></button>
            <textarea class="stable" name="description"><?php echo $row['description'];?></textarea>
        </div>
    </div>
    <?php
}
?>
<script>
    jQuery(document).ready(function($) {

        $('.delete').bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope'), id = parseInt(prnt.attr('data-id'));

            if(!isNaN(id) && confirm('Удалить доставку "'+ $('input[name="name"]', prnt).val() +'"')) {

                obj.attr('disabled', true);

                $.ajax(
                    {
                        url:'shippings.php',
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
                                $('#shipp-list').html(response.result);
                            }
                        }
                    }
                );
            }
        });

        $('.action').bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope');

            obj.attr('disabled', true);

            obj.simpleWaiter({prnt:obj});

            $.ajax(
                {
                    url:'shippings.php',
                    data:$('input, textarea', prnt).serialize(),
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
