<?php
final class MaxUploadFilesizeCheck extends Check {
	const MIN_VALUE = 8192; // 8 MB
	
	public function __construct() {
		$this->caption = 'Max. Upload Filesize';	
	}
	
	public function performCheck() {
		$value = Utils::parseInteger(ini_get('upload_max_filesize'));
		
		if($value < self::MIN_VALUE) {
            $this->result = CheckResult::FAIL;
            $this->message = '<p>An max upload size of '.ini_get('upload_max_filesize').' (' . Utils::formatBytes($value) . ') is too small for a file hoster. Change <code class="inline">upload_max_filesize</code> in your <code class="inline">php.ini</code>. 8M or more are recommend</p>';
            
        } else {
            $this->result = CheckResult::OK;
			$this->message = '<p>Your current value: '.ini_get('upload_max_filesize').' (' . Utils::formatBytes($value) . ')</p>';
        }
	}
}

ConfigurationChecks::addCheck(new MaxUploadFilesizeCheck);
?>