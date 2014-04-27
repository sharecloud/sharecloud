<?php
final class Utils {
	/**
	 * Returns a list of files in a directory
	 * @static
	 * @param string Absolute path of directory
	 * @return string[] File list
	 */
	public static function getFilelist($directory) {
		if(!is_dir($directory)) {
			throw new DirectoryNotFoundException();	
		}
		
		$files	= array();
		
		if(substr($directory, -1) != '/') {
			$directory .= '/';	
		}
		
		if($handle	= opendir($directory)) {
			while(false !== ($file = readdir($handle))) {
				if($file == '.' || $file ==  '..') {
					continue;
				}
				
				if(is_file($directory . $file)) {
					$files[] = $file;	
				}
			}
			
			closedir($handle);
		}
		
		return $files;
	}
	
	/**
	 * Returns a list of directories in a directory
	 * @static
	 * @param string Absolute path of directory
	 * @return string[] Directory list
	 */
	public static function getDirectorylist($directory) {
		if(!is_dir($directory)) {
			throw new DirectoryNotFoundException();	
		}
		
		$folders	= array();
		
		if(substr($directory, -1) != '/') {
			$directory .= '/';	
		}
		
		if($handle	= opendir($directory)) {
			while(false !== ($file = readdir($handle))) {
				if($file == '.' || $file ==  '..') {
					continue;	
				}
				
				if(is_dir($directory . $file)) {
					$folders[] = $file;	
				}
			}
			
			closedir($handle);
		}
		
		return $folders;
	}
	
	/**
	 * Returns a GET-property
	 * @static
	 * @param string Key of property
	 * @param mixed Default value if property doesn't exist
	 * @param mixed Value
	 */
	public static function getGET($property, $default = false) {
		if(!array_key_exists($property, $_GET)) {
			return $default;	
		}
		
		return $_GET[$property];
	}
	
	/**
	 * Returns a POST-property
	 * @static
	 * @param string Key of property
	 * @param mixed Default value if property doesn't exist
	 * @param mixed Value
	 */
	public static function getPOST($property, $default = false) {
		if(!array_key_exists($property, $_POST)) {
			return $default;	
		}
		
		return $_POST[$property];
	}
	
	/**
	 * Returns a COOKIE-property
	 * @static
	 * @param string Key of property
	 * @param mixed Default value if property doesn't exist
	 * @param mixed Value
	 */
	public static function getCOOKIE($property, $default = false) {
		if(!array_key_exists($property, $_COOKIE)) {
			return $default;
		}
		
		return $_COOKIE[$property];
	}
	
	/**
	 * Creates a hash
	 * @static
	 * @param string Plain password
	 * @param string Salt
	 * @return string Hash
	 */
	public static function createPasswordhash($clearPswd, $salt) {
		return hash(User::HASH_ALGO, sprintf("%s%s", $clearPswd, $salt));
	}
	
	/**
	 * Formats filesize to a human-readable format
	 * @static
	 * @param int Filesize (bytes)
	 * @return string HR-format
	 */
	public static function formatBytes($size) {
		$units = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		
		return round($size, 2) . ' ' . $units[$i];
	}
    
    public static function formatBitrate($bitrate) {
        if(is_numeric($bitrate)) {
            return substr(round($bitrate), 0, -3).' kbit/s';   
        } else {
            return 'unknown';
        }
    }
    
	/**
	 * Parses PHP style integer values like 1M, 2G, ...
	 * @static
	 * @param int PHP style integer
	 * @return int Integer value in Bytes
	 */
	public static function parseInteger($input) {
		if(is_numeric(substr($input, -1))) {
			return $input;	
		}
		
		$value = substr($input, 0, -1);
		$suffix = substr($input, -1);
		
		switch($suffix) {
			case 'G':
				$value *= 1024;
			
			case 'M':
				$value *= 1024;
				
			case 'K':
				$value *= 1024;				
		}
		
		return $value;
	}
	
    public static function isLocalhostServer() {
        return ($_SERVER['SERVER_ADDR'] == '127.0.0.1') || ($_SERVER['SERVER_ADDR'] == '::1');
    }
}

?>