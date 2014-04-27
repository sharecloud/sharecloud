<?php
final class OfficeHandler extends HandlerBase {
    public function __construct() {
        parent::__construct();
        parent::registerMIME("application/vnd.openxmlformats-o");
    }
    
    protected function invokeHandler() {
        
        
        $error = array();
        if(Utils::isLocalhostServer()) {
            $error[] = System::getLanguage()->_('NoLocalhost');
        } 
        
        if($this->file->permission->level != FilePermissions::PUBLIC_ACCESS) {
            $error[] = System::getLanguage()->_('OnlyPublicFiles');
        }
        
        $router = Router::getInstance();
        $link = $router->build('DownloadController', 'raw', $this->file);
        $link = "http://view.officeapps.live.com/op/view.aspx?src=".urlencode($link);
        
        $this->smarty->assign('error', join('<br>', $error));
        $this->smarty->assign('link', $link);
        $this->smarty->display('handler/office.tpl');
    }
}

new OfficeHandler;

?>