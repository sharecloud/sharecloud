<?php
final class HomeController extends ControllerBase {
	public function onBefore($action = '', array $params) {
		parent::checkAuthentification();	
	}
	
	public function index() {
		System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
		exit;	
	}
}
?>