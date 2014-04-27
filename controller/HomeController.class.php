<?php
final class HomeController extends ControllerBase {
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
	}
	
	public function index() {
		System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
		exit;	
	}
}
?>