{strip}
<div class="block" id="block_facebook">

    <img src="{$oConfig->Get('plugin.facebook.logo_url')}" width="100" height="40" alt="Facebook">

    {if $oConfig->Get('plugin.facebook.page.url')}
    
        <ul class="switcher" id="block_facebook_menu">
            {*менюшки можно поменять местами, надо лишь внести изменения в конфигурацию*}
            <li{if $oConfig->GetValue('plugin.facebook.block.active')=='fans'} class="active"{/if}><a href="#" id="menu_facebook_fans" onclick="lsToggleFacebookBlock('facebook_fans'); return false;">{$aLang.friends}</a></li>
            <li{if $oConfig->GetValue('plugin.facebook.block.active')=='recs'} class="active"{/if}><a href="#" id="menu_facebook_recs" onclick="lsToggleFacebookBlock('facebook_recs'); return false;">{$aLang.recommendations}</a></li>
            {hook run='block_facebook_nav_item'}
        </ul>

        <div class="block-content">
            <script language="JavaScript" type="text/javascript">
                $(document).ready(function() {
                    $('#facebook_fans').html('<iframe src="http://www.facebook.com/plugins/likebox.php?href={cfg name="plugin.facebook.page.url"}&amp;width='+$('#facebook_fans').prop('clientWidth')+'&amp;colorscheme=light&amp;connections=15&amp;stream=false&amp;header=false&amp;height=370" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'+$('#facebook_fans').prop('clientWidth')+'px; height:370px;" allowTransparency="true"></iframe>');
                    $('#facebook_recs').html('<iframe src="http://www.facebook.com/plugins/recommendations.php?site={cfg name="plugin.facebook.page.domain"}&amp;width='+$('#facebook_fans').prop('clientWidth')+'&amp;height=370&amp;header=false&amp;colorscheme=light&amp;font=arial&amp;border_color=#aaaaaa" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'+$('#facebook_fans').prop('clientWidth')+'px; height:370px;display:block;"></iframe>');
                    {hook run='block_facebook_script_item'}
                });
            </script>
            <div id="facebook_fans"></div>
            <div id="facebook_recs"></div>
            {hook run='block_facebook_tab_item'}
        </div>

        <div id="plugin_author">
                {if $sAction=='index' or $sEvent=='good'}
                    Plugin Facebook by <a style="color:silver;" href="http://sergeymarin.com#ls-facebook">Sergey Marin</a>
                {else}
                    <noindex>Plugin Facebook by <a style="color:silver;" href="http://sergeymarin.com#ls-facebook" rel="nofollow">Sergey Marin</a></noindex>
                {/if}
                {hook run='block_facebook_copyright_item'}
            </div>

    {else}
        <div class="block-content">
            {$aLang.plugin_is_not_configured}
            {if $oUserCurrent && $oUserCurrent->isAdministrator()}<a href="{router page='facebook'}setup/">{$aLang.run_setup}</a>{/if}
        </div>
    {/if}
</div>
{/strip}