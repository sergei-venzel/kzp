{*Smarty*}


<div class="fix_main">
       
    <div class="main">
        <div class="left_block noprint">
        
            {if $sub_pages}
            {html_list data=$sub_pages class="arrow"}
            {/if}

            {include file='catalog_navigation.tpl'}
            
            {if $left_zone}
			<br clear="all" />
			<div class="u-left-zone">{$left_zone}</div>
			{/if}
            
            {*{if $price_list}*}
            {*<h2>Прайс-лист</h2>*}
                {*{foreach from=$price_list item=val}*}
                {*<a href="{$val->im_src}" class="price">Скачать*}
                    {*{if ($val->last_time_mod && $val->size)}({$val->last_time_mod}, {$val->size})*}
                    {*{else}*}
                        {*{if ($val->last_time_mod)}({$val->last_time_mod}){/if}*}
                        {*{if ($val->size)}({$val->size}){/if}*}
                    {*{/if}*}
                {*</a>*}
                {*{/foreach}*}
            {*{/if}*}
            
            
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
            <!--<div class="top_img noprint">
                <img src="{$theme_path}/images/top_img.jpg" alt="" title="" />
            </div>-->
            {if $bradcrumb}
            {html_list data=$bradcrumb class="bradcrumb"}
            {/if}
            
            <div class="headline-1 boxed clear">{$head_line}</div>
                
                <div class="content">
                    {$page_content}
                </div>
            
            <div class="line_clear">&nbsp;</div>
                         
        </div>
        
        <div class="line_clear">&nbsp;</div>
    </div>


</div>

{*{debug}*}