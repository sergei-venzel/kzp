<?php
/**
 * list.php
 * Date: 16.03.17
 *
 * @var array $items
 * @link http://kz.loc/?set=4&gallery=2&item=5
 */
$stop = '';
?>
<div class="boxed clearfix" id="">
    <?php foreach($items as $galleryId => $category):?>
        <div>
            <div><small>категория: </small><strong><?php echo $category['name'];?></strong></div>
        <?php foreach($category['products'] as $row):?>
        <p class="active link" data-link="gallery=<?php echo $galleryId;?>&item=<?php echo $row['id'];?>" data-name="<?php echo htmlspecialchars($row['name']);?>"><?php echo $row['name'];?></p>
        <?php endforeach;?>
        </div>
    <?php endforeach;?>
</div>
<script>
    jQuery(document).ready(function($) {

        $('.active.link', '#display-list').bind('click', function() {

            var obj = $(this), prnt = obj.parents('.tost'), mother = prnt.parents('td');
            var iFld = $('input[name="c_links"]', mother),
                nFld = $('input[name="c_gal"]', mother),
                dsp = $('.fake-link', mother);
            var extName = obj.attr('data-name'), extLink = obj.attr('data-link');
            iFld.val(extLink);
            nFld.val(extName);
            dsp.attr('data-label', extName);
            prnt.empty();
        });
    });
</script>
