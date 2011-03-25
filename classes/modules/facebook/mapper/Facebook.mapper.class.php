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
     * Возвращает информацию об опубликованном топике
     * @param  $oTopic
     * @return array
     */
    public function GetPublishInfoByTopic($oTopic) {
        $sql = "SELECT
					topic_id,
					date,
					publish_id,
					status
                FROM
                    ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')." as f
                WHERE
                    f.topic_id = ?
                ";
        $aRows=$this->oDb->select($sql,$oTopic->getId());

        $aResult = array();
        if (isset($aRows[0])) {
            $aResult = $aRows[0];
            list($aResult['page_id'],$aResult['post_id']) = explode('_',$aResult['publish_id']);
        }
        
        return $aResult;
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
			publish_id,
			status
			)
			VALUES(?d,  ?,	?, ?)
		";
		if ($iId=$this->oDb->query($sql,$topic_id,date('Y-m-d H:i:s'),$publish_id,'published')) {
			return $iId;
		}
		return false;
    }

    /**
     * Добавляет блокировку на публикацию топика
     * @param  $topic_id
     * @return bool
     */
    public function PreventTopicPublish($topic_id) {
       $sql = "INSERT INTO ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')."
			(topic_id,
			date,
			status
			)
			VALUES(?d, ?, ?)
		";
		if ($iId=$this->oDb->query($sql,$topic_id,date('Y-m-d H:i:s'),'blocked')) {
			return $iId;
		}
		return false;
    }


    /**
     * Удаляет из базы информацию о публикации топка в Facebook
     * @param  $publish_id
     * @return bool
     */
    public function DeleteTopicPublishByPublishId($publish_id) {
       $sql = "DELETE FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')."
                WHERE
                    publish_id = ?
		";
		if ($this->oDb->query($sql,$publish_id)) {
			return true;
		}
		return false;
    }

    public function DeleteTopicPublish($topic_id) {
       $sql = "DELETE FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')."
                WHERE
                    topic_id = ?
		";
		if ($this->oDb->query($sql,$topic_id)) {
			return true;
		}
		return false;
    }

    public function SaveSettings($app_id,$app_key,$app_secret,$pageId,$pageUrl) {
        require_once Config::Get('path.root.engine').'/lib/external/XXTEA/encrypt.php';
        
        $sql="SELECT 1 FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings')." WHERE id=?";
        $aRows=$this->oDb->select($sql,1);

        $sqlMid="
                SET
                    id = ?d,
                    appId = ?,
                    appKey = ?,
                    appSecret = ?,
                    pageId = ?,
                    pageUrl = ?
                ";

        if (!$aRows) {
            $sql="INSERT INTO ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings').' '.$sqlMid;
        } else {
            $sql="UPDATE ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings').' '.$sqlMid.' WHERE id=1';
        }

        if ($iId=$this->oDb->query($sql,
                               1,
                               $app_id,
                               base64_encode(xxtea_encrypt($app_key, Config::Get('module.blog.encrypt'))),
                               base64_encode(xxtea_encrypt($app_secret, Config::Get('module.blog.encrypt'))),
                               $pageId,
                               $pageUrl)) {
            return $iId;
        }

        return false;

	}

    public function GetSettings($id=1) {
        require_once Config::Get('path.root.engine').'/lib/external/XXTEA/encrypt.php';

        $sql="SELECT * FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings')." WHERE id=?";
        if ($aRows=$this->oDb->select($sql,$id)) {
            if (isset($aRows[0])) {
                $aResult=$aRows[0];
                $aResult['appKey']    = xxtea_decrypt(base64_decode($aResult['appKey']),Config::Get('module.blog.encrypt'));
                $aResult['appSecret'] = xxtea_decrypt(base64_decode($aResult['appSecret']),Config::Get('module.blog.encrypt'));
                return $aResult;
            }
        }

        return false;

	}

}