<?php
final class ApiController extends ControllerBase {
	public function index() { }
	
	public function login() {
		$username = Utils::getPOST('username', '');
		$password = Utils::getPOST('password', '');
		
		$response = new AjaxResponse();
		
		$user = User::find('username', $username);
			
		if($user != NULL && $user->login($password)) {
			$response->success = true;
			$response->data = new Object();
			$response->data->token = System::getSession()->getSID();
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('LogInFailed');		
		}
		
		$response->send();
	}
	
	public function logout() {
		$response = new AjaxResponse();
		
		System::getSession()->logout();
		
		$response->success = true;
		
		$response->send();
	}
	
	public function onBefore($action = '', array $params) {
		System::$isXHR = true;
		
		if($action != 'login') {
			parent::checkAuthentification();
		}
	}
	
	public function listDirectory() {
		$folder = Utils::getPOST('folder_id', -1);
		
		$response = new AjaxResponse();
		
		$response->data = $folder;
		
		try {
			$folder = Folder::find('_id', intval($folder)); // do not remove intval() here!
			
			if($folder == NULL) {
				throw new FolderNotFoundException();	
			}
			
			$response->success = true;
			$response->data = $folder->toJSON(true);
		} catch(FolderNotFoundException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFolderNotFound');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');	
		}
		
		$response->send();
	}
	
	public function getFile() {
		System::$isXHR = true;
		parent::checkAuthentification();
		
		$response = new AjaxResponse();
		
		$file = File::find('_id', Utils::getPOST('file_id', -1));
		
		if($file != NULL) {
			$response->success = true;
			$response->data = $file->toJSON();
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFileNotFound');
		}
		
		$response->send();
	}
	
	public function quota() {
		$response = new AjaxResponse();
		$response->success = true;
		$response->data = new Object();
		$response->data->quota = System::getUser()->quota;
		$response->data->available = System::getUser()->getFreeSpace();
		
		$response->send();	
	}
	
	public function upload() {
		$fileInput = new FileUpload('file', '', true);
		$folder = Utils::getPOST('folder', false);
		
		$response = new AjaxResponse();
		
		try {
			if($fileInput->validate('') && $folder !== false) {
				$permission = FilePermission::getDefault();
				$permission->save();
				
				$folder = Folder::find('_id', intval($folder));
				
				if($folder == NULL) {
					throw new FolderNotFoundException();	
				}
				
				$file = new File();
				$file->filename = $fileInput->filename;
				$file->folder = $folder;
				$file->permission = $permission;
				
				$file->upload($fileInput->uploaded_file);
				
				$file->save();
							
				$response->success = true;
				$response->data = $file->toJSON();
			} else {
				throw new Exception();
			}
		} catch(QuotaExceededException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorQuotaExceeded');
		} catch(FolderNotFoundException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFolderNotFound');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		}
		
		$response->send();
	}
	
	public function remote() {
		$url = Utils::getPOST('url', '');
		$filename = Utils::getPOST('filename', '');
		
		$folder = Utils::getPOST('folder', false);
		
		$response = new AjaxResponse();
		
		if(!System::getPreference("DOWNLOAD_VIA_SERVER")) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFeatureDisabled');
			
			echo $response;
			exit;
		}
		
		try {
			if(!empty($url) && !empty($filename) && $folder !== false) {
				$permission = FilePermission::getDefault();
				$permission->save();
				
				$folder = Folder::find('_id', intval($folder));
				
				if($folder == NULL) {
					throw new FolderNotFoundException();	
				}
				
				$file = new File();
				$file->filename = $filename;
				$file->folder = $folder;
				$file->permission = $permission;
				
				$file->remote($url);
				
				$file->save();
				
				$response->success = true;
				$response->data = $file->toJSON();
			} else {
				throw new Exception();	
			}
		} catch(UploadException $e) {
			$response->success = false;
			$response->message = $e->getMessage();
		} catch(QuotaExceededException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorQuotaExceeded');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		}
		
		$response->send();
	}
	
	public function rename() {
		$response = new AjaxResponse();
		
		$folder = Utils::getPOST('folder_id', 0);
		$file	= Utils::getPOST('file_id', 0);
		$name	= Utils::getPOST('name', '');		
		
		try {
			if($folder > 0) {
				$folder = Folder::find('_id', intval($folder));
				
				if($folder != NULL) {
					$folder->name = $name;
					$folder->save();
					
					$response->success = true;
				} else {
					throw new Exception();	
				}
			} else if($file > 0) {
				$file = File::find('_id', intval($file));
				
				if($file != NULL) {
					$file->filename = $name;
					$file->save();	
					
					$response->success = true;
				} else {
					throw new Exception();	
				}
			} else {
				throw new Exception(); // shorter than pasting the stuff from the catch-block xD
			}
		} catch(InvalidFilenameException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidFilename');	
		} catch(FolderAlreadyExistsException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFolderAlreadyExists');	
		} catch(InvalidFolderNameException $e) {
		 	$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidFolderName');		
		} catch(NotAuthorisedException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('PermissionDenied');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');	
		}
		
		$response->send();
	}
	
	public function move() {
		$folders = Utils::getPOST('folders', '');
		$files = Utils::getPOST('files', '');
		
		$target = Utils::getPOST('target', false);
		
		$response = new AjaxResponse();
		
		if($target !== false && ($folders != '' || $files != '')) {
			try {
				$target = Folder::find('_id', intval($target)); // do not remove intval() here!
				
				if($folders != '') {
					foreach(explode(',', $folders) as $folder) {
						$f = Folder::find('_id', $folder);
						$f->move($target);
						$f->save();
					}
				}
				
				if($files != '') {
					foreach(explode(',', $files) as $file) {
						$f = File::find('_id', $file);
						$f->move($target);
						$f->save();
					}
				}
				
				$response->success = true;
			} catch(NotAuthorisedException $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('PermissionDenied');
			} catch(Exception $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('ErrorInvalidParamter');	
				$response->data = get_class($e) . ': ' .$e->getMessage();
			}
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		}
		
		$response->send();
	}
	
	public function delete() {
		$folders = Utils::getPOST('folders', '');
		$files = Utils::getPOST('files', '');
		
		$response = new AjaxResponse();
		
		if($folders != '' || $files != '') {
			try {
				if($folders != '') {
					foreach(explode(',', $folders) as $folder) {
						$f = Folder::find('_id', $folder);
						$f->delete();
					} 
				}
				
				if($files != '') {
					foreach(explode(',', $files) as $file) {
						$f = File::find('_id', $file);
						$f->delete();
					}
				}
				
				$response->success = true;
			} catch(NotAuthorisedException $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('PermissionDenied');
			} catch(Exception $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('ErrorInvalidParamter');
			}
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');	
		}
		
		$response->send();
	}
	
	public function addFolder() {
		$parent = Utils::getPOST('parent_id', -1);
		$name = Utils::getPOST('name', '');
		
		$response = new AjaxResponse();
		
		if($parent >= 0 && !empty($name)) {
			try {
				$folder = new Folder();
				$folder->name = $name;
				$folder->parent = Folder::find('_id', intval($parent)); // do not remove intval() here!
				
				$folder->save();
				
				$response->success = true;
				$response->message = System::getLanguage()->_('SuccessAddFolder');
				
				$response->data = $folder->toJSON();
			} catch(InvalidFolderNameException $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('ErrorInvalidFolderName'); 
			} catch(FolderAlreadyExistsException $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('ErrorFolderAlreadyExists'); 
			} catch(Exception $e) {
				$response->success = false;
				$response->message = System::getLanguage()->_('ErrorInvalidParamter');
			}
		} else if(empty($name)) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorEmptyFolderName');
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		}
		
		$response->send();
	}
	
    public function permission() {
        $permission = Utils::getPOST('permission', false);
        $password = Utils::getPOST('password', '');
        $file_alias = Utils::getPOST('file_alias', false);
		$file_id = Utils::getPOST('file_id', false);

        $response = new AjaxResponse();
		
		try {
			if($file_alias != false) {
				$file = File::find('alias', $file_alias);
			} else if($file_id != false) {
				$file = File::find('_id', $file_id);	
			} else {
				throw new Exception();	
			}
			
			$file->permission->level = $permission;
			$file->permission->password = $password;
			
			$file->permission->save();
			
			$response->success = true;
		} catch(InvalidArgumentException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		} catch(InvalidPasswordException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidPassword');
		} catch(NotAuthorisedException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('PermissionDenied');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParamter');
		}
  
        $response->send();
    }
	
	public function download() {
		$file_alias = Utils::getPOST('file_alias', false);
		$file_id = Utils::getPOST('file_id', false);
		
		$fileObj = NULL;
		
		if($file_alias != false) {
			$fileObj  = File::find('alias', $file_alias);
		} else if($file_id != false) {
			$fileObj = File::find('_id', $file_id);	
		} 
		
		if($fileObj != NULL) {
			$fileObj->download(true, false);	
		} else {
			System::displayError(System::getLanguage()->_('ErrorFileNotFound') , '404 Not Found');
		}
	}
	
	public function getFolderSize() {
		
		$folder_id = Utils::getPOST('folder_id', false);
		
		if($folder_id != false) {
			$folder  = Folder::find('_id', $folder_id);
		}
		
		$response = new AjaxResponse();
		
		if($folder != NULL) {
			
			$folder_size = $folder->getContentSize();
			$response->success = true;
			$response->message = Utils::formatBytes($folder_size);
			
		}
		
		$response->send();
		
	}
}
?>