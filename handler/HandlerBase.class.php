<?php
/**
 * Base class for DownloadHandler
 * every handler must extend this class
 */
abstract class HandlerBase {
	/**
	 * Registered extensions
	 * @var string[]
	 */
	private $extensions = array();
	
	/**
	 * Registered MIME
	 * @var string[]
	 */
	private $mimes = array();
	
	/**
	 *The smarty var
	 *@var Smarty
	*/
	protected $smarty = NULL;
	
	/**
	 *The current file
	 *@var File
	 */
	protected $file = NULL;
	
	/**
	 * Construct
	 * Will register the Class
	 */
	public function __construct() {
		DownloadHandler::registerHandler($this);
	}
	
	/**
	 * Registers an extension (or bunch of extensions)
	 * @param string|string[] Extension(s)
	 */
	public function registerExtension($ext) {
		if(is_array($ext)) {
			$this->extensions = $ext;
		} else {
			$this->extensions = array($ext);
		}
        
        $mimes = array();
        
        foreach($this->extensions as $key => $value) {
            if(array_key_exists($value, MIMETypes::$map)) {
                $mimes = array_merge($mimes, array(MIMETypes::$map[$value]));
            }
        }
        
        $this->registerMIME($mimes);
	}
	
	/**
	 * Registers a MIME type (or bunch of types)
	 * @param string|string[] MIME(s)
	 */
	protected function registerMIME($mimes) {
		if(is_array($mimes)) {
			$this->mimes = array_merge($this->mimes, $mimes);
		} else {
			$this->mimes[] = $mimes;	
		}
	}
	
	/**
	 * Checks if an extension is registered
	 * in this handler
	 * @param string Extension
	 * @return bool Result
	 */
	public function isRegisteredExtension($ext) {
		return in_array($ext, $this->extensions);
	}
	
	/**
	 * Checks if a MIME type is registered
	 * in this handler
	 * @param string MIME type
	 * @return bool Result
	 */
	public function isRegisteredMIME($mime) {
	    return in_array($mime, $this->mimes);
	}
	
	/**
	 * Display this class
	 */
	private function prepareSmarty() {	
		$this->smarty = new Template();
		
		$this->smarty->assign('heading', $this->file->filename);
		$this->smarty->assign('title', $this->file->filename);
		$this->smarty->assign('file', $this->file);
		$this->smarty->requireResource('file');
        
		$select = new Select('permission', '', FilePermissions::getAll());
		$select->selected_value = $this->file->permission->level;
		$this->smarty->assign('permission', $select->render());
	}
	
	/**
	 * Invokes Handler: prepares Smarty, calls Setup-function and
	 * finally invokes handler
	 * @param object File
	 */
	public function invoke(File $file) {
		$this->file = $file;
		
		$this->prepareSmarty();
		$this->setup();
		$this->invokeHandler();
	}
	
	/**
	 * Function called before invokeHandler()
	 * so handler can setup data
	 */
	protected function setup() { }
	
	/**
	 * Invokes specific handler
	 * @abstract
	 */
	protected abstract function invokeHandler();
}
?>