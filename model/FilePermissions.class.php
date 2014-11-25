<?php
final class FilePermissions {
	const PUBLIC_ACCESS = 1;
	const RESTRICTED_ACCESS = 2;
	const PRIVATE_ACCESS = 3;
	
	public static function parse($input) {
		switch($input) {
			case 1:
				return FilePermissions::PUBLIC_ACCESS;
				
			case 2:
				return FilePermissions::RESTRICTED_ACCESS;
				
			case 3:
				return FilePermissions::PRIVATE_ACCESS;	
				
			default:
				throw new InvalidArgumentException('$input has wrong format');
		}
	}
	
	public static function tryParse($input) {
		try {
			$result = self::parse($input);			
			return true;
		} catch(InvalidArgumentException $e) {
			return false;	
		}
	}
	
	public static function getAll() {
		return array(
			FilePermissions::PUBLIC_ACCESS => System::getLanguage()->_('PermissionPublic'),
			FilePermissions::RESTRICTED_ACCESS => System::getLanguage()->_('PermissionProtected'),
			FilePermissions::PRIVATE_ACCESS => System::getLanguage()->_('PermissionPrivate')
		);
	}
}
?>