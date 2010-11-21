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

class PluginFacebook_HookFacebook extends Hook {

        protected static $oTopic=NULL;

        public function RegisterHook() {
                $aCnf=Config::Get('plugin.facebook');

                // Блок
                $this->AddHook("init_action", "initAction", __CLASS__);

                // Заголовки OpenGraph
                $this->AddHook("topic_show", "HookSetTopic", __CLASS__);
                $this->AddHook("template_html_head_begin", "HookInsertOpenGraphHeaders", __CLASS__);

                switch($aCnf['strategy']) {
                    case 'STRATEGY_RATING':
                    case 'STRATEGY_MAIN':
                        $this->AddHook('module_rating_votetopic_after', 'HookRatingVoteTopicAfter', __CLASS__, 2);
                    break;
                }
        }

        function initAction($aVars) {
            $this->Viewer_AddBlock(
                    'right',
                    'facebook',
                    array('plugin' => 'facebook'),
                    Config::Get('plugin.facebook.facebook_block_priority')
            );
        }

        public function HookRatingVoteTopicAfter($args) {
            // вызов прошел успешно
            if ($args['result']==1) {
                //$oUser  =   $args['params'][0];
                $oTopic =   $args['params'][1];
                //$iValue =   $args['params'][2];

                // можно ли опубликовать топик в Facebook
                $bCanPublishTopic=$this->PluginFacebook_ModuleFacebook_canPublishTopic($oTopic);

                if ($bCanPublishTopic==true) {
                    $this->PluginFacebook_ModuleFacebook_PublishTopic($oTopic);
                }
            }
            
            return true;
        }

        public function HookSetTopic($args) {
            self::$oTopic=$args['oTopic'];
        }

        // вставка OpenGraf тэгов в случае, если ранее был определен топик
        public function HookInsertOpenGraphHeaders() {
            if (!self::$oTopic) return;

            // список media-данных
            $aMedia=$this->PluginFacebook_ModuleFacebook_getMedia(self::$oTopic,true);
            
            if (count($aMedia)>0) {
                switch($aMedia[0]['type']) {
                    case 'flash':
                        $sImage=$aMedia[0]['imgsrc'];
                    break;
                    case 'image':
                        $sImage=$aMedia[0]['src'];
                    break;
                }
            } else {
                // В случае отсутствия media-данных подставляем картинку по умолчанию
                $sImage=Config::Get('plugin.facebook.default_post_image');
                // если не задана своя картинка, использовать дефолтную
                if (!$sImage) $sImage=Plugin::GetTemplateWebPath('facebook').'images/default.jpg';
            }

            // Передаем в шаблон данные
            $this->Viewer_Assign('sTitle',self::$oTopic->getTitle());
            $this->Viewer_Assign('sImage',$sImage);
		    return $this->Viewer_Fetch(Plugin::GetTemplatePath('facebook').'/inject.header.tpl');
        }
}