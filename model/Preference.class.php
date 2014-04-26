<?php
final class Preference extends ModelBase {
	private $id = 0;
	
	private $key;
	private $value;
	private $type;
	
	public function __construct() { }
	
	protected function assign(array $row) {
		$this->isNewRecord = false;
		
		$this->id	= $row['_id'];
		
		$this->key	= $row['key'];
		$this->value= $row['value'];
		$this->type	= $row['type'];
	}
	
	public function save() {
		$data = array(
			':type'		=> $this->type,
			':value'	=> $this->value
		);
		
		if($this->isNewRecord) {
			$sql = System::getDatabase()->prepare('UPDATE globalpreferences SET type = :type, value = :value WHERE _id = :id');
			$sql->execute($data);
			
			$this->id	= System::getDatabase()->lastInsertId();
			$this->isNewRecord = false;
		} else {
			$data[':id'] = $this->id;
			
			$sql = System::getDatabase()->prepare('UPDATE globalpreferences SET type = :type, value = :value WHERE _id = :id');
			$sql->execute($data);
		}
	}
	
	public function delete() { }
	
	public function __get($property) {
		if(property_exists($this, $property)) {
			if($property == 'value' && $this->type == 'bool') {
				return ($this->value === 'true' ? true : false);	
			}
			
			return $this->$property;
		}
		
		throw new InvalidArgumentException('Property $property does not exist');
	}
	
	public function __set($property, $value) {
		if(property_exists($this, $property)) {
			if($property == 'value' && $this->type == 'bool') {
				$this->value = ($value == true ? 'true' : 'false');
				return;
			}
			
			$this->$property = $value;	
		}
	}
	
	public static function find($column = '*', $value = NULL, array $options = array()) {
		$query = 'SELECT * FROM globalpreferences';
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
			$preference = new Preference();
			$preference->assign($sql->fetch());
			
			return $preference;
		} else {
			$list = array();
			
			while($row = $sql->fetch()) {
				$preference = new Preference();
				$preference->assign($row);
				
				$list[] = $preference;	
			}
			
			return $list;
		}	
	}
	
	public static function compare($a, $b) {
		return strcmp($a->key, $b->key);	
	}
}
?>