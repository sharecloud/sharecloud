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
	
	/**
	 * Finds certain records
	 * @static
	 * @param string Column name
	 * @param string Value
	 * @param string[] Options (available: sortby, order, limit)
	 * @return NULL|object|object[] Object(s)
	 */
	public static function find($column = '*', $value = NULL, array $options = array()) { }
	
	/**
	 * Compares two instances
	 * @static
	 * @param object Object 1
	 * @param object Object 2
	 * @return int -1, 0 or 1
	 */
	public static function compare($a, $b) {
		return -1;	
	}
}
?>