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
class PluginFacebook_BlockFacebook extends Block {

        public function Exec() {
            $this->Viewer_Assign('sFacebookTemplateWebPathPlugin',Plugin::GetTemplateWebPath(__CLASS__));    
        }

}