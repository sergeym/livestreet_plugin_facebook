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
class PluginFacebook_ModuleFacebook_MapperFacebook extends Mapper {	

    /**
     * Проверка опубликованности топика
     * @param  $oTopic
     * @return bool
     */
	public function isTopicPublished($oTopic) {
		$sql = "SELECT
					1
					FROM
						".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')." as f
					WHERE
						f.topic_id = ?
					";
        
        $aRows=$this->oDb->select($sql,$oTopic->getId());

        return count($aRows)==0?false:true;
	}

    /**
     * Добавление топика в список опубликованных
     * @param  $topic_id
     * @param  $publish_id
     * @return array|bool|void
     */
    public function TopicPublish($topic_id, $publish_id) {
       $sql = "INSERT INTO ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')."
			(topic_id,
			date,
			publish_id
			)
			VALUES(?d,  ?,	?d)
		";
		if ($iId=$this->oDb->query($sql,$topic_id,date('Y-m-d H:i:s'),$publish_id)) {
			return $iId;
		}
		return false;
    }
}