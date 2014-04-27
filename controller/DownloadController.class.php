<?php
final class DownloadController extends ControllerBase {
	private $file = NULL;
	
	private function loadFile() {
		if($this->file != NULL) {
			return;	
		}
		
		$this->file = File::find('alias', $this->getParam('alias', ''));
		
		if($this->file == NULL) {
			System::displayError(System::getLanguage()->_('ErrorFileNotFound') , '404 Not Found');
		}
        
        if(System::getUser() != NULL) {
            $user_id = System::getUser()->uid;
        } else {
            $user_id = -1;
        }

        if($user_id != $this->file->uid) {
            if($this->file->permission->level == FilePermissions::PRIVATE_ACCESS) {
				System::displayError(System::getLanguage()->_('PermissionDenied'), '403 Forbidden');
                exit;
            } elseif ($this->file->permission->level == FilePermissions::RESTRICTED_ACCESS) {
                if(is_array(System::getSession()->getData("authenticatedFiles"))) {
                    if(!in_array($this->file->alias, System::getSession()->getData("authenticatedFiles"))) {
                        System::forwardToRoute(Router::getInstance()->build('AuthController', 'authenticateFile', $this->file));
                        exit;   
                    }
                } else {
                    System::forwardToRoute(Router::getInstance()->build('AuthController', 'authenticateFile', $this->file));
                    exit;
                }
            }
        }
        
	}
	
	public function show() {
		$this->loadFile();
		$handler = DownloadHandler::getHandler($this->file->ext, $this->file->mime);
		$handler->invoke($this->file);	
	}
	
	public function raw() {
		$this->loadFile();
		
		$this->file->download(true);
	}
	
	public function embed() {
		$this->loadFile();
		
		$this->file->download(true, false);	
	}
	
	public function force() {
		$this->loadFile();
		
		$this->file->download();
	}
	
	public function resize() {
		$this->loadFile();
		$imageResize = new ImageResize($this->file);
		$imageResize->resize(900);
	}
	
	public function folder() {
		$folder = Folder::find('_id', $this->getParam('id', ''));
		if($folder != NULL) {
			$folder->downloadAsZip();
		} else {
			throw new FolderNotFoundException;
			exit;
		}
	}
}
?>