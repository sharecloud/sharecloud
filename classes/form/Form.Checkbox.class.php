<?php
class Checkbox extends FormElement {
	/**
	 * Checked-state of checkbox
	 * @var boolean
	 */
	public $checked = false;
	
	/**
	 * Constructor
	 * @param string Field name
	 * @param string Label
	 * @param boolean Required field?
	 */
	public function __construct($name, $label, $required = false) {
		$this->type		= 'checkbox';
		
		$this->name		= $name;
		$this->label	= $label;
		$this->required	= $required;
		
		$this->error_msg= System::getLanguage()->_('ErrorPleaseCheck');
	}
	
	public function validate($input) {
		$input	= (bool)$input;
		
		if((bool)$this->required == true && $input == false) {
			$this->error = $this->error_msg;
			return false;
		}
		
		return true;
	}
	
	public function setValue($value, $ignore_value = false) {
		if($this->checked == NULL || ($this->checked != NULL && $ignore_value == true)) {
			$this->checked = $value;
		}
	}
	
	public function render() {
		$html	= array();
		
		$html[] = '<div class="form-group'.(!empty($this->error) ? ' has-error' : '').'">';
		$html[] = '	<div class="col-sm-offset-2 col-sm-10">';
		$html[] = '		<label>';
		$html[] = '			<input type="checkbox" name="'.$this->name.'" id="'.$this->getDOMId().'" value="true" '.($this->checked == true ? 'checked="checked" ' : '').'/>';
		$html[] = '			'. $this->label;
		$html[] = '		</label>';
		
		if(!empty($this->error)) {
			$html[] = '		<span class="help-block">' . $this->error . '</span>';
		}
		
		$html[] = '	</div>';
		$html[] = '</div>';
		
		return implode("\n", $html);
	}
	
	protected function renderElement() { }
}
?>