<?php
final class MaxUploadFilesizeCheck extends Check {
	public function __construct() {
		$this->caption = 'Max. Upload Filesize';	
	}
	
	public function performCheck() {
		if(substr(ini_get('upload_max_filesize'), 0, -1) < 8) {
            $this->result = CheckResult::FAIL;
            $this->message = '<p>An max upload size of '.ini_get('upload_max_filesize').' is too small for a file hoster. Change <code class="inline">upload_max_filesize</code> in your <code class="inline">php.ini</code>. 8M or more are recommend</p>';
            
        } else {
            $this->result = CheckResult::OK;
			$this->message = '<p>Your current value: '.ini_get('upload_max_filesize').'</p>';
        }
	}
}

ConfigurationChecks::addCheck(new MaxUploadFilesizeCheck);
?>