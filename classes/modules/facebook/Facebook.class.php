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
class PluginFacebook_ModuleFacebook extends Module {
	protected $FB; // API Facebook
	protected $oMapper;
	protected $aCfg=NULL;

	/**
	 * Инициализация
	 */
	public function Init() {
        // facebook API
        $path=Plugin::GetPath('facebook').'classes/lib/facebook-php-sdk/src/facebook.php';
        include $path;

        $this->aCfg=Config::Get('plugin.facebook');

        // API
		$this->FB=new Facebook(array(
            'appId'  => $this->aCfg['application']['id'],
            'secret' => $this->aCfg['application']['secret'],
            'cookie' => true
        ));

        // Маппер
        $this->oMapper=Engine::GetMapper(__CLASS__);
	}

    /**
     * Проверка настроек и наличия прав у приложения писать на стену
     * @return bool
     */
    public function CheckRightsOK() {

        $sKey='Plugin_Facebook_CheckRightsOK_'.$this->aCfg['page']['id'];
        $aResult=$this->Cache_Get($sKey);

        if (!$aResult || !isset($aResult['rights'])) {

        if (isset($this->aCfg) &&
            isset($this->aCfg['page']) &&
            isset($this->aCfg['page']['id']) &&
            isset($this->aCfg['application']) &&
            isset($this->aCfg['application']) &&
            isset($this->aCfg['application']['id'])&&
            isset($this->aCfg['application']['api'])&&
            isset($this->aCfg['application']['secret'])) {

                $aResult=array(
                    'rights'=>$this->FB->api(array(
                        'method'=>'pages.isappadded',
                        'page_id'=>$this->aCfg['page']['id'],
                    )
                ));

                $this->Cache_Set($aResult,$sKey);
            }
            else
            {
                $aResult=array(
                    'rights'=>false
                );
            }
        }

        return (bool)$aResult['rights'];
    }

    /**
     * Опубликовать топик
     * @param  $oTopic
     * @return bool
     */
    public function PublishTopic($oTopic) {
        if (!$this->CheckRightsOK()) { return false; }

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
                preg_match("#/v/(\w+)#",$sVideo,$aVideoIdMatch);
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
                preg_match('/http:\/\/www\.youtube[^"]+/', $sObj, $video_matches);
                $aVideo[strpos($sText,$video_matches[0])]=$video_matches[0];
            }
        }

        return $aVideo;
    }

    /**
     * Публикация на стену через API Facebook
     * @param  $attachment
     * @return bool
     */
    public function _publish($attachment) {
        if (!$this->CheckRightsOK()) { return false; }

        try {
            $aResult=$this->FB->api(array(
                'method'=>'stream.publish',
                'uid'=>$this->aCfg['page']['id'],
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

        $sKey='PluginFacebook_isTopicPublished_'.$oTopic->getId();

        $aResult=$this->Cache_Get($sKey);

        if ($aResult && isset($aResult['bResult'])) {
            // вытаскиваем результат из кэша
            $bResult=$aResult['bResult'];
        } else {
            // если в кэше не нашли, получаем заново
            $bResult=$this->oMapper->isTopicPublished($oTopic);
            // и сохраняем в кэш
            $this->Cache_Set(array('bResult'=>$bResult),$sKey);
        }
        return (bool)$bResult;
    }

    /**
     * Проверка возможности опубликовать топик на стене
     * @param  $oTopic
     * @return bool
     */
    public function canPublishTopic($oTopic) {
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
}
?>