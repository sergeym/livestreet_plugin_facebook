<div style="border-top:1px dotted navy;border-bottom:1px dotted navy;padding:5px 0; margin:10px 0;">
<img src="{$oConfig->Get('plugin.facebook.logo_url')}" alt="Facebook" align="right">
{if $bEditMode && $bPublished}
    {* сюда попадают при редактировании уже опубликованного в FB топика *}
    <p><strong>Этот топик опубликован в Facebook</strong></p>
    {if $oUserCurrent and $oUserCurrent->isAdministrator()}
        <p><label for="topic_delete_facebook"><input type="checkbox" value="1" class="checkbox" name="topic_delete_facebook" id="topic_delete_facebook"> &mdash; удалить из Facebook</label><br>
        <span class="form_note">Если отметить эту галку, то анонс топика будет удален со страницы в Facebook (опция доступна только администраторам)</span></p>
    {/if}
{elseif !$bPublishBlocked}
    {* сюда попадают при редактировании неопубликованного в FB топика, который не заблокирован от публикации *}
    {if $oUserCurrent and $oUserCurrent->isAdministrator()}
    <p><label for="topic_publish_facebook"><input type="checkbox" value="1" class="checkbox" name="topic_publish_facebook" id="topic_publish_facebook"> &mdash; опубликовать в Facebook</label><br>
    <span class="form_note">Если отметить эту галку, то анонс топика будет опубликован в Facebook (опция доступна только администраторам)</span></p>
    {else}
        {* сообщение для обычных пользователей *}
        {if $aPluginConfig.strategy=='STRATEGY_MAIN'}
            <p>Если этот топик попадет на главную страницу, анонс на него будет добавлен на <a href="{$aPluginConfig.page.url}" target="_blank">страничку нашего сайта в Facebook</a></p>
        {elseif $aPluginConfig.strategy=='STRATEGY_RATING'}
            <p>Если за этот топик {$aPluginConfig.STRATEGY_RATING.rating|declension:'проголосует;проголосуют;проголосуют':'ru'} {$aPluginConfig.STRATEGY_RATING.rating} {$aPluginConfig.STRATEGY_RATING.rating|declension:'человек;человека;человек':'ru'}, анос на него будет добавлен на <a href="{$aPluginConfig.page.url}" target="_blank">страничку нашего сайта в Facebook</a></p>
        {/if}
    {/if}
{/if}

{if !$bPublished && $oUserCurrent and $oUserCurrent->isAdministrator()}
    {if $bPublishBlocked}
    <p><label for="topic_allow_facebook"><input type="checkbox" value="1" class="checkbox" name="topic_allow_facebook" id="topic_allow_facebook"> &mdash; разрешить добавление в Facebook</label><br>
    <span class="form_note">Если отметить эту галку, то анонс топика будет опубликован в Facebook при выполнении условий добавления (опция доступна только администраторам)</span></p>
    {else}
    <p><label for="topic_deny_facebook"><input type="checkbox" value="1" class="checkbox" name="topic_deny_facebook" id="topic_deny_facebook"> &mdash; заблокировать добавление в Facebook</label><br>
    <span class="form_note">Если отметить эту галку, то анонс топика не попадет в Facebook при выполнении условий добавления (опция доступна только администраторам)</span></p>
    {/if}
{/if}
</div>