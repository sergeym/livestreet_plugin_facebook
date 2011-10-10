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

class PluginFacebook_HookFacebook extends Hook {

        protected static $oTopic=NULL;
        protected static $aHeaders=NULL;

        public function RegisterHook() {
            
                $this->PluginFacebook_ModuleFacebook_LoadSettings();

                $aCnf=Config::Get('plugin.facebook');

                // Блок
                $this->AddHook("init_action", "initAction", __CLASS__);

                // Заголовки OpenGraph
                $this->AddHook("topic_show", "HookSetTopic", __CLASS__);
                // Заголовки передаваемые из сторонних скриптов
                $this->AddHook('PluginFacebook_Set_OpenGraph_Headers', 'HookSetOpenGraphHeaders', __CLASS__);

                // Диалог редактирования топика
                $this->AddHook("topic_edit_show", "HookSetTopic", __CLASS__);

                //Вставка OG тэгов в заголовок
                $this->AddHook("template_html_head_begin", "HookInsertOpenGraphHeaders", __CLASS__);
                //Вставка контролов публикации и удаления в форму редактирования топика
                $this->AddHook("template_form_add_topic_topic_end", "HookInsertFacebookControlsToTopicAddOrEditForm", __CLASS__);

                // Голосование за топик
                $this->AddHook('module_rating_votetopic_after', 'HookRatingVoteTopicAfter', __CLASS__, 2);

                // Обработка формы добавления и редактирования топика
                $this->AddHook('topic_add_after', 'HookAddTopicAfter', __CLASS__);
                $this->AddHook('topic_edit_after', 'HookEditTopicAfter', __CLASS__);
        }

        function initAction($aVars) {
            /*$this->Viewer_AddBlock(
                    'right',
                    'facebook',
                    array('plugin' => 'facebook'),
                    Config::Get('plugin.facebook.facebook_block_priority')
            );*/
            $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath(__CLASS__).'css/widget.css');
        }


        /**
         * Вставка контролов для публикации/удаления топиков в форме создания/редактирования топика
         * @return
         */
        public function HookInsertFacebookControlsToTopicAddOrEditForm() {
            // это режим редактирования?
            $bEditMode=(isset(self::$oTopic) && self::$oTopic);
            // В режиме редактирования, проверить, публиковался ли топик
            $aPublishInfo = $bEditMode?$this->PluginFacebook_ModuleFacebook_GetPublishInfoByTopic(self::$oTopic):array();
            $bPublished=(isset($aPublishInfo['status']) && $aPublishInfo['status']=='published');
            $bPublishBlocked=(isset($aPublishInfo['status']) && $aPublishInfo['status']=='blocked');
            $aPluginConfig=Config::Get('plugin.facebook');

            $this->Viewer_Assign('bEditMode',$bEditMode);
            $this->Viewer_Assign('aPublishInfo',$aPublishInfo);
            $this->Viewer_Assign('bPublished',$bPublished);
            $this->Viewer_Assign('bPublishBlocked',$bPublishBlocked);
            $this->Viewer_Assign('aPluginConfig',$aPluginConfig);
		    return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__).'/inject.add_topic.tpl');
        }

        public function HookRatingVoteTopicAfter($args) {
            // вызов прошел успешно
            if ($args['result']==1) {
                //$oUser  =   $args['params'][0];
                $oTopic =   $args['params'][1];
                //$iValue =   $args['params'][2];

                // можно ли опубликовать топик в Facebook
                $bCanPublishTopic=$this->PluginFacebook_ModuleFacebook_CanPublishTopic($oTopic);

                if ($bCanPublishTopic==true) {
                    $this->PluginFacebook_ModuleFacebook_StartAPI();
                    $this->PluginFacebook_ModuleFacebook_PublishTopic($oTopic);
                }
            }
            
            return true;
        }

        /**
         * Обрабатывает редактирование топика
         * @param  $args
         * @return bool
         */
        public function HookEditTopicAfter($args){
            $oTopic = $args['oTopic'];

            if ($oTopic->getPublish()) {

                $oUserCurrent=$this->User_GetUserCurrent();

                // Админские регалии
                if ($oUserCurrent && $oUserCurrent->isAdministrator()) {

                    $topic_delete_facebook=getRequest('topic_delete_facebook',null,'post'); // принудительно удалить
                    $topic_publish_facebook=getRequest('topic_publish_facebook',null,'post'); // принудительно опубликовать
                    $topic_deny_facebook=getRequest('topic_deny_facebook',null,'post'); // блокировать добавление
                    $topic_allow_facebook=getRequest('topic_allow_facebook',null,'post'); // отменить блокировку

                    // Инициализация API
                    $this->PluginFacebook_ModuleFacebook_StartAPI();

                    // Пришел приказ публиковать
                    if ($topic_publish_facebook) {
                        // Подгрузка блога, т.к. при публикации используется его заголовок
                        $oTopic->setBlog($this->Blog_GetBlogById($oTopic->GetBlogId()));
                        // публикация
                        $this->PluginFacebook_ModuleFacebook_PublishTopic($oTopic);
                    } elseif ($topic_delete_facebook) { // пришел приказ удалить
                        $aPublishInfo = $this->PluginFacebook_ModuleFacebook_GetPublishInfoByTopic($oTopic);
                        $this->PluginFacebook_ModuleFacebook_Delete($aPublishInfo['publish_id'],false);
                    } elseif ($topic_deny_facebook) {
                        $this->PluginFacebook_ModuleFacebook_PreventTopicPublish($oTopic);
                    } elseif ($topic_allow_facebook) {
                        $this->PluginFacebook_ModuleFacebook_AllowTopicPublish($oTopic);
                    }
                }

                // Чистка кэша при апдэйте
                $sCacheKey = 'open_graph_topic_id_'.$oTopic->getId();
                $this->Cache_Delete($sCacheKey);
            }
            return true;
        }

        /**
         * Обрабатывает добавление топика
         * @param  $args
         * @return bool
         */
        public function HookAddTopicAfter($args){
            $oTopic = $args['oTopic'];

            if ($oTopic->getPublish()) {
                $oUserCurrent=$this->User_GetUserCurrent();

                $topic_publish_facebook=$topic_deny_facebook=null;

                // Проверка админских опций
                if ($oUserCurrent && $oUserCurrent->isAdministrator()) {
                    $topic_publish_facebook=getRequest('topic_publish_facebook',null,'post'); // принудительно опубликовать
                    $topic_deny_facebook=getRequest('topic_deny_facebook',null,'post'); // блокировать добавление
                }

                // Проверка на возможность публикации
                if ($this->PluginFacebook_ModuleFacebook_CanPublishTopic($oTopic)==true && $topic_deny_facebook!==true) {
                    $topic_publish_facebook=true;
                }

                // Трубуется опубликовать
                if ($topic_publish_facebook) {
                    // Подгрузка блога, т.к. при публикации используется его заголовок
                    $oTopic->setBlog($this->Blog_GetBlogById($oTopic->GetBlogId()));
                    // Инициализация API
                    $this->PluginFacebook_ModuleFacebook_StartAPI();
                    // публикация
                    $this->PluginFacebook_ModuleFacebook_PublishTopic($oTopic);
                } elseif ($topic_deny_facebook) { // требуется заблокировать
                    // запрет публикации в ФБ
                    $this->PluginFacebook_ModuleFacebook_PreventTopicPublish($oTopic);
                }
            }
            return true;
        }

        /**
         * Запоминает топик до последующей обработки хуком template_html_head_begin
         * @param $args
         * @return void
         */
        public function HookSetTopic($args) {
            self::$oTopic=$args['oTopic'];
        }

        /**
         * Запоминает переданные заголовки до последующей обработки хуком template_html_head_begin
         * @param $args
         * @return void
         */
        public function HookSetOpenGraphHeaders($args) {
            if (is_array($args) && isset($args['aHeaders'])) {
                self::$aHeaders = $args['aHeaders'];
            }
        }

        // вставка OpenGraf тэгов в случае, если ранее был определен топик
        public function HookInsertOpenGraphHeaders() {
            if (!self::$oTopic && !self::$aHeaders) return;

            $sTitle = $aVideo = $sImage = null;

            if (self::$aHeaders) {
                $aMedia = self::$aHeaders;
                if (isset(self::$aHeaders['title'])) {
                    $sTitle = self::$aHeaders['title'];
                }
            } elseif (self::$oTopic) {
                // Получаем список media-данных из топика
                $aMedia=$this->PluginFacebook_Facebook_GetMedia(self::$oTopic,true);
                $sTitle = self::$oTopic->getTitle();
            }

            if ($aMedia) {
                switch($aMedia['type']) {
                    case 'video':
                        $aVideo=$aMedia;
                    break;
                    case 'image':
                        $sImage=$aMedia['picture'];
                    break;
                }
            } else {
                // В случае отсутствия media-данных подставляем картинку по умолчанию
                $sImage=Config::Get('plugin.facebook.default_post_image');
                // если не задана своя картинка, использовать дефолтную
                if (!$sImage) $sImage=Plugin::GetTemplateWebPath(__CLASS__).'images/default.jpg';
            }
            // Передаем в шаблон данные
            $this->Viewer_Assign('sTitle',$sTitle);
            $this->Viewer_Assign('sImage',$sImage);
            $this->Viewer_Assign('aVideo',$aVideo);
		    return $this->Viewer_Fetch(Plugin::GetTemplatePath('facebook').'/inject.header.tpl');
        }
}