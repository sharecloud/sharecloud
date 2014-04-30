<?php
final class AuthController extends ControllerBase{
	public function login() {
		// Redirect to browser if user is already logged in
		if(System::getUser() != NULL) {
			System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
			exit;	
		}
		
		$username = Utils::getPOST('username');
		$password = Utils::getPost('password');
		$error	  = false;
		
		if(Utils::getPOST('submit', false) != false) {
			$user = User::find('username', $username);
				
			if($user != NULL && $user->login($password)) {
				System::forwardToRoute( Router::getInstance()->build('HomeController', 'index'));
				exit;	
			} else {
				$error = true;
			}
			
		}
		
		System::$bodyClass .= 'preventautosync';
		
		
		$smarty	= new Template();
		$smarty->assign('title', System::getLanguage()->_('LogIn'));
		$smarty->assign('error', $error);
		
		$smarty->requireResource('auth');
		
		$smarty->display('auth/auth.tpl');
	}
	
	public function logout() {
		System::getSession()->logout();
		
		System::getSession()->setData('successMsg', System::getLanguage()->_('LogOutSuccess'));
		
		System::forwardToRoute( Router::getInstance()->build('HomeController', 'index'));
		exit;
	}
    
    public function authenticateFile() {
        $file_alias = $this->getParam('alias');
        $form       = new Form('form-authenticateFile');
        $fieldset   = new Fieldset('');
        
        $password   = new Password('password', System::getLanguage()->_('Password'), true);
        $password->autofocus = true;
		
        $file_alias_field = new Hidden('file_alias');
        $file_alias_field->setValue($file_alias, true);
        
        $fieldset->addElements($file_alias_field, $password);
        $form->addElements($fieldset);
        
        if(Utils::getPOST('submit', false) != false) {
            if($form->validate()) {
                $file = File::getByAlias($file_alias);
				
				if($file->permission->verify($password->value)) {
					System::getSession()->setData('authenticatedFiles',
						array_merge(
							array($file->alias), 
							System::getSession()->getData('authenticatedFiles', array()
						)
					));
					
					System::forwardToRoute(Router::getInstance()->build('DownloadController', 'show', $file));
					exit;
				} else {
					$password->error = System::getLanguage()->_('InvalidPassword');	
				}
            }
        }
        
        $form->setSubmit(new Button(System::getLanguage()->_('Authenticate'), 'icon icon-proceed'));
        
        $smarty = new Template();
        $smarty->assign('title', System::getLanguage()->_('Authenticate'));
        $smarty->assign('heading', System::getLanguage()->_('ProtectedAccessAuthMsg'));
        $smarty->assign('form', $form->__toString());
		
		$smarty->assign('BODY_CLASS', 'preventautosync');
        $smarty->display('form.tpl');              
    }
	
	public function lostpw() {
		$form = new Form('form-lostpw');
		
		$fieldset = new Fieldset(System::getLanguage()->_('LostPW'));
		
		$email = new Text('email', System::getLanguage()->_('EMail'), true);
		$fieldset->addElements($email);	
		$form->addElements($fieldset);
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				try {
					LostPW::createRequest($email->value);
					
					$smarty = new Template();
					$smarty->assign('title', System::getLanguage()->_('LostPW'));
					$smarty->assign('heading', System::getLanguage()->_('LostPW'));	
					$smarty->assign('msg', System::getLanguage()->_('LostPWSuccessMail'));
					
					$smarty->display('lostpw/success.tpl');	
					exit;				
				} catch(UserNotFoundException $e) {
					$email->error = System::getLanguage()->_('EMailNotFound');	
				} catch(MailFailureException $e) {
					$email->error = System::getLanguage()->_('EMailFailure');	
				}
			}
		}
		
		$form->setSubmit(new Button(System::getLanguage()->_('Proceed')));
		$form->addButton(new Button(
			System::getLanguage()->_('Cancel'),
			'',
			Router::getInstance()->build('AuthController', 'login')
		));
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('LostPW'));
		$smarty->assign('heading', System::getLanguage()->_('LostPW'));
		$smarty->assign('form', $form->__toString());
		$smarty->display('form.tpl');
	}
	
	public function lostpw_check() {
		$hash = $this->getParam('hash', '');
		
		if(!LostPW::hashExists($hash)) {
			System::displayError(System::getLanguage()->_('HashNotFound'), '404 Not Found');
		}
		
		$form = new Form('form-lostpw', Router::getInstance()->build('AuthController', 'lostpw_check', array('hash' => $hash)));
		
		$fieldset = new Fieldset(System::getLanguage()->_('Password'));
		$password = new Password('password', System::getLanguage()->_('Password'), true);
		$password->binding = new Databinding('password');
		$password->minlength = System::getPreference('PASSWORD_MIN_LENGTH');
		
		$password2 = new Password('password2', System::getLanguage()->_('ReenterPassword'), true);
		
		$fieldset->addElements($password, $password2);
		$form->addElements($fieldset);
		
		$form->setSubmit(new Button(System::getLanguage()->_('Proceed')));
		
		if(Utils::getPOST('submit', false) != false) {
			if($password->value != $password2->value) {
				$password2->error = System::getLanguage()->_('ErrorInvalidPasswords');
			} else if($form->validate()) {
				LostPW::resetPassword($hash, $password->value);
				
				$smarty = new Template();
				$smarty->assign('title', System::getLanguage()->_('LostPW'));
				$smarty->assign('heading', System::getLanguage()->_('LostPW'));	
				$smarty->assign('msg', System::getLanguage()->_('LostPWSuccess'));
				
				$smarty->display('lostpw/success.tpl');	
				exit;	
			}
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('LostPW'));
		$smarty->assign('heading', System::getLanguage()->_('LostPW'));
		$smarty->assign('form', $form->__toString());
		$smarty->display('form.tpl');
	}
}
?>