<?php
/**
 * Represents a folder
 */
final class FilePermission extends ModelBase {
	/**
	 * ID
	 * @var int
	 */
	private $id;
	
	private $level;
	
	private $password;
	
	private $salt;
	
	const READONLY = 'id,salt';
	
	public function __construct() { }
		
	public function assign(array $row) {
		$this->isNewRecord = false;
		
		$this->id		= $row['_id'];
		$this->password	= $row['password'];
		
		$this->salt		= $row['salt'];
		$this->level	= FilePermissions::parse($row['permission']);
	}
	
	public function save() {
		$data = array(
			':password'	=> $this->password,
			':salt'		=> $this->salt,
			':level'	=> $this->level
		);

		if($this->level == FilePermissions::RESTRICTED_ACCESS && strlen($this->password) < PASSWORD_MIN_LENGTH) {
			throw new InvalidPasswordException();	
		} else if($this->level != FilePermissions::RESTRICTED_ACCESS) {
			$data[':password'] = $data[':salt'] = '';	
		}
		
		if($this->isNewRecord) {
			$sql = System::getDatabase()->prepare('INSERT INTO file_permissions (permission, password, salt) VALUES (:level, :password, :salt)');
			$sql->execute($data);
			
			$this->id = System::getDatabase()->lastInsertId();
		} else {
			$data[':id'] = $this->id;
			
			$sql = System::getDatabase()->prepare('UPDATE file_permissions SET permission = :level, password = :password, salt = :salt WHERE _id = :id');
			$sql->execute($data);
		}
	}
	
	/**
	 * Getter
	 */
	public function __get($property) {
		if(property_exists($this, $property)) {
			return $this->$property;	
		}
	}
	
	/**
	 * Setter
	 */
	public function __set($property, $value) {
		if(!in_array($property, explode(',', FilePermission::READONLY))) {
			if($property == 'password') {
				$this->salt		= hash('sha512', uniqid());
				$this->password	= Utils::createPasswordHash($value, $this->salt);	
			} else if($property == 'level') {
				$this->level = FilePermissions::parse($value);
			} else {
				$this->$property = $value;	
			}
		} else {
			throw new InvalidArgumentException('Property '.$property.' is readonly');	
		}
	}
	
	/**
	 * Delete permission
	 */
	public function delete() {
		$sql = System::getDatabase()->prepare('DELETE FROM file_permissions WHERE _id = :id');
		$sql->execute(array(':id' => $this->id));
	}
	
	/**
	 * Verifies a password
	 * @param string Password
	 * @return bool Result (true = valid password)
	 */
	public function verify($password) {
		return (Utils::createPasswordHash($password, $this->salt) == $this->password);	
	}
	
	public static function getDefault() {
		$permission = new FilePermission();
		$permission->level = System::getPreference("DEFAULT_FILE_PERMISSION");
		$permission->save();
		return $permission;	
	}
	
	public static function find($column = '*', $value = NULL, array $options = array()) {
		$query = 'SELECT * FROM file_permissions';
		$params = array();

		if($column != '*' && strlen($column) > 0 && $value !== NULL) {
			$query .= ' WHERE '.$column.' = :value';
			$params[':value'] = $value;
		}

		if(isset($options['orderby']) && isset($options['sort'])) {
			$query .= ' ORDER BY :column ' . strtoupper($options['sort']);
			$params[':column'] = $options['orderby'];
		}
		
		if(isset($options['limit'])) {
			$query .= ' LIMIT ' . $options['limit'];
		}
		
		$sql = System::getDatabase()->prepare($query);
		$sql->execute($params);
		
		if($sql->rowCount() == 0) {
			return NULL;
		} else if($sql->rowCount() == 1) {
			$permission = new FilePermission();
			$permission->assign($sql->fetch());
			
			return $permission;
		} else {
			$list = array();
			
			while($row = $sql->fetch()) {
				$permission = new FilePermission();
				$permission->assign($row);
				
				$list[] = $permission;	
			}
			
			return $list;
		}	
	}
}
?>