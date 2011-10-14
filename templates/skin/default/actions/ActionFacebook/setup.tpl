{include file='header.tpl' noShowSystemMessage=false menu='facebook'}

{strip}
<div id="PluginFacebook" style="display: none;">
    <!-- SmartWizard -->
    <div id="wizard" class="swMain">
        <ul>
            <li><a href="#step-1">
            <label class="stepNumber">1</label>
            <span class="stepDesc">
               {$aLang.application}<br />
               <small>{$aLang.collecting_data}</small>
            </span>
        </a></li>
            <li><a href="#step-2">
            <label class="stepNumber">2</label>
            <span class="stepDesc">
               {$aLang.page}<br />
               <small>{$aLang.for_publishing}</small>
            </span>
        </a></li>
            <li><a href="#step-3">
            <label class="stepNumber">3</label>
            <span class="stepDesc">
               {$aLang.testing}<br />
               <small>{$aLang.of_tokens}</small>
            </span>
         </a></li>
            <li><a href="#step-4">
            <label class="stepNumber">4</label>
            <span class="stepDesc">
               {$aLang.saving}<br />
               <small>{$aLang.to_database}</small>
            </span>
        </a></li>
        </ul>
        <div id="step-1">
        <h2 class="StepTitle">{$aLang.step} 1: {$aLang.facebook_application}</h2>
            <div class="step-data">
            <p>
                {$aLang.create_facebook_app}<br />
                <span class="note">{$aLang.create_facebook_app_note}</span><br />
                <a href="http://php.net/manual/en/book.curl.php" target="_blank">cUrl</a>: {if $bCurlInstalled}{$aLang.installed}{else}{$aLang.not_installed}{/if}<br />
                <a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXml</a>: {if $bSimpleXmlInstalled}{$aLang.installed}{else}{$aLang.not_installed}{/if}
            </p>

            <p>{$aLang.fill_facebook_app_data}</p>
            <p><label>{$aLang.application_id}:</label><input class="wide" id="app_id" name="app_id" type="text" placeholder="{$aLang.application_id}" value="{$pluginCfg.app_id}" required="required" autofocus="autofocus"></p>
            <p><label>{$aLang.application_secret}:</label><input class="wide" id="app_secret" name="app_secret" type="text" placeholder="{$aLang.application_secret}" value="{$pluginCfg.app_secret}" required="required"></p>
            </div>
        </div>
        <div id="step-2">
        <h2 class="StepTitle">{$aLang.step} 2: {$aLang.page}</h2>
            <div class="step-data">
            <p>
                {$aLang.select_facebook_page}<br>
                <span class="note">{$aLang.create_facebook_page}</span>
            </p>
            <p id="login-button">{$aLang.page_list_will_load_automaticly}: <fb:login-button perms="publish_stream,offline_access,manage_pages,user_groups,read_insights" size="medium" onlogin="refreshPages()">Connect</fb:login-button></p>

            <p id="page-selector">
                <select id="page_select" disabled="disabled">
                {if $pluginCfg.pageId}
                    <option selected="selected" access_token={$pluginCfg.access_token} value="{$pluginCfg.pageId}">{$pluginCfg.pageId}</option>
                {/if}
                </select>
            </p>
            </div>
        </div>
        <div id="step-3">
        <h2 class="StepTitle">{$aLang.step} 3: {$aLang.testing}</h2>
              <div class="step-data">
              <p>{$aLang.test_plugin}: <input type="button" id="make-test-btn" value="{$aLang.start_test}" onclick="makeTest()"></p>
              <ul class="test-list">
                <li id="test-stage-1">{$aLang.test_publishing}</li>
                <li id="test-stage-2">{$aLang.test_removing}</li>
              </ul>
              </div>
        </div>
        <div id="step-4">
        <h2 class="StepTitle">{$aLang.step} 4: {$aLang.saving}</h2>
              <div class="step-data">
                  <p>{$aLang.settings_will_be_save_into_database} <span style="color:green;word-wrap:normal;"><nobr>$config['module']['blog']['encrypt']</nobr></span></p>

                  <p><label>{$aLang.application_id}:</label><input class="wide" id="fin_app_id" type="text" value="" disabled="disabled"></p>
                  <p><label>{$aLang.application_secret}:</label><input class="wide" id="fin_app_secret" type="text" value="" disabled="disabled"></p>
                  <p><label>{$aLang.page_id}:</label><input class="wide" id="fin_page_id" type="text" value="" disabled="disabled"></p>
                  <input id="fin_access_token" type="hidden" value="">
              </div>
        </div>
    </div>
    <!-- SmartWizard -->
</div>

<div id="fb-root"></div>
{/strip}

<script type="text/javascript">
    // кэширование картинки лоадера
    heavyImage = new Image();
    heavyImage.src = "{$sTemplateWebPathPlugin}images/loader.gif";
</script>

{include file='footer.tpl'}