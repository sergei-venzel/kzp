{*Smarty*}
<script language="JavaScript">
var ajs = '/?set={$smarty.get.set}';
</script>
{literal}
<script language="JavaScript">
try {
	$.getScript('/js/site/nav.js');
	$.getScript('/js/site/basket.js');
}
catch(e) {
	
}
</script>
{/literal}

<div class="fix_main">
       
    <div class="main">
        <div class="left_block noprint">
        
            {if $catalog_menu}
			{html_list_top_menu data=$catalog_menu class='arrow cat-menu'}
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

			<div class="headline-1 boxed clear">{$head_line}</div>
                
                <div class="content">

<div class="basket-page">
	
	{if $items}
	<form action="/?set={$smarty.get.set}" method="post" name="basket" id="basket_order">
		
		<div id="elements">
			<div style="float:left;width:100%;margin-bottom:10px;">{$page_content}</div>
			{foreach from=$items item=product key=ind}
			<div id="item" class="i_{$product->id}">
				<span class="remove" title="Удалить из корзины" id="product_{$product->id}">&nbsp;</span>
				<input type="hidden" name="prod_id[]" value="{$product->id}" />
				<div class="card">
					<div class="card-title">
						<p>{$product->item_name}</p>
					</div>
					
					<div class="preview">
						<img src="{$product->cat_id|string_format:$thumbs}{$product->photo}" alt="" />
					</div>
					
					<div class="spec">
						<div class="details">{$product->short}</div>
						<div class="cost {if $product->discount > 0}discount{/if}">Цена:<span class="real">
                                {if $product->ruprice}
                                    {$product->ruprice}
                                {else}
                                    ${$product->price}
                                {/if}
                                </span>
							{if $product->discount > 0}
								{if $product->rudiscount}<span class="sale">{$product->rudiscount}</span>{else}{$product->discount}{/if}
							{/if}
                        </div>
					</div>
				</div>
				
				<div class="quantity">
					<p class="lbl">Количество</p>
					<input type="text" name="q[]" value="{$product->quantity}" />
				</div>
				<div class="sum">
					<p class="lbl">Сумма</p>
					<span>{if $product->ruprice_sum}
                        RUR <b>{$product->ruprice_sum}</b>
                        {else}
                        $ <b>{$product->price_sum}</b>
                    {/if}</span>
				</div>
			</div>
			{/foreach}
			
			
			<div class="recalc">
				<input type="submit" id="recalc" name="recalc" value="" />
				<p>Итого:
				{if $ru_sum}
					<span id="final_ru">{$basket_sum}</span>&nbsp;<small>RUR</small>
					{else}
					<span id="final">{$basket_sum}</span>&nbsp;<small>USD</small>
				{/if}
				</p>
			</div>
			
			<div class="submit">
				<label for="nm" style="clear: both;">Контактное лицо</label>
				<input type="text" id="nm" name="nm" class="require" />
				
				<label for="umail">Контактный Email<br />
				<span style="color:red">Если у вас почтовый ящик на <b>MAIL.RU</b>, возможна "недоставка письма".<br />В этом случае письмо с подробностями заказа будет отправлено вам непосредственно администратором сайта.</span>
				</label>
				<input type="text" id="umail" name="umail" class="require" />
				
				<img id="captcha" src="/vc/CaptchaSecurityImages.php?v={$img}" />
				<label for="cptch">Код на картинке</label>
				<input type="text" name="s_code" id="cptch" value="" class="require" />
				
				<label for="comm">Укажите пожалуйста:<br/>Ф.И.О., Адрес, Индекс, Способ оплаты (WebMoney, Qiwi, Яндекс)</label>
				<textarea id="comm" name="comm"></textarea>
				<div class="o_mess">&nbsp;</div>
				<input class="order" type="submit" name="order" value="" id="submt" />
			</div>
			
		</div>
		
	</form>
	<div style="clear:left;float:left;width:100%;display:none;font-size:12pt;color:red;" id="guidemess">{$guidemess}</div>
	{else}
	<div id="elements"><div class="empty_mess"><p>Ваша корзина пуста.</p>
	<p>Для выбора товара, перейдите в каталог товаров.</p></div></div>
	{/if}

</div>

				</div>
            
            <div class="line_clear">&nbsp;</div>
                         
        </div>
        
        <div class="line_clear">&nbsp;</div>
    </div>


</div>

{*{debug}*}