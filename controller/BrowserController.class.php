<?php
final class BrowserController extends ControllerBase {

	private static $fontawesomeFileicons = array(
		'jpg' => 'fa-file-image-o',
		'jpeg' => 'fa-file-image-o',
		'gif' => 'fa-file-image-o',
		'png' => 'fa-file-image-o',
		'svg' => 'fa-file-image-o',

		// Videos
		'mp4' => 'fa-file-video-o',
		'mv4' => 'fa-file-video-o',
		'webm' => 'fa-file-video-o',
		'ogg' => 'fa-file-video-o',
		'flv' => 'fa-file-video-o',

		// Music
		'm4a' => 'fa-file-audio-o',
		'mp3' => 'fa-file-audio-o',

		// PDF
		'pdf' => 'fa-file-pdf-o',

		'htm' => 'fa-file-code-o',
		'html' => 'fa-file-code-o', // HTML
		'css' => 'fa-file-code-o', // CSS
		'java' => 'fa-file-code-o', // Java
		'c' => 'fa-file-code-o', // C
		'cpp' => 'fa-file-code-o', // C++
		'h' => 'fa-file-code-o',
		'm' => 'fa-file-code-o', // Objective C
		'cs' => 'fa-file-code-o', // C#
		'xaml' => 'fa-file-code-o', // K.A.
		'xml' => 'fa-file-code-o', // XML
		'mobileconfig' => 'fa-file-code-o', // Apple mobileconfig
		'patch' => 'fa-file-code-o',
		'diff' => 'fa-file-code-o', // git
		'vb' => 'fa-file-code-o', // VisualBasic
		'csv' => 'fa-file-code-o', // CSV
		'py' => 'fa-file-code-o', // Python
		'rb' => 'fa-file-code-o', // Ruby
		'pl' => 'fa-file-code-o', // Perl
		'php' => 'fa-file-code-o', // PHP
		'scala' => 'fa-file-code-o', // Scala
		'go' => 'fa-file-code-o', // Go
		'markdown' => 'fa-file-code-o',
		'mdown' => 'fa-file-code-o',
		'mkdn' => 'fa-file-code-o',
		'mkd' => 'fa-file-code-o',
		'md' => 'fa-file-code-o', // Markdown
		'json' => 'fa-file-code-o', // JSON
		'js' => 'fa-file-code-o', // JavaScript
		'coffee' => 'fa-file-code-o', // Coffeescript
		'actionscript' => 'fa-file-code-o',
		'as' => 'fa-file-code-o', // ActionScript
		'http' => 'fa-file-code-o', // HTTP
		'lua' => 'fa-file-code-o', // LUA Script
		'scpt' => 'fa-file-code-o',
		'applescript' => 'fa-file-code-o', // AppleScript
		'sql' => 'fa-file-code-o',
		'p' => 'fa-file-code-o',
		'pp' => 'fa-file-code-o',
		'pas' => 'fa-file-code-o', // Delphi / Pascal
		'vala' => 'fa-file-code-o', // Vala
		'd' => 'fa-file-code-o', // D
		'shader' => 'fa-file-code-o',
		'sl' => 'fa-file-code-o',
		'rib' => 'fa-file-code-o', // RenderMan RSL / RenderMan RIB
		'mel' => 'fa-file-code-o', // Maya Embedded Language
		'glslv' => 'fa-file-code-o',
		'glsl' => 'fa-file-code-o',
		'vert' => 'fa-file-code-o', // GLSL
		'st' => 'fa-file-code-o', // SmallTalk
		'lisp' => 'fa-file-code-o', // LISP
		'ini' => 'fa-file-code-o', // INI
		'bat' => 'fa-file-code-o', // Batch
		'sh' => 'fa-file-code-o', // Shell
		'cmake' => 'fa-file-code-o', // CMAKE
		'b' => 'fa-file-code-o', // BrainFuck
		'hs' => 'fa-file-code-o', // Haskell

		'txt' => 'fa-file-text-o',

		'zip' => 'fa-file-archive-o',
		'rar' => 'fa-file-archive-o'


	);

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
		$smarty->assign('fafileicons', BrowserController::$fontawesomeFileicons);

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