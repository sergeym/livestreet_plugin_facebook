{strip}
{if count($aTopics)>0}
	{foreach from=$aTopics item=oFacebookTopic}
       {assign var="oTopic" value=$oFacebookTopic->getTopic()}
       {assign var="oUser" value=$oTopic->getUser()}
        <div class="topic">
            <h1 class="title">
                    <a href="{$oTopic->getUrl()}" class="title-topic">{$oTopic->getTitle()|escape:'html'}</a>
            </h1>

            <a href="#" onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');" class="favourite {if $oUserCurrent && $oTopic->getIsFavourite()}active{/if}"></a>

            <ul class="info">
                <li class="date">{date_format date=$oTopic->getDateAdd()}</li>
                <li class="username"><a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a></li>
                <li>&rArr;</li>
                <li class="date facebook_status_{$oFacebookTopic->getStatus()}">{if $oFacebookTopic->getStatus()=='published'}<a href="{$oFacebookTopic->GetFacebookLink()}">Опубликовано</a>{elseif $oFacebookTopic->getStatus()=='blocked'}Заблокированно{/if}, {date_format date=$oFacebookTopic->getDate()}</li>
            </ul>

        </div>
	{/foreach}
    {include file='paging.tpl' aPaging="$aPaging"}
{else}
	<div class="padding">{$aLang.blog_no_topic}</div>
{/if}
{/strip}