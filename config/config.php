<?php
/*
 * Copyright © 2011 Sergey Marin
 *
 * Плагин Facebook: публикация в ленту страницы (page) и добавление виджетов
 * Автор: Sergey Marin
 * Профиль: http://livestreet.ru/profile/HangGlider/
 * Сайт: http://sergeymarin.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

$config = array();

/*************************
    Стратегии отправки топиков на стену
    Раскомментируйте выбранную строку, что бы выбрать нужную стратегию
*************************/

/*************************
    отправлять когда топик появится на главной
*************************/
#$config['strategy']='STRATEGY_MAIN';

/*************************
    отправлять после получения получения определенного количества голосов
*************************/
$config['strategy']='STRATEGY_RATING';
$config['STRATEGY_RATING']['rating']=0; // необходимое количество голосов. 0 - мгновенный вывод




/************************
    Настройки блока Facebook //
************************/

// Активная закладка
$config['block']['active']='fans';

// Логотип. Путь от TemplateWebPath
$config['logo_url'] = 'images/facebook.jpg';

// Правила вывода виджета
Config::Set('block.rule_facebook',array(
        'path' => array(),
        'action' => array('blog','personal_blog','top','people','my','people','index','stream','tag','facebook'),
        'blocks'  => array(
            'right' => array(
                  'facebook' => array('priority'=>0,'params'=>array('plugin'=>'facebook'))
            )
        ),
        'clear' => false,
));

// Домен виджета рекомендаций (автоматическое определение)
$config['page']['domain']=parse_url(Config::Get('path.root.web'),PHP_URL_HOST);





/************************
    OpenGraph
************************/

// Картинка по умолчанию для OpenGraph тэгов на случай отстутствия media-данных в описании
#$config['default_post_image']='___path.root.web___/path to my image.jpg';
$config['default_post_image']=NULL;





/************************
    Системные
************************/

// Таблица БД для списка опубликованных топиков
$config['db']['table']['plugin_facebook_topic_list'] = '___db.table.prefix___plugin_facebook_topic_list';
$config['db']['table']['plugin_facebook_settings'] = '___db.table.prefix___plugin_facebook_settings';

// Настройка роутера
// подсказка по установке http://sitename.com/facebook/
Config::Set('router.page.facebook', 'PluginFacebook_ActionFacebook');

// Определение подключенного фреймворка
if (substr(LS_VERSION,0,3)=='0.4') {
    $config['js']='mootools';
} else {
    $skin = Config::Get('view.skin');
    switch($skin) {
        case 'new':
            $config['js']='mootools';
        break;
        case 'new-jquery':
        default:
            $config['js']='jquery';
    }
}

return $config;
