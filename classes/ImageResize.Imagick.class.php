<?php
final class ImageResize {
	/**
	 * Imagick object
	 * @var object
	 */
	private $image;
	
	/**
	 * Image file object
	 * @var object
	 */
	private $file;
	
	/**
	 * Constructor
	 * @param object File object
	 */
	public function __construct(File $file) {
		$this->file = $file;
		$this->image = new Imagick($this->file->getAbsPath());
		
		// Set output format to PNG
		$this->image->setImageFormat('png');
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
		
		$imageprops = $this->image->getImageGeometry();
		
		// We do not make images bigger
		if($width >= $imageprops['width']) {
			$this->file->download(true, false);
		}
		
		// Compute new height
		if($height == 'auto' || !is_numeric($height)) {
			$height = $imageprops['height'] / ($imageprops['width'] / $width);
		}
		
		// Resize
		$this->image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
		
		// Output correct header
		header('Content-Type: image/png');
		
		// Output image
		echo $this->image->getImageBlob();
		
		exit;
	}
}

?>