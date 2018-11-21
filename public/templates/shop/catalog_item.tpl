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
        	<div class="content">

				{if $head_line}<div class="headline-1 boxed clear">{$head_line}</div>{/if}
				{if $item}
				<div class="clearfix">&nbsp;</div>
				<div class="card" style="float:none;">
					<div class="card-title">
						<p>{$item->item_name}</p>
					</div>
					
					<div class="preview">
						{if $item->photo}<a href="{$big}{$item->photo}" class="highslide"><img src="{$thumbs}{$item->photo}" alt="" /></a>{/if}
						<div class="highslide-caption">{$item->item_name}</div>
					</div>
					
					<div class="spec">
						<div class="details">{$item->short}</div>
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
				{$item->spec}
				{/if}
        		
        	</div>
        	<div class="line_clear">&nbsp;</div>
        </div>
	
	<div class="line_clear">&nbsp;</div>
	</div>
</div>

{*{debug}*}