<?php
/**
 * Represents an uploaded file
 */
final class File extends ModelBase {
	/**
	 * Directory where all files are stored
	 * @const string
	 */
	const FILEDIR = '/uploads/';
	
	/** 
	 * Read-only properties
	 */
	const READONLY = 'id,alias,uid,mime,size,file,hashes,time,ext';
    
	/**
	 * ID
	 * @var int
	 */
	private $id = 0;
	
	/**
	 * Folder ID
	 * @var int
	 */
	private $folderid = NULL;
	
	/**
	 * Alias used for URLs
	 * @var string
	 */
	private $alias;
	
	/**
	 * User-ID of uploader
	 * @var int
	 */
	private $uid;
	
	/**
	 * Filename
	 * @var string
	 */
	private $filename;
	
	/**
	 * MIME-Type
	 * @var string
	 */
	private $mime;
	
	/**
	 * Filesize
	 * @var int
	 */
	private $size = 0;
	
	/**
	 * Secured filename
	 * @var string
	 */
	private $file = NULL;
	
	/**
	 * Hashesarray
	 * @var array
	 */
	private $hashes = array();
	
	/**
	 * Number of downloads
	 * @var int
	 */
	private $downloads = 0;
	
	/**
	 * Permission
	 * @var int
	 */
	private $permission;
	
	/**
	 * Password for authentification
	 * (encrypted)
	 * @var string
	 */
	private $password;
	
	/**
	 * Salt for password encryption
	 * @var string
	 */
	private $salt;
	
	/**
	 * Upload time
	 * @var int
	 */
	private $time;
	
	/**
	 * Extension
	 * @var string
	 */
	private $ext;
	
	/**
	 * Read-only mode?
	 * @var boolean
	 */
	private $readonly = false;
	
	/**
	 * Constructor
	 * @param int File-ID
	 * @throws InvalidArgumentException, Exception
	 */
	public function __construct() {
		$this->permission = DEFAULT_FILE_PERMISSION;	
	}
	
	public function __destruct() {
		if($this->id == 0 && !empty($this->file)) {
		    Log::sysLog("File", "Empty or corrupt file was deleted");
			// File was not saved -> delete file in uploads/ directory
			@unlink(SYSTEM_ROOT . File::FILEDIR . $this->file);	
		}
	}
	
	protected function assign(array $row) {
		$this->isNewRecord = false;
		
		$this->id		= $row['_id'];
		$this->folderid	= $row['folder_ID'];
		
		$this->alias	= $row['alias'];
		$this->uid		= $row['user_ID'];
		
		$this->filename	= $row['filename'];
		$this->mime		= $row['mime'];
		$this->size		= $row['size'];
		
		$this->file		= $row['file'];
		
		$this->hashes	= json_decode($row['hashes']);
		$this->downloads = $row['downloads'];
		$this->time 	= $row['time'];
		
		$this->permission = FilePermissions::parse($row['permission']);
		$this->password = $row['password'];
		$this->salt = $row['salt'];	
		
		$this->ext 		= File::getExtension($this->filename);
		
		if(System::getUser() == NULL || System::getUser()->uid != $this->uid) {
			$this->readonly = true;	
		}
		
		if(!file_exists($this->getAbsPath())) {
			$sql = System::getDatabase()->prepare('DELETE FROM files WHERE _id = :id');
			$sql->execute(array(':id' => $this->id));
			
			throw new FileNotFoundException();	
		}	
	}
	
	public function save() {
		if($this->readonly == true && !System::getUser()->isAdmin) {
			throw new NotAuthorisedException();
		}
		
		if($this->permission == FilePermissions::RESTRICTED_ACCESS && strlen($this->password) < PASSWORD_MIN_LENGTH) {
			throw new InvalidPasswordException();	
		} else if($this->permission != FilePermissions::RESTRICTED_ACCESS) {
			$this->password = $this->salt = '';
		}
		
		$data = array(
			':folderid'	=> $this->folderid,
			':filename'	=> $this->filename,
			':downloads'=> $this->downloads,
			':permission' => $this->permission,
			':password' => $this->password,
			':salt' => $this->salt
		);
		
		if($this->filename == '') {
			throw new InvalidFilenameException();	
		}
		
		if($this->isNewRecord) {			
			if(System::getUser()->quota > 0 && $this->size > System::getUser()->getFreeSpace()) {
			    Log::sysLog("File", "Quota of User with id ".System::getUser()->id." exceeded");
				throw new QuotaExceededException();
			}
			
			$this->alias    = self::createAlias();
			
			$data[':alias'] = $this->alias;
			$data[':uid']	= $this->uid;
			$data[':mime']	= $this->mime;
			$data[':size']	= $this->size;
			$data[':file']	= $this->file;
			$data[':hashes']= json_encode($this->hashes);
			$data[':time']	= time();
			
			$sql = System::getDatabase()->prepare('INSERT INTO files (folder_ID, alias, user_ID, filename, mime, size, file, hashes, downloads, time, permission, password, salt) VALUES (:folderid, :alias, :uid, :filename, :mime, :size, :file, :hashes, :downloads, :time, :permission, :password, :salt)');
			$sql->execute($data);
			
			$this->id	= System::getDatabase()->lastInsertId();
		} else {
			$data[':id']	= $this->id;
			
			$sql = System::getDatabase()->prepare('UPDATE files SET folder_id = :folderid, filename = :filename, downloads = :downloads, permission = :permission, password = :password, salt = :salt WHERE _id = :id');
			$sql->execute($data);
		}
	}
	
	/**
	 * Deletes a file
	 */
	public function delete() {
		if($this->readonly && !System::getUser()->isAdmin) {
			throw new NotAuthorisedException();
		}
		
		$success = @unlink($this->getAbsPath());
		
		if($success == true) {		
			$sql = System::getDatabase()->prepare('DELETE FROM files WHERE _id = :id');
			$sql->execute(array(':id' => $this->id));
		} else {
			throw new FilesystemException();
		}
	}

	public function verifyPassword($password){
		return Utils::createPasswordHash($password, $this->salt) == $this->password;
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
			if($property == 'password') {
				$this->salt		= hash('sha512', uniqid());
				$this->password	= Utils::createPasswordHash($value, $this->salt);
				return;
			} else if($property == 'permission') {
				$this->permission = FilePermissions::parse($value);
				return;
			} else if($property == 'folder' && $value === NULL) {
				$this->folderid = NULL;
				return;
			} else if($property == 'folder' && $value instanceof Folder) {
				$this->folderid = $value->id;
				return;
			}
			
			$this->$property = $value;
			
			if($property == 'filename') {
				$this->ext = File::getExtension($this->filename);	
			}
		} else {
			throw new InvalidArgumentException('Property '.$property.' is readonly');
		}
	}
	
	/**
	 * Sends header in order to download 
	 * file
	 * @param bool Raw-output? (true = raw, false = download)
	 * @param bool Increase download counter (default: true)
	 */
	public function download($raw = false, $count = true) {
	    
        if(!DEV_MODE) {      
            ini_set('error_reporting','0');
            ini_set('display_errors','Off');
            ini_set('display_startup_errors','Off');
    		error_reporting(0);
            set_time_limit(0);
            ob_clean();
        }
        
        if($raw === false) {
			if($count == true) {
				$this->incDownloadsCounter();
			}
			
            header('Content-Type: '.$this->mime);
            header('Content-Disposition: attachment; filename="'.$this->filename.'"');
            header('Content-Length: '.$this->size);
        
            readfile($this->getAbsPath());
            exit;
        } else {
            
            /**
             * WOO BiG THANKS TO http://www.php.net/manual/de/function.fread.php#106999
             */
        
    		ob_start();
        
            //workaround for IE filename bug with multiple periods / multiple dots in filename
            //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
            $filename = (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
                          preg_replace('/\./', '%2e', $this->filename, substr_count($this->filename, '.') - 1) :
                          $this->filename;
            
        
            //check if http_range is sent by browser (or download manager)
            $is_resume = false;
            if(isset($_SERVER['HTTP_RANGE'])) {
				$is_resume = true;
                list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        
                if ($size_unit == 'bytes')
                {
                    //multiple ranges could be specified at the same time, but for simplicity only serve the first range
                    //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                    list($range, $extra_ranges) = explode(',', $range_orig, 2);
                } else {
                    $range = '';
                }
            }
            else
            {
				if($count == true) {
					$this->incDownloadsCounter();
				}
				
                $range = '';
            }
        
            //figure out download piece from range (if set)
            
            @list($seek_start, $seek_end) = explode('-', $range, 2);
        
            //set start and end based on range (if set), else set defaults
            //also check for invalid ranges.
            $seek_end = (empty($seek_end)) ? ($this->size - 1) : min(abs($seek_end),($this->size - 1));
            $seek_start = (empty($seek_start) || $seek_end < abs($seek_start)) ? 0 : max(abs($seek_start),0);
        
            //add headers if resumable
            if ($is_resume)
            {
                //Only send partial content header if downloading a piece of the file (IE workaround)
                if ($seek_start > 0 || $seek_end < ($size - 1))
                {
                    header('HTTP/1.1 206 Partial Content');
                }
        
                header('Accept-Ranges: bytes');
                header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$this->size);
            }
        
            //headers for IE Bugs (is this necessary?)
            //header('Cache-Control: cache, must-revalidate');   
            //header('Pragma: public');
        
            header('Content-Type: ' . $this->mime);
            header('Content-Disposition: inline; filename="' . $this->filename . '"');
            header('Content-Length: '.($seek_end - $seek_start + 1));
        
            //open the file
            $fp = fopen($this->getAbsPath(), 'rb');
            //seek to start of missing part
            fseek($fp, $seek_start);
        
            //start buffered download
            while(!feof($fp))
            {
                //reset time limit for big files
                print(fread($fp, 1024*8));
                @flush();
                @ob_flush();
            }
        
            fclose($fp);
            exit;
        }
    
    
	}
	
	/**
	 * Increments downloads counter
	 */
	private function incDownloadsCounter() {
		$sql = System::getDatabase()->prepare('UPDATE files SET downloads = downloads + 1 WHERE _id = :id');
		$sql->execute(array(':id' => $this->id));
		
		$sql = System::getDatabase()->prepare('SELECT downloads FROM files WHERE _id = :id');
		$sql->execute(array(':id' => $this->id));
		
		$row = $sql->fetch();
		
		$this->downloads = $row['downloads'];
	}
	
	/**
	 * Returns file contents
	 * @return string Content
	 */
	public function getContent() {				
		return file_get_contents($this->getAbsPath());	
	}
	
	/**
	 * Moves a file
	 * @param object Folder (target)
	 */
	public function move(Folder $folder) {
		if($folder->id != $this->folderid) {		
			$this->folderid = $folder->id;
		}
	}
	
	/**
	 * Gets the absolute path to the file
	 * @return string Path
	 */
	public function getAbsPath() {
		return SYSTEM_ROOT . File::FILEDIR . $this->file;	
	}
	
	/** 
	 * Textual represenation of a file
	 * @return string
	 */
	public function __toString() {
		return $this->filename;	
	}
	
	public function upload($source) {
		if($this->file != NULL) {
			throw new Exception();	
		}
		
		$this->file = File::createFilename();
		$this->time = time();
		$this->uid	= System::getUser()->uid;
		
		if(!file_exists($source)) {
		    Log::sysLog("File", "File upload was corrupt. Source File does not exist");
			throw new UploadException('SourceFileDoesntExist');
		}
		
		if(!move_uploaded_file($source, SYSTEM_ROOT . File::FILEDIR . $this->file)) {
		    Log::sysLog("File", "Move error.");
			throw new UploadException('MoveFileError');
		}
		
		$this->mime = File::determineMime($this->file, $this->filename);
		$this->size = filesize($this->getAbsPath());
		
		// Generate hashes
		foreach (explode(',', SUPPORTED_FILE_HASHES) as $value) {
			$this->hashes[$value] = hash_file(trim($value), SYSTEM_ROOT . File::FILEDIR . $this->file);
		}
	}
	
	public function put() {
		if($this->file != NULL) {
			throw new Exception();	
		}
		
		$filesize = $_SERVER["CONTENT_LENGTH"];
		
		$this->file = File::createFilename();
		$this->time = time();
		$this->uid	= System::getUser()->uid;
		
		$putdata = fopen('php://input', 'r');
		$handle = fopen(SYSTEM_ROOT . File::FILEDIR . $this->file, 'w');
		
		while($data = fread($putdata, 1024)) {
			fwrite($handle, $data);	
		}
		
		fclose($handle);
		fclose($putdata);
		
		$this->mime = File::determineMime($this->file, $this->filename);
		$this->size = filesize($this->getAbsPath());
		
		if($this->size != $filesize) {
			// downloads was aborted
			@unlink($this->getAbsPath());

			throw new InvalidFilesizeException();
		}
		
		// Generate hashes
		foreach (explode(',', SUPPORTED_FILE_HASHES) as $value) {
			$this->hashes[$value] = hash_file(trim($value), SYSTEM_ROOT . File::FILEDIR . $this->file);
		}

	}
	
	public function remote($source) {
		if($this->file != NULL) {
			throw new Exception();	
		}
		
		$this->file = File::createFilename();
		$this->time = time();
		$this->uid	= System::getUser()->uid;
		
		// Add http:// is necessary
		if(substr($source, 0, 7) == 'http://') {
			
		} elseif (substr($source, 0, 8) == 'https://') {
			
		} elseif (substr($source, 0, 6) == 'ftp://') {
			
		} else {
			$source = 'http://' . $source;    
		}
		
		// Download file
		$rh = @fopen($source, 'r');
        $wh = @fopen(SYSTEM_ROOT . File::FILEDIR . $this->file, 'w+');
        
        if ($rh===false) {
            throw new UploadException('CannotOpenDomain');
            return;
        } 
        
        if($wh===false) {
            Log::sysLog("File", "Cannot write for remote upload");
            throw new UploadException('CannotOpenWriteFile');
            return;
        }
        
        while (!feof($rh)) {
            if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                throw new UploadException('CannotWrite');
                return;
            }
        }
        fclose($rh);
        fclose($wh);
		
		// Determine MIME type
		$this->mime = File::determineMime(SYSTEM_ROOT . File::FILEDIR . $this->file);
		
		if((empty($this->mime) || $this->mime == 'application/octet-stream') && !empty($http_response_header) && is_array($http_response_header)) {
			foreach ($http_response_header as $key => $value) {
				if(substr($value, 0, 14) == 'Content-Type: ') {
					$pos = strpos($value, ';');
					if($pos === false) {
						$this->mime = substr($value, 14);
					} else {
						$this->mime = substr($value, 14, $pos - 14);
					}
				}
			}
		}
		
		$this->size = filesize($this->getAbsPath());
		
		// Generate hashes
		foreach (explode(',', SUPPORTED_FILE_HASHES) as $value) {
			$this->hashes[$value] = hash_file($value, SYSTEM_ROOT . File::FILEDIR . $this->file);
		}
	}
	
	/**
	 * Returns an object used for JSON encoding
	 * @return object
	 */
	public function toJSON() {
		$obj = new Object();
		
		$obj->id		= $this->id;
		$obj->folderid	= $this->folderid;
		
		$obj->alias		= $this->alias;
		
		$obj->filename	= $this->filename;
		$obj->ext		= $this->ext;
		$obj->mime		= $this->mime;
		$obj->size		= $this->size;
		
		$obj->hashes	= $this->hashes;
		$obj->downloads = $this->downloads;
		
		$obj->url	= Router::getInstance()->build('DownloadController', 'show', $this);
		
		return $obj;
	}
	
	    
    /**
     * Returns an array. 0 => filename 1 => extension
	 * If no extension avaiable it returns the filename as string
     * @return mixed The Array with two indexes (name & extenstion) or a string containing the filename
     */
	public function getSplittedFilename() {
	    
        $splittedName = explode(".", $this->filename);
        $splittedName = array_map("trim", $splittedName);
		
        switch(count($splittedName)) {
            
            case 0: 
                return $this->filename;
                break;
            
            case 1:
                return $this->filename;
                break;
                
            case 2:
                return $splittedName;
                break;
            
            default:
                $ext = array_pop($splittedName);
                return array(implode(".", $splittedName), $ext);
        }
        
        
	}
	
	
	
	/**
	 * Determines MIME type
	 * @static
	 * @param string Filename
	 * @return string MIME type
	 */
	private static function determineMime($source, $filename = '') {
        $getID3 = new getID3;
        $fileInfo = $getID3->analyze($source);
		
        if(!empty($fileInfo['mime_type'])) {
            return $fileInfo['mime_type'];
        }
		
		$ext = '';
		
		if(!empty($filename)) {
            $ext = File::getExtension($filename);
		}
		
		return MIMETypes::getMIME($ext);
	}
	
	/**
	 * Creates a random filename
	 * @static
	 * @return string Filename
	 */
	private static function createFilename() {
		do {
			$filename = hash('sha512', microtime() . uniqid());
		} while(file_exists(SYSTEM_ROOT . File::FILEDIR . $filename));
		
		return $filename;			
	}
	
	/**
	 * Creates a random alias
	 * @static
	 * @return string Alias
	 */
	public static function createAlias() {
		do {
			$alias = md5(uniqid() . microtime());
			
			$sql = System::getDatabase()->prepare('SELECT _id FROM files WHERE alias = :alias');
			$sql->execute(array(':alias' => $alias));
		} while($sql->rowCount() == 1);
		
		return $alias;
	}
	
	/**
	 * Determines an extension of a given filename
	 * @static
	 * @param string Filename
	 * @param string Extension
	 */
	private static function getExtension($filename) {
		$pos = strrpos($filename, '.');
		
		if($pos !== false) {
			if($pos == 0) {
				return '.'; // Dot-File like .htaccess
			}
			return strtolower(substr($filename, strrpos($filename, '.') + 1));
		}
		
		return '';
	}
	
	public static function find($column = '*', $value = NULL, array $options = array()) {
		$query = 'SELECT * FROM files';
		
		if($column != '*' && strlen($column) > 0) {
			if($value == NULL) {
				$query .= ' WHERE '.Database::makeTableOrColumnName($column).' IS NULL';
			} else {
				$query .= ' WHERE '.Database::makeTableOrColumnName($column).' = :value';
				$params[':value'] = $value;
			}
			
			if(!isset($options['get_all_files']) && System::getUser() != NULL && $column != 'user_ID') {
				$query .= ' AND user_ID = :uid';
				$params[':uid'] = System::getUser()->uid;	
			}
		} else {
			if(System::getUser() == NULL) {
				return NULL;	
			}
			
			$params[':uid'] = System::getUser()->uid;
			$query .= ' WHERE user_ID = :uid';	
		}

		if(isset($options['orderby']) && isset($options['sort'])) {
			$query .= ' ORDER BY '. Database::makeTableOrColumnName($options['orderby']) .' ' . strtoupper($options['sort']);
		}
		
		if(isset($options['limit'])) {
			$query .= ' LIMIT ' . $options['limit'];
		}
		
		$sql = System::getDatabase()->prepare($query);
		$sql->execute($params);
		
		if($sql->rowCount() == 0) {
			return NULL;
		} else if($sql->rowCount() == 1) {
			$file = new File();
			$file->assign($sql->fetch());
			
			return $file;
		} else {
			$list = array();
			
			while($row = $sql->fetch()) {
				$file = new File();
				$file->assign($row);
				
				$list[] = $file;	
			}
			
			return $list;
		}	
	}
	
	public static function compare($a, $b) {
		return strcmp($a->filename, $b->filename);	
	}
}
?>
