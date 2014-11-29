<?php
final class BrowserController extends ControllerBase {
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
	}
	
	public function index() {
		$folder_id = NULL;
		
		if(is_numeric($this->getParam('id', NULL))) {
			$folder_id = $this->getParam('id', NULL);
		}
		
		try {
			$folder = Folder::find('_id', $folder_id);
		} catch(FolderNotFoundException $e) {
			System::displayError(System::getLanguage()->_('ErrorFolderNotFound') , '404 Not Found');
		}
		
		$folder->loadFiles();
		$folder->loadFolders();
		
		$files = $folder->files;
		$breadcrumb = array();		
		
		if(Utils::getPOST('submit', false) !== false) {
			$delete = Utils::getPOST('delete', array());
			$count = count($delete);
			
			if($count > 0 && count($files) > 0) {
				foreach($files as $file) {
					for($i = 0; $i < $count; ++$i) {
						if($file->id == $delete[$i]) {
							$file->delete();	
						}
					}
				}
			}
			
			if($folder->id == 0) {
				System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
			} else {
				System::forwardToRoute(Router::getInstance()->build('BrowserController', 'show', array('id' => $folder->id)));	
			}
			exit;
		}
		
		// Breadcrumb
		$f = $folder;
		while($f != NULL && $f->id != 0) {
			if($f->name != '') {
				$breadcrumb[] = $f;	
			}
			
			$f = Folder::find('_id', $f->pid);
		}
		
		$breadcrumb = array_reverse($breadcrumb);
		
		$smarty = new Template();
		$smarty->assign('files', $folder->files);
		$smarty->assign('folders', $folder->folders);		
		$smarty->assign('title', System::getLanguage()->_('Files'));
		
		$smarty->assign('currentFolder', $folder);
		$smarty->assign('breadcrumb', $breadcrumb);
		$smarty->assign('AvailableFolders', Folder::getAll());
		
		$smarty->assign('remoteDownloadSetting', DOWNLOAD_VIA_SERVER);
		
        $smarty->requireResource('browser');
        
		$smarty->display('files/index.tpl');		
	}
	
	public function show() {
		$this->index();	
	}
	
	public function addFolder() {
		$form = new Form('form-addfolder', Router::getInstance()->build('BrowserController', 'addFolder'));
		
		$fieldset = new Fieldset(System::getLanguage()->_('AddFolder'));
		
		$name = new Text('name', System::getLanguage()->_('FolderName'), true);		
		$parent = new Select('parent', System::getLanguage()->_('ParentFolder'), Folder::getAll());
		$parent->selected_value = Utils::getGET('parent', 0);
		
		$fieldset->addElements($name, $parent);
		
		$form->addElements($fieldset);
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				try {
					$folder = new Folder($parent->selected_value);
					$folder->addFolder($name->value);
					
					if($folder->id == 0) {
						System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
					} else {
						System::forwardToRoute(Router::getInstance()->build('BrowserController', 'show', $folder->id));	
					}
					exit;
				} catch(InvalidFolderNameException $e) {
					$name->error = System::getLanguage()->_('ErrorInvalidFolderName'); 
				} catch(FolderAlreadyExistsException $e) {
					$name->error = System::getLanguage()->_('ErrorFolderAlreadyExists'); 
				} catch(Exception $e) {
					$name->error = System::getLanguage()->_('ErrorInvalidParameter');
				}
			}
		}
		
		$form->setSubmit(new Button(System::getLanguage()->_('Create'), 'icon icon-new-folder'));
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('AddFolder'));
		
		$smarty->assign('form', $form->__toString());
		$smarty->display('form.tpl');	
	}
	
	public function permissions() {
		try {
			$file = File::find('alias', $this->getParam('alias', ''));
		} catch(FileNotFoundException $e) {
			System::displayError(System::getLanguage()->_('ErrorFileNotFound') , '404 Not Found');	
		}
		
		$form = new Form('form-permissions', '');
		$fieldset = new Fieldset(System::getLanguage()->_('PermissionSetting'));
		
		$permission = new Select('permission', System::getLanguage()->_('Permission'), FilePermissions::getAll());
		$permission->selected_value = $file->permission;
		
		$password = new Password('password', System::getLanguage()->_('Password'));
		
		$fieldset->addElements($permission, $password);
		$form->addElements($fieldset);
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				if($permission->selected_value == 2 && empty($password->value)) {
					$password->error = System::getLanguage()->_('InvalidPassword');	
				} else {
					$file->permission->setPermission($permission->selected_value, $password->value);
					
					System::forwardToRoute(Router::getInstance()->build('DownloadController', 'download', $file));
					exit;
				}
			}
		}
		
		$form->addButton(new Button(
			System::getLanguage()->_('Cancel'),
			'icon icon-cancel',
			Router::getInstance()->build('DownloadController', 'download', $file)
		));
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('PermissionSetting'));
		
		$smarty->assign('form', $form->__toString());
		$smarty->display('form.tpl');	
	}
}
?>