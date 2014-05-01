<?php
final class UploadController extends ControllerBase {
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
	}
	
	public function upload() {
		$form = new Form('form-upload', Router::getInstance()->build('UploadController', 'upload'));
		$form->setAttribute('data-noajax', 'true');
		$form->setEnctype();
		
		$fieldset = new Fieldset(System::getLanguage()->_('General'));
		$folderInput = new Select('folder', System::getLanguage()->_('ChooseFolder'), Folder::getAll());
		$folderInput->selected_value = Utils::getGET('parent', 0);
		
		$fieldset->addElements($folderInput);		
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('FileUpload'));
		$fileInput = new FileUpload('file', System::getLanguage()->_('ChooseFile'), false);
		
		$fieldset->addElements($fileInput);
		$form->addElements($fieldset);
		
        
        if(DOWNLOAD_VIA_SERVER) {
         
        
    		$fieldset = new Fieldset(System::getLanguage()->_('UploadFromURL'));
    		$url = new Text('url', System::getLanguage()->_('EnterURL'), false);
    		$name = new Text('name', System::getLanguage()->_('Name'), false);
            $name->setValue(System::getLanguage()->_('DownloadedFile'));		
		    $fieldset->addElements($url, $name);
            $form->addElements($fieldset);
           
        }
		
		$fieldset = new Fieldset(System::getLanguage()->_('PermissionSetting'));
		
		$permissionInput = new Select('permissions', System::getLanguage()->_('Permission'), FilePermissions::getAll());
        $permissionInput->selected_value = DEFAULT_FILE_PERMISSION;
        
		$password = new Password('password', System::getLanguage()->_('Password'), false);
		
		$fieldset->addElements($permissionInput, $password);		
		$form->addElements($fieldset);
		
		if(Utils::getPOST('submit', false) != false) {
			if($permissionInput->selected_value == 2 && empty($password->value)) {
				$password->error = System::getLanguage()->_('ErrorEmptyTextfield');
			} else if($form->validate() && (!empty($url->value) || !empty($fileInput->uploaded_file))) {
				$permission = new FilePermission();
				$permission->level = $permissionInput->selected_value;
				$permission->password = $password->value;
				
				$permission->save();				
				
				// Specify input control for error display
				$err = (empty($url->value) ? $fileInput : $url);
				
				try {
					$folder = Folder::find('_id', intval($folderInput->selected_value)); // do not remove intval() here
					$file = new File();

					$file->folder= $folder;
					$file->permission = $permission;
					
					if(empty($url->value)) {
						$file->filename = $fileInput->filename;
						$file->upload($fileInput->uploaded_file);
					} else {
						$file->filename = $name->value;
						$file->remote($url->value);
					}
					
					$file->save();
					
					System::forwardToRoute(Router::getInstance()->build('BrowserController', 'show', $folder));
					exit;
				} catch(UploadException $e){
					$fileInput->filename = '';
					$fileInput->uploaded_file = '';
					$err->error = $e->getMessage();
					if($e->getCode() != 0) {
						$err->error .= ' Code: '.$e->getCode();
					}
				} catch(QuotaExceededException $e) {
					$err->error = System::getLanguage()->_('ErrorQuotaExceeded');
				} catch(Exception $e) {
					$fileInput->filename = '';
					$fileInput->uploaded_file = '';
					$err->error = System::getLanguage()->_('ErrorWhileUpload') . ' ' . $e->getMessage();
				}
			}
		}
		
        $form->setSubmit(new Button(System::getLanguage()->_('Upload'), 'open'));
		
		if($folderInput->selected_value == 0) {
			$form->addButton(new Button(
				System::getLanguage()->_('Cancel'),
				'',
				Router::getInstance()->build('BrowserController', 'index')
			));
		} else {
			$form->addButton(new Button(
				System::getLanguage()->_('Cancel'),
				'',
				Router::getInstance()->build('BrowserController', 'show', new Folder($folderInput->selected_value))
			));
		}
		
		$smarty	= new Template();
		$smarty->assign('title', System::getLanguage()->_('Upload'));
		$smarty->assign('heading', System::getLanguage()->_('FileUpload'));
		$smarty->assign('form', $form->__toString());
		$smarty->assign('BODY_CLASS', 'preventreload');
        
        $smarty->requireResource('upload');
        
		$smarty->display('form.tpl');
	}
}
?>