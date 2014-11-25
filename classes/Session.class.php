<?php
final class Session {
	/**
	 * Holds current Session ID
	 * @var string
	 */
	private $sid;
	
	/**
	 * Holds current User ID
	 * @var int
	 */
	private $uid = NULL;
	
	/**
	 * Holds current UAS
	 * @var string
	 */
	private $uas = 'unknown';
	
	/**
	 * Holds session data
	 * @var mixed[]
	 */
	private $data	= array();
	
	/**
	 * Session duration
	 * @const int
	 */
	const DURATION = 900;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cleanUp();
		
		if(isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
			$this->uas = $_SERVER['HTTP_USER_AGENT'];	
		}
		
		if(!$this->restoreSession()) {
			$this->createSession();	
		}
		
		$this->saveSession();
	}
	
	/**
	 * Tries to restore an active session
	 * @return bool Success (true = session was restored, false = no session to restore)
	 */
	private function restoreSession() {
		$cookie	= Utils::getCOOKIE(SESSION_NAME, false);
		
		/*
		 * If we are using an API, we also have
		 * the opportunity to provide the SID
		 * in the HTTP POST request
		 */		
		$token = Utils::getPOST('token', false);
		
		if($cookie == false && $token != false) {
			$cookie = $token;	
		}
		
		if($cookie == false) {
			return false;
		}
		
		$sql = System::getDatabase()->prepare('SELECT * FROM sessions WHERE sid = :sid AND browser = :browser');
		$sql->execute(array(':sid' => $cookie, ':browser' => $this->uas));
		
		if($sql->rowCount() == 1) {
			$row = $sql->fetch();
			
			$this->sid = $row['sid'];
			$this->uid = $row['uid'];
			$this->data = unserialize($row['data']);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Creates a new session
	 */
	private function createSession() {
		$this->sid	= $this->createSID();
		
		$sql = System::getDatabase()->prepare('INSERT INTO sessions (sid, browser, lastactivity, uid, data) VALUES (:sid, :browser, :lastactivity, :uid, :data)');
		
		$sql->execute(array(
			':sid' => $this->sid,
			':browser'	=> $this->uas,
			':lastactivity'	=> time(),
			':uid'	=> $this->uid,
			':data'	=> serialize($this->data)
		));
		
		setcookie(SESSION_NAME, $this->sid, 0, '/', $_SERVER['HTTP_HOST'], false, true); 
	}
	
	/**
	 * Log-Out
	 */
	public function logout() {
		$this->uid = NULL;
		$this->saveSession();
	}
	
	/**
	 * Removes expired sessions
	 */
	private function cleanUp() {
		$sql = System::getDatabase()->prepare('DELETE FROM sessions WHERE lastactivity < :lastactivity');
		$sql->execute(array(':lastactivity' => (time() - Session::DURATION)));
	}
	
	/**
	 * Saves the current session in DB
	 */
	private function saveSession() {
		$sql	= System::getDatabase()->prepare('UPDATE sessions SET lastactivity = :lastactivity, uid = :uid, data = :data WHERE sid = :sid');
		$sql->execute(array(
			':lastactivity'	=> time(),
			':uid'	=> $this->uid,
			':data'	=> serialize($this->data),
			':sid'	=> $this->sid
		));
	}
	
	/**
	 * Sets session data
	 * @param string Key
	 * @param mixed Value
	 */
	public function setData($key, $value) {
		$this->data[$key] = $value;
		
		$this->saveSession();
	}
	
	/**
	 * Returns session data
	 * @param string Key
	 * @param mixed Default value if Key doesn't exist (false per default)
	 * @return mixed Data
	 */
	public function getData($key, $default = false) {
		if(array_key_exists($key, $this->data)) {
			return $this->data[$key];	
		}
		
		return $default;
	}
	
	/**
	 * Returns current Session ID
	 * @return string Session ID
	 */
	public function getSID() {
		return $this->sid;	
	}
	
	/**
	 * Returns current User ID
	 * @return int User ID
	 */
	public function getUID() {
		return $this->uid;	
	}
	
	/**
	 * Sets the current User ID
	 * @param int User ID
	 */
	public function setUID($uid) {
		$this->uid = $uid;
		$this->saveSession();	
	}
	
	/**
	 * Creates a new, unique Session ID
	 * @return string Session ID
	 */
	private function createSID() {
		do {
			$sid = md5(uniqid());
		} while(!Session::checkSID($sid));
		
		return $sid;
	}
	
	/**
	 * Checks if a Session ID already exists
	 * @param string Session ID
	 * @return boolean Result (true = Session ID already exists)
	 */
	private static function checkSID($sid) {
		$sql = System::getDatabase()->prepare('SELECT sid FROM sessions WHERE sid = :sid');
		$sql->execute(array('sid' => $sid));
		
		return ($sql->rowCount() == 0);	
	}
}
?>