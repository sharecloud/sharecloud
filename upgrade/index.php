<?php
error_reporting(E_ALL);
define('SYSTEM_ROOT', realpath(dirname(__FILE__) . '/../'));

require_once SYSTEM_ROOT . '/classes/smarty/Smarty.class.php';
require_once SYSTEM_ROOT . '/classes/AutoloadHelper.class.php';
require_once SYSTEM_ROOT . '/install/ConfigurationChecks.class.php';

if(file_exists(SYSTEM_ROOT . '/system/config.php')) {
	require_once SYSTEM_ROOT . '/system/config.php';	
} else {
	header('Location: install.php');
	exit;	
}

$autoload = AutoloadHelper::getInstance();
$autoload->addPattern('Form.%s.class.php');
$autoload->addDirectory(
	SYSTEM_ROOT . '/classes',
	SYSTEM_ROOT . '/classes/form'
);
spl_autoload_register(array($autoload, 'invoke'));

final class Upgrade {
	public static function run($action = '') {
		$smarty = new Smarty();
        
		$smarty->muteExpectedErrors();
		
		$smarty->setCacheDir(SYSTEM_ROOT . '/classes/smarty/cache/');
		$smarty->setCompileDir(SYSTEM_ROOT . '/classes/smarty/templates_c/');
		$smarty->setTemplateDir(SYSTEM_ROOT . '/upgrade/view/');
		
        $smarty->caching = Smarty::CACHING_OFF;
        $smarty->force_compile = true;
        
		$smarty->assign('title', 'Upgrade');
		
		switch($action) {
			case 'success':
				self::success($smarty);
				break;
			
			default:
				self::selectUpgrade($smarty);
				break;
		}
	}
	
	private static function selectUpgrade(Smarty $smarty) {
		$upgrades = array();
		
		foreach(Utils::getFilelist(SYSTEM_ROOT . '/upgrade/scripts/') as $file) {
			if(preg_match('~upgrade-(.*?)-(.*?).php~', $file, $matches)) {
				$upgrades[$file] = "Version " . $matches[1] . " -> " . $matches[2];	
			}
		}
		
		if(Utils::getPOST('submit', false) !== false) {
			$upgrade = Utils::getPOST('upgrade', '');
			
			if(array_key_exists($upgrade, $upgrades) && file_exists(SYSTEM_ROOT . '/upgrade/scripts/' . $upgrade)) {
				include_once SYSTEM_ROOT . '/upgrade/scripts/' . $upgrade;
			}
		}
		
		$smarty->assign('upgrades', $upgrades);	
		$smarty->assign('heading', 'Select upgrade');	
		$smarty->assign('curStep', 1);
		$smarty->display('upgrade.tpl');	
	}
	
	private static function success(Smarty $smarty) {
		$smarty->assign('heading', 'Upgrade finished');
		$smarty->assign('curStep', 2);
		
		$smarty->display('success.tpl');
	}
}

Upgrade::run(Utils::getGET('action', ''));
?>