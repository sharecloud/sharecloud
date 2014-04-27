<?php
final class DatabaseCheck extends Check {
	public function __construct() {
		$this->caption = 'Database connection';	
	}
	
	public function performCheck() {
		if(!defined('DATABASE_HOST') || !defined('DATABASE_USER') || !defined('DATABASE_PASS') || !defined('DATABASE_NAME')) {
			$this->result = CheckResult::FAIL;
			return;	
		}
		
		if(DATABASE_HOST == '') {
			$this->result = CheckResult::FAIL;	
			$this->message = '<p>Please specify a database host</p>';
			return;
		}
		
		if(DATABASE_NAME == '') {
			$this->result = CheckResult::FAIL;
			$this->message = '<p>Please specify a database name</p>';
			return;	
		}
		
		try {
			$db = new Database('mysql:host='.DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
			
			// Does the database already exist?
			$sql = $db->prepare('SHOW DATABASES LIKE \''.DATABASE_NAME.'\'');
			$sql->execute();
			
			if($sql->rowCount() == 0) {
				$this->result = CheckResult::FAIL;
				$this->message = '<p>Database '.DATABASE_NAME.' is not available</p>';
			} else {
				$this->result = CheckResult::OK;
				$this->message = '<p>Database connection works fine.</p>';
			}
		} catch(PDOException $e) {
			$this->result = CheckResult::FAIL;
			$this->message = '<p>Connection Error to MySQL. Host: '.DATABASE_HOST. ' User: '. DATABASE_USER;
			if(DATABASE_PASS == '') { // empty() does not seem to work with constants -.-
				$this->message .= ' used Password: NO (Password is empty).';                     
			} else {
				$this->message .= ' used Password: YES.'; 
			}
			
			$this->message .= '</p>';
		}
	}
}

ConfigurationChecks::addCheck(new DatabaseCheck);
?>