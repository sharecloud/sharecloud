<?php
/**
 * Represents a form element
 */
abstract class FormElement {
	/**
	 * Name
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * Label
	 *
	 * @var string
	 */
	public $label;
	
	/**
	 * Value
	 *
	 * @var mixed
	 */
	public $value;
	
	/**
	 * Required element?
	 * 
	 * @var boolean
	 */
	public $required = false;
	
	/**
	 * Current error string
	 *
	 * @var string
	 */
	public $error;
	
	/**
	 * Collection of error strings
	 *
	 * @var string[]
	 */
	public $error_msg = array();
	
	/**
	 * Read-only?
	 *
	 * @var boolean
	 */
	public $readonly = false;
	
	/**
	 * Callback function for setValue()
	 *
	 * @var object
	 */
	public $callback = NULL;
	
	/** 
	 * Property name of databound object
	 *
	 * @var string
	 */
	public $binding  = NULL;
	
	/**
	 * Specifies if input value must be cleaned
	 *
	 * @var boolean
	 */
	public $cleanup = true;
	
	/**
	 * Element type (text, textarea, select, ...)
	 *
	 * @var string
	 */
	public $type = '';
	
	/**
	 * Validates a input value
	 *
	 * @abstract
	 * @param mixed Input value
	 * @return boolean Result
	 */
	abstract public function validate($input);
	
	/**
	 * Sets current value
	 *
	 * @abstract
	 * @param mixed Value
	 * @param booean Force override value
	 */
	abstract public function setValue($value, $ignore_value = false);
	
	/**
	 * Renders an element (shall not be overriden!)
	 *
	 * @final
	 * @return string HTML source code
	 */
	public function render() {
		$html	= array();
		
		$html[] = '<div class="form-group'.(!empty($this->error) ? ' has-error' : '').'">';
		
		if(strlen($this->label) > 0) {
			if($this->readonly == false) {
				$html[]	= '	<label class="col-sm-2 control-label" for="'.$this->getDOMId().'">'.$this->label.': '.((bool)$this->required == true ? '*' : '').'</label>';
			} else {
				$html[]	= '	<label class="col-sm-2 control-label">'.$this->label.': '.((bool)$this->required == true ? '*' : '').'</label>';
			}
		}
		
		$html[] = '	<div class="col-sm-10">';
		
		$html[] = $this->renderElement();
		
		if(!empty($this->error)) {
			$html[] = '	<span class="help-block">'.$this->error.'</span>';
		}
		
		$html[] = '	</div>';
		$html[] = '</div>';
		
		return implode("\n", $html);
	}
	
	/**
	 * Renders specific element (override this!)
	 *
	 * @return string HTML source code
	 */
	protected function renderElement() { }
	
	/**
	 * Returns ID for HTML DOM
	 *
	 * @return string DOM ID
	 */
	protected function getDOMId() {
		return $this->type.'-'.$this->name;	
	}
	
	/**
	 * Serialises attributes
	 *
	 * @static
	 * @param string[] Attributes
	 * @param string HTML source code
	 */
	protected static function serialiseAttributes($attr) {
		$html = '';
		
		foreach($attr as $a => $v) {
			$html .= $a.'="'.$v.'" ';
		}
		
		return $html;
	}
}
?>