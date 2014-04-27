<?php
/**
 * Represents Navigation
 */
final class Navigation {
	/**
	 * Holds all elements
	 * @var object[]
	 */
	public static $elements = array();
	
	/**
	 * Adds an element to navigation
	 * @param object Element
	 */
	public static function addElement(NavigationElement $element) {
		self::$elements[] = $element;
	}
}

/**
 * Represents an element in a navigation
 */
final class NavigationElement {
	/**
	 * Label
	 * @var string
	 */
	private $label	= '';
	
	/**
	 * Controller
	 * @var string
	 */
	private $controller = '';
	
	/**
	 * Action
	 * @var string
	 */
	private $action = '';
	
	/**
	 * Constructor
	 * @param string Label
	 * @param string Controller
	 * @param string Action
	 */
	public function __construct($label, $controller = '', $action = '') {
		$this->label = $label;
		$this->controller = $controller;
		$this->action = $action;	
	}
	
	/**
	 * Checks if element is currently opened
	 * @final
	 * @return bool Ergebnis
	 */
	final public function isCurrent() {		
		$controller = $action = '';
		$info = Router::getCurrentController();
		
		if(count($info) == 2) {
			list($controller, $action) = $info;
			
			return ($controller == $this->controller);
		}
		
		return false;
	}
	
	/**
	 * Getter
	 */
	public function __get($property) {
		if(property_exists($this, $property)) {
			return $this->$property;	
		}
		
		throw new InvalidArgumentException();
	}
}
?>