<div class="block block-type-facebook" id="block_facebook">

    {if $oConfig->Get('plugin.facebook.page.url')}

        <header class="block-header sep">

            <img src="{$oConfig->Get('plugin.facebook.logo_url')}" width="100" height="40" alt="Facebook">

            <ul class="nav nav-pills">
                <li class="{if $oConfig->GetValue('plugin.facebook.block.active')=='fans'}active {/if}js-block-facebook-item" data-type="fans"><a href="#" id="menu_facebook_fans">{$aLang.plugin.facebook.friends}</a></li>
                <li class="{if $oConfig->GetValue('plugin.facebook.block.active')=='recs'}active {/if}js-block-facebook-item" data-type="recs"><a href="#" id="menu_facebook_recs">{$aLang.plugin.facebook.recommendations}</a></li>
                {hook run='block_facebook_nav_item'}
            </ul>
        </header>

        <script language="JavaScript" type="text/javascript">
            $(function() {
                $('#facebook_fans').html('<iframe src="http://www.facebook.com/plugins/likebox.php?href={cfg name="plugin.facebook.page.url"}&amp;width='+$('#facebook_fans').prop('clientWidth')+'&amp;colorscheme=light&amp;connections=15&amp;stream=false&amp;header=false&amp;height=370" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'+$('#facebook_fans').prop('clientWidth')+'px; height:370px;" allowTransparency="true"></iframe>');
                $('#facebook_recs').html('<iframe src="http://www.facebook.com/plugins/recommendations.php?site={cfg name="plugin.facebook.page.domain"}&amp;width='+$('#facebook_fans').prop('clientWidth')+'&amp;height=370&amp;header=false&amp;colorscheme=light&amp;font=arial&amp;border_color=#aaaaaa" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'+$('#facebook_fans').prop('clientWidth')+'px; height:370px;display:block;"></iframe>');
                {hook run='block_facebook_script_item'}
                ls.blocks.initSwitch('facebook');
            });
        </script>

        <div class="block-content">
            <div class="js-block-facebook-content" data-type="fans" id="facebook_fans"></div>
            <div class="js-block-facebook-content" data-type="recs" id="facebook_recs" style="display: none;"></div>
        </div>
    {else}
        <div class="block-content">
            {$aLang.plugin.facebook.plugin_is_not_configured}
            {if $oUserCurrent && $oUserCurrent->isAdministrator()}<a href="{router page='facebook'}setup/">{$aLang.plugin.facebook.run_setup}</a>{/if}
        </div>
    {/if}
    <footer id="plugin_author">
        {if $sAction=='index' or $sEvent=='good'}
            <a href="http://facebook-for-livestreet.ru/">Plugin Facebook</a> by <a style="color:silver;" href="http://sergeymarin.com#ls-facebook">Sergey Marin</a>
        {else}
            <a href="#" onclick="document.location.href='http://facebook-for-livestreet.ru/'">Plugin Facebook</a> by <a style="color:silver;" href="#" onclick="document.location.href='http://sergeymarin.com#ls-facebook'">Sergey Marin</a>
        {/if}
        {hook run='block_facebook_copyright_item'}
    </footer>
</div>
