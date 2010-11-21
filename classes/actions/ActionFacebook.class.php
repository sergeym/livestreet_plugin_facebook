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
    }

    /**
     * Вывод инструкции и теста
     * @return void
     */
    protected function EventIndex() {
        if (isPost('make_test')) {
            // тестируем. Отправлям крайний пост
            $bMakeTest=(bool)getRequest('make_test',null,'post');
            if (!empty($bMakeTest)) {
                $aResult=$this->Topic_GetTopicsCollective(0,1);
                $aTopics=array_values($aResult['collection']);
                if (isset($aTopics[0])) {
                    $this->PluginFacebook_ModuleFacebook_PublishTopic($aTopics[0]);
                }
            }
		}

        // флаг проверки прав
        $facebookRightsOK=$this->PluginFacebook_ModuleFacebook_CheckRightsOK();
        
        // в шаблон
        $this->Viewer_Assign('pluginCfg',Config::Get('plugin.facebook'));
        $this->Viewer_Assign('facebookRightsOK',$facebookRightsOK);
        $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath('facebook').'css/index.css');
        
        // меняем заголовок старницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin_facebook_setup_title'));
        
    }
}