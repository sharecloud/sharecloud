<?php
/**
 * Represents a folder
 */
final class Folder extends ModelBase {
	/**
	 * ID
	 * @var int
	 */
	private $id = 0;
	
	/**
	 * Parent folder ID
	 * @var int
	 */
	private $pid = 0;
	
	/**
	 * Name
	 * @var string
	 */
	private $name;
	
	/**
	 * Path (for nice URLs)
	 * @var string
	 */
	private $path;
	
	/**
	 * Folders
	 * @var object[]
	 */
	private $folders = array();
	
	/**
	 * Files
	 * @var object[]
	 */
	private $files = array();
	
	/**
	 * Invalid folder names
	 * @const string
	 */
	const INVALID_NAMES = 'add,edit,delete';
	
	const READONLY = 'id,pid,name,path,folders,files';
	
	/**
	 * Constructor
	 * @param int ID
	 */
	public function __construct() { }
	
	protected function assign(array $row) {
		$this->isNewRecord = false;
		
		$this->id	= $row['_id'];
		$this->pid	= $row['parent'];
		$this->name	= $row['name'];	
		
		$this->createPath();
	}
	
	public function save() {
		$data = array(
			':parent'	=> $this->pid,
			':name'		=> $this->name
		);
		
		if($this->name == '') {
			throw new InvalidFolderNameException();	
		}
		
		// Test if foldername is available
		$sql = System::getDatabase()->prepare('SELECT _id FROM folders WHERE name = :name AND _id != :id AND parent = :parent');
		$sql->execute(array(':name' => $this->name, ':parent' => $this->pid, ':id' => $this->id));
		
		if($sql->rowCount() != 0) {
			throw new FolderAlreadyExistsException();	
		}
		
		if($this->isNewRecord) {
			$data[':uid'] = System::getUser()->uid;
			
			$sql = System::getDatabase()->prepare('INSERT INTO folders (parent, user_ID, name) VALUES (:parent, :uid, :name)');
			$sql->execute($data);
			
			$this->id = System::getDatabase()->lastInsertId();
			$this->createPath();
		} else {
			$data[':id'] = $this->id;
			
			$sql = System::getDatabase()->prepare('UPDATE folders SET parent = :parent, name = :name WHERE _id = :id');
			$sql->execute($data);
		}
	}
	
	
	private function createPath() {
		// Generate path
		$path = array();
		
		$f = $this;
        
		while($f != NULL && $f->id != 0) {
			if($f->name != '') {
				$path[] = Folder::nameToURL($f->name);
			}
			
			$f = Folder::find('_id', $f->pid);
		}
		
		$this->path = implode('/', array_reverse($path));	
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
		if(!in_array($property, explode(',', File::READONLY))) {
			if($property == 'parent' && $value instanceof Folder) {
				$this->pid = $value->id;
				return;	
			}
			
			$this->$property = $value;
			
			$this->createPath();
		} else {
			throw new InvalidArgumentException('Property '.$property.' is readonly');
		}
	}
	
	/**
	 * Loads folders
	 */
	public function loadFolders() {
		$folders = Folder::find('parent', $this->id);
		
		if(!is_array($folders) && $folders != NULL) {
			$this->folders = array($folders);
		} else {
			$this->folders = $folders;	
			
			if($this->folders != NULL) {
				usort($this->folders, array('Folder', 'compare'));
			}
		}
	}
	
	/**
	 * Loads files
	 */
	public function loadFiles() {
		$files = File::find('folder_ID', $this->id);
		
		if(!is_array($files) && $files != NULL) {
			$this->files = array($files);
		} else {
			$this->files = $files;
			
			if($this->files != NULL) {
				usort($this->files, array('File', 'compare'));
			}
		}
	}
	
	/**
	 * Downloads whole folder as zip
	 */
	
	public function downloadAsZip() {
		
		ob_start();
		
		$this->loadFiles();
		$this->loadFolders();
		
		$archive = new ZipArchive();
		
		$zipfile = tempnam(sys_get_temp_dir(), "FIL");
		
		$archive->open($zipfile, ZipArchive::OVERWRITE);
		$this->addToArchive($archive, "", true);
		$archive->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$this->name.'.zip"');
        header('Content-Length: '.filesize($zipfile));
    	
		if(!DEV_MODE) ob_clean();
		
        readfile($zipfile);
		unlink($zipfile);
		exit();
	}
	
	private function addToArchive(ZipArchive $archive, $dir, $root = false) {
		$this->loadFiles();
		$this->loadFolders();
		
		if(!$root) {
			$archive->addEmptyDir($dir . $this->name);
			$dir .= $this->name . "/";
		} else {
			// Support if someone download an empty folder
			if((count($this->files) + count($this->folders)) == 0) {
				$archive->addEmptyDir($dir . $this->name);
				return;
			}
		}
		
		if(count($this->files) > 0) {
			foreach ($this->files as $file) {
				$archive->addFile($file->getAbsPath(), $dir . $file->filename);
			}
		}
		
		if(count($this->folders) > 0) {
			foreach ($this->folders as $folder) {
				$folder->addToArchive($archive, $dir);
			}
		}		
	}
	
	/**
	 * Deletes folder
	 */
	public function delete() {		
		$this->loadFolders();
		$this->loadFiles();
				
		if(count($this->folders) > 0) {
			foreach($this->folders as $folder) {
				$folder->delete();	
			}
		}
		
		if(count($this->files) > 0) {
			foreach($this->files as $file) {
				$file->delete();	
			}
		}
		
		$sql = System::getDatabase()->prepare('DELETE FROM folders WHERE _id = :id');
		$sql->execute(array(':id' => $this->id));
	}
	
	/**
	 * Moves a folder
     * @param object Folder (target)
	 */
	public function move(Folder $target) {
		if($this->pid != $target->id) {
			if($target->id == $this->id) {
				throw new InvalidArgumentException('Target folder must not be actual folder');	
			}
			
			if($target->isSubfolderOf($this)) {
				throw new InvalidArgumentException('Target folder must not be child of actual folder');	
			}
			
			$this->pid = $target->id;
		}
	}
	
	/**
	 * Checks if current folder is subfolder of given folder
	 * @param object Folder
	 * @return bool Result
	 */
	private function isSubfolderOf(Folder $folder) {
		if($this->id == 0) {
			return false;	
		}
        
        
		$f = Folder::find('_id', $this->pid);
        if($this->pid == $folder->id || ($f != NULL && $f->isSubfolderOf($folder))) {
			return true;	
		}
        
		return false;
	}
	
	/**
	 * Returns an object used for JSON encoding
	 * @param boolean Determines whether response should include directory listing or not
	 * @return object
	 */
	public function toJSON($dirListing = false) {
		$obj = new Object();
		
		$obj->id	= $this->id;
		$obj->pid	= $this->pid;
		$obj->name	= $this->name;
		
		$obj->path  = $this->path;
		
		$obj->url	= Router::getInstance()->build('BrowserController', 'show', $this);
		
		if($dirListing == true) {
			$this->loadFiles();
			$this->loadFolders();
			
			$obj->files = array();
			$obj->folders = array();
			
			if(count($this->files) > 0) {
				foreach($this->files as $file) {
					$obj->files[] = $file->toJSON();	
				}
			}
			
			if(count($this->folders) > 0) {
				foreach($this->folders as $folder) {
					$obj->folders[] = $folder->toJSON();	
				}
			}
		}
		
		return $obj;
	}
	
	
	public function getContentSize() {
		$this->loadFiles();
		$this->loadFolders();
		
		$size = 0;
		
		if(count($this->files) > 0) {
			foreach ($this->files as $key => $file) {
				$size += $file->size;
			}
		}
		
		if(count($this->folders) > 0) {
			foreach ($this->folders as $key => $folder) {
				$size += $folder->getContentSize();
			}
		}
		
		return $size;
		
	}
	
	/**
	 * Gets a list of folders incl. subfolders
	 * @return object[]
	 */
	public static function getAll($parent = 0, $keys = true, $exclude = array(), $prefix = ' / ') {	
		$list = array();
		
		// Add root folder if necessary
		if($parent == 0) {
			if($keys == true) {
				$list[0] = $prefix;	
			} else {
				$list[] = $prefix;	
			}
		}
		
		$folders = Folder::find('parent', $parent);
		
		if($folders != NULL) {
			if(!is_array($folders)) {
				$folders = array($folders);	
			}
			
			if(count($folders) > 0) {
				foreach($folders as $folder) {
					if(!in_array($folder->id, $exclude)) {
						if($keys == true) {
							$list[$folder->id] = $prefix . $folder->name;	
						} else {
							$list[] = $prefix . $folder->name;	
						}
						
						$list += Folder::getAll($folder->id, $keys, $exclude, $prefix . $folder->name . ' / ');
					}
				}
			}
		}
		
		return $list;
	}
	
	public static function find($column = '*', $value = NULL, array $options = array()) {
		$columns = parent::getColumns('folders');
		
		$query = 'SELECT * FROM folders';
		$params = array(':uid' => System::getUser()->uid);

		if($column != '*' && strlen($column) > 0 && $value !== NULL) {
			if(!in_array($column, $columns)) {
				throw new InvalidArgumentException('The column `'.$column.'` was not found');	
			}
			
			$query .= ' WHERE `'.$column.'` = :value AND `user_ID` = :uid';
			$params[':value'] = $value;
		} else {
			$query .= ' WHERE `user_ID` = :uid';	
		}

		if(isset($options['orderby']) && isset($options['sort'])) {
			if(!in_array($options['orderby'], $columns)) {
				throw new InvalidArgumentException('The column `'.$options['orderby'].'` was not found');	
			}
			
			$query .= ' ORDER BY `'. $options['orderby'] .'` ' . strtoupper($options['sort']);
		}
		
		if(isset($options['limit'])) {
			$query .= ' LIMIT ' . $options['limit'];
		}
		
		$sql = System::getDatabase()->prepare($query);
		$sql->execute($params);
		
		if($sql->rowCount() == 0) {
			if($column == '_id' && $value === 0) {
				return new Folder();
			}
			
			return NULL;
		} else if($sql->rowCount() == 1) {
			$folder = new Folder();
			$folder->assign($sql->fetch());
			
			return $folder;
		} else {
			$list = array();
			
			while($row = $sql->fetch()) {
				$folder = new Folder();
				$folder->assign($row);
				
				$list[] = $folder;	
			}
			
			return $list;
		}	
	}
	
	public static function compare($a, $b) {
		return strcmp($a->name, $b->name);	
	}
	
	public static function nameToURL($name) {
		$name	= preg_replace('~([^A-Za-z0-9-])~s', '-', $name);
		$name	= preg_replace('~-+~', '-', $name);
		
		return $name;
	}


}
?>