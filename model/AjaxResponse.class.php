<?php
final class AjaxResponse {
	public $success = true;
	public $message = '';
	
	public $data = NULL;
	
	public function __toString() {
		return json_encode($this);
	}
	
	public function send() {
		header('Content-Type: application/json');
		echo $this;
		
		exit();
	}
}
?>