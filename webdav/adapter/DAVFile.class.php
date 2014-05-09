<?php

use Sabre\DAV;

class DAVFile extends DAV\File implements DAV\IFile {
	
	private $file;
	
	public function __construct($file) {
		if(System::getUser() == NULL) {
			$this->file = NULL;
			return;
		}
		$this->file = $file;
	}
	
	public function delete() {
		$this->file->delete();
	}
	
	public function getName() {
		return $this->file->filename;
	}
	
	public function setName($name) {
		$this->file->filename = $name;
		$this->file->save();
	}
	
	public function getLastModified() {
		return $this->file->time;
	}
	
	
	/** File Specific **/
	
	public function put($data) {
		return $this->file->setContent($data);
	}
	
	public function get() {
		return $this->file->getContent();
	}
	
	public function getETag() {
		return '"'.$this->file->alias.'"';
	}
	
	public function getContentType() {
		return $this->file->mime;
	}
	
	public function getSize() {
		return $this->file->size;
	}
	
	public function moveTo($path) {
		$root = new DAVFolder('/');

		if($path == '') {
			$parentid = $root->getId();
		} else {
			$foldernames = explode('/', $path);
			
			$parent = $root;
			foreach ($foldernames as $name) {
				$child = $parent->getChild($name);
				$parent = $child;
			}
			
			$parentid = $child->getID();
		}

		if(is_numeric($parentid)) {
			$this->file->folderid = $parentid;
			$this->file->save();
		}
		
	}
	
	
}