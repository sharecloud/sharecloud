<?php
class Fieldset extends ElementCollection {
	public $label;
	
	public function __construct($label) {
		$this->label = $label;
	}
	
	public function render() {
		$html	= array('<fieldset>');
		
		$html[]	= '<legend>'.$this->label.'</legend>';
		
		foreach($this->getElements() as $element) {
			$html[] = $element->render();
		}
		
		$html[]	= '</fieldset>';
		return implode("\n", $html);
	}
}
?>