<?php
final class L10N {
	/**
	 * List of available languages
	 * @static
	 * @var string[]
	 */
	private static $available = array();
	
	/**
	 * Current language
	 * @var string
	 */
	private $current;
	
	/**
	 * List of all language strings
	 * @var string[]
	 */
	private $strings = array();
	
	/**
	 * Constructor
	 * @param string Language (abbrev.) to load
	 * @throws InvalidArgumentException
	 */
	public function __construct($language) {
		if($language == NULL) {
			$language = LANGUAGE;	
		}
		
		if(array_key_exists($language, L10N::$available)) {
			$this->current = $language;
			
			$ini = SYSTEM_ROOT . '/languages/'.$this->current.'/'.$this->current.'.ini';
			
			if(file_exists($ini)) {
				$this->strings = parse_ini_file($ini, false);
				
				if(!is_array($this->strings)) {
					throw new InvalidLanguageFileException();	
				}
			} else {
				throw new InvalidLanguageFileException();	
			}
		} else {
			throw new LanguageNotFoundException();
		}
	}
	
	/**
	 * Gets a language string by given key
	 * @param string Key
	 * @param mixed Param 1
	 * @param mixed Param 2 ...
	 * @return string String
	 */
	public function _() {
		$args = func_get_args();
		$argc = count($args);
		
		
		if($argc == 0) {
			throw new InvalidArgumentException('Missing argument $key');	
		}
		
		$key = $args[0];
		$param = array();
		
		for($i = 1; $i < $argc; ++$i) {
			$param[$i] = $args[$i];	
		}
		
		if(array_key_exists($key, $this->strings)) {
			if(count($param) > 0) {
				$param[0] = $this->strings[$key];
				ksort($param);
				
				return call_user_func_array('sprintf', $param);
			}
			
			return $this->strings[$key];
		} else {
			// Exception??
			return $key;
		}
	}
	
	/**
	 * Gets a list of all available keys and strings
	 * @return string[]
	 */
	public function getAllStrings() {
		return $this->strings;	
	}
	
	/**
	 * Registers a language
	 * @static
	 * @param string Abbreviation (like en, de, ..)
	 * @param string Full language name (like English, German,...)
	 */
	public static function registerLanguage($abbreviation, $language) {
		if(!array_key_exists($abbreviation, L10N::$available)) {
			L10N::$available[$abbreviation] = $language;	
		}
	}
	
	/**
	 * Returns list of available languages
	 * @return string[] Languages
	 */
	public static function getLanguages() {
		return L10N::$available;	
	}
}


?>