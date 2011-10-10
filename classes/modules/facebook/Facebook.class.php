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
class PluginFacebook_ModuleFacebook extends Module {
	protected $FB=NULL; // API Facebook
	protected $oMapper;
	protected $aCfg=NULL;
    protected $_settingsLoaded=false;
    protected $FbUser=NULL;

	/**
	 * Инициализация
	 */
	public function Init() {
        // Маппер
        $this->oMapper=Engine::GetMapper(__CLASS__);
	}

    public function StartAPI($settings=null) {
        if ($this->FB) return true;

        if (!$settings) {
            $this->LoadSettings();
        } else {
            Config::Set('plugin.facebook.application.id', $settings['id']);
            Config::Set('plugin.facebook.application.secret', $settings['secret']);
            Config::Set('plugin.facebook.application.access_token', $settings['access_token']);
        }
        
        $this->aCfg=Config::Get('plugin.facebook');

        // API
        if ($this->aCfg['application']['id'] && $this->aCfg['application']['secret'] && $this->aCfg['application']['access_token']) {
            // facebook API
            $path=Plugin::GetPath(__CLASS__).'classes/lib/facebook-php-sdk/src/facebook.php';
            include $path;

            // Игнорировать сертификаты
            Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
            Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

            $this->FB=new Facebook(array(
                'appId'  => $this->aCfg['application']['id'],
                'secret' => $this->aCfg['application']['secret'],
            ));

            $this->FB->setAccessToken($this->aCfg['application']['access_token']);
        }
    }

    /**
     * Опубликовать топик
     * @param  $oTopic
     * @return bool
     */
    public function PublishTopic($oTopic) {
        if ($this->isTopicPublished($oTopic)) { return false; }
        $aAttachment = array(
            'link' => $oTopic->getUrl(),
            'name' => $oTopic->getTitle(),
            'caption' => $oTopic->getBlog()->getTitle(),
            'description' => strip_tags($oTopic->GetTextShort()),
        );

        // Получаем список media-материалов
        $aMedia = $this->getMedia($oTopic,false);
        $aAttachment = array_merge($aAttachment,$aMedia);
        // публикуем в Facebook
        $aPublishId=$this->_publish($aAttachment);

        // записываем в опубликованные
        if ($aPublishId && isset($aPublishId['id'])) {
            $this->oMapper->TopicPublish($oTopic->getId(),$aPublishId['id']);

            $sKey='PluginFacebook_GetPublishInfoByTopic_'.$oTopic->getId();
            $this->Cache_Delete($sKey);
            return $aPublishId['id'];
        }

        return null;
    }

    /**
     * Запретить публиковать этот топик в Facebook
     * @param  $oTopic
     * @return void
     */
    public function PreventTopicPublish($oTopic) {
        $this->oMapper->PreventTopicPublish($oTopic->GetId());
    }

    /**
     * Разрешить публиковать топик в Facebook
     * @param  $oTopic
     * @return void
     */
    public function AllowTopicPublish($oTopic) {
        $this->oMapper->DeleteTopicPublish($oTopic->GetId());
        $sKey='PluginFacebook_GetPublishInfoByTopic_'.$oTopic->getId();
        $this->Cache_Delete($sKey);
    }

    /**
     * Опубликовать текст
     * @param  $oTopic
     * @return bool
     */
    public function PublishCustomAttachment($aAttachment,$page_id=false) {
        // публикуем в Facebook
        $sPublishId=$this->_publish($aAttachment,$page_id);

        return $sPublishId;
    }

    /**
     * Возвращает массив media-элементов.
     * @param  $oTopic - сущность топика
     * @param bool $bSort - сортировать по расположению элемента в тексте
     * @return array
     */
    public function getMedia($oTopic,$bSort=false) {
        $aResult=$aMedia=array();

        $sCacheKey = 'open_graph_topic_id_'.$oTopic->getId();

        if (false === ($aResult = $this->Cache_Get($sCacheKey))) {

            switch($oTopic->GetType()) {
                case 'photoset':
                    if ($oMainPhoto = $oTopic->GetPhotosetMainPhoto()) {
                        $aMedia=array(
                            array(
                                'type'=>'image',
                                'picture'=>$oMainPhoto->GetPath()
                            )
                        );
                    }
                break;
                case 'topic':

                    // подключение картинок
                    $aImages=$this->_findImages($oTopic->GetTextShort());
                    $aMedia=array();
                    foreach($aImages as $key => $aImg) {
                        $aMedia[$key]=array(
                            'type' => 'image',
                            'picture' => $aImg['src']
                        );
                    }
                    // подключение видео
                    $aVideos=$this->_findVideo($oTopic->GetTextShort());

                    if ($aVideos) {
                        foreach($aVideos as $key => $sVideo) {
                            $_v = $this->_getDataForVideo($sVideo);
                            if ($_v) {
                                $aMedia[$key]=$_v;
                            }
                        }
                    }
                break;
                default:
                    $this->Hook_Run('Plugin_Facebook_Unidentified_Topic_Type', array('oTopic'=>$oTopic,'aMedia'=>$aMedia));
            }

            // сортировка по ключу, если требуется
            if ($bSort==true) ksort($aMedia);
            // отдаем без ключей
            $_aRes = array_values($aMedia);
            $aResult = count($_aRes)>0?$_aRes[0]:array();
            // Кладем в кэш на год
            $this->Cache_Set($aResult, $sCacheKey, array('facebook_reset'), 60*60*24*31*12);
        }
        //print_r($aResult);
        return $aResult;
    }

    protected function _getDataForVideo($sVideo) {
        $sDomain = implode('.',array_slice(explode('.',strtolower(parse_url($sVideo,PHP_URL_HOST))),-2));
        switch($sDomain) {
            case 'vimeo.com':
                $_sQS = parse_url($sVideo,PHP_URL_QUERY);
                $_aQS=array();
                parse_str($_sQS,$_aQS);
                $_v = $this->_getDataByVimeoClipId($_aQS['clip_id']);
                if($_v) {
                    return array_merge(array('source'=>$sVideo),$_v);
                } else {
                    return null;
                }
            break;
            case 'youtube.com':
                if (preg_match('/\/v\/(.+)&/i',$sVideo,$aMatches)) {
                    return array_merge(array('source'=>$sVideo),$this->_getDataByYoutubeClipId($aMatches[1]));
                } else {
                    return null;
                }
            break;
        }
    }

    protected function _getDataByVimeoClipId($sId) {
        try {
            if ($xml = simplexml_load_file('http://vimeo.com/api/v2/video/'.$sId.'.xml')) {
                return array(
                    'picture' => ''.$xml->video->thumbnail_small,
                    'type' => 'application/x-shockwave-flash',
                    'width' => 0+$xml->video->width,
                    'height' => 0+$xml->video->height,
                    'type' => 'video',
                    'videotype' => 'application/x-shockwave-flash'
                );
            }
        } catch (Exception $e) {
            return null;
        }
    }

    protected function _getDataByYoutubeClipId($sId) {
        return array(
            'picture' => 'http://img.youtube.com/vi/'.$sId.'/1.jpg',
            'type' => 'application/x-shockwave-flash',
            'width' => 480,
            'height' => 360,
            'type' => 'video',
            'videotype' => 'application/x-shockwave-flash'
        );
    }

    /**
     * Ищет в тексте картинки и вощвращает массив содержащий атрибуты src, var, title
     * @param  $sText
     * @return array
     */
    protected function _findImages($sText) {
        preg_match_all('/<img[^>]+>/i',$sText, $aImages);
        $aImg = array();

        foreach($aImages as $img_tag)
        {
            if (count($img_tag)>0) {
                preg_match_all('/(alt|title|src)="([^"]*)"/i',$img_tag[0], $result);
                $aImgProps=array();

                foreach($result[1] as $key => $value) {
                    $aImgProps[$value]=$result[2][$key];
                }

                $aImg[strpos($sText,$aImgProps['src'])]=$aImgProps;
            }
        }
        return $aImg;
    }

    /**
     * Ищет в тесте видео и возвращает массив содержащий ссылки на flash-видео
     * @param  $sText
     * @return array
     */
    protected function _findVideo($sText) {

        preg_match_all('/<object .*<\/object>/i',$sText,$obj_matches);
        $aVideoObj=isset($obj_matches[0])?$obj_matches[0]:array();
        $aVideos = array();
        foreach($aVideoObj as $sVideo) {
            $regex  = '/<param\s*name\s*=\s*"movie"\s*value\s*=\s*"(.*)"/Ui';
            if (preg_match($regex, $sVideo, $aMatches)) {
                $aVideos[strpos($sText,$sVideo)] = $aMatches[1];
            }
        }

        return $aVideos;
    }

    /**
     * Публикация на стену через API Facebook
     * @param  $attachment
     * @return bool
     */
    public function _publish($attachment,$page_id=false) {
        if ($page_id==false) {
            $page_id=$this->aCfg['page']['id'];
        }

        try {
            $aResult=$this->FB->api('/'.$page_id.'/feed', 'POST', $attachment);
        } catch (Exception $e){
            $this->Logger_Error($e->getMessage());
            return false;
        }

        return $aResult;
    }

    /**
     * Проверка топика на опубликованность.
     * @param  $oTopic
     * @return bool
     */
    public function isTopicPublished($oTopic) {
        return (bool)$this->GetPublishInfoByTopic($oTopic);
    }

    /**
     * Отдает информацию об опубликованном топике
     * @param  $oTopic
     * @return bool
     */
    public function GetPublishInfoByTopic($oTopic) {
        $sKey='PluginFacebook_GetPublishInfoByTopic_'.$oTopic->getId();

        $aResult=$this->Cache_Get($sKey);
        if ($aResult && isset($aResult['bResult'])) {
            // вытаскиваем результат из кэша
            $bResult=$aResult['bResult'];
        } else {
            // если в кэше не нашли, получаем заново
            $bResult=$this->oMapper->GetPublishInfoByTopic($oTopic);
            // и сохраняем в кэш
            $this->Cache_Set(array('bResult'=>$bResult),$sKey,array('facebook_reset'));
        }
        return $bResult;
    }

    /**
     * Проверка возможности опубликовать топик на стене
     * @param  $oTopic
     * @return bool
     */
    public function CanPublishTopic($oTopic) {
        // если топик уже опубликован, его нельзя снова публиковать
        if ($this->isTopicPublished($oTopic)==true) { return false; }
        $aCnf=Config::Get('plugin.facebook');
        switch ($aCnf['strategy']) {
            case 'STRATEGY_MAIN':
                if ($oTopic->getRating()>=Config::Get('module.blog.index_good') ||
                    $oTopic->getPublishIndex()) {
                    return true;
                }
            break;
            case 'STRATEGY_RATING':
                if ($oTopic->getRating()>=Config::Get('plugin.facebook.STRATEGY_RATING.rating')) {
                    return true;
                }
            break;
        }
        return false;
    }

    /**
     * Удаляет анонс топика в Facebook и запись из базы.
     * В случае, когда анонс удален вручную, удаляет запись из базы.
     * @param  $publish_id
     * @param bool $leaveDbLink
     * @return bool
     */
    public function Delete($publish_id,$leaveDbLink=false) {
        // в $publish_id содержатся идентификаторы страницы и поста разделенные символом подчеркивания
        list($page_id,$post_id)=explode('_',$publish_id);

        $bResult=false;

        try {
            $aPublish=$this->FB->api($publish_id);
            //echo $aPublish;exit;
            if ($aPublish) {
                $bResult = $this->FB->api('/'.$publish_id,'DELETE');
            } else {
                $bResult=true;
            }

            if ($bResult==true && $leaveDbLink==false) {
                $iTopicId=$this->oMapper->GetTopicIdByPublishId($publish_id);

                $this->oMapper->DeleteTopicPublish($iTopicId);

                $sKey='PluginFacebook_GetPublishInfoByTopic_'.$iTopicId;
                $this->Cache_Delete($sKey);
            }

        } catch (Exception $e){
            $this->Logger_Error('PluginFacebook_ModuleFacebook->Delete: '.$e->getMessage().'; $publish_id='.$publish_id.', $page_id='.$page_id);
            return false;
        }

        return $bResult;
    }

    /**
     * Сохранить настройки плагина
     * @param  $app_id
     * @param  $app_key
     * @param  $app_secret
     * @param  $pageId
     * @return bool
     */
    public function SaveSettings($app_id,$app_secret,$access_token,$pageId) {

        if ($app_id && $app_secret && $pageId && $access_token) {

            try {

                if ($pageInfo=$this->GetPageInfoById($pageId)) {
                    if ($this->oMapper->SaveSettings($app_id,$app_secret,$access_token,$pageId,$pageInfo['link'])) {
                        $sKey = 'PluginFacebook_GetSettings_id_1';
                        $this->Cache_Delete($sKey);
                    }
                }

                return true;
                
            } catch (Exception $e) {
                $this->Logger_Error('PluginFacebook_ModuleFacebook->SaveSettings: '.$e->getMessage());
                return false;
            }

        } else {
            return false;
        }
        
    }

    /**
     * Получить настройки плагина
     * @param int $id
     * @return
     */
    public function GetSettings($id=1,$nocache=false) {
        $sKey = 'PluginFacebook_GetSettings_id_'.$id;
        if ($nocache==true || false === ($aResult = $this->Cache_Get($sKey))) {
			if ($aResult=$this->oMapper->GetSettings($id)) {
				$this->Cache_Set($aResult, $sKey, array('facebook_reset'), 60*60*24*1);
			}
		}

        return $aResult;
    }

    /**
     * Загрузить настройки плагина в конфигурацию сайта
     * @param int $id
     * @param bool $force
     * @return
     */
    public function LoadSettings($id=1, $force=false) {
        if ($this->_settingsLoaded==true && $force==false) {
            return;
        }
        
        $aConfig = $this->GetSettings($id);

        Config::Set('plugin.facebook.application.id',$aConfig['app_id']);
        Config::Set('plugin.facebook.application.secret',$aConfig['app_secret']);
        Config::Set('plugin.facebook.application.access_token',$aConfig['access_token']);
        Config::Set('plugin.facebook.page.id',$aConfig['page_id']);
        Config::Set('plugin.facebook.page.url',$aConfig['page_url']);

        $this->_settingsLoaded=true;
        return;
    }

    /**
     * Получить информацияю о странице (page) по индентификатору
     * @param  $id
     * @param array $aData
     * @return bool
     */
    public function GetPageInfoById($id,$aData=array('name','link')) {
        try {
            $aResult=$this->FB->api('/'.$id.'?fields='.implode(',',$aData));
        } catch (Exception $e){
            $this->Logger_Error('PluginFacebook_ModuleFacebook->GetPageInfoById: '.$e->getMessage());
            return false;
        }

        return $aResult;
    }

    public function GetFacebookTopicsCollective($iPage,$iPerPage) {
        $aFacebookTopics = $this->oMapper->GetFacebookTopics($iCount,$iPage,$iPerPage);
        $aTopicId = array();
        foreach($aFacebookTopics as $oFbTopic) {
            $aTopicId[]=$oFbTopic->GetTopicId();
        }

        $aTopics = $this->Topic_GetTopicsAdditionalData($aTopicId);
        foreach($aFacebookTopics as $oFbTopic) {
            $oFbTopic->setTopic($aTopics[$oFbTopic->GetTopicId()]);
            
        }

        return array(
            'collection' => $aFacebookTopics,
            'count' => $iCount
        );
    }

    public function GetUserAccounts($user_id,$access_token) {
        try {
            if ($aResult=$this->FB->api('/'.$user_id.'/accounts', array('access_token'=>$access_token))) {
                return $aResult['data'];
            }
            return false;
        } catch (Exception $e) {
            $this->Logger_Error('PluginFacebook_ModuleFacebook->GetUserAccounts: '.$e->getMessage());
            return false;
       }

    }

    public function SetOpenGraphHeaders($aHeaders) {
        $this->Hook_Run('PluginFacebook_Set_OpenGraph_Headers',array('aHeaders'=>$aHeaders));
    }

}
?>