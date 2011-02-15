{include file='header.light.tpl'}

<div id="PluginFacebook">
<h1>Настройка плагина Facebook</h1>
<div class="description">
<h3>Этот плагин обучен:</h3>
<ul class="features">
    <li>Публиковать топики на стену на <a href="http://www.facebook.com/FacebookPages">странице (page)</a> в Facebook</li>
    <li>Добавлять OpenGraph теги в заголовок страницы. Поддерживаются видео и картинки, найденные в кратком описании (до ката) топика</li>
    <li>Показывать список людей "залайкавших" страницу вашего сайта</li>
    <li>Показывать список страниц, которые кто-то "залайкал"</li>
</ul>
</div>

<div class="setup_block">
<ol>
    <li{if $pluginCfg.page.id} class="complete"{/if}>
        <strong>Создайте страницу сайта в Facebook</strong>
        Это можно сделать <a href="http://www.facebook.com/pages/create.php">на этой странице</a>. На этом этапе, вам нужно найти Page ID. Перейдите в режим редактирования страницы. В URL, параметр id будет этим идентификатором. Его необходимо записать в файл конфигурации плагина.
    </li>
    <li{if $pluginCfg.application.id && $pluginCfg.application.api && $pluginCfg.application.secret} class="complete"{/if}>
        <strong>Создайте приложение Facebook</strong>
        С помощью этого приложения будут публиковаться топики на стену созданной страницы. Создать приложение можно <a href="http://www.facebook.com/developers/createapp.php">на этой странице</a>. Полученные Application ID, Ключ API, Application Secret необходимо записать в файл конфигурации плагина.</li>
    <li{if $facebookRightsOK} class="complete"{/if}>
        <strong>Дайте приложению необходимые права</strong>
        <a href="https://graph.facebook.com/oauth/authorize?client_id=191123180907739&redirect_uri=http://www.facebook.com/connect/login_success.html&scope=read_stream,publish_stream,offline_access">На этой странице</a> и <a href="http://www.facebook.com/connect/prompt_permissions.php?api_key={cfg name='plugin.facebook.application.api'}&v=1.0&next=http://www.facebook.com/connect/login_success.html?xxRESULTTOKENxx&display=popup&ext_perm=publish_stream&enable_profile_selector=1&profile_selector_ids={cfg name='plugin.facebook.page.id'}">на этой странице</a>.
    </li>
    <li><strong>Настройте дополнительные параметры</strong>
        <ul>
            <li>Выберите стратегию публикации</li>
            <li>Укажите путь к картинке по умолчанию для заголовка OpenGraph</li>
            <li>Укажите путь к странице сайта на Facebook в настройке </li>
        </ul>
    </li>
</ol>
{if $facebookRightsOK}
<div>Плагин настроен и работает нормально. Попробуйте отправить сообщение на стену страницы (необходим хотя бы один топик на главной странице).</div>
<form action="./" method="post">
<input type="hidden" name="make_test" value="1">
<input type="submit" value="Отправить">
</form>
{/if}
</div>

<div id="adv">Жертвы и благодарности принимаются по кошелькам: R174355371433, Z111983445034, E394292522571</div>

</div>
{include file='footer.light.tpl'}