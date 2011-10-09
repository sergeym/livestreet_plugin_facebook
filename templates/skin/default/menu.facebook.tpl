<ul class="menu">
	<li {if $sMenuItemSelect=='facebook'}class="active"{/if}><a href="{router page='facebook'}">Facebook</a>
    {if $sMenuItemSelect=='facebook'}
		<ul class="sub-menu">
            <li {if $sEvent=='index'}class="active"{/if}><div><a href="{router page='facebook'}">{$aLang.about_plugin}</a></div></li>
			<li {if $sEvent=='setup'}class="active"{/if}><div><a href="{router page='facebook'}setup">{$aLang.setup}</a></div></li>
            <li {if $sEvent=='postings'}class="active"{/if}><div><a href="{router page='facebook'}postings">{$aLang.postings}</a></div></li>
			{hook run='menu_facebook_facebook_item'}
		</ul>
    {/if}
	</li>
	{hook run='menu_facebook'}
</ul>