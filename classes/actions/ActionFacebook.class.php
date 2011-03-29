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
class PluginFacebook_ActionFacebook extends ActionPlugin {

    protected $oUserCurrent=null;
    protected $sPlugin='facebook';

    public function Init() {
        $this->oUserCurrent=$this->User_GetUserCurrent();

        if (!$this->oUserCurrent or !$this->oUserCurrent->isAdministrator()) {
			return $this->EventNotFound();
		}

        $sWebPluginSkin=Plugin::GetTemplateWebPath(__CLASS__);
        $this->Viewer_Assign('sWebPluginSkin', $sWebPluginSkin);
    }

    protected function RegisterEvent() {
        $this->SetDefaultEvent('index');
        $this->AddEvent('index','EventIndex');
        $this->AddEvent('ajaxtest','EventAjaxTest');
        $this->AddEvent('ajaxsave','EventAjaxSave');
    }

    /**
     * Вывод инструкции и теста
     * @return void
     */
    protected function EventIndex() {
        $aConfig=$this->PluginFacebook_ModuleFacebook_GetSettings();
        // в шаблон
        $this->Viewer_Assign('pluginCfg',$aConfig);
        $this->Viewer_Assign('facebookRightsOK',false);
        // меняем заголовок старницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin_facebook_setup_title'));
    }

    /**
     * Мастер проверки
     * @return void
     */
    protected function EventAjaxTest() {
        $this->Viewer_SetResponseAjax();

        $app_id=getRequest('app_id',null,'post');
		$app_key=getRequest('app_key',null,'post');
		$app_secret=getRequest('app_secret',null,'post');
		$pageId=getRequest('page_id',null,'post');
        $action=getRequest('action',null,'post');
        $publish_id=getRequest('publish_id',null,'post');

        $sPublishId='';$bResult=null;$aPageInfo=array();

        if ($app_id && $app_key && $app_secret && $pageId) {

            try {
                $this->PluginFacebook_ModuleFacebook_UpdateMapperSettings($app_id,$app_key,$app_secret);

                switch ($action) {
                    case 'publish':

                        if ($aPageInfo=$this->PluginFacebook_ModuleFacebook_GetPageInfoById($pageId,array('page_url','name'))) {
                        
                            $aAttachment=array(
                                'caption' => 'Проверка. Random: '.rand(0,99999),
                                'name' => 'Модуль Facebook для LiveStreet',
                                'href' => Config::Get('path.root.web'),
                                'description' => 'Эта запись создана с помощью плагина Facebook для блого-социального движка LiveStreet. Данная запись создана программой настройки плагина. Если Вы владелец сайта '.Config::Get('path.root.web').' и не смогли удалить это сообщение через программу настройки, Вы можете сделать это вручную.'
                            );

                            $sPublishId = $this->PluginFacebook_ModuleFacebook_PublishCustomAttachment($aAttachment,$pageId);
                        }

                        if ($sPublishId) {
                            $this->Message_AddNoticeSingle('Добавлено','Не удалось добавить сообщение');
                        } else {
                            $this->Message_AddErrorSingle($this->Lang_Get('system_error'),'Не удалось добавить сообщение');
                        }



                    break;
                    case 'delete':
                        $bResult=$this->PluginFacebook_ModuleFacebook_Delete($publish_id);
                        if ($bResult) {
                            $this->Message_AddNoticeSingle('Успешно','Сообщение было удалено');
                        } else {
                            $this->Message_AddErrorSingle($this->Lang_Get('system_error'),'Не удалось удалить запись');
                        }
                    break;
                }

            } catch (Exception $e) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'),'Критическая ошибка при обработке запроса');
            }
        }

        if ($sPublishId) {
		    $this->Viewer_AssignAjax('sPublishId', $sPublishId);
        }
        
        if ($bResult) {
		    $this->Viewer_AssignAjax('bResult', $bResult);
        }

        if ($aPageInfo) {
            $this->Viewer_AssignAjax('aPageInfo', $aPageInfo);
        }
    }

    /**
     * Сохранение настроек
     * @return void
     */
    protected function EventAjaxSave() {
        $this->Viewer_SetResponseAjax();
		$bStateError=true;
        $sMsg='';
        $sMsgTitle='';

        $app_id=getRequest('app_id',null,'post');
		$app_key=getRequest('app_key',null,'post');
		$app_secret=getRequest('app_secret',null,'post');
		$pageId=getRequest('page_id',null,'post');

        $sPublishId='';$bResult=null;

        if ($app_id && $app_key && $app_secret && $pageId) {
            $bResult=$this->PluginFacebook_ModuleFacebook_SaveSettings($app_id,$app_key,$app_secret,$pageId);
        }

        $this->Viewer_AssignAjax('bStateError', $bStateError);
        $this->Viewer_AssignAjax('sMsgTitle',$sMsgTitle);
		$this->Viewer_AssignAjax('sMsg',$sMsg);
    }
}