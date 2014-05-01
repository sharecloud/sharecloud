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
		
		if(Utils::getPOST('submit', false) != false) {
			$user = User::find('username', $username);
				
			if($user != NULL && $user->login($password)) {
				System::forwardToRoute( Router::getInstance()->build('HomeController', 'index'));
				exit;	
			} else {
				System::getSession()->setData('errorMsg', System::getLanguage()->_('LogInFailed'));
			}
			
		}
		
		$smarty	= new Template();
		$smarty->assign('title', System::getLanguage()->_('LogIn'));
		
		$smarty->requireResource('auth');
		
		$smarty->display('auth/login.tpl');
	}
	
	public function logout() {
		System::getSession()->logout();		
		System::getSession()->setData('successMsg', System::getLanguage()->_('LogOutSuccess'));
		
		System::forwardToRoute( Router::getInstance()->build('AuthController', 'login'));
		exit;
	}
    
    public function authenticateFile() {
        $file_alias = $this->getParam('alias');
        $file = File::find('alias', $file_alias);
		
		if($file == NULL) {
			System::displayError(System::getLanguage()->_('ErrorFileNotFound') , '404 Not Found');
		}
		
		$password = Utils::getPOST('password', '');		
		$errorMsg = '';
        
        if(Utils::getPOST('submit', false) != false) {
			if($file->permission->verify($password)) {
				System::getSession()->setData('authenticatedFiles',
					array_merge(
						array($file->alias), 
						System::getSession()->getData('authenticatedFiles', array()
					)
				));
				
				System::forwardToRoute(Router::getInstance()->build('DownloadController', 'show', $file));
				exit;
			} else {
				$errorMsg = System::getLanguage()->_('InvalidPassword');	
			}
        }
		
        $smarty = new Template();
        $smarty->assign('title', System::getLanguage()->_('Authenticate'));
		$smarty->assign('infoMsg', System::getLanguage()->_('ProtectedAccessAuthMsg'));
		$smarty->assign('errorMsg', $errorMsg);
		
		$smarty->requireResource('auth');
		
        $smarty->display('auth/file.tpl');              
    }
	
	public function lostpw() {
		$email = Utils::getPOST('email', '');
		$errorMsg = '';
		
		if(Utils::getPOST('submit', false) !== false) {
			try {
				LostPW::createRequest($email);
				
				System::getSession()->setData('successMsg', System::getLanguage()->_('LostPWSuccessMail'));
				System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
			} catch(UserNotFoundException $e) {
				$errorMsg = System::getLanguage()->_('EMailNotFound');	
			} catch(MailFailureException $e) {
				$errorMsg = System::getLanguage()->_('EMailFailure');	
			}
		}
		
		$smarty = new Template();
		
		$smarty->assign('title', System::getLanguage()->_('LogIn'));
		$smarty->assign('errorMsg', $errorMsg);
		
		$smarty->requireResource('auth');
		$smarty->display('auth/lostpw.tpl');
	}
	
	public function lostpw_check() {
		$hash = $this->getParam('hash', '');
		
		if(!LostPW::hashExists($hash)) {
			System::getSession()->setData('errorMsg', System::getLanguage()->_('HashNotFound'));
			System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
		}
		
		$password = Utils::getPOST('password', '');
		$password2 = Utils::getPOST('password2', '');
		$errorMsg = '';
		
		if(Utils::getPOST('submit', false) != false) {
			if(strlen($password) < PASSWORD_MIN_LENGTH) {
				$errorMsg = sprintf(System::getLanguage()->_('PasswordMinLength'), PASSWORD_MIN_LENGTH);
			} else if($password != $password2) {
				$errorMsg = System::getLanguage()->_('ErrorInvalidPasswords');	
			} else {
				LostPW::resetPassword($hash, $password);
				
				System::getSession()->setData('successMsg', System::getLanguage()->_('LostPWSuccess'));
				System::forwardToRoute(Router::getInstance()->build('BrowserController', 'index'));
			}
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('LostPW'));
		
		$smarty->assign('successMsg', '');
		$smarty->assign('form_url', Router::getInstance()->build('AuthController', 'lostpw_check', array('hash' => $hash)));
		
		$smarty->assign('errorMsg', $errorMsg);
		
		$smarty->requireResource('auth');		
		$smarty->display('auth/lostpw.newpw.tpl');
	}
}
?>