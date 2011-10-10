
<div class="block" id="block_facebook">

    <div class="tl"><div class="tr"></div></div>
    <div class="cl"><div class="cr">

        <img src="{$oConfig->Get('plugin.facebook.logo_url')}" width="100" height="40" alt="Facebook">

        {if $oConfig->Get('plugin.facebook.page.url')}

            <ul class="block-nav" id="block_facebook_menu">
                <li class="active"><strong></strong><a href="#" id="menu_facebook_fans" onclick="lsToggleFacebookBlock('facebook_fans'); return false;">{$aLang.friends}</a></li>
                <li><a href="#" id="menu_facebook_recs" onclick="lsToggleFacebookBlock('facebook_recs'); return false;">{$aLang.recommendations}</a><em></em></li>
                {hook run='block_facebook_nav_item'}
            </ul>

            <div class="block-content">
                <script language="JavaScript" type="text/javascript">
                {literal} window.addEvent('domready', function() { {/literal}
                    $('facebook_fans').set('html','<iframe src="http://www.facebook.com/plugins/likebox.php?href={cfg name="plugin.facebook.page.url"}&amp;width='+$('facebook_fans').clientWidth+'&amp;colorscheme=light&amp;connections=15&amp;stream=false&amp;header=false&amp;height=370" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'+$('facebook_fans').clientWidth+'px; height:370px;" allowTransparency="true"></iframe>');
                    $('facebook_recs').set('html','<iframe src="http://www.facebook.com/plugins/recommendations.php?site={cfg name="plugin.facebook.page.domain"}&amp;width='+$('facebook_fans').clientWidth+'&amp;height=370&amp;header=false&amp;colorscheme=light&amp;font=arial&amp;border_color=#aaaaaa" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'+$('facebook_fans').clientWidth+'px; height:370px;display:block;"></iframe>');
                {literal} }); {/literal}
                </script>


                <div id="facebook_fans"></div>
                <div id="facebook_recs"></div>

                {hook run='block_facebook_tab_item'}
            </div>

            <div id="plugin_author">
                Plugin Facebook by <a style="color:silver;" href="http://sergeymarin.com#ls-facebook">Sergey Marin</a>
                {hook run='block_facebook_copyright_item'}
            </div>
        {else}
            <div class="block-content">
                {$aLang.plugin_is_not_configured}
                {if $oUserCurrent && $oUserCurrent->isAdministrator()}<a href="{router page='facebook'}setup/">{$aLang.run_setup}</a>{/if}
            </div>
        {/if}

    </div></div>

    <div class="bl"><div class="br"></div></div>

</div>
