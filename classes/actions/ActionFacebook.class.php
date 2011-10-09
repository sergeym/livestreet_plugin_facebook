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
    }

    protected function RegisterEvent() {
        $this->SetDefaultEvent('index');
        $this->AddEvent('index','EventIndex');
        $this->AddEvent('setup','EventSetup');
        $this->AddEventPreg('/^postings/i','/^(page(\d+))?$/i','EventPostings');
        $this->AddEvent('postings','EventPostings');

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
     * Настройка связи с Facebook
     * @return void
     */
    protected function EventSetup() {
        $aConfig=$this->PluginFacebook_ModuleFacebook_GetSettings(1,true);
        // в шаблон
        $this->Viewer_Assign('pluginCfg',$aConfig);
        $this->Viewer_Assign('facebookRightsOK',false);

        $this->Viewer_Assign('bCurlInstalled',function_exists('curl_init'));
        $this->Viewer_Assign('bSimpleXmlInstalled',function_exists('simplexml_load_file'));

        if (Config::Get('plugin.facebook.js')!=='jquery') {
            $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/jquery.js');
            $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/jquery.noconflict.js');
        }

        $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/jquery.smartWizard-2.0.min.js');
        $this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__).'js/jsetup.js');
        $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath(__CLASS__).'css/smart_wizard.css');
        // меняем заголовок старницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin_facebook_setup_title'));
    }

    /**
     * Вывод инструкции и теста
     * @return void
     */
    protected function EventPostings() {

        $iPage=$this->GetParamEventMatch(0,2) ? $this->GetParamEventMatch(0,2) : 1;


        $aResult=$this->PluginFacebook_Facebook_GetFacebookTopicsCollective($iPage,Config::Get('module.topic.per_page'));
        $aTopics=$aResult['collection'];
        $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('module.topic.per_page'),4,Router::GetPath('facebook').'postings');
        $this->Viewer_Assign('aTopics',$aTopics);
		$this->Viewer_Assign('aPaging',$aPaging);
        // меняем заголовок старницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin_facebook_setup_title'));
    }

    /**
     * Мастер проверки
     * @return void
     */
    protected function EventAjaxTest() {
        $this->Viewer_SetResponseAjax('json');

        $app_id=getRequest('app_id',null,'post');
		$app_secret=getRequest('app_secret',null,'post');
		$pageId=getRequest('page_id',null,'post');
        $action=getRequest('action',null,'post');
        $publish_id=getRequest('publish_id',null,'post');
        $user_id=getRequest('user_id',null,'post');
        $access_token=getRequest('access_token',null,'post');

        $sPublishId='';$bResult=null;$aPageInfo=array();

        if ($app_id && $app_secret) {

            try {
                $this->PluginFacebook_ModuleFacebook_StartAPI(array('id'=>$app_id,'secret'=>$app_secret,'access_token'=>$access_token));
                switch ($action) {
                    case 'publish':

                        if ($aPageInfo=$this->PluginFacebook_ModuleFacebook_GetPageInfoById($pageId,array('link','name'))) {
                        
                            $aAttachment = array(
                                'message' => "Тест модуля Facebook для LiveStreet",
                                'link' => Config::Get('path.root.web'),
                                //'picture' => "",
                                'name' => 'Модуль Facebook для LiveStreet',
                                'caption' => 'Проверка. Случайное число: '.rand(0,99999),
                                'description' => 'Эта запись создана с помощью плагина Facebook для блого-социального движка LiveStreet. Данная запись создана программой настройки плагина. Если Вы владелец сайта '.Config::Get('path.root.web').' и не смогли удалить это сообщение через программу настройки, Вы можете сделать это вручную.',
                                //'actions' => "",
                                //'picture'=> "",
                                //'privacy' => "EVERYONE",
                            );

                            $sPublishId = $this->PluginFacebook_ModuleFacebook_PublishCustomAttachment($aAttachment,$pageId);
                        }

                        if ($sPublishId) {
                            $_aPublishId = explode('_',$sPublishId['id']);
                            $this->Message_AddNoticeSingle('<a href="http://www.facebook.com/permalink.php?story_fbid='.$_aPublishId[1].'&id='.$_aPublishId[0].'" target="_blank">Соббщение добавлено</a> в ленту Facebook', 'Успешно');
                        } else {
                            $this->Message_AddErrorSingle('Не удалось добавить сообщение',$this->Lang_Get('system_error'));
                        }
                    break;
                    case 'delete':
                        $bResult=$this->PluginFacebook_ModuleFacebook_Delete($publish_id);
                        if ($bResult) {
                            $this->Message_AddNoticeSingle('Сообщение было удалено', 'Успешно');
                        } else {
                            $this->Message_AddErrorSingle('Не удалось удалить запись',$this->Lang_Get('system_error'));
                        }
                    break;
                    case 'accounts':
                        if ($aAccounts=$this->PluginFacebook_ModuleFacebook_GetUserAccounts($user_id,$access_token)) {
                            $this->Viewer_AssignAjax('aAccounts', $aAccounts);
                        }
                    break;
                }

            } catch (Exception $e) {
                $this->Message_AddErrorSingle('Критическая ошибка при обработке запроса', $this->Lang_Get('system_error'));
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
        $this->Viewer_SetResponseAjax('json');

        $app_id=getRequest('app_id',null,'post');
		$app_secret=getRequest('app_secret',null,'post');
		$pageId=getRequest('page_id',null,'post');
		$access_token=getRequest('access_token',null,'post');

        $bResult=null;

        $this->PluginFacebook_ModuleFacebook_StartAPI(array('id'=>$app_id,'secret'=>$app_secret,'access_token'=>$access_token));

        if ($app_id && $app_secret && $pageId && $access_token) {
            $bResult=$this->PluginFacebook_ModuleFacebook_SaveSettings($app_id,$app_secret,$access_token,$pageId);
        }

        if ($bResult==true) {
            $this->Message_AddNoticeSingle('Настройки были сохранены', 'Успешно');
        } else {
            $this->Message_AddErrorSingle('Не удалось сохранить настройки',$this->Lang_Get('error'));
        }
    }

    public function _AddBlock($sPlace,$sPath) {
        $_v = substr(LS_VERSION,0,3);
        switch ($_v) {
            case '0.4':
                $this->Viewer_AddBlock($sPlace,$this->getTemplatePathPlugin().$sPath);
                break;
            case '0.5':
            default:
                $this->Viewer_AddBlock($sPlace,$sPath,array('plugin'=>'facebook'));
        }


    }

    public function EventShutdown() {
        $this->Viewer_Assign('sEvent', $this->sCurrentEvent);
        $this->Viewer_Assign('sMenuItemSelect', 'facebook');
        $this->_AddBlock('right','actions/ActionFacebook/sidebar.tpl');
        $this->Viewer_AddMenu('facebook', Plugin::GetTemplatePath(__CLASS__).'/menu.facebook.tpl');
    }
}