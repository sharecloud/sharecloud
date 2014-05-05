<?php
final class ImageHandler extends HandlerBase {
	public function __construct() {
		parent::__construct();
		parent::registerExtension(array(
			'jpg', 'jpeg', 'gif', 'png', 'svg'
		));
	}
	
	protected function invokeHandler() {
		$specific = array();
		
		$size = getimagesize($this->file->getAbsPath());
		if($size !== false) {
			$specific['imagesize'] = $size[0].' x '.$size[1].' px';	
		} else {
			$specific['imagesize'] = System::getLanguage()->_('Unknown');	
		}
		
		if(extension_loaded('imagick') && class_exists('Imagick')) {
			$i = new Imagick($this->file->getAbsPath());
			$specific['format'] = $i->getimageformat();
		} else {
			$specific['format'] = System::getLanguage()->_('Unknown');	
		}
		
		$this->smarty->assign('specific', $specific);
		$this->smarty->display('handler/image.tpl');
	}
}

new ImageHandler;
?>