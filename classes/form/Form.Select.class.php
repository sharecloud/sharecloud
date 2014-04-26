<?php
class Select extends FormElement {
	/**
	 * Width
	 *
	 * @var int
	 */
	public $width;
	
	/**
	 * Possible options
	 *
	 * @var string[]|string[][]
	 */
	public $options = array();
	
	/**
	 * Specifies if you can choose multiple items
	 */
	public $multiple = false;

	public $select_text = '';
	
	/**
	 * Selected value (or values if $this->multiple == true)
	 *
	 * @var string|string[]
	 */
	public $selected_value = false;
	
	/**
	 * Specifies if element is disabled
	 *
	 * @var boolean
	 */
	public $disabled = false;
	
	/**
	 * Constructor
	 * @param string Field name
	 * @param string Label
	 * @param string[]|string[][] Options
	 */
	public function __construct($name, $label, $options = array(), $select_text = '') {
		$this->name		= $name;
		$this->label	= $label;
		$this->select_text = $select_text;
		$this->required = false;
		
		$this->type = 'select';
		
		$this->error_msg= array(
			0	=> System::getLanguage()->_('ErrorChooseOption'),
			1	=> System::getLanguage()->_('ErrorInvalidOption')
		);
		
		if(!empty($select_text)) {
			$this->required = true;
		}
		
		if(is_array($options) && count($options) > 0) {
			$this->options = $options;
			return true;
		} else {
			return false;
		}
	}
	
	public function validate($input) {
		if($this->disabled == true) {
			return true;
		}
		
		if((bool)$this->required == true) {
			if(empty($input)) {
				$this->error = $this->error_msg[0];
				return false;
			}
		} else {
			if(empty($input)) {
				return true;
			}
		}
		
		if(is_array($input)) {
			foreach($input as $value) {
				if(!self::array_multi_key_exists($value, $this->options)) {
					$this->error = $this->error_msg[1];
					return false;
				}
			}
		} else {
			if(!self::array_multi_key_exists($input, $this->options)) {
				$this->error = $this->error_msg[1];
				return false;
			}
		}
		
		return true;
	}
	
	public function setValue($value, $ignore_value = false) {
		if(empty($this->selected_value) || (!empty($this->selected_value) && $ignore_value)) {
			
			if(is_array($value)) {
				$this->selected_value = array();
				
				foreach($value as $input) {
					if(!self::array_multi_key_exists($input, $this->options)) {
						$this->error = $this->error_msg[1];
					} else {					
						$this->selected_value[] = $input;
					}
				}
				
				return;
			}
						
			$this->selected_value = $value;
		}
	}
	
	public function select_all($ignore_value = false) {
		if(empty($this->selected_value) || (!empty($this->selected_value) && $ignore_value)) {
			
			if(is_array($value)) {
				$this->selected_value = array();
				
				foreach($value as $input) {
					if(!self::array_multi_key_exists($input, $this->options)) {
						$this->error = $this->error_msg[1];
						return;
					}
					
					$this->select_all2();
				}
				
				return;
			}
						
			$this->select_all2();
		}
	}
	
	private function select_all2() {
		foreach($this->options as $key => $value) {
			if(!is_array($value)) {
				$this->selected_value[] = $key;	
			} else {
				foreach($value as $k => $v) {
					$this->selected_value[] = $k;	
				}
			}
		}
	}
	
	protected function renderElement() {	
		$html	= array();
		
		$attr	= array(
			'name'	=> $this->name,
			'id'	=> $this->getDOMId(),
			'class'	=> 'form-control'
		);
		
		if($this->width > 0) {
			$attr['style']	= 'width: '.$this->width;
		}
		
		if($this->multiple == true) {
			$attr['multiple'] = 'multiple';
			$attr['name']	= $this->name.'[]';
			$attr['size']	= 10;
		}
		if($this->disabled == true) {
			$attr['disabled'] = 'disabled';
		}

		$html[]	= '	<select ' . $this->serialiseAttributes($attr) . '>';
		
		if(!empty($this->select_text) && $this->multiple == false) {
			$html[] = '		<option value="">'.$this->select_text.'</option>';
		}
		
		if(count($this->options) > 0) {
			foreach($this->options as $value => $option) {
				if(is_array($option)) {
					$html[] = '		<optgroup label="'.$value.'">';
					
					foreach($option as $val => $opt) {
						if($this->selected_value == $val || (is_array($this->selected_value) && in_array($val, $this->selected_value))) {
							$html[] = '			<option value="'.$val.'" selected="selected">'.$opt.'</option>';
						} else {
							$html[] = '			<option value="'.$val.'">'.$opt.'</option>';
						}
					}
					
					$html[] = '		</optgroup>';
				} else {
					if($this->selected_value == $value || (is_array($this->selected_value) && in_array($value, $this->selected_value))) {
						$html[] = '		<option value="'.$value.'" selected="selected">'.$option.'</option>';
					} else {
						$html[] = '		<option value="'.$value.'">'.$option.'</option>';
					}
				}
			}
		}
		
		$html[]	= '	</select>';
		
		return implode("\n", $html);
	}
	
	private static function array_multi_key_exists($needle, $haystack) {
		foreach($haystack as $key => $value) {
			if($needle == $key) {
				return true;
			}
			
			if(is_array($value)) {
				if(self::array_multi_key_exists($needle, $value)) {
					return true;
				}
			}
		}
		
		return false;
	}
}
?>