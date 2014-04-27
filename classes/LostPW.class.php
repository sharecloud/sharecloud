<?php
final class LostPW {
	/**
	 * Hash lifetime
	 * @const int
	 */
	const HASH_LIFETIME = 900; // 15 min
	
	/**
	 * Hash algorithm
	 * @const string
	 */
	const HASH_ALGO = 'sha1';
	
	/**
	 * Create new request and sends email to user
	 * @static
	 * @param string Mail adress
	 * @throws MailFailureException, UserNotFoundException
	 */
	public static function createRequest($mail) {
		LostPW::cleanUp();
		
		$user = User::getByEMail($mail);
		
		// Delete old requests
		$sql = System::getDatabase()->prepare('DELETE FROM lostpw WHERE user_ID = :uid');
		$sql->execute(array(':uid' => $user->uid));
		
		// Create new request
		$hash = LostPW::createHash();
		$sql = System::getDatabase()->prepare('INSERT INTO lostpw (user_ID, hash, time) VALUES (:uid, :hash, :time)');
		$sql->execute(array(':uid' => $user->uid, ':hash' => $hash, ':time' => time()));
		
		// Send Mail
		$content = new Template();
		
		$content->assign('link', Router::getInstance()->build('AuthController', 'lostpw_check', array('hash' => $hash)));
		$content->assign('user', $user);
		$content->assign('title', System::getLanguage()->_('LostPW'));
		
		// Determine template file
		$tpl = 'mails/lostpw.'.LANGUAGE.'.tpl';

		foreach($content->getTemplateDir() as $dir) {
			$file = 'mails/lostpw.'.$user->lang.'.tpl';
			
			if(file_exists($dir . $file)) {
				$tpl = $file;
				break;	
			}
		}
		
		$mail = new Mail(System::getLanguage()->_('LostPW'), $content->fetch($tpl), $user);
		$mail->send();
	}
	
	/**
	 * Changes a password
	 * @static
	 * @param string Request hash
	 * @param string New password
	 */
	public static function resetPassword($hash, $password) {
		LostPW::cleanUp();
		
		$sql = System::getDatabase()->prepare('SELECT user_ID FROM lostpw WHERE hash = :hash');
		$sql->execute(array(':hash' => $hash));
		
		if($sql->rowCount() != 1) {
			throw new HashNotFoundException();
		}
		
		$row = $sql->fetch();
		
		$user = new User($row['user_ID']);
		
		// Change password
		$user->password = $password;
		$user->submitChanges();
		
		// Delete hash
		$sql = System::getDatabase()->prepare('DELETE FROM lostpw WHERE hash = :hash');
		$sql->execute(array(':hash' => $hash));
	}
	
	/**
	 * Generates new hash
	 * @static
	 * @return string
	 */
	private static function createHash() {
		do {
			$hash = hash(LostPW::HASH_ALGO, microtime() . uniqid());
		} while(LostPW::hashExists($hash));
		
		return $hash;
	}
	
	/**
	 * Checks if hash already exists
	 * @static
	 * @param string Hash
	 * @return bool
	 */
	public static function hashExists($hash) {
		$sql = System::getDatabase()->prepare('SELECT _id FROM lostpw WHERE hash = :hash');
		$sql->execute(array(':hash' => $hash));
		
		return $sql->rowCount() == 1;	
	}
	
	/**
	 * Deletes all expired hashes
	 * @static
	 */
	private static function cleanUp() {
		$sql = System::getDatabase()->prepare('DELETE FROM lostpw WHERE time < :time');
		$sql->execute(array(':time' => time() - LostPW::HASH_LIFETIME));
	}
}
?>