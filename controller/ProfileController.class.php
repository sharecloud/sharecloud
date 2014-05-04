<?php
final class ProfileController extends ControllerBase {	
	protected function onBefore($action = '') {
		parent::checkAuthentification();	
	}
	
	private function getListOfMailAdresses(User $exclude = NULL) {
		$list = array();
		
		$users = User::find('*');
		
		if(is_array($users)) {
			foreach($users as $user) {
				if($exclude == NULL || $exclude->uid != $user->uid) {
					$list[] = $user->email;	
				}
			}
		} else if($users != NULL && ($exclude == NULL || $exclude->uid != $users->uid)) {
			$list[] = $users->email;	
		}
		
		return $list;
	}

	public function index() {
		$user = System::getUser();
		
		$form	= new Form('form-profile');
		$form->setAttribute('data-noajax', 'true');
		$form->binding = $user;
		
		$fieldset = new Fieldset(System::getLanguage()->_('General'));
		
		$firstname = new Text('firstname', System::getLanguage()->_('Firstname'), true);
		$firstname->binding = new Databinding('firstname');
		
		$lastname = new Text('lastname', System::getLanguage()->_('Lastname'), true);
		$lastname->binding = new Databinding('lastname');
		
		$email = new Text('email', System::getLanguage()->_('EMail'), true);
		$email->binding = new Databinding('email');
		$email->blacklist = $this->getListOfMailAdresses($user);
		$email->error_msg[4] = System::getLanguage()->_('ErrorMailAdressAlreadyExists');
		
		$language = new Radiobox('lang', System::getLanguage()->_('Language'), L10N::getLanguages());
		$language->binding = new Databinding('lang');
		
		$fieldset->addElements($firstname, $lastname, $email, $language);
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('Password'));
		$password = new Password('password', System::getLanguage()->_('Password'));
		$password->minlength = PASSWORD_MIN_LENGTH;
		
		$password->binding = new Databinding('password');
		$password2 = new Password('password2', System::getLanguage()->_('ReenterPassword'));
		
		$fieldset->addElements($password, $password2);
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('Settings'));
		$quota = new Text('quota', System::getLanguage()->_('Quota'));
		
		if($user->quota > 0) {
			$quota->value = System::getLanguage()->_('QuotaAvailabe', Utils::formatBytes($user->getFreeSpace()), Utils::formatBytes($user->quota));
		} else {
			$quota->value = System::getLanguage()->_('Unlimited');	
		}
		
		$quota->readonly = true;
		
		$fieldset->addElements($quota);
		$form->addElements($fieldset);
		
		if(Utils::getPOST('submit', false) !== false) {
			if(!empty($password->value) && $password->value != $password2->value) {
				$password2->error = System::getLanguage()->_('ErrorInvalidPasswords');
			} else {			
				if($form->validate()) {				
					$form->save();
					System::getUser()->save();
					
					System::getSession()->setData('successMsg', System::getLanguage()->_('ProfileUpdated'));
					
					System::forwardToRoute(Router::getInstance()->build('ProfileController', 'index'));
					exit;
				}
			}
		} else {
			$form->fill();	
		}
		
		$form->setSubmit(new Button(System::getLanguage()->_('Save'), 'floppy-disk'));
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('MyProfile'));
		$smarty->assign('heading', System::getLanguage()->_('MyProfile'));
		$smarty->assign('form', $form->__toString());
		$smarty->display('form.tpl');
	}
}
?>