<?php
final class ImageResize {
	/**
	 * GD Image handle
	 * @var resource
	 */
	private $handle;
	
	/**
	 * Image file object
	 * @var object
	 */
	private $file;
	
	/**
	 * Width
	 * @var int
	 */
	private $width;
	
	/**
	 * Height
	 * @var int
	 */
	private $height;
	
	/**
	 * MIME
	 * @var string
	 */
	private $mime;
	
	/**
	 * Constructor
	 * @param object File object
	 */
	public function __construct(File $file) {
		$this->file = $file;
		
		list($this->width, $this->height, $this->mime) = getimagesize($this->file->getAbsPath());
		
		switch($this->mime) {
			case IMAGETYPE_JPEG:
				$this->handle	= imagecreatefromjpeg($this->file->getAbsPath());
			break;
			
			case IMAGETYPE_PNG:
				$this->handle	= imagecreatefrompng($this->file->getAbsPath());
			break;
			
			case IMAGETYPE_GIF:
				$this->handle	= imagecreatefromgif($this->file->getAbsPath());
			break;
			
			case IMAGETYPE_BMP:
			case IMAGETYPE_WBMP:
				$this->handle	= imagecreatefromwbmp($this->file->getAbsPath());
			break;
			
			default:
				throw new UnsupportedImageFormatException();
				return false;
			break;
		}
	}
	
	/**
	 * Resize and output image
	 * @param int New width
	 * @param int New height (default: 'auto', which preserves aspect ratio)
	 */
	public function resize($width, $height = 'auto') {
		if($width <= 0) {
			throw new InvalidArgumentException('Width must be greater than 0');	
		}
		
		// We do not make images bigger
		if($width >= $this->width) {
			$this->file->download(true, false);
		}
		
		// Compute new height
		if($height == 'auto' || !is_numeric($height)) {
			$height = $this->height / ($this->width / $width);
		}
		
		$resized = imagecreatetruecolor($width, $height);
		
		// Set transparency
		$this->setTransparency($resized, $width, $height);
		
		// Resize
		imagecopyresampled($resized, $this->handle, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		
		// Output
		header('Content-Type: image/png');
		imagepng($resized);
		imagedestroy($resized);
		exit;
	}
	
	/**
	 * Sets transparency in resized image
	 * @param resource Handle
	 * @param int Width
	 * @param int Height
	 */
	private function setTransparency($handle, $width, $height) {
		switch($this->mime) {
			case IMAGETYPE_PNG:
				imagealphablending($handle, false);
				$colour	= imagecolorallocatealpha($handle, 0, 0, 0, 127);
				imagefilledrectangle($handle, 0, 0, $width, $height, $colour);
				imagesavealpha($handle, true);
			break;
			case IMAGETYPE_GIF:
				imagefill($handle, 0, 0, imagecolorallocate($handle, 255, 255, 255));
			break;
		}
	}
    
    function __destruct() {
        imagedestroy($this->handle);
    }
}

?>