<?php
final class Databinding {
	public $property;
	public $direction;
	
	public function __construct($property, $direction = DatabindingDirection::Both) {
		$this->property		= $property;
		$this->direction	= $direction;
	}
}

final class DatabindingDirection {
	const SourceToDest	= -1;
	const Both 			=  0;
	const DestToSource	=  1;
}
?>