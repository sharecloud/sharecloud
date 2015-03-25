<?php
final class System {
	/**
	 * Holds current database connection
	 * @var object
	 */
	private static $database = NULL;
	
	/**
	 * Holds current session
	 * @var object
	 */
	private static $session = NULL;
	
	/**
	 * Holds current user (if available)
	 * @var object
	 */
	private static $user = NULL;
	
	/**
	 * Navigation
	 * @var object
	 */
	private static $navigation = NULL;
	
	/**
	 * Language
	 * @var object
	 */
	private static $language = NULL;
    
	/**
	 * Is XHR/API request?
	 * @var boolean
	 */
	public static $isXHR = false;
	
	/**
	 * Initialises the system
	 * @static
	 */
	public static function init() {
		self::redirectHTTPS();		
		Router::getInstance()->init(HOST_PATH, MOD_REWRITE);
		
		if(defined('DATABASE_SOCKET') && DATABASE_SOCKET != '') {
			self::$database = new Database('mysql:dbname='.DATABASE_NAME.';unix_socket='.DATABASE_SOCKET, DATABASE_USER, DATABASE_PASS);
		} else {
			self::$database = new Database('mysql:dbname='.DATABASE_NAME.';host='.DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
		}
		
		self::$session = new Session();
		self::$user = (System::getSession()->getUID() != NULL ? User::find('_id', System::getSession()->getUID()) : NULL);
		self::$language = new L10N(System::getUser() != NULL ? System::getUser()->lang : LANGUAGE);
		self::buildNavigation();
	}
	
	/**
	 * Builds navigation
	 */
	private static function buildNavigation() {
		if(self::getUser() != NULL) {
			Navigation::addElement(new NavigationElement(System::getLanguage()->_('Files'), 'BrowserController', 'index', true, 'hdd-o'));
			
			if(self::getUser()->isAdmin) {
				Navigation::addElement(new NavigationElement(System::getLanguage()->_('Users'), 'UsersController', 'index', true, 'users'));
                Navigation::addElement(new NavigationElement(System::getLanguage()->_('Log'), 'LogController', 'index', true, 'bullhorn'));
				Navigation::addElement(new NavigationElement(System::getLanguage()->_('Admin'), 'AdminController', 'index', true, 'th-large'));                
			}
		}
	}
	
	/**
	 * Gets Database connection
	 * @return object PDO
	 */
	public static function getDatabase() {
		return self::$database;	
	}
	
	/**
	 * Gets Session
	 * @return object
	 */
	public static function getSession() {
		return self::$session;	
	}
	
	/**
	 * Gets current user
	 * @return object
	 */
	public static function getUser() {
		return self::$user;	
	}
	
	/**
	 * Gets Language
	 * @return object
	 */
	public static function getLanguage() {
		return self::$language;	
	}
    
	/**
	 * Gets base URL
	 * @return string URL
	 */
	public static function getBaseURL() {
		if(Utils::isSSL() || (defined('HTTPS') && HTTPS == true)) {
			$url = 'https://'; 
		} else {
			$url = 'http://';	
		}
		
		$url .= HOST_NAME . HOST_PATH;
		
		if(substr($url, -1) == '/') {
			$url = substr($url, 0, -1);
		}
		
		return $url;
	}
	
	/**
	 * Run the core system
	 */
	public static function run() {
		Router::getInstance()->run();	
	}
	
	/**
	 * Throws a global error
	 * @param string Error string
	 * @param string HTTP status code (Format: XXX Status Name, where XXX is numeric status code)
	 */
	public static function displayError($error, $status = NULL) {
		if($status == NULL) {
			header('HTTP/1.0 500 Internal Server Error');	
		} else {
			header('HTTP/1.0 '.$status);	
		}
		
		if(System::$isXHR) {
			echo '{"success": false, "message": '. json_encode($error) .'}';
			exit;
		}
		
		try {
			$smarty = new Template();
			$smarty->assign('title', System::getLanguage()->_('Error'));
			$smarty->assign('heading', System::getLanguage()->_('Error'));
			
			$smarty->assign('message', $error);
			
			$smarty->display('error.tpl');
			
			exit;
		} catch(Exception $e) {
			die($error);
		}
	}
	
	/**
	 * Handle uncaught exceptions
	 */
	public static function handleException() {
		try {
			if(System::getLanguage() == NULL) {
				throw new Exception();	
			}
			
			$msg = System::getLanguage()->_('UnknownError');	
		} catch(Exception $e) {
			$msg = 'An unknown error occured.';	
		}
		
		self::displayError($msg);	
	}
	
	/**
	 * Redirects to HTTPS site if HTTPS is enabled
	 * in config.php and the user visits this script
	 * using HTTP
	 */
	public static function redirectHTTPS() {
		if(defined('HTTPS') && HTTPS == true && !Utils::isSSL() && isset($_SERVER['REQUEST_URI'])) {
			// HTTPS is enabled in config and currently not used by user -> redirect
			header('Location: https://' . HOST_NAME . $_SERVER['REQUEST_URI']);
			exit;
		}
	}
    
    /**
     * Get Global Preference by Key
     * @param string the key
     * @return mixed the value
     */
     public static function getPreference($key) {
         return System::getPreferences()->getValue($key);
     }
     
     /**
      * @param string a Route created by Router::getInstance()->build()
      * @return void will exit Application and forward to Route
      */
      public static function forwardToRoute($route) {
          header('Location: '.$route);
          exit;
      }
}
?>
