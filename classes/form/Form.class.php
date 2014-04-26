<?php
/** 
 * Form
 */
class Form extends ElementCollection {
	/**
	 * Form name
	 *
	 * @var string
	 */
	private $name = '';
	
	/**
	 * List of HTML attributes
	 *
	 * @var string[]
	 */
	private $attributes = array();
	
	/**
	 * Encoding-Type
	 *
	 * @var string
	 */
	private $enctype = '';
	
	/**
	 * URL des Formular-Ziels
	 *
	 * @var string
	 */
	private $action = '';
	
	/**
	 * GET oder POST
	 *
	 * @var string
	 */
	private $method = '';
	
	/**
	 * Additional buttons
	 *
	 * @var mixed[]
	 */
	private $buttons = array();
	
	/**
	 * Submit-Button
	 *
	 * @var object
	 */
	private $submit;
	
    /**
     * Name of Submit button in HTML
     * 
     * @var string
     */
    private $submitName;
    
	/**
	 * Constructor
	 *
	 * @param string Name des Formulars
	 * @param string Formular-Ziel
	 * @param string Formular-Typ (POST)
	 */
	public function __construct($name, $action = '', $method = 'post') {
		$this->name	      = $name;
		$this->action     = $action;
		$this->method     = strtolower($method);
		
		$this->submit     = new Button(System::getLanguage()->_('Submit'));
        $this->submitName = 'submit';
	}
	
	/**
	 * Sets Encoding-Type
	 *
	 * @var string Encodingtype
	 */
	public function setEnctype($enctype = '') {
		if(empty($enctype)) {
			$this->enctype = 'multipart/form-data';
		} else {
			$this->enctype = $enctype;
		}
	}
	
	/**
	 * Adds an button
	 *
	 * @param object Button
	 */
	public function addButton(Button $button) {
		$this->buttons[] = $button;
	}
	
	/**
	 * Sets HTML attribute
	 * @param string Attribute name
	 * @param string Value
	 */
	public function setAttribute($key, $value) {
		$this->attributes[$key] = $value;	
	}
	
	/**
	 * Overrides submit button
	 *
	 * @param object Button
	 * @param string Name property of submit button (default: submit)
	 */
	public function setSubmit(Button $button, $submitName = 'submit') {
		$this->submit = $button;
        $this->submitName = $submitName;
	}
	
	protected static function serialiseAttributes($attr) {
		$html = '';
		
		foreach($attr as $a => $v) {
			$html .= $a.'="'.$v.'" ';
		}
		
		return $html;
	}
	
	/**
	 * Renders the form
	 *
	 * @return string HTML source code
	 */	
	public function render() {
		$html	= array();
		
		if(empty($this->action)) {
			$this->action = sprintf('%s://%s%s', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http'), $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
		}
		
		$attr	= array(
			'name'	=> $this->name,
			'class'	=> 'form-horizontal ' . $this->name,
			'action'=> $this->action,
			'method'=> $this->method,
			'role'	=> 'form'
		);
		
		if(count($this->attributes) > 0) {
			foreach($this->attributes as $key => $value) {
				$attr[$key] = $value;
			}
		}
		
		if(!empty($this->enctype)) {
			$attr['enctype'] = $this->enctype;
		}
		
		$html[]	= '<form ' . $this->serialiseAttributes($attr) . '>';
		
		foreach($this->getElements() as $element) {
			$html[] = $element->render();
		}
		
		$html[] = '<input type="hidden" name="'.$this->submitName.'" value="submit" />';

		$html[] = '<div class="buttons">';
		
		if($this->submit instanceof Button) {
			$html[] = '	<button class="btn btn-primary btn-sm" type="submit">' . ($this->submit->class != '' ? '<span class="glyphicon glyphicon-'.$this->submit->class.'"> </span> ' : '') . $this->submit->caption . '</button>';
		}
		
		foreach($this->buttons as $button) {
			$html[] = '	<a class="btn btn-default btn-sm" role="button" href="'.$button->url.'">' . ($button->class != '' ? '<span class="glyphicon glyphicon-'.$button->class.'"> </span> ' : '') . $button->caption . '</a>';
		}
		
		$html[] = '</div>';
		
		$html[] = '</form>';
		$html[] = '';
		
		return implode("\n", $html);
	}
	
	/**
	 * toString()
	 *
	 * @return string HTMl-Quelltext
	 */
	public function __toString() {
		return $this->render();
	}
}

final class Button {
	public $caption;
	public $url;
	public $class;
	
	public function __construct($caption, $class = '', $url = '') {
		$this->caption = $caption;
		$this->url = $url;
		$this->class = $class;	
	}
}
?>