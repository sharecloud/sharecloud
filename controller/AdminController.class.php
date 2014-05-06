<?php
final class AdminController extends ControllerBase {
	const UPDATE_CHECK = 'https://raw.githubusercontent.com/sharecloud/sharecloud/master/VERSION';
	
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
		parent::checkIfAdmin();
	}
	
	public function index() {
		// Get users
		$users = User::find('*', NULL, array('orderby' => '_id', 'sort' => 'DESC'));
		
		// If there is only one user		
		if(!is_array($users)) {
			$users = array($users);
		}
		
		// Get files
		$files = File::find('*');
		$num_users = count($users);
		
		if($num_users == 0) {
			$files_per_user = 0;
		} else {
			$files_per_user = round(count($files) / $num_users , 1);
		}
		
		// MIME statistics
		$sql = System::getDatabase()->query('SELECT COUNT(*) AS num, mime FROM files GROUP BY mime');
		$mimes = array();
		
		while($mime = $sql->fetch(PDO::FETCH_OBJ)) {
			$mimes[] = $mime;	
		}
		
		usort($mimes, function($a, $b) {
			if($a->num == $b->num) return 0;
			return ($a->num >= $b->num ? -1 : 1);
		});
		
		// Quota
		$used_space = 0;
		$available_space = disk_free_space(SYSTEM_ROOT . FILE_STORAGE_DIR);
		
		$userByQutoa = array();
		
		foreach($users as $user) {
			$used = $user->getUsedSpace();
			$used_space += $used;
			
			$obj = new Object();
			$obj->user = $user;
			$obj->used = $used;
			
			$userByQutoa[] = $obj;
		}
		
		usort($userByQutoa, function($a, $b) {
			if($a->used == $b->used) return 0;
			return ($a->used >= $b->used ? -1 : 1);
		});
		
		// Version
		$version = file_get_contents(SYSTEM_ROOT . '/VERSION');
		$phpversion = phpversion();
		
		$res = System::getDatabase()->query('SELECT VERSION() as mysql_version');
		$row = $res->fetch(PDO::FETCH_ASSOC);
		
		if(!isset($row['mysql_version'])) {
			$mysqlversion = System::getLanguage()->_('Unknown');
		} else {
			$mysqlversion = $row['mysql_version'];		
		}
		
		// Extensions
		$imagick = extension_loaded('imagick') && class_exists('Imagick');
		$rar = extension_loaded('rar') && class_exists('RarArchive');
		
		$maxpost = Utils::parseInteger(ini_get('post_max_size'));	
		$maxupload = Utils::parseInteger(ini_get('upload_max_filesize'));
		
		$smarty = new Template();
        $smarty->assign('title', System::getLanguage()->_('Admin'));
		$smarty->assign('heading', System::getLanguage()->_('Admin'));
		
		$smarty->assign('users', $users);
		$smarty->assign('files', $files);
		$smarty->assign('userByQutoa', $userByQutoa);		
		$smarty->assign('mimes', $mimes);
		
		$smarty->assign('filesPerUser', $files_per_user);
		$smarty->assign('usedSpace', $used_space);
		$smarty->assign('availableSpace', $available_space);
		
		$smarty->assign('version', $version);
		$smarty->assign('phpversion', $phpversion);
		$smarty->assign('mysqlversion', $mysqlversion);
		$smarty->assign('maxpost', $maxpost);
		$smarty->assign('maxupload', $maxupload);
		
		$smarty->assign('imagick', $imagick);
		$smarty->assign('rar', $rar);
		
		$smarty->requireResource('admin');
		
		$smarty->display('admin/index.tpl');		
	}
	
	public function updateCheck() {
		$response = new AjaxResponse();
		
		try {
			$remoteVersion = Utils::getRequest(self::UPDATE_CHECK);	
			$currentVersion = file_get_contents(SYSTEM_ROOT . '/VERSION');
			
			$rVersion = (int)str_replace('.', '', $remoteVersion);
			$cVersion = (int)str_replace('.', '', $currentVersion);
			
			$result = new Object();
			$result->isUpdateAvailable = ($rVersion > $cVersion);
					
			$response->success = true;
			$response->data = $result;
		} catch(RequestException $e) {
			$response->success = false;	
		}
		
		$response->send();
	}
}
?>