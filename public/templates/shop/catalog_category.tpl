{*Smarty*}

{include file='basket_script.tpl'}

<div class="fix_main">
       
    <div class="main">
        <div class="left_block noprint">
        	
        	<div id="basket">
				<h4>{if $basket_link}<a href="{$basket_link}" title="Перейти к оформлению">Ваша корзина:</a>{else}Ваша корзина:{/if}</h4>
				<p id="b_q">Выбрано товаров: <b>{$basket_items}</b></p>
				<p id="b_s">На сумму: <b>{$basket_sum}</b></p>
			</div>

	        {include file='catalog_navigation.tpl'}

            {*{if $catalog_menu}
			{html_list_top_menu data=$catalog_menu class='arrow cat-menu'}
			{/if}*}
			
			{if $left_zone}
			<br clear="all" />
			<div class="u-left-zone">{$left_zone}</div>
			{/if}
            
            {if $price_list}
            <h2>Прайс-лист</h2>
                {foreach from=$price_list item=val}
                <a href="{$val->im_src}" class="price">Скачать
                    {if ($val->last_time_mod && $val->size)}({$val->last_time_mod}, {$val->size})
                    {else}
                        {if ($val->last_time_mod)}({$val->last_time_mod}){/if}
                        {if ($val->size)}({$val->size}){/if}
                    {/if}
                </a>
                {/foreach}
            {/if}
            
            
            {if $announce_news}
            <h2>Новости</h2>
            <ul class="arrow announce">
            {foreach from=$announce_news item=val} 
                {if $val->src}                   
                    <li><a href="{$val->src}"><span class="date">{$val->date}</span></a>
                    {if $val->title}
                        <a href="{$val->src}">{$val->title}</a>
                    {else}
                        <a href="{$val->src}">{$val->text}</a>
                    {/if}
                    </li>
                {/if}
            {/foreach}
            </ul>
            <a class="all" href="{$announce_news.all_news}">Все {$announce_news.pname}</a>
            {/if}
        
            &nbsp;
        </div>
        
        <div class="right_block">
			{if $display_main_picture}
                <div class="img-gallery boxed">
                    <div class="top_img noprint" id="iSlide">
                        <img src="{$theme_path}/images/top_img.jpg" alt="" title="" />
                        <img src="{$theme_path}/images/first.jpg" alt="British Dragon, Neo Labs" title="Только лучшие производители" />
                        <img src="{$theme_path}/images/second.jpg?v=2" alt="" title="Гарантия качества" />
                        <img src="{$theme_path}/images/third.jpg" alt="" title="Консультации на форуме" />
                    </div>
                </div>
			{/if}
            {if $head_line}<div class="headline-1 boxed clear">{$head_line}</div>{/if}
                
                <div class="content catalog-page">
						
						{$page_content}
						
						{if $showcase}
						<div class="headline-2 boxed clear">Популярные товары</div>
						
						<div class="showcase boxed clear">
						{foreach from=$showcase item='trow'}
							{foreach from=$trow item='record'}
							<div class="showcase-item boxed corner-all">
								{if $record}
								<p><a rel="nofollow" href="/?set={$catalog_page}&gallery={$record->cat_id}&item={$record->id}"><b>{$record->item_name}</b></a></p>
								{if $record->photo != ''}
								<p><a rel="nofollow" href="/?set={$catalog_page}&gallery={$record->cat_id}&item={$record->id}"><img src="{$std}{$record->cat_id}{$th}{$record->photo}" alt="" title="{$record->item_name|escape:'html'}" /></a></p>
								{/if}
								{$record->short}
									<p class="cost {if $record->discount > 0}discount{/if}">Цена:

										{if $record->ruprice}
											<br /><b>RUR</b>:&nbsp;<span class="real">{$record->ruprice}</span>
										{else}
											<br /><b>USD</b>:&nbsp;<span class="real">{$record->price}</span>
										{/if}

										{if $record->discount > 0}
											{if $record->rudiscount}<span class="sale">{$record->rudiscount}</span>{else}{$record->discount}{/if}
										{/if}
									</p>
								{else}&nbsp;{/if}
							</div>
							{/foreach}
						{/foreach}
						</div>
						
						{/if}
						
						{if $items_list}

						{if $pagination}
						<div class="page-list">
							Страницы:{foreach from=$pagination item=page_num} [{if $page_num==$page_show}&nbsp;{$page_num}&nbsp;{else}<a href="/?set={$smarty.get.set}&gallery={$smarty.get.gallery}&page={$page_num}">&nbsp;{$page_num}&nbsp;</a>{/if}]{/foreach}
						</div>
						{/if}

						<div class="clearfix">&nbsp;</div>
						{foreach from=$items_list item=item}
						<div class="card">
							<div class="card-title">
								<p><a href="{$item->link}{if $smarty.get.smi}&smi={$smarty.get.smi}{/if}" title="подробнее...">{$item->item_name}</a></p>
							</div>
							
							<div class="preview">
								{if $item->photo}
								<a href="{$big}{$item->photo}" class="highslide"><img src="{$thumbs}{$item->photo}" alt="" /></a>
								<div class="highslide-caption">{$item->item_name}</div>
								{/if}
							</div>
							
							<div class="spec">
								<div class="details"><a rel="nofollow" href="{$item->link}" title="подробнее...">{$item->short}</a></div>
								<div class="cost {if $item->discount > 0}discount{/if}">
									{if $item->price != 0}
									Цена:
									{if $item->ruprice}
									<br /><b>RUR</b>:&nbsp;<span class="real">{$item->ruprice}</span>
                                        {else}
                                    <br /><b>USD</b>:&nbsp;<span class="real">{$item->price}</span>
									{/if}
										{if $item->discount > 0}
											{if $item->rudiscount}<span class="sale">{$item->rudiscount}</span>{else}{$item->discount}{/if}
										{/if}
                                    {/if}
								</div>
								{if $item->price != 0 AND $basket_link}<div class="inbasket" id="com_{$item->id}" title="Добавить в корзину">купить</div>{/if}
							</div>
						</div>
						{/foreach}
						<div class="clearfix">&nbsp;</div>
						{if $pagination}
						<div class="page-list">
							Страницы:{foreach from=$pagination item=page_num} [{if $page_num==$page_show}&nbsp;{$page_num}&nbsp;{else}<a href="/?set={$smarty.get.set}&gallery={$smarty.get.gallery}&page={$page_num}">&nbsp;{$page_num}&nbsp;</a>{/if}]{/foreach}
						</div>
						{/if}

						{/if}

					{if $category_content}{$category_content}{/if}
                </div>
            
            <div class="line_clear">&nbsp;</div>
                         
        </div>
        
        <div class="line_clear">&nbsp;</div>
    </div>


</div>
{literal}
<script>
	jQuery(document).ready(function($) {
        $('#iSlide').cycle({
            fx:        'fade'
//            direction: 'left' // one of up|down|left|right  default=left
//            delay:    -2000
        });

        $('.section-item').on('click', function(e) {

            if($(this).hasClass('expanded')) {
                $(this).removeClass('expanded');
            }
            else {
                $('.section-item').removeClass('expanded');
                $(this).addClass('expanded');
            }
        });
    });
</script>
{/literal}
{*{debug}*}