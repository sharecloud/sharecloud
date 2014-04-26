<?php
/**
 * Database wrapper for PDO
 */
final class Database extends PDO {
	/**
	 * Number of SQL Statements
	 * @var int
	 */
	private $numStatements = 0;
	
	/**
	 * Number of SQL Queries
	 * @var int
	 */
	private $numQueries = 0;
	
	/**
	 * Constructor
	 * calls PDO::__construct()
	 * @param string DSN
	 * @param string Username
	 * @param string Password
	 * @param mixed[] Driver options
	 */
	public function __construct($dsn, $username = '', $password = '', array $driver_options = array()) {
		parent::__construct($dsn, $username, $password, $driver_options);
		
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("DBStatement", array($this)));
		$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}
	
	/**
	 * Returns number of SQL Queries
	 * @return int Number of SQL Queries
	 */
	public function getNumQueries() {
		return $this->numQueries;
	}
	
	/**
	 * Increases number of SQL Queries
	 */
	public function incNumQueries() {
		++$this->numQueries;	
	}
	
	/**
	 * Increases number of SQL Statements
	 */
	public function incNumStatements() {
		++$this->numStatements;	
	}
	
	/**
	 * calls PDO::exec()
	 */
	public function exec($query) {
		$this->incNumQueries();
		
		return parent::exec($query);	
	}
	
	/**
	 * calls PDO::prepare()
	 */
	public function prepare($statement, $options = array()) {
		$this->incNumStatements();
		
		return parent::prepare($statement, $options);
	}
	
	/**
	 * calls PDO::query()
	 */
	public function query($statement) {
		$this->incNumQueries();
		$this->incNumStatements();
		
		return parent::query($statement);	
	}
}

final class DBStatement extends PDOStatement {
	private $database;
	
	protected function __construct(Database $database) {
		$this->database = $database;
	}
	
	public function execute($bound_input_parameters = array()) {
		$this->database->incNumQueries();
		return parent::execute($bound_input_parameters);
	}
}
?>