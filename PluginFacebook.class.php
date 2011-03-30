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
		if (!($this->isTableExists('sm_plugin_facebook_topic_list') && $this->isTableExists('prefix_plugin_facebook_settings'))) {
			/**
			 * При активации выполняем SQL дамп
			 */
			$this->ExportSQL(dirname(__FILE__).'/dump.sql');
		}
		return true;
	}

    /**
     * Инициализация плагина
     * @return void
     */
	public function Init() {
        $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath(__CLASS__).'css/index.css');
        $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/facebook.js');
        $this->Viewer_Assign('sFacebookTemplateWebPathPlugin',Plugin::GetTemplateWebPath(__CLASS__));
	}

    /**
     * Деактивация плагина
     * @return boolean
     */
	public function Deactivate() {
		return true;
	}
}