<?php
final class AutoloadHelper {
	/**
	 * Holds all directories where 
	 * classes are searched
	 * @var string[]
	 */
	private $directories = array();
	
	/**
	 * Holds all patterns to resolve the
	 * filename that belongs to a classname
	 * @var string[]
	 */
	private $pattern = array();
	
	/**
	 * Holds only instance of AutoloadHelper
	 * @static
	 * @var object
	 */
	private static $instance = NULL;
	
	/**
	 * Returns only instance of AutoloadHelper
	 * @static
	 * @return object
	 */
	public static function getInstance() {
		if(self::$instance == NULL) {
			self::$instance = new AutoloadHelper();	
		}
		
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->addPattern('%s.class.php');
	}
	
	/**
	 * Adds one ore more directories to
	 * the list of directories to search in
	 * @param string Directory 1
	 * @param string Directory 2 etc.
	 */
	public function addDirectory() {
		$args = func_get_args();
		
		if(count($args) > 0) {
			foreach($args as $arg) {
				if(!in_array($arg, $this->directories)) {
					if(substr($arg, -1) != '/') {
						$arg .= '/';
					}
					
					$this->directories[] = $arg;	
				}
			}
		}
	}
	
	/**
	 * Adds a pattern to resolve the filename of a class
	 * - %s => $classname
	 * - %l => strtolower($classname)
	 * - %u => strtoupper($classname)
	 * @param string Pattern
	 */
	public function addPattern($pattern) {
		$this->pattern[] = $pattern;
	}
	
	/**
	 * Tries to load a class
	 * @param string Class name
	 * @throws AutoloadException
	 */
	public function invoke($class) {
		if(count($this->directories) == 0) {
			//throw new AutoloadException($class);
			return false;
		}
		
		$files	= array();
		
		if(count($this->pattern) > 0) {
			foreach($this->pattern as $pattern) {
				$files[] = str_replace(
								array('%s', '%u', '%l'),
								array($class, strtoupper($class), strtolower($class)),
								$pattern);
			}
		}
		
		foreach($this->directories as $dir) {
			foreach($files as $file) {
				if(file_exists($dir . $file)) {
					include_once $dir . $file;
					return true;
				}
			}
		}
		
		//throw new AutoloadException($class);
		return false;
	}
}

class AutoloadException extends Exception {
    public function __construct($class) {
        $this->message = 'Could not load class: '.$class;   
    }
}

?>