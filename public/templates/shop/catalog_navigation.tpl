{if $sections_navigation}
	{if $sections_navigation.grouped}
		{foreach from=$sections_navigation.grouped item=sectGroup}
			<div class="boxed clear section-item {if $sectGroup.expanded}expanded{/if}">
				<h3>{$sectGroup.section}</h3>
				<ul class="arrow cat-menu">
					{foreach from=$sectGroup.items item=sectionCategory}
						<li class="{if $sectionCategory.current}current{/if}"><a href="/?{$sectionCategory.link}&smi={$sectGroup.id}">{$sectionCategory.p_name}</a></li>
					{/foreach}
				</ul>
			</div>
		{/foreach}
	{/if}
	{if $sections_navigation.alone}
		<ul class="arrow cat-menu">
			{foreach from=$sections_navigation.alone item=sectionCategory}
				<li class="out-of-section {if $sectionCategory.current}current{/if}"><a href="/?{$sectionCategory.link}">{$sectionCategory.p_name}</a></li>
			{/foreach}
		</ul>
	{/if}
{/if}
{literal}
	<script>
        jQuery(document).ready(function($) {

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