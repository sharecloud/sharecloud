<?php
final class LogEntry extends ModelBase {
	private $id;
	private $level = '';
	private $message = '';
	
	private $file = '';
	private $line = '';
	private $date = '';
	private $counter = 1;
	private $sender = '';
	
	private $log	= 'system';
	
	public function __construct() {	}
	
	protected function assign(array $row) {
		$this->isNewRecord = false;
		
		$this->id		= $row['_id'];
		$this->level	= $row['errlevel'];
		$this->message	= $row['errmsg'];
		
		$this->file		= $row['file'];
		$this->line		= $row['line'];
		$this->date		= $row['date'];
		$this->counter	= $row['counter'];
		$this->sender	= $row['sender'];
		
		$this->log		= $row['log'];
	}
	
	public function save() {
		$data = array(
			':level'	=> $this->level,
			':message'	=> $this->message,
			':file'		=> $this->file,
			':line'		=> $this->line,
			':counter'	=> $this->counter,
			':sender'	=> $this->sender,
			':log'		=> $this->log
		);	
		
		if($this->isNewRecord) {
			if($this->log == 'php') {
				// Check if log entry already exists
				$sql = 'SELECT * from log WHERE `errlevel` = :errlevel AND `errmsg` = :errmsg AND `file` = :file AND `line` = :line';
				$sql = System::getDatabase()->prepare($sql);
				$sql->execute(array(
					'errlevel'  => $this->level,
					'errmsg'    => $this->message,
					'file'      => $this->file,
					'line'      => $this->line,
				));
				
				if($sql->rowCount() == 1) {	
					// Log entry already exists -> log old one
					$this->assign($sql->fetch());
					$this->counter++;
					
					// Update counter
					$updateSQL = 'UPDATE log SET counter = ( counter + 1 ) WHERE _id = :id';
					$updateSQL = System::getDatabase()->prepare($updateSQL);
					$updateSQL->execute(array(
						'id'    => $this->id
					));	
					
					return;
				}
			}
			
				
			$sql	= System::getDatabase()->prepare('INSERT INTO log (errlevel, errmsg, file, line, counter, sender, log) VALUES (:level, :message, :file, :line, :counter, :sender, :log)');
			$sql->execute($data);
			$this->id	= System::getDatabase()->lastInsertId();
		} else {
			$data[':id']	= $this->id;
			
			$sql	= System::getDatabase()->prepare('UPDATE log SET errlevel = :level, errmsg = :message, file = :file, line = :line, counter = :counter, sender = :sender, log = :log WHERE _id = :id');
			$sql->execute($data);
		}
	}
	
	public function delete() {
		$sql = System::getDatabase()->prepare('DELETE FROM log WHERE _id = :id');
		$sql->execute(array(':id' => $this->id));
	}
	
	/**
	 * Global setter
	 * @param string Property name
	 * @param mixed Property value
	 */
	public function __set($property, $value) {
		if($property == 'id') {
			throw new InvalidArgumentException('ID is read-only and cannot be set');	
		}
		
		if(property_exists($this, $property)) {
			$this->$property = $value;	
		} else {
			throw new InvalidArgumentException('Property '.$property.' does not exist (class: '.get_class($this).')');
		}
	}
	
	/**
	 * Global getter
	 * @param string Property name
	 */
	public function __get($property) {
		if(property_exists($this, $property)) {
			return $this->$property;	
		}
		
		throw new InvalidArgumentException('Property '.$property.' does not exist (class: '.get_class($this).')');
	}
	
	public static function find($column = '*', $value = NULL, array $options = array()) {
		$query = 'SELECT * FROM log';
		$params = array();
		
		if($column != '*' && strlen($column) > 0 && $value != NULL) {
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
			$row = $sql->fetch();
			
			$entry = new LogEntry();
			$entry->assign($row);
			
			return $entry;
		} else {
			$list = array();
			
			while($row = $sql->fetch()) {
				$entry = new LogEntry();
				$entry->assign($row);
				
				$list[] = $entry;	
			}
			
			return $list;
		}
	}
	
	public static function compare($a, $b) {
		if($a->date == $b->date) {
			return 0;	
		}
		
		return ($a->date > $b->date ? 1 : -1);
	}
	
	public static function deleteAll() {
        $sql = "TRUNCATE TABLE log";
        $sql = System::getDatabase()->query($sql);
    }
}
?>