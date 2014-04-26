<?php
final class DefaultHandler extends HandlerBase {
	public function __construct() {
		parent::__construct();
	}
	
	protected function invokeHandler() {
		$this->smarty->display('handler/default.tpl');
	}
}
?>