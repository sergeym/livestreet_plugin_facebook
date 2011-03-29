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
	protected $FB; // API Facebook
	protected $oMapper;
	protected $aCfg=NULL;
    protected $_settingsLoaded=false;

	/**
	 * Инициализация
	 */
	public function Init() {
        // Маппер
        $this->oMapper=Engine::GetMapper(__CLASS__);
        // facebook API
        $path=Plugin::GetPath('facebook').'classes/lib/facebook-php-sdk/src/facebook.php';
        include $path;

        $this->LoadSettings();
        $this->aCfg=Config::Get('plugin.facebook');

        // Игнорировать сертификаты
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

        // API
        if ($this->aCfg['application']['id'] && $this->aCfg['application']['secret']) {
            $this->FB=new Facebook(array(
                'appId'  => $this->aCfg['application']['id'],
                'secret' => $this->aCfg['application']['secret'],
                'cookie' => true
            ));
        }
	}

    /**
     * Обновить настройки Маппера.
     * @param  $app_id
     * @param  $app_key
     * @param  $app_secret
     * @return bool
     */
    public function UpdateMapperSettings($app_id,$app_key,$app_secret) {
        Config::Set('plugin.facebook.application.id',$app_id);
        Config::Set('plugin.facebook.application.key',$app_key);
        Config::Set('plugin.facebook.application.secret',$app_secret);

        $this->FB=new Facebook(array(
                'appId'  => $app_id,
                'secret' => $app_secret,
                'cookie' => true
        ));

        return true;
    }

    /**
     * Опубликовать топик
     * @param  $oTopic
     * @return bool
     */
    public function PublishTopic($oTopic) {
        if ($this->isTopicPublished($oTopic)) { return false; }

        $aAttachment=array(
            'caption' => $oTopic->getBlog()->getTitle(),
            'name' => $oTopic->getTitle(),
            'href' => $oTopic->getUrl(),
            'description' => strip_tags($oTopic->GetTextShort())
        );

        // Получаем список media-материалов
        $aAttachment['media']=$this->getMedia($oTopic,false);

        // публикуем в Facebook
        $sPublishId=$this->_publish($aAttachment);

        // записываем в опубликованные
        if ($sPublishId) {
            $this->oMapper->TopicPublish($oTopic->getId(),$sPublishId);
        }

        return $sPublishId;
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
     * Возвращает массив media-элементов. Готов к встраиванию в attachment
     * @param  $oTopic - сущность топика
     * @param bool $bSort - сортировать по расположению элемента в тексте
     * @return array
     */
    public function getMedia($oTopic,$bSort=false) {
        // подключение картинок
        $aImages=$this->_findImages($oTopic->GetTextShort());
        $aMedia=array();
        foreach($aImages as $key => $aImg) {
            $aMedia[$key]=array(
                "type" => "image",
                "src" => $aImg['src'],
                "href" => $oTopic->getUrl()
            );
        }
        // подключение видео
        $aVideos=$this->_findVideos($oTopic->GetTextShort());

        foreach($aVideos as $key => $sVideo) {
            // обработка видео YouTube
            if (strpos($sVideo,'www.youtube.com')) {
                preg_match("#/v/([\w\-]+)#",$sVideo,$aVideoIdMatch);
                if (isset($aVideoIdMatch[1])) {
                    $VideoId=$aVideoIdMatch[1];

                    $aMedia[$key]=array('type' => 'flash',
                                 'swfsrc' => 'http://www.youtube.com/v/'.$VideoId.'&hl=en&fs=1',
                                 'imgsrc' => 'http://img.youtube.com/vi/'.$VideoId.'/default.jpg?h=100&w=200&sigh=__wsYqEz4uZUOvBIb8g-wljxpfc3Q=',
                                 'width' => '160',
                                 'height' => '120',
                                 'expanded_width' => '480',
                                 'expanded_height' => '385');
                }

            }
        }

        // сортировка по ключу, если требуется
        if ($bSort==true) ksort($aMedia);
        // отдаем без ключей
        return array_values($aMedia);
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
    protected function _findVideos($sText) {
        preg_match_all('/<object(.*)<\/object>/i',$sText.$sText,$obj_matches);
        $aVideoObj=isset($obj_matches[0])?$obj_matches[0]:array();

        $aVideo=array();

        if (is_array($aVideoObj)) {
            foreach($aVideoObj as $sObj) {
                if (preg_match('/http:\/\/www\.youtube[^"]+/', $sObj, $video_matches)) {
                    $aVideo[strpos($sText,$video_matches[0])]=$video_matches[0];
                }
            }
        }

        return $aVideo;
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
            $aResult=$this->FB->api(array(
                'method'=>'stream.publish',
                //'target_id'=>$this->aCfg['page']['id'],
                'uid'=>$page_id,
                'attachment'=>$attachment
            ));
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
            $this->Cache_Set(array('bResult'=>$bResult),$sKey);
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
            $RealPublish=$this->FB->api($publish_id);

            if ($RealPublish) {

                $bResult=$this->FB->api(array(
                    'method'    =>  'stream.remove',
                    'post_id'   =>  $publish_id,
                    'uid'       =>  $page_id
                ));

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
    public function SaveSettings($app_id,$app_key,$app_secret,$pageId) {

        $this->UpdateMapperSettings($app_id,$app_key,$app_secret);

        if ($app_id && $app_key && $app_secret && $pageId) {

            try {

                if ($pageInfo=$this->GetPageInfoById($pageId)) {
                    if ($this->oMapper->SaveSettings($app_id,$app_key,$app_secret,$pageId,$pageInfo['page_url'])) {
                        $sKey = 'PluginFacebook_GetSettings_id_1';
                        $this->Cache_Delete($sKey);
                    }
                }

                return false;
                
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
    public function GetSettings($id=1) {
        $sKey = 'PluginFacebook_GetSettings_id_'.$id;

        if (false === ($aResult = $this->Cache_Get($sKey))) {
			if ($aResult=$this->oMapper->GetSettings($id)) {
				$this->Cache_Set($aResult, $sKey, array(), 60*60*24*1);
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

        Config::Set('plugin.facebook.application.id',$aConfig['appId']);
        Config::Set('plugin.facebook.application.api',$aConfig['appKey']);
        Config::Set('plugin.facebook.application.secret',$aConfig['appSecret']);
        Config::Set('plugin.facebook.page.id',$aConfig['pageId']);
        Config::Set('plugin.facebook.page.url',$aConfig['pageUrl']);

        $this->_settingsLoaded=true;
        return;
    }

    /**
     * Получить информацияю о странице (page) по индентификатору
     * @param  $id
     * @param array $aData
     * @return bool
     */
    public function GetPageInfoById($id,$aData=array('name','page_url')) {
        try {
            $aResult=$this->FB->api(array(
                'method'=>'pages.getinfo',
                'fields'=>$aData,
                'page_ids'=>array($id)
            ));
        } catch (Exception $e){
            $this->Logger_Error('PluginFacebook_ModuleFacebook->GetPageInfoById: '.$e->getMessage());
            return false;
        }

        return $aResult[0];
    }
}
?>