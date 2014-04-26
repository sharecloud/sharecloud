<?php
class Password extends Text {
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
		parent::__construct($name, $label, $required, $valid, $maxlength, $minlength);
		
		$this->cleanup	= false;		
		$this->type		= 'password';
	}
	
	protected function renderElement() {
		$attr	= array(
			'name'	=> $this->name,
			'id'	=> $this->getDOMId()
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
		
		return '	<input class="form-control" type="password" ' . $this->serialiseAttributes($attr) . '/>';
	}
}
?>