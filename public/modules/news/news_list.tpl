{*Smarty*}

{include file="../../$theme_path/basket_script.tpl"}

<div class="fix_main">
    <div class="main">
        <div class="left_block noprint">

            <div id="basket">
                <h4>{if $basket_link}<a href="{$basket_link}" title="Перейти к оформлению">Ваша корзина:</a>{else}Ваша корзина:{/if}</h4>
                <p id="b_q">Выбрано товаров: <b>{$basket_items}</b></p>
                <p id="b_s">На сумму: <b>{$basket_sum}</b></p>
            </div>
            {if $newsSections}
                <div class="boxed clear news-sections">
                    {foreach from=$newsSections item='section'}
                        <div {if $smarty.get.section == $section.id}class="current"{/if} ><a href="{$base_uri}&section={$section.id}">{$section.title}</a></div>
                    {/foreach}
                </div>
            {/if}
            {if $catalog_menu}
                {html_list_top_menu data=$catalog_menu class='arrow cat-menu'}
            {/if}

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
        </div>

        <div class="right_block">
            {if $display_main_picture}
                <div style="position: relative;max-height: 300px;max-width: 705px;margin:auto;height: 300px;">
                    <div class="top_img noprint" id="iSlide">
                        <img src="{$theme_path}/images/top_img.jpg" alt="" title="" />
                        <img src="{$theme_path}/images/first.jpg" alt="British Dragon, Neo Labs" title="Только лучшие производители" />
                        <img src="{$theme_path}/images/second-2.jpg" alt="" title="Гарантия качества" />
                        <img src="{$theme_path}/images/third.jpg" alt="" title="Консультации на форуме" />
                    </div>
                </div>
            {/if}
            <div class="headline-1 boxed clear">{$head_line}</div>

            <div class="newses user boxed clear">

                {if $newses}

                    {*paginate*}
                    {if $paginate}
                        <ul data-before="Страницы" class="paginate boxed clear">
                            {foreach from=$paginate item='rec' key='k'}
                                <li class="boxed ui-corner-all {if $smarty.get.npr == $k}active{/if}"><a href="{$rec.link}">{$rec.name}</a></li>
                            {/foreach}
                        </ul>
                    {/if}
                    {*paginate*}

                    {foreach from=$newses item='single'}
                        <div class="news-item boxed clear">
                            <h2><a href="{$base_uri}&news_item={$single->id}{if $smarty.get.npr>0}&back={$smarty.get.npr}{/if}" title="{$single->title|escape:'html'}">{$single->title}</a></h2>
                            {if $single->announce != ''}
                                <div class="announce-text boxed clear">
                                    <p>{$single->announce}</p>
                                    <a rel="nofollow" class="more clear" href="{$base_uri}&news_item={$single->id}{if $smarty.get.npr>0}&back={$smarty.get.npr}{/if}">Далее&nbsp;&raquo;</a>
                                </div>
                            {/if}
                        </div>

                    {/foreach}

                    {*paginate*}
                    {if $paginate}
                        <div class="clear"><br clear="all" /></div>
                        <ul data-before="Страницы" class="paginate boxed clear">
                            {foreach from=$paginate item='rec' key='k'}
                                <li class="boxed ui-corner-all {if $smarty.get.npr == $k}active{/if}"><a href="{$rec.link}"><a href="{$rec.link}">{$rec.name}</a></li>
                            {/foreach}
                        </ul>
                    {/if}
                    {*paginate*}

                {/if}

            </div>

            <div class="line_clear">&nbsp;</div>

        </div>

        <div class="line_clear">&nbsp;</div>
    </div>


</div>

{*{debug}*}