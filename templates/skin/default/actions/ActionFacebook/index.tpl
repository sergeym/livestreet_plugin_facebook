{include file='header.light.tpl'}
<script type="text/javascript" src="{cfg name='path.root.engine_lib'}/external/JsHttpRequest/JsHttpRequest.js"></script>
<script type="text/javascript" src="{cfg name='path.root.engine_lib'}/external/MooTools_1.2/mootools-1.2.js?v=1.2.4"></script>

<div id="PluginFacebook">
<h1>Плагин Facebook</h1>
<div class="description">
<h3>Этот плагин обучен:</h3>
<ul class="features">
    <li>Публиковать топики на стену на <a href="http://www.facebook.com/FacebookPages" target="_blank">странице (page)</a> в Facebook</li>
    <li>Добавлять <a href="http://developers.facebook.com/docs/opengraph/" target="_blank">Open Graph</a> теги в заголовок страницы. Поддерживаются видео и картинки, найденные в кратком описании (до ката) топика</li>
    <li>Показывать друзей Вашего сайта</li>
    <li>Показывать рекомендуемые пользователями страницы</li>
</ul>

<div>
    <h3>Поддержите разработчика:</h3>
    <table class="money">
        <tr>
            <td><img src="{$sWebPluginSkin}images/wm.png" width="88" height="31" alt="Яндекс.Деньги" /></td>
            <td><img src="{$sWebPluginSkin}images/ym.png" width="88" height="31" alt="Яндекс.Деньги" /></td>
        </tr>
        <tr>
            <td>
                <dl class="money">
                    <dt>Рубли</dt><dd>R174355371433</dd>
                    <dt>Доллары</dt><dd>Z111983445034</dd>
                    <dt>Евро</dt><dd>E394292522571</dd>
                    <dt>Гривны</dt><dd>U211495792234</dd>
                </dl>
            </td>
            <td>
                <dl class="money">
                    <dt>Кошелек</dt><dd>4100135965614</dd>
                </dl>

                <form action="https://money.yandex.ru/donate.xml" method="post">
                    <input type="hidden" name="to" value="4100135965614"/>
                    <input type="hidden" name="s5" value="pig"/>
                    <input type="submit" value="Вознаградить" />
                </form>                
            </td>
        </tr>
    </table>


</div>

<h3>Настройка плагина</h3>
<div id="setup">
    <fieldset>
        <legend>Приложение</legend>
        <ol>
            <li><a href="http://www.facebook.com/developers/createapp.php" target="_blank">Создайте приложение</a> Facebook и заполните необходимые поля.</li>
            <li>
                <label for="app_id">ID Приложения</label>
                <input id="app_id" name="app_id" type="text" placeholder="Application ID" value="{$pluginCfg.appId}" required="required" autofocus="autofocus">
            </li>
            <li>
                <label for="app_key">Ключ API</label>
                <input id="app_key" name="app_key" type="text" value="{$pluginCfg.appKey}" placeholder="Application API Key" required="required">
            </li>
            <li>
                <label for="app_secret">Секрет приложения</label>
                <input id="app_secret" name="app_secret" type="text" value="{$pluginCfg.appSecret}" placeholder="Application Secret" required="required">
            </li>
            <li>
                <input type="button" value="Запросить список страниц" onclick="update_pages()">
                {if $pluginCfg.appId && $pluginCfg.appKey && $pluginCfg.appSecret}
                    {assign var="bAutoPageLoad" value=true}
                {else}
                    {assign var="bAutoPageLoad" value=false}
                {/if}
                <div id="no-pages-warn">Не удалось найти страницы. Попробуйте <a href="http://www.facebook.com/pages/create.php" target="_blank">создать новую</a>.</div>
            </li>
        </ol>
    </fieldset>

    <fieldset>
        <legend>Страница</legend>
        <ol>
            <li>
                <label for="page_select">Основная страница</label>
                    <select id="page_select" disabled="disabled">
                    {if $pluginCfg.pageId}
                        <option selected="selected" value="{$pluginCfg.pageId}">{$pluginCfg.pageId}</option>
                    {/if}
                    </select>
                    <input id="page_select_rights" type="button" onclick="check_permission()" /><br />
                    <input type="text" id="page_id" name="page_id" value="{$pluginCfg.pageId}" />

                    <div id="page_permissions_warn">
                        У приложения нет прав для создания записей на выбранной странице.<br /><br />
                        <input type="button" id="add_permissions" value="Запросить права" onclick="showPermissionDialog()" />
                    </div>

                    <input type="text" value="" style="width:100%" name="page_url" id="page_url" disabled="disabled" />
            </li>
        </ol>
    </fieldset>

    <fieldset>
        <legend>Проверить и сохранить</legend>
        <ol>
            <li>
            <div id="screen-test">
                Для проверки настроек будет произведена попытка публикации тестового сообщения на стену. После этого, убедитесь, что сообщение было успешно опубликовано и удалите его. Если все этапы прошли успешно, сохраните настройки плагина.<br /><br />
                <input type="button" value="Проверить" onclick="test()" />
            </div>

            <div id="screen-open">
                Ваше сообщение было опубликовано на стене &laquo;<span id="screen-open-page-name"></span>&raquo;. Откройте стену страницы, что бы проверить. После этого, вы сможете удалить тестовое сообщение.<br /><br />
            </div>

            <div id="screen-delete">
                Если сообщение появилось на стене, его необходимо удалить. Сделайте это, что бы перейти к сохранению настроек плагина.<br /><br />
            </div>

            <div id="screen-save">
                <strong>Поздравляем, настройки прошли все проверки!</strong><br /><br />Теперь их можно сохранить. Имейте в виду, что настройки этого плагина будут сохранены в базе данных.
                Значения &laquo;Ключ API&raquo; и &laquo;Секрет приложения&raquo; будут зашифрованы стандартными средствами LiveStreet, с использованием ключа XXTEA шифрования, определенного в конфигурации <span style="color:green;word-wrap:normal;">$config['module']['blog']['encrypt']</span><br /><br />
            </div>

            <div id="screen-save-compete">
                <strong>Ваши настройки были сохранены!</strong><br />
                Помните, разработчик очень радуется материальной поддержке. Если Вам понравился этот плагин, отправить благодарность можно по&nbsp;реквизитам указанным вверху этой страницы.<br /><br />
                <a href="javascript:showTest()" style="border-bottom:double;text-decoration:none;">в начало</a>
            </div>
            </li>
        </ol>
    </fieldset>
</div>

    <div id="fb-root"></div>

<script type="text/javascript">


        {literal}



        function showPermissionDialog() {
            app_id = $('app_id').value;
            page_id = getPageId();

            FB.provide('UIServer.Methods', {
              'permissions.request': {
                size : { width: 575, height: 240 },
                url : 'connect/uiserver.php',
                transform : function(call) {
                    if (call.params.display == 'dialog') {
                      call.params.display = 'iframe';
                      call.params.channel = FB.UIServer._xdChannelHandler(
                          call.id,
                          'parent.parent'
                        );
                      call.params.cancel = FB.UIServer._xdNextHandler(call.cb, call.id, 'parent.parent', true );
                      call.params.next = FB.UIServer._xdResult(call.cb, call.id, 'parent.parent', false );
                    }
                    return call;
                  }
              }
            });

            FB.ui({
                method: 'permissions.request',
                enable_profile_selector: 1,
                profile_selector_ids: page_id,
                perms: 'publish_stream'
            },
            function(response) {
                if (response.perms != null) {
                    check_permission();
                }
            });
        }

        /**
         * Эта функция будет вызвана в случае успешного входа
         */
        function callbackLogin(response) {
            // Запрашиваем информацию о прилжении
            FB.api(
            {
                method: 'application.getPublicInfo',
                application_id: app_id
            }, callbackApplications);
        }

        /**
         * ... будет вызвана, после получения свойств приложения
         * @param response
         */
        function callbackApplications(response) {
            // Выводим ключик в форму
            if (!$('app_key').value !== response.api_key) {
                alert('Ошибка: ключ API указан не верно. Возможно, Вы неверно скопировали ключ.')
            } else {
                // Запрашиваем информацию о страничках юзера
                FB.api(
                {
                    method: 'pages.getinfo',
                    fields: 'page_id,name'
                }, callbackPages);
            }
        }

        /**
         * ... будет вызвана, после получения всех страниц пользователя
         * @param response
         */
        function callbackPages(response) {
            pageId = null; // ID уже выбранной страницы

            if (response.length==0) {
                $('no-pages-warn').show();
            } else {
                $('no-pages-warn').hide();
            }

            // Если модуль уже настроен, то в page_select уже выбрана эта страница.
            optPageSelected = $('page_select').getSelected();
            if (typeof(optPageSelected)=='object' && optPageSelected.length>0 && typeof(optPageSelected[0])!==undefined) {
                pageId = optPageSelected[0].value;
            }

            // Очистка селектбокса со страничками
            $('page_select').innerHTML='';

            // Добавление страниц пользователя в селектбокс
            for(i=0;i<response.length;i++) {
                e = new Element('option', {
                    text: response[i].name,
                    value: response[i].page_id,
                    title: response[i].page_url
                });

                // если добавляемая страница, была ранее выбрана, то делаем её выбранной
                if (e.value==pageId) {
                    e.selected = 'selected';
                }
                // Добавляем страницу в селектбокс
                e.inject($('page_select'));
            }

            // Активируем заполненный селектбокс
            $('page_select').disabled='';

            selectedPage = $('page_select').getSelected();

            if (typeof(selectedPage)=='object' && typeof(selectedPage[0])=='object') {
                $('page_id').value=selectedPage[0].value;
                $('page_select_rights').removeClass('perm').removeClass('noperm').removeClass('refresh').addClass('check');
                check_permission(); // проверка прав на публикацию
            }
        }

        function test() {
            app_id = $('app_id').value;
            app_key = $('app_key').value;
            app_secret = $('app_secret').value;
            page_id = getPageId();

            if (!(app_id && app_key && app_secret && page_id)) {
                alert('Введены не все данные');
                return;
            }
            wait('on');
            JsHttpRequest.query(
                'POST /facebook/ajaxtest',
                {
                    app_id:app_id,
                    app_key:app_key,
                    app_secret:app_secret,
                    page_id:page_id,
                    action:'publish',
                    security_ls_key: {/literal}'{$LIVESTREET_SECURITY_KEY}'{literal}
                },
                function(result, errors) {
                    wait('off');
                    if (!result) {
                        msgErrorBox.alert('Error','Please try again later');
                        return;
                    }
                    if (result.bStateError) {
                        alert(result.sMsgTitle+"\r\n"+result.sMsg);
                    } else {
                        if (result.sPublishId) {
                            el = $('open_button');

                            if (!el) {
                                el = new Element('input', {
                                    type:'button',
                                    value:'Открыть запись',
                                    id:'open_button'
                                });

                                $('screen-open').grab(el);
                            }

                            el.set('onclick', 'openTestRecord(\''+result.aPageInfo.page_url+'\',\''+result.sPublishId+'\')');
                            $('page_url').set('value',result.aPageInfo.page_url);
                            $('screen-test').hide();
                            $('screen-open').show();
                            $('screen-open-page-name').set('text', result.aPageInfo.name);


                        }
                    }
                },
                true
            );

        }

        function openTestRecord(url,sPublishId) {

            window.open(url+'?sk=wall','_blank');

            el = new Element('input', {
                type:'button',
                value:'Удалить запись',
                id:'remove_button',
                onclick:'deleteTestRecord("'+sPublishId+'")'
            });
            $('screen-delete').grab(el);

            $('screen-open').hide();
            $('screen-delete').show();
        }

        function showSaveSettings() {
            // удаляем кнопку "удалить"
            $('remove_button').dispose();

            if ($('go_save_button')) {
                $('go_save_button').dispose();
            }

            el = $('save_settings');

            if (!el) {
                // добавляем кнопку "сохранить настройки"
                el = new Element('input', {
                    type:'button',
                    value:'Сохранить настройки',
                    id:'save_settings',
                    onclick:'saveSettings()'
                });
                $('screen-save').grab(el);
            }

            $('screen-delete').hide();
            $('screen-save').show();
        }

        function deleteTestRecord(publishId) {
            app_id = $('app_id').value;
            app_key = $('app_key').value;
            app_secret = $('app_secret').value;
            page_id = getPageId();

            if (!(app_id && app_key && app_secret && page_id)) {
                alert('Введены не все данные');
                return;
            }

            wait('on');
            JsHttpRequest.query(
                'POST /facebook/ajaxtest',
                {
                    app_id:app_id,
                    app_key:app_key,
                    app_secret:app_secret,
                    page_id:page_id,
                    action:'delete',
                    publish_id:publishId,
                    security_ls_key: {/literal}'{$LIVESTREET_SECURITY_KEY}'{literal}
                },
                function(result, errors) {
                    wait('off');
                    if (!result) {
                        msgErrorBox.alert('Error','Please try again later');
                        return;
                    }

                    if (result.bStateError) {
                        alert(result.sMsgTitle+"\r\n"+result.sMsg);

                        el = $('go_save_button');
                        if (!el) {
                            el = new Element('input', {
                                type:'button',
                                value:'Игнорировать',
                                id:'go_save_button',
                                onclick:'showSaveSettings();'
                            });
                            $('screen-delete').grab(el);
                        }
                    } else {
                        showSaveSettings();
                    }
                },
                true
            );
        }

        function saveSettings(){
            app_id = $('app_id').value;
            app_key = $('app_key').value;
            app_secret = $('app_secret').value;
            page_id = getPageId();

            if (!(app_id && app_key && app_secret && page_id)) {
                alert('Введены не все данные');
                return;
            }

            wait('on');
            JsHttpRequest.query(
                'POST /facebook/ajaxsave',
                {
                    app_id:app_id,
                    app_key:app_key,
                    app_secret:app_secret,
                    page_id:page_id,
                    security_ls_key: {/literal}'{$LIVESTREET_SECURITY_KEY}'{literal}
                },
                function(result, errors) {
                    wait('off');
                    if (!result) {
                        msgErrorBox.alert('Error','Please try again later');
                        return;
                    }

                    if (result.bStateError) {
                        msgErrorBox.alert('Ошибка','Не удалось сохранить запись');
                    } else {
                        $('screen-save').hide();
                        $('screen-save-compete').show();
                    }
                },
                true
            );
        }

        function showTest() {
            $('screen-save-compete').hide();
            $('screen-test').show();
        }

        /**
         * Фукция получает идентификатор приложения, и используя его, авторизует пользователя и получает список его страниц (page).
         */
        function go() {
            login(callbackLogin)
        }

        function update_pages() {
            login(function(response) {
                // если авторизуемся, то запрашиваем список страниц пользователя
                var query = FB.Data.query("SELECT page_id, name, type, page_url FROM page WHERE type <> 'application' AND type <> 'communuty' AND page_id IN (SELECT page_id FROM page_admin WHERE uid = {0})", window.fb_uid);
                query.wait(callbackPages);
                
            });
        }

        /** Авторизация */
        function login(callback) {
            app_id = $('app_id').value;
            if (!app_id) {
                alert('Не введен идентификатор приложения');
                return false;
            }
            // Стандартный процесс авторизации
            window.fbAsyncInit = function() {
                FB.init({appId: app_id, status: true, cookie: true, xfbml: true});

                FB.login(function(response) {
                    if (response.session) {
                        window.fb_uid=response.session.uid;
                        //Залогинились
                        if (response.perms) {
                            // и у нас есть права
                            if (typeof(callback)!==undefined) {
                                callback(response);
                            }
                        } else {
                            alert('Мало прав');
                        }
                    } else {
                        // user is not logged in
                        alert('Надо залогиниться');
                    }
                }, {perms:'manage_pages,publish_stream'});
                ///}, {perms:'publish_stream',enable_profile_selector: 1});
            };
            (function() {
                var e = document.createElement('script'); e.async = true;
                e.src = document.location.protocol +
                  '//connect.facebook.net/ru_RU/all.js';
                document.body.appendChild(e);
            }());
        }

        function check_permission() {
            $('page_select_rights').removeClass('perm').removeClass('noperm').removeClass('check').addClass('refresh');
             var query = FB.Data.query('SELECT uid, publish_stream FROM permissions WHERE uid = {0}', getPageId()); // IN (SELECT page_id FROM page_admin WHERE uid = {0})', app_id);
                query.wait(function(response) {
                    if (typeof(response)!==undefined && typeof(response[0])!==undefined) {
                        data=response[0];
                        if (data.publish_stream==1) {
                            option = $('page_select').getSelected().getLast();
                            $('page_url').set('value',option.get('title'));
                            $('page_permissions_warn').hide();
                            $('page_select_rights').removeClass('refresh').removeClass('noperm').removeClass('check').addClass('perm');
                            return;
                        } else {
                            $('page_permissions_warn').show();
                        }
                    }
                    $('page_select_rights').removeClass('refresh').removeClass('perm').removeClass('check').addClass('noperm');
                });
        }

        function wait(action) {
            waitEl = $('wait');

            if (!waitEl) {
                waitEl=new Element('div', {
                    id: 'wait',
                    style: 'position:absolute;display:hidden;background:url("{/literal}{$sWebPluginSkin}{literal}images/atom.gif") no-repeat 50% 50% #EEEEEE;'
                });
                waitEl.inject(document.body)
            }

            if (action=='on' || action==true) {
                base = $('screen-test').getParent();
                size = base.getSize();
                pos  = base.getPosition();
                waitEl.setPosition({x:pos.x+2, y:pos.y+2});
                waitEl.setStyle('width',size.x-2);
                waitEl.setStyle('height',size.y-2);
                waitEl.show();
            } else if (action=='off' || action==false) {
                waitEl.hide();
            }
        }

        function getPageId() {
            return $('page_id').get('value');
        }

        $('page_select').addEvent('change', function(e){
            val=$('page_select').getSelected().get('value')[0];
            $('page_id').set('value',val);
            check_permission();
        });

        $('page_id').addEvent('keyup', function(e){
            
            page_id=e.target.get('value');

            aOpts=$('page_select').getElements('option');

            bFound=false;
            for(i in aOpts) {
                if (aOpts[i].value==page_id) {
                    aOpts[i].selected = 'selected';
                    bFound=true;
                    if ($('custom-option')) { $('custom-option').dispose(); }
                } else {
                    aOpts[i].selected = '';
                }
            }

            if (!bFound) {
                if ($('custom-option')==null) {
                    e = new Element('option', {
                        id: 'custom-option',
                        value: ''
                    });
                    e.inject($('page_select'));
                }

                $('custom-option').value=page_id;
                $('custom-option').text=page_id;
                $('custom-option').selected='selected';

            }
            
            
            //val=$('page_select').getSelected().get('value')[0];
            //$('page_id').set('value',val);
        });
        
        {/literal}



        {if $bAutoPageLoad==true}
        /** Если форма была заполнена, выполнить автоподгрузку списка страниц */
        //window.addEvent('domready', update_pages);
        {/if}
</script>
        <p id="userName"></p>


{if isset($aPostErrors)}
<ul class="error">
    {foreach item=error from=$aPostErrors}
    <li>{$error}</li>
    {/foreach}
</ul>
{/if}

</div>
{include file='footer.light.tpl'}