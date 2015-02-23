<?php
final class ApiController extends ControllerBase {
	private $method = 'get';
	
	private $request = NULL;
	
	protected function onBefore($action = '') {
		System::$isXHR = true;
		
		// First: check method
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		
		if($this->method == 'get' || $this->method == 'post') {	
			// Check content-type
			$content_type = explode(';', $_SERVER['CONTENT_TYPE']);
			if($this->method == 'post' && $content_type[0] != 'application/json') {
				System::displayError('Content type must be set to "application/json"', '400 Bad Request');
			}
			
			$this->request = json_decode(file_get_contents('php://input'));
		} else if($this->method != 'put') {
			System::displayError(System::getLanguage()->_('ErrorInvalidParameter'), '405 Method Not Allowed');
		}
		
		if($action != 'login') {
			parent::checkAuthentification();
		}
	}
	
	/**
	 * Returns an input value from
	 * the JSON request object
	 * @param string Name of the property
	 * @param mixed Default value if the property is not available
	 */
	private function getRequestParam($property, $default = NULL) {
		if($this->request != NULL && property_exists($this->request, $property)) {
			return $this->request->$property;
		}
		
		return $default;
	}
	
	public function index() { }
	
	public function login() {
		$username = $this->getRequestParam('username', '');
		$password = $this->getRequestParam('password', '');
		
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
	
	public function listDirectory() {
		$folder = $this->getRequestParam('folder_id', NULL);
		
		$response = new AjaxResponse();
		
		$response->data = $folder;
		
		try {
			$folder = Folder::find('_id', $folder);
			
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
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function getFile() {
		System::$isXHR = true;
		parent::checkAuthentification();
		
		$response = new AjaxResponse();
		
		$file = File::find('_id', $this->getRequestParam('file_id', NULL));
		
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
		$filename = $this->getParam('filename', '');
		$folder = $this->getParam('folder', NULL);
		
		$response = new AjaxResponse();
		
		try {
			$folder = Folder::find('_id', $folder);

			if ($folder == NULL) {
				throw new FolderNotFoundException();
			}

			$file = new File();
			$file->filename = $filename;
			$file->folder = $folder;

			$file->put();
			$file->save();
			$response->data = $file->toJSON();

			$response->success = true;
		} catch(InvalidFilesizeException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('UploadAborted');
		} catch(QuotaExceededException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorQuotaExceeded');
		} catch(FolderNotFoundException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFolderNotFound');
		} catch(Exception $e) {
			Log::sysLog('ApiController::upload', 'Upload Error! Folder was not set or file is invalid');
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function remote() {
		$url = $this->getRequestParam('url', '');
		$filename = $this->getRequestParam('filename', '');
		$folder = $this->getRequestParam('folder', NULL);
		
		$response = new AjaxResponse();
		
		if(!DOWNLOAD_VIA_SERVER) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorFeatureDisabled');
			
			echo $response;
			exit;
		}
		
		try {
			if(!empty($url) && !empty($filename)) {
				$folder = Folder::find('_id', $folder);
				
				if($folder == NULL) {
					throw new FolderNotFoundException();	
				}
				
				$file = new File();
				$file->filename = $filename;
				$file->folder = $folder;
				
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
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function rename() {
		$response = new AjaxResponse();
		
		$folder = $this->getRequestParam('folder_id', NULL);
		$file	= $this->getRequestParam('file_id', NULL);
		$name	= $this->getRequestParam('name', '');
		
		try {
			if(!is_null($folder)) {
				$folderObj = Folder::find('_id', $folder);
				
				if(!is_null($folderObj)) {
					$folderObj->name = $name;
					$folderObj->save();
					
					$response->success = true;
				} else {
					throw new Exception();	
				}
			} else if(!is_null($file)) {
				$fileObj = File::find('_id', $file);
				
				if(!is_null($fileObj)) {
					$fileObj->filename = $name;
					$fileObj->save();	
					
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
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function move() {
		$folders = $this->getRequestParam('folders', array());
		$files = $this->getRequestParam('files', array());
		$target = $this->getRequestParam('target', '');
		
		$response = new AjaxResponse();
		
		if(is_array($folders) || is_array($files)) {
			try {
				$target = Folder::find('_id', $target);
				
				if(is_array($folders) && count($folders) > 0) {
					foreach($folders as $folder) {
						$f = Folder::find('_id', $folder);
						$f->move($target);
						$f->save();
					}
				}
				
				if(is_array($files) && count($files) > 0) {
					foreach($files as $file) {
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
				$response->message = System::getLanguage()->_('ErrorInvalidParameter');
				$response->data = get_class($e) . ': ' .$e->getMessage();
			}
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function delete() {
		$folders = $this->getRequestParam('folders', array());
		$files = $this->getRequestParam('files', array());
		
		$response = new AjaxResponse();
		
		if(is_array($folders) || is_array($files)) {
			try {
				if(is_array($folders) && count($folders) > 0) {
					foreach($folders as $folder) {
						$f = Folder::find('_id', $folder);
						$f->delete();
					} 
				}
				
				if(is_array($files) && count($files) > 0) {
					foreach($files as $file) {
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
				$response->message = System::getLanguage()->_('ErrorInvalidParameter');
			}
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
	public function addFolder() {
		$parent = $this->getRequestParam('parent_id', NULL);
		$name = $this->getRequestParam('name', '');
		
		$response = new AjaxResponse();
		
		if($parent >= 0 && !empty($name)) {
			try {
				$folder = new Folder();
				$folder->name = $name;
				$folder->parent = Folder::find('_id', $parent);
				
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
				$response->message = System::getLanguage()->_('ErrorInvalidParameter');
			}
		} else if(empty($name)) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorEmptyFolderName');
		} else {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
		
		$response->send();
	}
	
    public function permission() {
        $permission = $this->getRequestParam('permission', NULL);
        $password = $this->getRequestParam('password', '');
        $file_alias = $this->getRequestParam('file_alias', NULL);
		$file_id = $this->getRequestParam('file_id', NULL);

        $response = new AjaxResponse();
		
		try {
			if($permission == NULL || !FilePermissions::tryParse($permission)) {
				throw new Exception();	
			}
			
			if($file_alias != NULL) {
				$file = File::find('alias', $file_alias);
			} else if($file_id != NULL) {
				$file = File::find('_id', $file_id);
			} else {
				throw new Exception();	
			}
			
			$file->permission = $permission;
			$file->password = $password;
			
			$file->save();
			
			$response->success = true;
		} catch(InvalidArgumentException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		} catch(InvalidPasswordException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidPassword');
		} catch(NotAuthorisedException $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('PermissionDenied');
		} catch(Exception $e) {
			$response->success = false;
			$response->message = System::getLanguage()->_('ErrorInvalidParameter');
		}
  
        $response->send();
    }
	
	public function download() {
		$file_alias = $this->getRequestParam('file_alias', NULL);
		$file_id = $this->getRequestParam('file_id', NULL);
				
		$fileObj = NULL;
		
		if($file_alias != NULL) {
			$fileObj  = File::find('alias', $file_alias);
		} else if($file_id != NULL) {
			$fileObj = File::find('_id', $file_id);	
		} 
		
		if($fileObj != NULL) {
			$fileObj->download(true, false);	
		} else {
			System::displayError(System::getLanguage()->_('ErrorFileNotFound') , '404 Not Found');
		}
	}
	
	public function getFolderSize() {
		$folder_id = $this->getRequestParam('folder_id', NULL);
		$folder  = Folder::find('_id', $folder_id);
		
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
