<?php

/**
 * @var array $items
 */
foreach($items as $row) {

    ?>
    <div class="boxed clearf section-scope corner-all" data-id="<?php echo $row['id'];?>" style="margin:4px 0 8px 0;padding:4px 2px;background-color:#efe;border:1px solid #dedede;">
        <div class="section-data">
            <input type="text" name="section_name" placeholder="Наименование Раздела" value="<?php echo htmlspecialchars($row['name']);?>" style="width:140px;" />
            <input type="number" name="sort_order" placeholder="Порядок сортировки" value="<?php echo (int) $row['sort_order'];?>" style="width:40px;" />

            <span class="add-items fa fa-lg fa-btn fa-list" title="Добавить категории в Раздел"></span>
            <button class="action fa fa-lg fa-btn fa-save" title="Сохранить"></button>
            <button class="delete fa fa-lg fa-btn fa-minus-circle attention" title="Удалить Раздел"></button>
            <div style="position:relative;width:0;height:0;display:inline-block;">
                <div class="boxed tost corner-all shadow paper" style="right:auto;left:0;top:-20px;display:none;">
                    <span class="hide fa fa-btn fa-lg fa-close"></span>
                </div>
            </div>
        </div>
        <div class="section-categories boxed clearf corner-all">
            <?php foreach($row['categories'] as $cat_item):?>
            <span class="sect-item" data-cat-id="<?php echo $cat_item['categoryId'];?>"><?php echo $cat_item['p_name'];?></span>
            <?php endforeach;?>
        </div>
    </div>
<?php
}
?>
<script>
    jQuery(document).ready(function($) {

        $('.section-scope').bind('store', function(e) {

            var obj = $(this), inp = $('input[name="catId"]', obj);
            if(!inp.length) {
                obj.prepend($('<input type="hidden" name="catId" />'));
                inp = $('input[name="catId"]', obj);
            }

            var sectCats = $('.section-categories', obj), ids = $('.sect-item', sectCats).map(function(ind, el) {

                return parseInt($(el).attr('data-cat-id'));
            }).get();

            inp.val(ids.join(','));
        }).trigger('store');

        $('.delete').bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope'), id = parseInt(prnt.attr('data-id'));

            if(!isNaN(id) && confirm('Удалить раздел "'+ $('input[name="section_name"]', prnt).val() +'"')) {

                obj.attr('disabled', true);

                $.ajax(
                    {
                        url:'sections.php',
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
                                $('#sections-list').html(response.result);
                            }
                        }
                    }
                );
            }
        });

        $('.action').bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope'), id = parseInt(prnt.attr('data-id'));

            $('.paper', prnt).hide();

            if(!isNaN(id)) {

                obj.attr('disabled', true);

                obj.simpleWaiter({prnt:obj});

                $.ajax(
                    {
                        url:'sections.php',
                        data:{
                            action:'modifySection',
                            id:id,
                            section_name:$('input[name="section_name"]', prnt).val(),
                            sort_order:$('input[name="sort_order"]', prnt).val(),
                            section_cats:$('input[name="catId"]', prnt).val() || ''
                        },
                        cache:false,
                        dataType:'json',
                        type:'post',
                        success:function(response) {

                            obj.attr('disabled', false).css('color','');
                            obj.trigger('stop');
                            if(response.error) {
                                show_save_alert(response.error);
                            }
                        }
                    }
                );
            }
        });

        $('.tost .hide').bind('click', function(e) {
            $(this).parent().hide();
        });

        $('.add-items').bind('click', function(e) {

            var obj = $(this), prnt = obj.parents('.section-scope'), id = parseInt(prnt.attr('data-id')), paper = $('.paper', prnt);
            var sectCats = $('.section-categories', prnt);

            $('.paper').hide();

            $('.link', paper).removeClass('active');

            if(!$('.cat-item', paper).length) {

                $.ajax(
                    {
                        url:'sections.php',
                        data:{action:'catList'},
                        cache:true,
                        dataType:'json',
                        type:'get',
                        success:function(response) {

                            if(response.error) {
                                show_save_alert(response.error);
                            }
                            else {

                                var el, cat, catNode, sectNode;
                                for(el = 0; el < response.result.length; el++) {

                                    cat = response.result[el];
                                    catNode = $('<p class="link" data-cat-id="'+cat['id']+'"></p>');
                                    catNode.html(cat['p_name']);
                                    catNode.bind('click', function(e) {

                                        var catId = $(this).attr('data-cat-id');
                                        var catMap = $('.sect-item', sectCats).map(function(idn, el) {
                                            return $(el).attr('data-cat-id');
                                        }).get();

                                        if(catMap.indexOf(catId) < 0) {

                                            $(this).addClass('active');
                                            sectNode = $('<span data-cat-id="'+ catId +'" class="sect-item"></span>');
                                            sectNode.html($(this).html()).dynamicSection(
                                                {
                                                    onremove:function() {
                                                        this.remove();
                                                        prnt.trigger('store');
                                                        $('button.action', prnt).css('color', '#f00');
                                                    }
                                                }
                                            );
                                            sectCats.append(sectNode);
                                            prnt.trigger('store');
                                            $('button.action', prnt).css('color', '#f00');
                                        }
                                    });
                                    paper.append(catNode);
                                }
                                paper.show();
                            }
                        }
                    }
                );
            }
            else {
                paper.show();
            }
        });

        $('.section-categories .sect-item').dynamicSection({
            onremove:function() {
                var prnt = this.parents('.section-scope');
                this.remove();
                prnt.trigger('store');
            }
        });
    });
</script>
