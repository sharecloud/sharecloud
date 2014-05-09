<?php 

use Sabre\DAV;

class DAVAuth extends Sabre\DAV\Auth\Backend\AbstractBasic {
	
	protected function validateUserPass($username, $password) {
		$user = User::find('username', $username);
		
		if($user != NULL && $user->login($password)) {
			System::setUser($user);
			$this->currentUser = $user;
			return true;
		} else {
			System::setUser(NULL);
			$this->currentUser = NULL;
			
			return false;	
		}
	}
	
	
}


?>