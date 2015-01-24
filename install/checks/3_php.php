<?php
final class PHPCheck extends Check {
	public function __construct() {
		$this->caption = 'PHP Version';	
	}
	
	public function performCheck() {
		if(version_compare(PHP_VERSION, '5.3.2', '<')) {
            $this->message = '<p>Your PHP Version ('.PHP_VERSION.') is too old. You must update at least to PHP 5.3.2.</p>';
			$this->result = CheckResult::FAIL;
        } elseif(version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->message= '<p>Your PHP Version ('.PHP_VERSION.') is old but you can keep it. You should update to PHP 5.4.0 or higher.</p>';
            $this->result = CheckResult::OK;
        } elseif(version_compare(PHP_VERSION, '5.4.0', '>=')) {
			$this->message = '<p>Your running PHP Version: '.PHP_VERSION.'.</p>';
			$this->result = CheckResult::OK;
        }
	}
}

ConfigurationChecks::addCheck(new PHPCheck);
?>