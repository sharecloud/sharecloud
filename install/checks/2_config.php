<?php
final class ConfigCheck extends Check {
	public function __construct() {
		$this->caption = 'Configuration file';	
	}
	
	public function performCheck() {		
		if(!file_exists(SYSTEM_ROOT . '/system/config.php')) {
			$this->message = '<p>Please move</p><code>'. SYSTEM_ROOT . '/system/config.php.sample</code><p>to</p><code>'. SYSTEM_ROOT . '/system/config.php</code><p>and prepare it with your data</p>';
			
			$this->result = CheckResult::FAIL;
		} else {
			$this->result = CheckResult::OK;	
		}
	}
}

ConfigurationChecks::addCheck(new ConfigCheck);
?>