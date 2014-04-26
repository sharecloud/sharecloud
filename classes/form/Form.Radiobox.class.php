<?php
class Radiobox extends FormElement {
	/**
	 * Items
	 *
	 * @var string[]
	 */
	public $items;
	
	/**
	 * Selected value
	 * 
	 * @var string
	 */
	public $selected_value;
	
	/**
	 * Construct
	 * @param string Field name
	 * @param string Label
	 * @param boolean Required field?
	 * @param string Selected value 
	 */
	public function __construct($name, $label, $items = array(), $selected_value = NULL) {
		$this->type		= 'radio';
		
		$this->name		= $name;
		$this->label	= $label;
		$this->items	= $items;
		$this->selected_value = $selected_value;
	}
	
	public function validate($input) {
		if(!array_key_exists($input, $this->items)) {
			$this->error = $this->error_msg;
			return false;
		}
		
		return true;
	}
	
	public function setValue($value, $ignore_value = true) {
		if(array_key_exists((string)$value, $this->items)) {
			$this->selected_value = $value;
		}
	}
	
	protected function renderElement() {		
		$html	= array();
		
		$html[] = '<div class="radio">';
		
		$i	= 0;
		foreach($this->items as $value => $label) {
			$html[] = '<div class="radio">';
			$html[] = '	<label>';
			$html[] = '		<input type="radio" name="'.$this->name.'" id="'.$this->getDOMId().'-'.$value.'" value="'.$value.'" '.($value == $this->selected_value ? 'checked="checked" ' : '').' />';
			$html[] = '		' . $label;			
			$html[] = '	</label>';	
			$html[] = '</div>';
			$i++;
		}
		
		$html[] = '</div>';
		
		return implode("\n", $html);
	}
}
?>