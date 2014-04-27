<?php
/**
 * Base class for all controllers
 */
abstract class ControllerBase {
	/** 
	 * Params given by URL
	 * @var string[]
	 */
	private $params = array();
	
	/**
	 * Setter for params
	 */
	public function __set($property, $value) {
		if(!property_exists($this, $property)) {
			throw new InvalidArgumentException('Property '.$property.' does not exist (class: '.get_class($this).')');
		}
		
		if($property == 'params' && count($this->params) == 0 && is_array($value)) {
			$this->params = $value;
		}
	}
	
	/**
	 * Getter
	 * @obsolete
	 */
	public function __get($property) {
		if(!property_exists($this, $property)) {
			throw new InvalidArgumentException('Property '.$property.' does not exist (class: '.get_class($this).')');
		}
		
		return $this->$property;
	}
	
	/**
	 * Checks if user is authentificated
	 * if not - user is redirected to login page
	 */
	public final function checkAuthentification() {
		if(System::getUser() == NULL) {
			if(System::$isXHR) {
				System::displayError(System::getLanguage()->_('PermissionDenied'), '403 Forbidden');
			} else {			
				System::forwardToRoute(Router::getInstance()->build('AuthController', 'login'));
				exit;
			}
		}
	}
	
	/**
	 * Checks if user is an admin
	 * if not - HTTP 403 is shown
	 */	
	public final function checkIfAdmin() {
		if(System::getUser() == NULL || !System::getUser()->isAdmin) {
			System::displayError(System::getLanguage()->_('PermissionDenied'), '403 Forbidden');	
		}
	}
	
	/**
	 * Gets a parameter
	 * @param string Param name
	 * @param mixed Default value (if param does not exist)
	 * @return mixed Value
	 */
	public final function getParam($key, $default = false) {
		if(array_key_exists($key, $this->params)) {
			return $this->params[$key];
		}
		
		return $default;
	}
	
	/**
	 * Stuff to be executed before controller actions is executed
	 * @param string Current action
	 * @param string[] Parameters
	 */
	protected function onBefore($action = '') { }
	
	/**
	 * Stuff to be executed after controller action was executed
	 * @param string Current action
	 * @param string[] Parameters
	 */
	protected function onFinished($action = '') { }
	
	/**
	 * Run a specific controller
	 * @static
	 * @final
	 * @param string Controller name
	 * @param string Action
	 * @param mixed Params
	 */
	public final static function runController($controller, $action, array $params) {
		if(class_exists($controller, true) && method_exists($controller, $action)) {
			$c = new $controller;
			$c->params = $params;
			
			call_user_func(array($c, 'onBefore'), $action);
			call_user_func(array($c, $action));
			call_user_func(array($c, 'onFinished'), $action);
		} else {
			// Show error message or redirect to home page
			die('Controller not found');
		}
	}
}
?>