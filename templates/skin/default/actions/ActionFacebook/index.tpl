{include file='header.tpl' noShowSystemMessage=false menu='facebook'}
{strip}
<div id="PluginFacebook">
    <h1>{$aLang.plugin_facebook}</h1>

    <ul class="features">
        <li>{$aLang.publishing_on_wall}</li>
        <li>{$aLang.open_graph_tags}</li>
        <li>{$aLang.widget_friends}</li>
        <li>{$aLang.widget_recommendations}</li>
    </ul>

    <h2>{$aLang.terms_of_use}</h2>
    <p>{$aLang.project_licensed_with_GPL2}</p>
    <p>{$aLang.additional_terms_of_usage}</p>

    <h2>{$aLang.about_author}</h2>
    <ul class="features">
        <li><a href="http://livestreet.ru/profile/hangglider/">{$aLang.livestreet_ru_profile_page}</a></li>
        <li><a href="http://livestreetcms.com/profile/hangglider/addons/">{$aLang.my_plugins}</a></li>
    </ul>
</div>
{/strip}
{include file='footer.tpl'}