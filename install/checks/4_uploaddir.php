<?php
final class UploadDirCheck extends Check {
	public function __construct() {
		$this->caption = 'Upload directory exists and is writable';	
	}
	
	public function performCheck() {
		if(!defined('FILE_STORAGE_DIR')) {
            $this->message = 'Check if you set the \'FILE_STORAGE_DIR\' setting in yout config.php';
			$this->result = CheckResult::FAIL;
			return;	
		}
		
		$dir = SYSTEM_ROOT . FILE_STORAGE_DIR;
		
		if(!is_dir($dir)) {
			// Try to create directory
			
			if(@mkdir($dir)) {
				if(is_writeable($dir)) {
					$this->result = CheckResult::OK;	
				} else if(!@chmod(SYSTEM_ROOT . '/uploads', 0600)){ // Try to make directory writeable
                    $this->message = '<p>Please make the folder</p><code>'. SYSTEM_ROOT . '/uploads</code><p>writable.</p>';
					$this->result = CheckResult::FAIL;
				} else {
					$this->result = CheckResult::OK;	
				}
			} else {
				$this->message = '<p>Please create the folder</p><code>'. SYSTEM_ROOT . '/uploads</code><p>and make it writable.</p>';	
				$this->result = CheckResult::FAIL;
			}
		} else {
			// Check permissions
			if(is_writeable($dir)) {
				$this->result = CheckResult::OK;	
			} else if (!@chmod(SYSTEM_ROOT . '/uploads', 0600)) {
				$this->result = '<p>Please make the folder</p><code>'. SYSTEM_ROOT . '/uploads</code><p>writable.</p>';
				$this->result = CheckResult::FAIL;
			} else {
				$this->result = CheckResult::OK;	
			}
		}
	}
}

ConfigurationChecks::addCheck(new UploadDirCheck);
?>