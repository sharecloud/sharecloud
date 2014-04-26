<?php
final class RARCheck extends Check {
	public function __construct() {
		$this->caption = 'RAR';	
	}
	
	public function performCheck() {
		if(extension_loaded('rar') && class_exists('RarArchive')) {
            $this->result = CheckResult::OK;
        } else {
            $this->result = CheckResult::POOR;
            $this->message = '<p>RarArchive is not installed. Please install RarArchive for the full experience.</p>On Linux run</p><code>pecl -v install rar</code><p>to install RarArchive. You might add</p><code>extension=rar.so</code><p>to your <code class="inline">php.ini</code> file.</p>';
        }	
	}
}

ConfigurationChecks::addCheck(new RARCheck, true);
?>