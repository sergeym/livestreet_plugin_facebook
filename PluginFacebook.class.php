<?php
/**
 *
 * @author Sergey Marin <http://sergeymarin.com>
 */
class PluginFacebook extends Plugin {

    const STRATEGY_DIRECT=1; // отправлять на стену сразу после публикации
    const STRATEGY_MAIN=2; // отправлять когда топик появится на главной
    const STRATEGY_RATING=3; // отправлять после получения получения определенного количества голосов
    const STRATEGY_WAIT=4; // отсылать после таймаута

        /**
         * Активация плагина
         * @return boolean
         */
	public function Activate() {
		if (!$this->isTableExists('prefix_plugin_facebook_queue')) {
			/**
			 * При активации выполняем SQL дамп
			 */
			$this->ExportSQL(dirname(__FILE__).'/dump.sql');
		}
		return true;
	}

        /**
         * Инициализация плагина
         * @return void
         */
	public function Init() {
        $this->Viewer_AppendStyle(Plugin::GetTemplateWebPath('facebook').'css/index.css');
        $this->Viewer_AppendScript(Plugin::GetTemplateWebPath('facebook').'js/facebook.js');
	}

        /**
         * Деактивация плагина
         * @return boolean
         */
	public function Deactivate() {
		return true;
	}
}