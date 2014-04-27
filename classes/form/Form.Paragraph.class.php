<?php
class Paragraph extends FormElement {
	public $content;
	
	public function __construct($content) {
		$this->content = $content;
	}
	
	public function setValue($value, $ignore_value = false) { }
	
	public function validate($input) {
		return true;
	}
	
	public function render() {		
		$content = '<p>'.$this->content.'</p>';
		$content = str_replace('<p></p>', '', $content);
	
		return $content;
	}
	
	protected function renderElement() { }
}

?>