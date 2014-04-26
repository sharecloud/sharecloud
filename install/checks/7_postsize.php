<?php
final class MaxPOSTSize extends Check {
	public function __construct() {
		$this->caption = 'Max. HTTP POST size';	
	}
	
	public function performCheck() {
		if((substr(ini_get('post_max_size'), 0, -1) < 8) || (substr(ini_get('post_max_size'), 0, -1) < substr(ini_get('upload_max_filesize'), 0, -1))) {
            $this->result = CheckResult::FAIL;
            $this->message = '<p>An max HTTP post size of '.ini_get('post_max_size').' is too small for a file hoster. It MUST be greater or equal to <code class="inline">upload_max_filesize</code>. Change <code class="inline">post_max_size</code> in your <code>php.ini</code>. 8M or more are recommend.</p>';
        } else {
            $this->result = CheckResult::OK;
			$this->message = '<p>Your current value: '.ini_get('post_max_size').'</p>';
			$this->message .= '<p>Change <code class="inline">post_max_size</code> in your <cody>php.ini</code> to modify this value.</p>';
        }	
	}
}

ConfigurationChecks::addCheck(new MaxPOSTSize);
?>