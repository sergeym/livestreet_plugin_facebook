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
if (!class_exists('Plugin')) {
    die('Hacking attemp!');
}
class PluginFacebook extends Plugin {
    /**
     * Активация плагина
     * @return boolean
     */
	public function Activate() {
		// Активация
        if (!$this->isTableExists('prefix_plugin_facebook_topic_list') && !$this->isTableExists('prefix_plugin_facebook_settings')) {
			$this->ExportSQL(dirname(__FILE__).'/dump.sql');
		}

        // проверка столбца "статус". Для избежания конфликта с предыдущими версиями
        /*if (!($this->isFieldExists('prefix_plugin_facebook_topic_list','status'))) {
			$this->ExportSQL(dirname(__FILE__).'/status.sql');
		}*/


		return true;
	}

    /**
     * Инициализация плагина
     * @return void
     */
	public function Init() {
        $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath(__CLASS__).'css/index.css');
        $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/facebook.js');
        Config::Set('plugin.facebook.logo_url',Plugin::GetTemplateWebPath(__CLASS__).Config::Get('plugin.facebook.logo_url'));
	}

    /**
     * Деактивация плагина
     * @return boolean
     */
	public function Deactivate() {
		return true;
	}
}