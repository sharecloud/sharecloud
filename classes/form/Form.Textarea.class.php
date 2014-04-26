<?php
class Textarea extends FormElement {
	/**
	 * Width
	 * 
	 * @var int
	 */
	public $width;
	
	/**
	 * Specifies if newer browsers
	 * can resize textarea
	 *
	 * @var boolean
	 */
	public $resize;
	
	/**
	 * Amount of columns
	 * @var int
	 */
	public $columns;
	
	/**
	 * Amount of rows
	 * @var int
	 */
	public $rows;
	
	/**
	 * Constructor
	 * @param string Field name
	 * @param string Label
	 * @param boolean Required field?
	 */
	public function __construct($name, $label, $required = false) {
		$this->type		= 'textarea';
		
		$this->name 	= $name;
		$this->label	= $label;
		$this->required = $required;
		
		$this->columns	= 30;
		$this->rows		= 10;
		
		$this->error_msg= System::getLanguage()->_('ErrorEmptyTextfield');
	}
	
	public function validate($input) {
		if((bool)$this->required == true && empty($input)) {
			$this->error = $this->error_msg;
			return false;
		}
		
		return true;
	}
	
	public function setValue($value, $ignore_value = false) {
		if(empty($this->value) || (!empty($this->value) && $ignore_value)) {
			$this->value = stripslashes($value);
		}
	}
	
	protected function renderElement() {
		$attr	= array(
			'name'	=> $this->name,
			'id'	=> $this->getDOMId(),
			'cols'	=> $this->columns,
			'rows'	=> $this->rows
		);
		
		if((bool)$this->required == true) {
			$attr['required'] = 'required';
		}
		
		if($this->resize == false) {
			$attr['style'] = 'resize: none; ';
		}
		
		if($this->width > 0) {
			$attr['style'] .= 'width: '.$this->width;
		}
		
		return '	<textarea '. $this->serialiseAttributes($attr). '>'.$this->value.'</textarea>';
	}
}
?>