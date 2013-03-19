<li {if $sEvent=='index'}class="active"{/if}><a href="{router page='facebook'}">{$aLang.plugin.facebook.about_plugin}</a><i></i></li>
<li {if $sEvent=='setup'}class="active"{/if}><a href="{router page='facebook'}setup">{$aLang.plugin.facebook.setup}</a><i></i></li>
<li {if $sEvent=='postings'}class="active"{/if}><a href="{router page='facebook'}postings">{$aLang.plugin.facebook.postings}</a><i></i></li>
{hook run='menu_facebook_facebook_item'}

