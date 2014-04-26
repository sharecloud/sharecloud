<?php
class Text extends FormElement {
	/**
	 * Min length
	 * @var int
	 */
	public $minlength = 0;
	
	/**
	 * Max length
	 * @var int
	 */
	public $maxlength = 0;
	
	/**
	 * Allowed input
	 * '*' or 'numeric'
	 * @var string
	 */
	public $valid;
		
	/**
	 * Blacklist
	 * @var string[]
	 */
	public $blacklist;
	
	/**
	 * Autofocus
	 * @var bool
	 */
	public $autofocus = false;
	
	/**
	 * Constructor
	 * @param string Field name
	 * @param string Label
	 * @param boolean Required field?
	 * @param string Allowed input
	 * @param int Max length
	 * @param int Min length
	 */
	public function __construct($name, $label, $required = false, $valid = '*', $maxlength = false, $minlength = false) {
		$this->type		= 'text';
		
		$this->name 	= $name;
		$this->label	= $label;
		$this->required = $required;
		$this->valid	= $valid;
		$this->maxlength= $maxlength;
		$this->minlength= $minlength;
		
		$this->error_msg= array(
			0 => System::getLanguage()->_('ErrorInvalidLengthMax'),
			1 => System::getLanguage()->_('ErrorInvalidLengthMin'),
			2 => System::getLanguage()->_('ErrorEmptyTextfield'),
			3 => System::getLanguage()->_('ErrorInvalidNumber'),
			4 => System::getLanguage()->_('ErrorInvalidInput')
		);
	}
	
	public function validate($input) {
		if(is_array($input)) {
			return true;	
		}
		
		if($this->callback != NULL) {
			$input	= call_user_func($this->callback, $input);
		}
		
		if(strlen($input) > 0) {		
			if(is_numeric($this->maxlength) && $this->maxlength > 0) {
				if(strlen($input) > $this->maxlength) {
					$this->error = sprintf($this->error_msg[0], $this->maxlength);
					return false;
				}
			}
			
			if(is_numeric($this->minlength) && $this->minlength > 0) {
				if(strlen($input) < $this->minlength) {
					$this->error = sprintf($this->error_msg[1], $this->minlength);
					return false;
				}
			}
		}
		
		if((bool)$this->required == true && strlen($input) == 0) {
			$this->error = $this->error_msg[2];
			return false;
		}
		
		if($this->valid != '*') {
			if($this->valid == 'numeric') {
				if(!is_numeric($input)) {
					$this->error = $this->error_msg[3];
					return false;
				}
			}
		}
		
		if(count($this->blacklist) > 0) {
			if(in_array($input, $this->blacklist)) {
				$this->error = $this->error_msg[4];
				return false;
			}
		}
		
		return true;
	}
	
	public function setValue($value, $ignore_value = false) {
		if(is_array($value)) {
			return;	
		}
		
		if(empty($this->value) || (!empty($this->value) && $ignore_value)) {
			$this->value = stripslashes($value);
		}
	}
	
	protected function renderElement() {
		$attr	= array(
			'name'	=> $this->name,
			'id'	=> $this->getDOMId(),
			'value'	=> $this->value
		);
		
		if($this->maxlength > 0) {
			$attr['maxlength']	= $this->maxlength;
		}
		
		if((bool)$this->required == true) {
			$attr['required'] = 'required';
		}
		
		if($this->autofocus == true) {
			$attr['data-autofocus'] = 'autofocus';	
		}
		
		if($this->valid == 'numeric' && ($this->minlength > 0 || $this->maxlength > 0)) {
			if($this->maxlength == 0) {
				$attr['pattern'] = '([0-9]{'.$this->minlength.'})';
				$attr['title']	 = 'Zahlenfeld (mind. '.$this->minlength.' Ziffern)';
			} elseif($this->minlength == 0) {
				$attr['pattern'] = '([0-9]{'.$this->maxlength.'})';
				$attr['title']	 = 'Zahlenfeld (max. '.$this->maxlength.' Ziffern)';
			} else {
				$attr['pattern'] = '([0-9]{'.$this->minlength.','.$this->maxlength.'})';
				$attr['title']	 = 'Zahlenfeld (mind. '.$this->minlength.', max. '.$this->maxlength.' Ziffern)';
			}
		}
		
		if($this->readonly == true) {
			$input	= '	<input type="hidden" '. $this->serialiseAttributes($attr) . '/>';
			$input .= '	<p class="form-control-static">' . $this->value . '</p>';
									
		} else {
			$input	= '	<input class="form-control" type="text" '. $this->serialiseAttributes($attr) . '/>';
		}
		
		return $input;
	}
}
?>