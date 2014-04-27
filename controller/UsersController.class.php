<?php
final class UsersController extends ControllerBase {
	protected function onBefore($action = '') {
		parent::checkIfAdmin();
	}

	private function getListOfUsernames(User $exclude = NULL) {
		$list = array();
		
		$users = User::find('*');
		
		if(is_array($users)) {
			foreach($users as $user) {
				if($exclude == NULL || $exclude->uid != $user->uid) {
					$list[] = $user->username;	
				}
			}
		} else if($users != NULL && ($exclude == NULL || $exclude->uid != $users->uid)) {
			$list[] = $users->username;	
		}
		
		return $list;
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
		$users = User::find();
		
		if(!is_array($users)) {
			$users = array($users);	
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('Users'));
		$smarty->assign('heading', System::getLanguage()->_('Users'));
		
		$smarty->assign('users', $users);
		
		$smarty->display('users/index.tpl');
	}
	
	public function add() {
		$user = new User();
		
		$form	= new Form('form-user', Router::getInstance()->build('UsersController', 'add'));
		$form->binding = $user;
		
		$fieldset	= new Fieldset(System::getLanguage()->_('General'));
		
		$username = new Text('username', System::getLanguage()->_('Username'), true);
		$username->binding = new Databinding('username');
		$username->blacklist = $this->getListOfUsernames();
		$username->error_msg[4] = System::getLanguage()->_('ErrorUsernameAlreayExists');
		$username->minlength = System::getPreference('USERNAME_MIN_LENGTH');
		
		$firstname = new Text('firstname', System::getLanguage()->_('Firstname'), true);
		$firstname->binding = new Databinding('firstname');
		
		$lastname = new Text('lastname', System::getLanguage()->_('Lastname'), true);
		$lastname->binding = new Databinding('lastname');
		
		$email = new Text('email', System::getLanguage()->_('EMail'), true);
		$email->binding = new Databinding('email');
		$email->blacklist = $this->getListOfMailAdresses();
		$email->error_msg[4] = System::getLanguage()->_('ErrorMailAdressAlreadyExists');
		
		$language = new Radiobox('lang', System::getLanguage()->_('Language'), L10N::getLanguages(), LANGUAGE);
		$language->binding = new Databinding('lang');
		
		$fieldset->addElements($username, $firstname, $lastname, $email, $language);
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('Password'));
		$password = new Password('password', System::getLanguage()->_('Password'), true);
		$password->minlength = System::getPreference('PASSWORD_MIN_LENGTH');
		
		$password->binding = new Databinding('password');
		$password2 = new Password('password2', System::getLanguage()->_('ReenterPassword'), true);
		
		$fieldset->addElements($password, $password2);
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('Settings'));
		$quota = new Text('quota', System::getLanguage()->_('Quota') . ' (MB)', true, 'numeric');
		$quota->binding = new Databinding('quota');
		
		$p = new Paragraph(System::getLanguage()->_('QuotaInfo'));
		
		$admin = new Radiobox('admin', System::getLanguage()->_('Admin'), array('1' => System::getLanguage()->_('YesStr'), '0' => System::getLanguage()->_('NoStr')));
		$admin->binding = new Databinding('isAdmin');
		
		$fieldset->addElements($quota, $p, $admin);		
		$form->addElements($fieldset);
		
		$form->setSubmit(new Button(
			System::getLanguage()->_('Save'),
			'icon icon-save'
		));
		
		$form->addButton(new Button(
			System::getLanguage()->_('Cancel'),
			'icon icon-cancel',
			Router::getInstance()->build('UsersController', 'index')
		));
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				if($quota->value < 0) {
					$quota->error = 'Quota must be > 0';	
				} else {
					$form->save();
					
					$user->quota *= 1048576; // Quota is MB
					$user->save();
					
					System::forwardToRoute(Router::getInstance()->build('UsersController', 'index'));
					exit;
				}
			}
		} else {
			$user->quota /= 1048576; // Quota is MB
			$form->fill();	
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('AddUser'));
		$smarty->assign('heading', System::getLanguage()->_('AddUser'));
		
		$smarty->assign('form', $form);
		$smarty->display('form.tpl');
	}
	
	public function edit() {
		$user = User::find('_id', $this->getParam('uid', 0));
		
		if($user == NULL) {
			System::displayError(System::getLanguage()->_('ErrorUserNotFound'), '404 Not Found');	
		}
		
		$form	= new Form('form-user', Router::getInstance()->build('UsersController', 'edit', $user));
		$form->binding = $user;
		
		$fieldset	= new Fieldset(System::getLanguage()->_('General'));
		
		$username = new Text('username', System::getLanguage()->_('Username'), true);
		$username->binding = new Databinding('username');
		$username->blacklist = $this->getListOfUsernames($user);
		$username->error_msg[4] = System::getLanguage()->_('ErrorUsernameAlreayExists');
		
		$firstname = new Text('firstname', System::getLanguage()->_('Firstname'), true);
		$firstname->binding = new Databinding('firstname');
		
		$lastname = new Text('lastname', System::getLanguage()->_('Lastname'), true);
		$lastname->binding = new Databinding('lastname');
		
		$email = new Text('email', System::getLanguage()->_('EMail'), true);
		$email->binding = new Databinding('email');
		$email->blacklist = $this->getListOfMailAdresses($user);
		$email->error_msg[4] = System::getLanguage()->_('ErrorMailAdressAlreadyExists');
		
		$language = new Radiobox('lang', System::getLanguage()->_('Language'), L10N::getLanguages(), LANGUAGE);
		$language->binding = new Databinding('lang');
		
		$fieldset->addElements($username, $firstname, $lastname, $email, $language);
		$form->addElements($fieldset);
		
		$fieldset = new Fieldset(System::getLanguage()->_('Password'));
		$password = new Password('password', System::getLanguage()->_('Password'));
		$password->binding = new Databinding('password');
		$password2 = new Password('password2', System::getLanguage()->_('ReenterPassword'));
		
		$fieldset->addElements($password, $password2);
		$form->addElements($fieldset);
		
		if($user->uid != System::getUser()->uid) {
			$fieldset = new Fieldset(System::getLanguage()->_('Settings'));
			$quota = new Text('quota', System::getLanguage()->_('Quota') . ' (MB)', true, 'numeric');
			$quota->binding = new Databinding('quota');
			
			$p = new Paragraph(System::getLanguage()->_('QuotaInfo'));
			
			$admin = new Radiobox('admin', System::getLanguage()->_('Admin'), array('1' => System::getLanguage()->_('YesStr'), '0' => System::getLanguage()->_('NoStr')));
			$admin->binding = new Databinding('isAdmin');
			
			$fieldset->addElements($quota, $p, $admin);		
			$form->addElements($fieldset);
		}
		
		$form->setSubmit(new Button(
			System::getLanguage()->_('Save'),
			'icon icon-save'
		));
		
		if($user->uid != System::getUser()->uid) {
			$form->addButton(new Button(
				System::getLanguage()->_('DeleteUser'),
				'icon icon-delete',
				Router::getInstance()->build('UsersController', 'delete', $user)
			));
		}
		
		$form->addButton(new Button(
			System::getLanguage()->_('Cancel'),
			'icon icon-cancel',
			Router::getInstance()->build('UsersController', 'index')
		));
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				if($quota->value < 0) {
					$quota->error = 'Quota must be > 0';	
				} else {
					$form->save();
					
					$user->quota *= 1048576; // Quota is MB
					$user->save();
					
					System::forwardToRoute(Router::getInstance()->build('UsersController', 'index'));
					exit;
				}
			}
		} else {
			$user->quota /= 1048576; // Quota is MB
			$form->fill();	
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('EditUser'));
		$smarty->assign('heading', System::getLanguage()->_('EditUser'));
		
		$smarty->assign('form', $form);
		$smarty->display('form.tpl');
	}
	
	public function delete() {
		$user = User::find('_id', $this->getParam('uid', 0));
		
		if($user == NULL) {
			System::displayError(System::getLanguage()->_('ErrorUserNotFound'), '404 Not Found');	
		} else if($user->uid == System::getUser()->uid) {
			System::displayError(System::getLanguage()->_('ErrorCannotDeleteYourself'), '403 Forbidden');		
		}
		
		$form	= new Form('form-user', Router::getInstance()->build('UsersController', 'delete', $user));
		$fieldset = new Fieldset(System::getLanguage()->_('Confirm'));
		
		$checkbox = new Checkbox('confirm', System::getLanguage()->_('ConfirmDeleteUser'), true);
		$p = new Paragraph(System::getLanguage()->_('ConfirmDeleteUserInfo'));
		
		$fieldset->addElements($checkbox, $p);
		$form->addElements($fieldset);
		
		$form->setSubmit(new Button(
			System::getLanguage()->_('Confirm'),
			'icon icon-delete'
		));
		
		$form->addButton(new Button(
			System::getLanguage()->_('Cancel'),
			'icon icon-cancel',
			Router::getInstance()->build('UsersController', 'index')
		));
		
		if(Utils::getPOST('submit', false) !== false) {
			if($form->validate()) {
				$user->delete();
				
				System::forwardToRoute(Router::getInstance()->build('UsersController', 'index'));
				exit;	
			}
		}
		
		$smarty = new Template();
		$smarty->assign('title', System::getLanguage()->_('DeleteUser'));
		$smarty->assign('heading', System::getLanguage()->_('DeleteUser'));
		
		$smarty->assign('form', $form);
		$smarty->display('form.tpl');
	}
}
?>