<?php
/*
 * Copyright © 2010 Sergey Marin
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

// Стратегии отправки топиков на стену //
// Раскомментируйте выбранную строку, что бы выбрать нужную стратегию //

// отправлять когда топик появится на главной
$config['strategy']='STRATEGY_MAIN';

// отправлять после получения получения определенного количества голосов
#$config['strategy']='STRATEGY_RATING';
#$config['STRATEGY_RATING']['rating']=5; // необходимое количество голосов

// Параметры для Facebook //

// Страница
$config['page']['id']=NULL;
$config['page']['url']=urlencode(NULL);

// Приложение
$config['application']['id']=NULL;
$config['application']['api']=NULL;
$config['application']['secret']=NULL;

// Настройки блока Facebook //

// Приоритет 0-в самый низ
$config['facebook_block_priority'] = 0;

// Картинка по умолчанию для OpenGraph тэгов на случай отстутствия media-данных в описании
#$config['default_post_image']='___path.root.web___/path to my image.jpg';
$config['default_post_image']=NULL;
// Домен для виджета рекомендаций (на автомате)
$config['page']['domain']=parse_url(Config::Get('path.root.web'),PHP_URL_HOST);

// Таблица БД для списка опубликованных топиков
$config['db']['table']['plugin_facebook_topic_list'] = '___db.table.prefix___plugin_facebook_topic_list';

// Настройка роутера //
// подсказка по установке http://sitename.com/facebook/
Config::Set('router.page.facebook', 'PluginFacebook_ActionFacebook');

return $config;