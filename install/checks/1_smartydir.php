<?php
final class SmartyDirCheck extends Check {
	public function __construct() {
		$this->caption = 'Smarty template_c folder is writable';
	}
	
	public function performCheck() {
		$dir = SYSTEM_ROOT . '/classes/smarty/templates_c/';
		
		if(!is_writable($dir)) {
			die("<html><body>It's required to make ".$dir." writeable!<br />On Linux try <code>chmod 777 ".$dir."</code><br />Don't worry! After this, installation get's much more prettier!</body></html>");
		}
		
		$this->result = CheckResult::OK;
	}
}

ConfigurationChecks::addCheck(new SmartyDirCheck);
?>