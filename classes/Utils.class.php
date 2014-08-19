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
	public static function createPasswordHash($clearPswd, $salt) {
		return crypt($clearPswd, '$6$' . $salt); // '$6$' is the sha512 trigger
	}
	
	/**
	 * Creates a salt
	 * @static
	 * @return string salt with length of 64 hex digits
	 */
	 
	public static function createPasswordSalt() {
		
		return hash("sha512", Utils::randomBytes(128, true, false));
		
	}
	
	
/*

Secure PHP random bytes
by @shoghicp

Basic Usage: string randomBytes( [ int $length = 16 [, bool $secure = true [, bool $raw = true [, mixed $startEntropy = "" [, &$rounds [, &$drop ]]]]]])

	$length = 16;
	$bytes = randomBytes($length, true, false); //This will return 32 secure hexadecimal characters
	$bytes = randomBytes($length, false, true); //This will return 16 binary characters


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

	public static function randomBytes($length = 16, $secure = true, $raw = true, $startEntropy = "", &$rounds = 0, &$drop = 0){
		static $lastRandom = "";
		$output = b"";
		$length = abs((int) $length);
		$secureValue = "";
		$rounds = 0;
		$drop = 0;
		while(!isset($output{$length - 1})){
			//some entropy, but works ^^
			$weakEntropy = array(
				is_array($startEntropy) ? implode($startEntropy):$startEntropy,
				serialize(stat(__FILE__)),
				__DIR__,
				PHP_OS,
				microtime(),
				(string) lcg_value(),
				(string) PHP_MAXPATHLEN,
				PHP_SAPI,
				(string) PHP_INT_MAX.".".PHP_INT_SIZE,
				serialize($_SERVER),
				serialize(get_defined_constants()),
				get_current_user(),
				serialize(ini_get_all()),
				(string) memory_get_usage().".".memory_get_peak_usage(),
				php_uname(),
				phpversion(),
				extension_loaded("gmp") ? gmp_strval(gmp_random(4)):microtime(),
				zend_version(),
				(string) getmypid(),
				(string) getmyuid(),
				(string) mt_rand(),
				(string) getmyinode(),
				(string) getmygid(),
				(string) rand(),
				function_exists("zend_thread_id") ? ((string) zend_thread_id()):microtime(),
				var_export(@get_browser(), true),
				function_exists("getrusage") ? @implode(getrusage()):microtime(),
				function_exists("sys_getloadavg") ? @implode(sys_getloadavg()):microtime(),
				serialize(get_loaded_extensions()),
				sys_get_temp_dir(),
				(string) disk_free_space("."),
				(string) disk_total_space("."),
				uniqid(microtime(),true),
				file_exists("/proc/cpuinfo") ? file_get_contents("/proc/cpuinfo") : microtime(),
			);

			shuffle($weakEntropy);
			$value = hash("sha512", implode($weakEntropy), true);
			$lastRandom .= $value;
			foreach($weakEntropy as $k => $c){ //mixing entropy values with XOR and hash randomness extractor
				$value ^= hash("sha256", $c . microtime() . $k, true) . hash("sha256", mt_rand() . microtime() . $k . $c, true);
				$value ^= hash("sha512", ((string) lcg_value()) . $c . microtime() . $k, true);
			}
			unset($weakEntropy);

			if($secure === true){
				$strongEntropyValues = array(
					is_array($startEntropy) ? hash("sha512", $startEntropy[($rounds + $drop) % count($startEntropy)], true):hash("sha512", $startEntropy, true), //Get a random index of the startEntropy, or just read it
					file_exists("/dev/urandom") ? fread(fopen("/dev/urandom", "rb"), 64) : str_repeat("\x00", 64),
					(function_exists("openssl_random_pseudo_bytes") and version_compare(PHP_VERSION, "5.3.4", ">=")) ? openssl_random_pseudo_bytes(64) : str_repeat("\x00", 64),
					function_exists("mcrypt_create_iv") ? mcrypt_create_iv(64, MCRYPT_DEV_URANDOM) : str_repeat("\x00", 64),
					$value,
				);
				$strongEntropy = array_pop($strongEntropyValues);
				foreach($strongEntropyValues as $value){
					$strongEntropy = $strongEntropy ^ $value;
				}
				$value = "";
				//Von Neumann randomness extractor, increases entropy
				$bitcnt = 0;
				for($j = 0; $j < 64; ++$j){
					$a = ord($strongEntropy{$j});
					for($i = 0; $i < 8; $i += 2){						
						$b = ($a & (1 << $i)) > 0 ? 1:0;
						if($b != (($a & (1 << ($i + 1))) > 0 ? 1:0)){
							$secureValue |= $b << $bitcnt;
							if($bitcnt == 7){
								$value .= chr($secureValue);
								$secureValue = 0;
								$bitcnt = 0;
							}else{
								++$bitcnt;
							}
							++$drop;
						}else{
							$drop += 2;
						}
					}
				}
			}
			$output .= substr($value, 0, min($length - strlen($output), $length));
			unset($value);
			++$rounds;
		}
		$lastRandom = hash("sha512", $lastRandom, true);
		return $raw === false ? bin2hex($output):$output;
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
	
	/**
	 * Determine if SSL is used
	 * @return bool
	 */
	public static function isSSL() {
		if(isset($_SERVER['HTTPS'])) {
			if(strtolower($_SERVER['HTTPS']) == 'on') {
				return true;	
			}
			
			if($_SERVER['HTTPS'] == '1') {
				return true;	
			}
		} else if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
			return true;	
		}
		
		return false;
	}
	
	/**
	 * Performs an HTTP GET request to
	 * a given URL
	 * @param string URL
	 * @return string Result
	 */
	public static function getRequest($url) {
		if(!function_exists('curl_init')) {
			// Very simple fallback
			$result = @file_get_contents($url);
			
			if($result === false) {
				throw new RequestException();
			}
			
			return $result;
		} else {
			$curl = curl_init($url);
			
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER , true);
			
			/**
			 * Ignore all SSL security - we cannot care
			 * about MITM attacks here or we have to ship
			 * certs for curl :/
			 */
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			
			$result = curl_exec($curl);
			
			if($result === false) {
				throw new RequestException(curl_error($curl), curl_errno($curl));
			}
			
			return $result;
		}
	}
}

?>