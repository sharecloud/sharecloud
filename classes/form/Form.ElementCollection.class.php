<?php
abstract class ElementCollection {
	/**
	 * Holds global validation result
	 *
	 * @var boolean
	 */
	private $validation = true;
	
	/**
	 * Databound object
	 *
	 * @var object|NULL
	 */
	public $binding = NULL;
	
	/**
	 * Element collection
	 *
	 * @var object[]
	 */
	private $elements = array();
	
	/**
	 * Adds one or more elements to collection
	 *
	 * @param object Element 1
	 * @param object Element 2
	 * @param object Element n
	 */
	final public function addElements() {
		$args	= func_get_args();
		
		foreach($args as $element) {
			if($element instanceof ElementCollection) {
				$element->binding = $this->binding;
				$this->elements[] = $element;	
			} else if($element instanceof FormElement) {
				if(isset($_POST[$element->name])) {
					$element->setValue($this->getFormData($element->name, $element->cleanup), true);
				}
				
				if($element instanceof Checkbox) {
					if(Utils::getPOST($element->name, 'false') != "true") {
						$element->setValue(0, true);
					} else {
						$element->setValue(1, true);
					}
				}
				
				$this->elements[] = $element;
			}
		}
	}
	
	/**
	 * Returns collection of elements
	 * 
	 * @return object[]
	 */
	final public function getElements() {
		return $this->elements;	
	}
	
	/**
	 * Validates ALL elements within this collection
	 *
	 * @return boolean Global result (false if one element is validated as false, true otherwise)
	 */
	final public function validate() {
		foreach($this->elements as $element) {
			if($element instanceof Fieldset && $element->validate() == false) {
				$this->validation = false;	
			} else if($element instanceof FormElement && $element->validate($this->getFormData($element->name, $element->cleanup)) == false) {
				$this->validation = false;
			}
		}

		return $this->validation;
	}
	
	/**
	 * Returns value of $_POST[$name]
	 *
	 * @param string $name
	 * @param boolean Clean-Up (if true trim(strip_tags($value)) is executed)
	 * @param mixed Value
	 */
	final private function getFormData($name, $cleanup = true) {
		if(is_array(Utils::getPOST($name, false))) {
			$data	= array();
			
			foreach(Utils::getPOST($name, array()) as $value) {
				if($cleanup) {
					$data[] = trim(strip_tags($value));
				} else {
					$data[] = $value;
				}
			}
			
			return $data;
		} else {
			return ($cleanup ? trim(strip_tags(Utils::getPOST($name))) : Utils::getPOST($name));
		}
	}
	
	/**
	 * Returns an element from collection
	 *
	 * @param string Name
	 * @return object Element
	 */
	final public function getElement($name) {
		foreach($this->elements as $element) {
			if($element->name == $name) {
				return $element;	
			}
			
			if($element instanceof ElementCollection) {
				$result	= $element->getElement($name);
				
				if($result !== NULL) {
					return $result;	
				}
			}
		}
		
		return NULL;
	}
	
	/**
	 * Saves all form data to databound class (if available)
	 */
	final public function save() {
		if($this->binding != NULL) {
			foreach($this->elements as $element) {
				if($element->binding instanceof Databinding && (property_exists($this->binding, $element->binding->property) || method_exists($this->binding, '__set'))) {
					if($element->binding->direction == DatabindingDirection::SourceToDest) {
						continue;	
					}
					
					$b	= $element->binding->property;
					
					if($element instanceof Text || $element instanceof Textarea) {
						$this->binding->$b = $element->value;
						continue;
					}
					
					if($element instanceof Checkbox) {
						$this->binding->$b = $element->checked;
						continue;
					}

					if($element instanceof Select || $element instanceof Radiobox) {
						$this->binding->$b = $element->selected_value;
						continue;
					}
				}
				
				if($element instanceof Fieldset) {
					$element->save();	
				}
			}
		}
	}
	
	/**
	 * Fills form with data from databound object (if available)
	 */
	final public function fill() {
		if($this->binding != NULL) {
			foreach($this->elements as $element) {
				if($element->binding instanceof Databinding && (property_exists($this->binding, $element->binding->property) || method_exists($this->binding, '__get'))) {
					if($element->binding->direction == DatabindingDirection::DestToSource) {
						continue;	
					}		
					
					$b	= $element->binding->property;
					$element->setValue($this->binding->$b);
				}
				
				if($element instanceof Fieldset) {
					$element->fill();	
				}
			}
		}	
	}
	
	/**
	 * Checks if element's value is empty
	 *
	 * @param string Name
	 * @return boolean Result
	 */
	public function isEmpty($name) {
		$value	= $this->getFormData($name);
		
		return empty($value);
	}
}
?>