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
     * Получить идентификатор топика по идентификатору публикации в Facebook
     * @param  $publish_id
     * @return null
     */
    public function GetTopicIdByPublishId($publish_id) {
        $sql = "SELECT
					topic_id
                FROM
                    ".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')."
                WHERE
                    publish_id = ?
                ";

        if ($aRow=$this->oDb->selectRow($sql,$publish_id)) {
			return $aRow['topic_id'];
		}
		return null;
    }

    /**
     * Удалить сведения о публикации в FB
     * @param  $topic_id
     * @return bool
     */
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

    /**
     * Сохранить настройки в БД. Часть настроек шифруется.
     * @param  $app_id
     * @param  $app_secret
     * @param  $pageId
     * @param  $pageUrl
     * @return bool
     */
    public function SaveSettings($app_id,$app_secret,$access_token,$pageId,$pageUrl) {
        require_once Config::Get('path.root.engine').'/lib/external/XXTEA/encrypt.php';
        
        $sql="SELECT 1 FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings')." WHERE id=?";
        $aRows=$this->oDb->select($sql,1);

        $sqlMid="
                SET
                    id = ?d,
                    app_id = ?,
                    app_secret = ?,
                    access_token = ?,
                    page_id = ?,
                    page_url = ?
                ";

        if (!$aRows) {
            $sql="INSERT INTO ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings').' '.$sqlMid;
        } else {
            $sql="UPDATE ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings').' '.$sqlMid.' WHERE id=1';
        }

        if ($iId=$this->oDb->query($sql,
                               1,
                               $app_id,
                               base64_encode(xxtea_encrypt($app_secret, Config::Get('module.blog.encrypt'))),
                               base64_encode(xxtea_encrypt($access_token, Config::Get('module.blog.encrypt'))),
                               $pageId,
                               $pageUrl)) {
            return $iId;
        }

        return false;

	}

    /**
     * Получить настройки
     * @param int $id
     * @return bool
     */
    public function GetSettings($id=1) {
        require_once Config::Get('path.root.engine').'/lib/external/XXTEA/encrypt.php';

        $sql="SELECT * FROM ".Config::Get('plugin.facebook.db.table.plugin_facebook_settings')." WHERE id=?";
        if ($aResult=$this->oDb->selectRow($sql,$id)) {
            $aResult['app_secret'] = xxtea_decrypt(base64_decode($aResult['app_secret']),Config::Get('module.blog.encrypt'));
            $aResult['access_token'] = xxtea_decrypt(base64_decode($aResult['access_token']),Config::Get('module.blog.encrypt'));
            return $aResult;
        }
        
        return false;
	}

    public function GetFacebookTopics(&$iCount,$iCurrPage,$iPerPage) {
        $sql = "SELECT
						t.topic_id,
						t.date,
						t.publish_id,
						t.status
					FROM
						".Config::Get('plugin.facebook.db.table.plugin_facebook_topic_list')." as t
                    ORDER BY
                        t.date DESC
					LIMIT ?d, ?d";
		$aTopics=array();
		if ($aRows=$this->oDb->selectPage($iCount,$sql,($iCurrPage-1)*$iPerPage, $iPerPage)) {
			foreach ($aRows as $aTopic) {
				$aTopics[]=Engine::GetEntity('PluginFacebook_ModuleFacebook_EntityFacebookTopic',$aTopic);
			}
		}
		return $aTopics;
    }

}