<?php
/**
 * Wrapper for Smarty template engine
 */
final class Template extends Smarty {
    
    private $JSRMS;
    
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
	    
        $this->JSRMS = new JSRMS();
        $this->JSRMS->requireResource('system');
        
		$this->muteExpectedErrors();
	
		$this->setCacheDir(SYSTEM_ROOT . '/classes/smarty/cache/');
		$this->setCompileDir(SYSTEM_ROOT . '/classes/smarty/templates_c/');
		$this->setTemplateDir(SYSTEM_ROOT . '/view/');
		
		$this->registerObject('Router', Router::getInstance(), array('build'), false);
		$this->registerObject('L10N', System::getLanguage(), array('_'), false);
		
		$this->assign('LoggedIn', System::getUser() != NULL);
		$this->assign('User', System::getUser());
        
		$this->assign('Navigation', Navigation::$elements);
		
		$this->assign('LangStrings', System::getLanguage()->getAllStrings());
		
		// Configuration
		$this->assign('HTTP_BASEDIR', System::getBaseURL());
		$this->assign('MOD_REWRITE', MOD_REWRITE);
		
		if(System::getSession()->getData('successMsg', '') != '') {
			$this->assign('successMsg', System::getSession()->getData('successMsg', ''));
			
			System::getSession()->setData('successMsg', '');
		}
		
		if(System::getSession()->getData('errorMsg', '') != '') {
			$this->assign('errorMsg', System::getSession()->getData('errorMsg', ''));
			
			System::getSession()->setData('errorMsg', '');
		}
		
		if(System::getSession()->getData('infoMsg', '') != '') {
			$this->assign('infoMsg', System::getSession()->getData('infoMsg', ''));
			
			System::getSession()->setData('infoMsg', '');
		}
	}

    public function display($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL) {
        $this->assign('resources', $this->JSRMS->renderHTMLTags());
        
        parent::display($template, $cache_id, $compile_id, $parent);
    }
    
    public function requireResource($name) {
        return $this->JSRMS->requireResource($name);
    }
    
}
?>