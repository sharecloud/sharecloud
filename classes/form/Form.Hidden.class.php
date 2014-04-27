<?php
class Hidden extends Text {
	/**
	 * Constructor
	 * @param string Field name
	 */
	public function __construct($name, $value = '') {
		parent::__construct($name, '');
		
		$this->type		= 'hidden';	
		$this->value	= $value;
	}
	
	public function render() {
		$attr	= array(
			'name'	=> $this->name,
			'id'	=> $this->getDOMId(),
			'value'	=> $this->value
		);
		
		return '	<input type="hidden" ' . $this->serialiseAttributes($attr) . '/>';
	}
	
	protected function renderElement() { }
}
?>