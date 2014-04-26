<?php
class FileUpload extends FormElement {	
	/**
	 * Holds path to tmp-path where php
	 * uploaded a file
	 * @var string
	 */
	public $uploaded_file;
	
	/**
	 * Holds original filename provided
	 * by browser
	 * @var string
	 */
	public $filename;
	
	/**
	 * Max. filesize of an uploaded file
	 * 0 means no restriction (it still
	 * must be less than upload_max_size and
	 * max_post_size)
     * @var int
	 */
	public $maxsize;
	
	/**
	 * Constructor
	 * @param string Field name
	 * @param string Label
	 * @param boolean Required field?
	 */
	public function __construct($name, $label, $required = false) {
		$this->type		= 'file';
		$this->name		= $name;
		$this->label	= $label;
		$this->required	= $required;
	}
	
	public function setValue($string, $ignore_value = true) { }
	
	public function validate($input) {
		if(isset($_FILES[$this->name])) {
			// Check filesize
			if($this->maxsize > 0 && filesize($_FILES[$this->name]['tmp_name']) > $this->maxsize) {
				$this->error	= sprintf(System::getLanguage()->_('ErrorInvalidFilesize', Utils::formatBytes($this->maxsize)));
				return false;
			}
			
			$this->filename = $_FILES[$this->name]['name'];
			$this->uploaded_file = $_FILES[$this->name]['tmp_name'];
			
			switch($_FILES[$this->name]['error']) {
				case UPLOAD_ERR_OK: // Only throw Exception if error > 0
					return true;
					break;
				
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$this->error = sprintf(System::getLanguage()->_('ErrorInvalidFilesize'), Utils::formatBytes(self::getBytes(ini_get('upload_max_filesize'))));
					return false;
					break;
				
				case UPLOAD_ERR_NO_FILE:
					if($this->required == true) {
						$this->error = System::getLanguage()->_('ErrorNoFileSelected');
						return false;	
					}
					
					return true;
				
				default:
					$this->error = System::getLanguage()->_('ErrorNoFileSelected');
					return false;
					break;	
			}
		} else if(!empty($this->filename) && !empty($this->uploaded_file)) {
			return true;
		} else if($this->required == true) {
			$this->error = System::getLanguage()->_('ErrorNoFileSelected');
			return false;
		}
		
		return true;
	}
	
	protected function renderElement() {
		$html	= array();
		
		if(empty($this->uploaded_file)) {
			$attr	= array(
				'name'	=> $this->name,
				'id'	=> $this->getDOMId(),
				'class'	=> 'form-control'
			);

			$html[]	= '	<input type="file" '. $this->serialiseAttributes($attr). '/>';
		} else {
			$hidden = new Hidden($this->name, $this->label);
			$hidden->setValue($this->uploaded_file.'||'.$this->filename);
			
			$html[] = $hidden->render();
			
			$html[] = '	<label>'.$this->label.'</label>';
			
			$html[] = '	<p class="form-control-static">';
			$html[]	= '		'.System::getLanguage()->_('MessageFileAlreadySelected');
			$html[] = '	</p>';
		}
		
		return implode("\n", $html);
	}
	
	/* Stupid php
	 * Source: http://php.net/manual/de/function.ini-get.php
	 */
	private function getBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		
		return $val;
	}
}
?>