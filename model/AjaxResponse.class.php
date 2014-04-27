<?php
final class AjaxResponse {
	public $success = true;
	public $message = '';
	
	public $data = NULL;
	
	public function __toString() {
		return json_encode($this);
	}
	
	public function send() {
		
		exit($this);
		
	}
}
?>