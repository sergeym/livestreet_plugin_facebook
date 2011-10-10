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
            // Первая установка
			$this->ExportSQL(dirname(__FILE__).'/dump.sql');
		} elseif (!$this->isTableExists('prefix_plugin_facebook_settings')) {
            // Обновление с версии 0.1
            $this->ExportSQL(dirname(__FILE__).'/dump_01_05.sql');
        } else {
            // Обновление с версии 0.3
            $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), 'prefix_plugin_facebook_settings');
            $sQuery="SHOW FIELDS FROM `{$sTableName}`";
            if ($aRows=$this->Database_GetConnect()->select($sQuery)) {
                if ($aRows[1]['Field']=='appId') { // Старый формат таблицы настроек
                    $this->ExportSQL(dirname(__FILE__).'/dump_03_05.sql');
                }
            }
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
        Config::Set('plugin.facebook.logo_url',Plugin::GetTemplateWebPath(__CLASS__).Config::Get('plugin.facebook.logo_url'));
	}

    /**
     * Деактивация плагина
     * @return boolean
     */
	public function Deactivate() {
        // Чистка кэша
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array("facebook_reset"));
		return true;
	}
}