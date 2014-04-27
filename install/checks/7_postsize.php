<?php
final class MaxPOSTSize extends Check {
	const MIN_VALUE = 8192; // 8 MB
	
	public function __construct() {
		$this->caption = 'Max. HTTP POST size';	
	}
	
	public function performCheck() {
		$value = Utils::parseInteger(ini_get('post_max_size'));		
		
		if($value < self::MIN_VALUE) {
            $this->result = CheckResult::FAIL;
            $this->message = '<p>An max HTTP post size of '.ini_get('post_max_size').' (' . Utils::formatBytes($value) . ') is too small for a file hoster. It MUST be greater or equal to <code class="inline">upload_max_filesize</code>. Change <code class="inline">post_max_size</code> in your <code>php.ini</code>. 8M or more are recommend.</p>';
        } else {
            $this->result = CheckResult::OK;
			$this->message = '<p>Your current value: '.ini_get('post_max_size').' (' . Utils::formatBytes($value) . ')</p>';
			$this->message .= '<p>Change <code class="inline">post_max_size</code> in your <cody>php.ini</code> to modify this value.</p>';
        }	
	}
}

ConfigurationChecks::addCheck(new MaxPOSTSize);
?>