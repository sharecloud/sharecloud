<?php

/**
 * Define the root path to the system
 */
define('SYSTEM_ROOT', realpath(dirname(__FILE__) . '/../'));


/**
 * Include system configurations
 */
 
if(!file_exists(SYSTEM_ROOT . '/system/config.php')) {
    header('Location: ./install/');
    exit;   
} else {
    require_once SYSTEM_ROOT . '/system/config.php';
} 

if(!defined('DEV_MODE')) {
    define('DEV_MODE', false);
}

date_default_timezone_set(SERVER_TIMEZONE);

// Error Handling
if(DEV_MODE) {
    ini_set('error_reporting','E_ALL');
    ini_set('display_errors','On');
    ini_set('display_startup_errors','On');
    error_reporting(-1); // Even better then E_ALL
} else {
    ini_set('error_reporting','0');
    ini_set('display_errors','Off');
    ini_set('display_startup_errors','Off');
    error_reporting(0);    
}

/**
 * Include Exceptions
 */
require_once SYSTEM_ROOT . '/classes/Exceptions.class.php';


/**
 * Include all relevant system classes
 */
require_once SYSTEM_ROOT . '/classes/AutoloadHelper.class.php';

/**
 * Setting up autoload directories
 */
$autoload = AutoloadHelper::getInstance();
$autoload->addPattern('Form.%s.class.php');
$autoload->addDirectory(
	SYSTEM_ROOT . '/controller',
	SYSTEM_ROOT . '/model',
	SYSTEM_ROOT . '/view',
	SYSTEM_ROOT . '/classes',
	SYSTEM_ROOT . '/classes/form'
);
spl_autoload_register(array($autoload, 'invoke'));

/**
 * Loading Logger
 */
require_once SYSTEM_ROOT . '/classes/Log.class.php';

/**
 * 3rd party libraries
 */
require_once SYSTEM_ROOT . '/classes/smarty/Smarty.class.php';

require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/getid3.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.archive.gzip.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.archive.rar.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.archive.szip.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.archive.tar.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.archive.zip.php';
require_once SYSTEM_ROOT . '/classes/3rdparty/getid3/module.misc.iso.php';

require_once SYSTEM_ROOT . '/classes/3rdparty/JSRMS/JSRMS.php';

require_once SYSTEM_ROOT . '/classes/3rdparty/parsedown/Parsedown.php';

/**
 * Include all handler
 */
require_once SYSTEM_ROOT . '/handler/HandlerBase.class.php';
require_once SYSTEM_ROOT . '/handler/SourceCodeHandler.class.php';
require_once SYSTEM_ROOT . '/handler/MarkdownHandler.class.php';
require_once SYSTEM_ROOT . '/handler/ImageHandler.class.php';
require_once SYSTEM_ROOT . '/handler/PDFHandler.class.php';
require_once SYSTEM_ROOT . '/handler/MediaHandler.class.php';
require_once SYSTEM_ROOT . '/handler/CompressedHandler.class.php';
require_once SYSTEM_ROOT . '/handler/OfficeHandler.class.php';
require_once SYSTEM_ROOT . '/handler/DefaultHandler.class.php';

/**
 * Include imagick imageresizer
 * if imagick is available
 */
if(extension_loaded('imagick') && class_exists('Imagick')) {
	require_once SYSTEM_ROOT . '/classes/ImageResize.Imagick.class.php';
}

/**
 * Load language files
 */
foreach(Utils::getDirectorylist(SYSTEM_ROOT . '/languages/') as $lang) {
	$langfile = SYSTEM_ROOT . '/languages/'.$lang.'/'.$lang.'.php';
	
	if(file_exists($langfile)) {
		require_once $langfile;
	}
}

if((is_dir(SYSTEM_ROOT . '/install') || is_dir(SYSTEM_ROOT . '/upgrade')) && !DEV_MODE) {
	System::displayError('For security reasons, please delete the <code>install/</code> and <code>upgrade/</code> directory.');	
}

// Init routing
require_once SYSTEM_ROOT . '/system/routing.php';
?>