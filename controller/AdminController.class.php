<?php
final class AdminController extends ControllerBase {
	const UPDATE_CHECK = 'https://github.com/sharecloud/sharecloud/raw/master/VERSION';
	
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
		parent::checkIfAdmin();
	}
	
	public function index() {
		
		// Get files
		
		$sql = System::getDatabase()->query('SELECT u._id, u.username, u.firstname, u.lastname, IFNULL(SUM(f.size), 0) AS totalUserSize FROM users u LEFT JOIN files f ON u._id = f.user_ID GROUP BY u._id');
		
		$quotaByUser = array();
		$used_space = 0;
		$num_users = 0;
		
		while($user = $sql->fetch(PDO::FETCH_OBJ)) {
			
			$used_space += $user->totalUserSize;
			
			
			$obj = new Object();
			$obj->username = $user->username;
			$obj->firstname = $user->firstname;
			$obj->lastname = $user->lastname;
			
			$obj->used = $user->totalUserSize;
			
			$userByQutoa[] = $obj;
			$num_users++;
		}
		
		$sql = System::getDatabase()->query('SELECT count(*) AS num_files from files');
		
		$num_files = $sql->fetch(PDO::FETCH_OBJ);
		$num_files = $num_files->num_files;
		
		
		if($num_users == 0) {
			$files_per_user = 0;
		} else {
			$files_per_user = round($num_files / $num_users , 1);
		}
		
		// Newest User
		$newUsers = User::find('*', NULL, array('orderby' => '_id', 'sort' => 'DESC'));

		
		if(!is_array($newUsers)) {
			$newUsers = array($newUsers);
		}
		
		
		// MIME statistics
		$sql = System::getDatabase()->query('SELECT COUNT(*) AS num, mime FROM files GROUP BY mime ORDER BY num DESC LIMIT 6');
		$mimes = array();
		while($mime = $sql->fetch(PDO::FETCH_OBJ)) {
			$mimes[] = $mime;
		}
		
		// Quota
		$available_space = disk_free_space(SYSTEM_ROOT . FILE_STORAGE_DIR);
		
		// Version
		$version = file_get_contents(SYSTEM_ROOT . '/VERSION');
		$phpversion = phpversion();
		
		$res = System::getDatabase()->query('SELECT VERSION() AS mysql_version');
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
		
		$smarty->assign('num_users', $num_users);
		$smarty->assign('num_files', $num_files);
		
		
		$smarty->assign('newUsers', $newUsers);		
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
			
			$result = new Object();
			$result->isUpdateAvailable = version_compare($remoteVersion, $currentVersion, '>');
					
			$response->success = true;
			$response->data = $result;
		} catch(RequestException $e) {
			$response->success = false;	
		}
		
		$response->send();
	}
}
?>