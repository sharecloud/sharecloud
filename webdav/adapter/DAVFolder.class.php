<?php

use Sabre\DAV;

class DAVFolder extends DAV\Collection implements DAV\INode {
	
	private $folder;
	private $children;
	
	function __construct($path) {
		if(System::getUser() == NULL) {
			$this->folder = NULL;
			$this->children = array();
			return;
		}
		
		if($path == "/") {
			$this->folder = Folder::find('_id', 0);
		} else {
			$this->folder = $path;
		}
		$this->folder->loadFiles();
		$this->folder->loadFolders();
		
		$children = array();
		
		foreach ($this->folder->folders as $key => $value) {
			$children[$value->name] = new DAVFolder($value);
		}
	
		foreach ($this->folder->files as $key => $value) {
			$children[$value->filename] = new DAVFile($value);
		}
		
		$this->children = $children;
	}
	
	public function delete() {
		$this->folder->delete();
	}
	
	public function getName() {
		return $this->folder->name;
	}
	
	public function setName($name) {
		$this->folder->name = $name;
		$this->folder->save();
	}
	
	public function getLastModified() {
		$lastModified = 0;
		
		foreach ($this->folder->files as $key => $value) {
			$lastModified = $value->time > $lastModified ? $value->time : $lastModified;
		}
		
		foreach ($this->folder->folders as $key => $value) {
			$tempFolder = new DAVFolder($value);
			$lastModified = $tempFolder->getLastModified() > $lastModified ? $tempFolder->getLastModified() : $lastModified;
		}
		
		return $lastModified;
	}
	
	/** Folder Specific **/
	
	public function getChild($child) {
		if($this->childExists($child))
			return $this->children[$child];
		else
			throw new DAV\Exception\NotFound();
	}
	
	public function getChildren() {
		return $this->children;
	}
	
	public function createFile($name, $data = NULL) {
		if($data == NULL) {
			return;
		} else if(is_resource($data)) {
			$data = stream_get_contents($data);
		}
		
		Log::sysLog("DAVFolder", "should upload File ".$name);
		
		File::create($name, $data, $this->folder->id);
		
	}
	
	public function createDirectory($name) {
		$folder = new Folder();
		$folder->name = $name;
		$folder->parent = $this->folder;
		
		$folder->save();
		$this->folder->loadFolders();
		return true;
	}
	
	public function childExists($child) {
		return key_exists($child, $this->children);
	}
	
	
	
}