<?php
final class ImagickCheck extends Check {
	public function __construct() {
		$this->caption = 'Imagick';	
	}
	
	public function performCheck() {
		if(extension_loaded('imagick') && class_exists('Imagick')) {
            $this->result = CheckResult::OK;
        } else {
            $this->result = CheckResult::POOR;
            $this->message = '<p>Imagick is not installed. Please install Imagick for the full experience.</p><p>On Ubuntu run</p><code>sudo apt-get update &#38;&#38; sudo apt-get install imagemagick libmagickwand-dev libmagickcore4 libmagickwand4 php5-imagick</code><p>to install Imagick.</p>';
        }	
	}
}

ConfigurationChecks::addCheck(new ImagickCheck, true);
?>