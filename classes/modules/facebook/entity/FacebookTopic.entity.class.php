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
class PluginFacebook_ModuleFacebook_EntityFacebookTopic extends Entity
{    
	public function GetFacebookLink() {

        $_aPublishId = explode('_',$this->GetPublishId());

        return 'http://www.facebook.com/permalink.php?story_fbid='.$_aPublishId[1].'&id='.$_aPublishId[0];
    }
}
?>