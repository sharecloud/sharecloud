<?php 
class LogController extends ControllerBase {
	protected function onBefore($action = '') {
		parent::checkIfAdmin();
	}
	
    public function index() {
        $entries = LogEntry::find('log', 'system');
		if($entries == NULL) {
			$entries = array();	
		} else if($entries instanceof LogEntry) {
			$entries = array($entries);	
		}
		usort($entries, array('LogEntry', 'compare'));
        
        $smarty = new Template();
        $smarty->assign('entries', $entries);
        $smarty->assign('showPHPEntries', false);
        $smarty->assign('title', System::getLanguage()->_('Log'));
        $smarty->assign('heading', System::getLanguage()->_('SystemEntries'));
        
        $smarty->display('log/log.tpl');
    }
    
    public function clear() {
        LogEntry::deleteAll();
        Log::sysLog('LogController', 'Log table cleared');
        
        System::forwardToRoute(Router::getInstance()->build('LogController', 'index'));
    }
    
    public function php() {
		parent::checkAuthentification();
		parent::checkIfAdmin();
		
        $entries = LogEntry::find('log', 'php');
		if($entries == NULL) {
			$entries = array();	
		} else if($entries instanceof LogEntry) {
			$entries = array($entries);	
		}
		usort($entries, array('LogEntry', 'compare'));
        
        $smarty = new Template();
        $smarty->assign('entries', $entries);
        $smarty->assign('title', System::getLanguage()->_('Log'));
        $smarty->assign('heading', System::getLanguage()->_('PHPEntries'));
        $smarty->assign('showPHPEntries', true);
        
        $smarty->display('log/log.tpl');
    }
}
?>