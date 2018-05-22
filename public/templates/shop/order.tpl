{*Smarty*}
{literal}
<style>
table.order {
	border-collapse: collapse;
	border-spacing: 0;
	width:90%;
}
.order td {
	border: 1px solid #444444;
	vertical-align: middle;
	padding: 0.5pt;
}
.m_order {
	font-family: Arial, serif;
	font-size: 10pt;
}
</style>
{/literal}

{if $list}
<div class="m_order">
{if $list.customer}
{foreach from=$list.customer item=customer}
<p>{$customer.label} <b>{$customer.value}</b></p>
{/foreach}
{/if}
<table class="order">
<tr>
<td>наименование</td>
<td>цена</td>
<td>кол-во</td>
<td>сумма</td>
</tr>
{foreach from=$list.order item=val}
<tr>
<td>{$val->item_name}</td>
<td>{if $val->ruprice}{$val->ruprice}{else}{$val->price}{/if}</td>
<td>{$val->quantity}</td>
<td>{if $val->ruprice_sum}{$val->ruprice_sum}{else}{$val->price_sum}{/if}</td>
</tr>
{/foreach}
</table>
<p>Итого: <b>{$list.total}</b></p>
</div>
{/if}
