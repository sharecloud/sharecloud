<?php
abstract class ModelBase {
	/**
	 * Flag if new record
	 * @var boolean
	 */
	protected $isNewRecord = true;
	
	/**
	 * Saves changes to DB (or creates record)
	 * @abstract
	 */
	public abstract function save();
	
	/**
	 * Deletes a record
	 * @abstract
	 */
	public abstract function delete();
	
	/**
	 * Assigns data from DB to model
	 * @abstract
	 */
	protected abstract function assign(array $row);
	
	protected static function getColumns($table) {
		$table = preg_replace("~[^A-Za-z0-9-_]*~", '', $table);
		
		$columns = array();
		
		$res = System::getDatabase()->query('DESCRIBE ' . $table);
		while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$columns[] = $row['Field'];	
		}
		
		return $columns;	
	}
}
?>