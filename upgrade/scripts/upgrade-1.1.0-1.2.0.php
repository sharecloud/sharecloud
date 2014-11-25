<?php
$basepath = SYSTEM_ROOT . '/upgrade/scripts/';

try {
	$sql = file_get_contents($basepath . 'upgrade-1.1.0-1.2.0-pre.sql');
	$db = new Database('mysql:dbname='.DATABASE_NAME.';host='.DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
	$db->exec($sql);
	
	$sql = $db->query('SELECT _id, password, salt, permission FROM file_permissions');
	
	while($row = $sql->fetch()) {
		$query = $db->prepare('UPDATE files SET password = :password, salt = :salt, permission = :permission WHERE file_permissions_ID = :id');
		$query->execute(array(
			':id' => $row['_id'],
			':password' => $row['password'],
			':salt' => $row['salt'],
			':permission' => $row['permission']
		));
	}
	
	$sql = file_get_contents($basepath . 'upgrade-1.1.0-1.2.0-post.sql');
	$db->exec($sql);
	
	header('Location: index.php?action=success');
	exit;
} catch(PDOException $e) {
	$smarty->assign('heading', 'Error during upgrade');
	$smarty->assign('error', $e->getMessage());
	$smarty->assign('url', 'index.php');
	$smarty->assign('curStep', 1);
	
	$smarty->display('error.tpl');
	exit;
}
?>