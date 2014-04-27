<?php
final class ConfigurationChecks {
	public static $checks = array();
	public static $extensions = array();
	
	private static $result = NULL;
	
	public static function loadChecks() {
		foreach(Utils::getFilelist(SYSTEM_ROOT . '/install/checks') as $file) {
			include_once SYSTEM_ROOT . '/install/checks/'.$file;	
		}
	}
	
	public static function addCheck(Check $check, $extension = false) {
		if($extension === true) {
			self::$extensions[] = $check;	
		} else {
			self::$checks[] = $check;		
		}
	}
	
	public static function performChecks() {
		$result = CheckResult::OK;
		
		foreach(self::$checks as $check) {
			if($check instanceof Check) {
				$check->performCheck();
				
				if($check->result == CheckResult::FAIL) {
					$result = CheckResult::FAIL;	
				} else if($check->result == CheckResult::POOR && $result != CheckResult::FAIL) {
					$result = CheckResult::POOR;	
				}
			}
		}
		
		foreach(self::$extensions as $check) {
			if($check instanceof Check) {
				$check->performCheck();
			}
		}
		
		self::$result = $result;
		
		return $result;
	}
	
	public static function getResult() {
		if(self::$result == NULL) {
			self::performChecks();	
		}
		
		return self::$result;
	}
}

abstract class Check {
	public $caption;
	public $result;
	public $message = '';
	
	abstract public function performCheck();
}

final class CheckResult {
	const OK = 0;
	const POOR = 1;
	const FAIL = 2;
}
?>