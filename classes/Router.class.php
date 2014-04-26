<?php
final class Router {
	/**
	 * All routes
	 * @var RewriteRule[]
	 */
	private $routes	= array();
	
	/**
	 * Base directory
	 * @var string
	 */
	private $base_dir	= '/';
	
	/**
	 * Determines if Apache's mod_rewrite
	 * is enabled
	 * @var boolean
	 */
	private $mod_rewrite= false;
	
	/**
	 * Holds the currently executing controller
	 * & action (Controller#action)
	 * @static
	 * @var string
	 */
	private static $currentController = '';
	
	/**
	 * Initialises the router
	 *
	 * @param string Base directory
	 * @param boolean Determines if Apache's mod_rewrite is enabled
	 */
	public function init($base_dir = '/', $mod_rewrite = false) {
		if(strlen($base_dir) == 0) {
			$base_dir = '/';	
		} else {
			if(substr($base_dir, 0, 1) != '/') {
				$base_dir = '/' . $base_dir;	
			}
			
			if(substr($base_dir, -1) != '/') {
				$base_dir = $base_dir . '/';	
			}
			
			$base_dir = preg_replace('~(\/)+~u', '/', $base_dir);
		}
		
		$this->base_dir		= $base_dir;
		$this->mod_rewrite	= $mod_rewrite;
	}
	
	/**
	 * Adds a new RewriteRule
	 *
	 * @param string Pattern
	 * @param string Controller
	 * @param string Action
	 */
	public function addRoute($pattern, $controller, $action) {
		if(!$this->routeExists($pattern)) {
			if(substr($pattern, 0, 1) == '/') {
				$pattern	= substr($pattern, 1);
			}
			
			if($pattern == false) {
				$pattern = '';	
			}
			
			$this->routes[] = new Route($pattern, $controller, $action);
		}
	}
	
	/**
	 * Determines controller and action 
	 */
	public function run() {		
		$uri	= $this->getCurrentURI();
		
		$route	= NULL;
		$params	= array();
		
		// Try to match a pattern
		foreach($this->routes as $r) {
			// Case 1: no params => pattern == uri
			if($r->getPattern() == $uri) {
				$route = $r;
				break;
			}
			
			// Case 2: params - let's go
			$result	= Router::matchRoute($r, $uri);
			
			if($result !== false && is_array($result)) {
				$params	= $result;
				$route	= $r;
				
				// Don't break here: maybe there is a
				// route that matches better :D
			}
		}
		
		if($route != NULL) {
			$this->runController($route, $params);
		} else {
			System::displayError(System::getLanguage()->_('ErrorRouteNotFound'), '404 Not Found');	
		}
	}
	
	/**
	 * Builds an URI
	 *
	 * @param string Controller name
	 * @param string Action name
	 * @param object|string[] Parameters to be used to build the URI (if array, the keys match to the params in the according RewriteRule, if object, properties are matched to the params in RewriteRule) - default: NULL
	 */
	public function build($controller, $action, $params = NULL) {
		$route	= $this->getRoute($controller, $action);
		
		if($route == NULL) {
			return '#';	
		}
		
		$uri	= $route->getPattern();
		
		if(is_array($params)) {
			foreach($params as $key => $value) {
				$uri	= str_replace(':'.$key.':', $value, $uri);
			}
		} else if(is_object($params) && $params != NULL) {
			preg_match_all('~:(.*?):~u', $uri, $matches);
			
			$keys	= array();
			
			if(count($matches[0]) > 0) {
				foreach($matches[1] as $match) {
					$keys[] = $match;	
				}
				
				$class	= new ReflectionClass(get_class($params));
				
				foreach($keys as $key) {
					try {
						$property	= $class->getProperty($key);
						
						if($property->isPublic()) {
							$uri	= str_replace(':'.$key.':', $params->$key, $uri);
						} else if($class->hasMethod('get'.ucfirst($key))) {
							$uri	= str_replace(':'.$key.':', call_user_func(array($params, 'get'.ucfirst($key))), $uri);	
						} else if($class->hasMethod('__get')) {
							$uri	= str_replace(':'.$key.':', $params->__get($key), $uri);
						}
					} catch(Exception $e) {
						continue;	
					}
				}
			}
		}
		
		return System::getBaseURL() . '/' . ($this->mod_rewrite == false ? 'index.php/' : '') . $uri;
	}
	
	/**
	 * Determines if a route already exists
	 * 
	 * @param string Pattern
	 * @return boolean Result
	 */
	private function routeExists($pattern) {
		if(count($this->routes) == 0) {
			return false;	
		}
		
		// Remove / at the beginning
		if(substr($pattern, 0, 1) == '/') {
			$pattern = substr($pattern, 1);	
		}
		
		foreach($this->routes as $route) {
			if($route->getPattern() == $pattern) {
				return true;	
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieves a RewriteRule according to
	 * given controler and action
	 *
	 * @param string Controller name
	 * @param string Action name
	 * @return RewriteRule
	 */
	private function getRoute($controller, $action) {
		foreach($this->routes as $route) {
			if($route->getController() == $controller && $route->getAction() == $action) {
				return $route;	
			}
		}
		
		return NULL;
	}
	
	/**
	 * Calls an action inside a controller
	 * (if at least one of them is not present
	 * nothing happens)
	 * 
	 * @param Route Route
	 */
	private function runController(Route $route, array $params) {
		$controller	= $route->getController();
		$action	 = $route->getAction();
		
		Router::$currentController = $controller . '#' . $action;
		
		ControllerBase::runController($controller, $action, $params);
	}
	
	/**
	 * Returns the current URI
	 */
	private function getCurrentURI($uri = false) {
		if($uri === false) {		
			// Fetching the Current Request-URI
			$uri	= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		}
				
		// Remove base directory
		if(substr($uri, 0, strlen($this->base_dir)) == $this->base_dir) {
			$uri	= substr($uri, strlen($this->base_dir));
		}
		
		// Remove / at the beginning
		if(substr($uri, 0, 1) == '/') {
			$uri	= substr($uri, 1);
		}
		
		// If mod_rewrite is disabled, remove index.php
		if($this->mod_rewrite == false && substr($uri, 0, 10) == 'index.php/') {
			$uri	= substr($uri, 10);
		}
		
		return $uri;	
	}
	
	/**
	 * Tries to match a route to a given URI
	 *
	 * @static
	 * @param Route Route
	 * @param string URI
	 * @return boolean|string[] If route doesn't match, false is returned - otherwise it returns an array containing matched params
	 */
	private static function matchRoute(Route $route, $uri) {
		// We want to store the params giving by the URI
		$p_keys		= array();
		$p_values	= array();
		
		// Given pattern
		$pattern	= $route->getPattern();
		
		// Transform the given pattern into a regular expression
		$pattern_regexp	= preg_replace('~:(.*?):~u', '(.*?)', $pattern);
		
		// Customise pattern_regexp
		$pattern_regexp	= preg_replace('~(.*?)\(\.\*\?\)$~u', '$1(.*)', $pattern_regexp);
		
		// Retrieve all keys
		preg_match_all('~:(.*?):~u', $pattern, $matches);
		
		if(count($matches[0]) == 0) {
			// obviously the pattern doesn't contain
			// any params -> must have been matched in case 1
			return false;
		}
		
		foreach($matches[1] as $m) {
			$p_keys[]	= $m;
		}
		
		unset($matches);
		
		// Now try to match the URI and store its values
		preg_match_all('~^'.$pattern_regexp.'$~u', $uri, $matches);
		
		if(count($matches[0]) == 0) {
			// URI doesn't match -> go on!
			return false;
		}
		
		$count	= count($matches);
		for($i = 1; $i < $count; ++$i) {
			$p_values[] = $matches[$i][0];	
		}
		
		return Router::buildArray($p_keys, $p_values);
	}
	
	/**
	 * Builds an array with given keys and values
	 * Warning: count($keys) must equal count($values) -
	 * otherwise an empty array is returned
	 *
	 * @static
	 * @param string[] Array keys
	 * @param string[] Array values
	 * @return string[] Array
	 */
	private static function buildArray($keys, $values) {
		if(count($keys) != count($values)) {
			return array();	
		}
		
		$array	= array();
		$count	= count($keys);
		
		for($i = 0; $i < $count; ++$i) {
			$array[$keys[$i]] = $values[$i];
		}
		
		return $array;
	}
	
	/**
	 * Instance of Rewrite-Obj
	 * @static
	 * @var object
	 */
	private static $instance = NULL;
	
	/** 
	 * Returns the instance of Rewrite-obj
	 * @static
	 * @returns object
	 */
	public static function getInstance() {
		if(Router::$instance == NULL) {
			Router::$instance = new Router();	
		}
		
		return Router::$instance;
	}
	
	/**
	 * Returns current controller & action
	 * @static
	 * @return string[] array(Controller, Action);
	 */
	public static function getCurrentController() {
		if(strpos(Router::$currentController, '#') === false) {
			return array();
		}
		
		return explode('#', Router::$currentController);
	}
}

/**
 * Represents a RewriteRule
 */
final class Route {
	/**
	 * Pattern
	 * @var string
	 */
	private $pattern;
	
	/**
	 * Controller name
	 * @var string
	 */
	private $controller;
	
	/**
	 * Action name
	 * @var string
	 */
	private $action;
	
	/**
	 * Constructor
	 *
	 * @param string Pattern
	 * @param string Controller
	 * @param string Action
	 */
	public function __construct($pattern, $controller, $action) {
		$this->pattern		= $pattern;
		$this->controller	= $controller;
		$this->action		= $action;
	}
	
	/**
	 * @return string Pattern
	 */
	public function getPattern() {
		return $this->pattern;	
	}
	
	/**
	 * @return string Controller
	 */
	public function getController() {
		return $this->controller;	
	}
	
	/**
	 * @return string Action
	 */
	public function getAction() {
		return $this->action;	
	}
}
?>