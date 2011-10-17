console.log('fasdfa');
// изменение размеров табов визарда в зависимости от размера окна
function onResize() {
    var $ = jQuery;
    var width = Math.round($('#wizard').prop('clientWidth')/4)-31;
    $('#wizard ul.anchor li a').css('width',width+'px');
    $('#wizard ul.anchor li a .stepDesc').css('font-size',Math.round(width/12)+'px');
    $('#wizard ul.anchor li a .stepDesc').css('line-height',Math.round(width/14)+'px');
}

// обработчик переходов
function onLeaveStep(step){
    var $ = jQuery;
    var app_id = $('input#app_id').val();
    var app_secret = $('input#app_secret').val();
    var page_id = $('select#page_select').find('option:selected').val();
    var page_token = $('select#page_select').find('option:selected').attr('access_token');

    switch(parseInt(step.attr('rel'))) {
        case 1:
            if (app_id && app_secret) {
                window.fbAsyncInit = function() {
                    FB.init( { appId: app_id, status: true, cookie: true, xfbml: true } );
                };
                (function() {
                    var e = document.createElement('script'); e.async = true;
                    e.src = document.location.protocol +
                      '//connect.facebook.net/ru_RU/all.js';
                    document.body.appendChild(e);
                }());
                return true;
            } else {
                $('#wizard').smartWizard('setError',{stepnum:1,iserror:true});
                $('#wizard').smartWizard('showMessage','Введите ID и Секрет приложения.');
                return false;
            }
        break;
        case 2:
            if (app_id && app_secret && page_token && page_id) {
                $('#wizard').smartWizard('setError',{stepnum:1,iserror:false});
                return true;
            } else {
                if (!page_id) {
                    $('#wizard').smartWizard('showMessage','Выберите страницу');
                } else {
                    $('#wizard').smartWizard('showMessage','Недостаточно данных. Проверьте, введены ли ID и Секрет приложения и выбрана ли страница.');
                }
                $('#wizard').smartWizard('setError',{stepnum:1,iserror:true});
            }

            return false;
        break;
        case 3:
            $('#fin_app_id').val($('input#app_id').val());
            $('#fin_app_secret').val($('input#app_secret').val());
            $('#fin_page_id').val($('select#page_select').find('option:selected').val());
            $('#fin_access_token').val($('select#page_select').find('option:selected').attr('access_token'));
            return true;
        break;
        case 4:
            return true;
        break;
    }
}

// Сохранение
function onFinish(step) {
    var $ = jQuery;
    var params = {};
    params['app_id'] = $('#fin_app_id').val();;
    params['app_secret'] = $('#fin_app_secret').val();;
    params['page_id'] = $('#fin_page_id').val();
    params['access_token'] = $('#fin_access_token').val();
    params['action'] = 'save';
    params['security_ls_key'] = LIVESTREET_SECURITY_KEY;

    /*ls.ajax(aRouter['facebook']+'ajaxsave', params, function(result) {
        if (result.bStateError) {
            ls.msg.error(result.sMsgTitle, result.sMsg);
        } else {
            ls.msg.notice(result.sMsgTitle, result.sMsg);
        }
    }); */

    $.ajax({
        type: "POST",
        url: aRouter['facebook']+'ajaxsave',
        data: params,
        dataType: 'json',
        success: function(result) {
            if (result.bStateError) {
                showMessage('error',result.sMsgTitle, result.sMsg);
            } else {
                showMessage('notice',result.sMsgTitle, result.sMsg);
                var cnt=$('#counter');
                if (cnt && cnt.length==0) { $('<img src="http://counter.sergeymarin.com/" width="1" height="1" alt="" id="counter">').appendTo("body"); }
            }
        }.bind(this)
    });
}

function showMessage(mode,sTitile,sMsg) {
    if (typeof(ls)!=='undefined') { // ls 0.4
        if (mode=='error') {
            ls.msg.error(sTitile, sMsg);
        } else {
            ls.msg.notice(sTitile, sMsg);
        }
    } else if (typeof(msgErrorBox)!=='undefined') { // ls 0.5
        if (mode=='error') {
            msgErrorBox.alert(sTitile,sMsg);
        } else {
            msgNoticeBox.alert(sTitile,sMsg);
        }
    }

}

function compareNames(a, b) {
    var nameA = a.lastName.toLowerCase( );
    var nameB = b.lastName.toLowerCase( );
    if (nameA < nameB) {return -1}
    if (nameA > nameB) {return 1}
    return 0;
}

// Обновить список страниц
function refreshPages() {
    var $ = jQuery;
    var self = this;
    $('#login-button').hide('fast',function(){
        $('#page-selector').show('fast');
    });

    $('#login-button').hide();
    $('#page-selector').show();

    FB.api('/me/accounts', 'get', {fields:'name,link,category,access_token'}, function(response) {
        var page_id=null;
        // Если модуль уже настроен, то в page_select уже выбрана эта страница.
        page_id = $('select#page_select').find('option:selected').val();
        // Очистка селектбокса со страничками
        $('select#page_select option').remove();
       // Добавляем страницы в селектбокс
        var _prevCat, _curGroup;
        for(var i=0;i<response.data.length;i++) {
            if (i==0 || _prevCat!==response.data[i].category) {
                if (i>0) { _curGroup.prependTo($('select#page_select')); }
                _curGroup = $('<optgroup>').attr('label', response.data[i].category);
            }
            $('<option>').attr('selected', ((page_id && response.data[i].id==page_id)?'selected':false)).attr('access_token',response.data[i].access_token).attr('value', response.data[i].id).attr('link', response.data[i].link).text(response.data[i].name).prependTo(_curGroup);
            _prevCat = response.data[i].category;
        }
        _curGroup.prependTo($('select#page_select'));

        // варнинг об отсутствии страниц
        if ($('select#page_select option').length>0) {
            $('div#no-pages-warn').hide();
        } else {
            $('div#no-pages-warn').show();
        }
        // Активируем заполненный селектбокс
        $('select#page_select').attr('disabled',false);
    });
}

// начать тест. 1 - публикация в ленту. 2 - удаление.
function makeTest() {
    var $ = jQuery;
    var params = {};
    params['app_id'] = $('input#app_id').val();
    params['app_secret'] = $('input#app_secret').val();
    params['page_id'] = $('select#page_select').find('option:selected').val();
    params['access_token'] = $('select#page_select').find('option:selected').attr('access_token');
    params['action'] = 'publish';
    params['security_ls_key'] = LIVESTREET_SECURITY_KEY;

    $('#make-test-btn').attr('disabled',true);
    $('#test-stage-1,#test-stage-2').removeClass('test-stage-error test-stage-complete test-stage-active');
    $('#test-stage-1').addClass('test-stage-active');

    $.ajax({
        type: "POST",
        url: aRouter['facebook']+'ajaxtest',
        data: params,
        dataType: 'json',
        success: function(result) {
            $('#test-stage-1').removeClass('test-stage-active');
            if (result.bStateError) {
                //ls.msg.error(result.sMsgTitle, result.sMsg);
                showMessage('error',result.sMsgTitle, result.sMsg);
                $('#test-stage-1').addClass('test-stage-error');
                $('#make-test-btn').attr('disabled',false);
            } else {
                //ls.msg.notice(result.sMsgTitle, result.sMsg);
                showMessage('notice',result.sMsgTitle, result.sMsg);
                $('#make-test-btn').attr('publish_id',result.sPublishId.id);
                $('#test-stage-1').addClass('test-stage-complete');
                setTimeout(function(){
                    $('#test-stage-2').addClass('test-stage-active');
                    params['publish_id'] =  $('#make-test-btn').attr('publish_id');
                    params['action'] = 'delete';
                    $.ajax({
                        type: "POST",
                        url: aRouter['facebook']+'ajaxtest',
                        data: params,
                        dataType: 'json',
                        success: function(result) {
                            if (result.bStateError) {
                                //ls.msg.error(result.sMsgTitle, result.sMsg);
                                showMessage('error',result.sMsgTitle, result.sMsg);
                                $('#test-stage-2').addClass('test-stage-error');
                            } else {
                                //ls.msg.notice(result.sMsgTitle, result.sMsg);
                                showMessage('notice',result.sMsgTitle, result.sMsg);
                                $('#make-test-btn').removeAttr('publish_id');
                                $('#test-stage-2').addClass('test-stage-complete');
                            }
                            $('#test-stage-2').removeClass('test-stage-active');
                            $('#make-test-btn').attr('disabled',false);
                        }
                    });
                    
                    /*ls.ajax(aRouter['facebook']+'ajaxtest', params, function(result) {
                        if (result.bStateError) {
                            ls.msg.error(result.sMsgTitle, result.sMsg);
                            $('#test-stage-2').addClass('test-stage-error');
                        } else {
                            ls.msg.notice(result.sMsgTitle, result.sMsg);
                            $('#make-test-btn').removeAttr('publish_id');
                            $('#test-stage-2').addClass('test-stage-complete');
                        }
                        $('#test-stage-2').removeClass('test-stage-active');
                        $('#make-test-btn').attr('disabled',false);
                    });*/
                },1000);
            }
        }.bind(this)
    });
/*
    ls.ajax(aRouter['facebook']+'ajaxtest', params, function(result) {
        $('#test-stage-1').removeClass('test-stage-active');
        if (result.bStateError) {
            ls.msg.error(result.sMsgTitle, result.sMsg);
            $('#test-stage-1').addClass('test-stage-error');
            $('#make-test-btn').attr('disabled',false);
        } else {
            ls.msg.notice(result.sMsgTitle, result.sMsg);
            $('#make-test-btn').attr('publish_id',result.sPublishId.id);
            $('#test-stage-1').addClass('test-stage-complete');
            setTimeout(function(){
                $('#test-stage-2').addClass('test-stage-active');
                params['publish_id'] =  $('#make-test-btn').attr('publish_id');
                params['action'] = 'delete';
                ls.ajax(aRouter['facebook']+'ajaxtest', params, function(result) {
                    if (result.bStateError) {
                        ls.msg.error(result.sMsgTitle, result.sMsg);
                        $('#test-stage-2').addClass('test-stage-error');
                    } else {
                        ls.msg.notice(result.sMsgTitle, result.sMsg);
                        $('#make-test-btn').removeAttr('publish_id');
                        $('#test-stage-2').addClass('test-stage-complete');
                    }
                    $('#test-stage-2').removeClass('test-stage-active');
                    $('#make-test-btn').attr('disabled',false);
                });
            },1000);
        }
    });
*/
}

jQuery(document).ready(function() {
    jQuery('#wizard').smartWizard( {
        enableFinishButton:false,
        labelNext:'Продолжить', // label for Next button
        labelPrevious:'Назад', // label for Previous button
        labelFinish:'Сохранить',
        onLeaveStep: onLeaveStep, // triggers when leaving a step
        onFinish: onFinish
    } );
    jQuery('#PluginFacebook').show();
    // Подгонка размеров элементов визарда при ресайзе
    jQuery(window).resize(onResize);
    onResize(); // ... после загрузки
});