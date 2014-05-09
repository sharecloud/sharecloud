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
}
?>