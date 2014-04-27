<?php
final class PDFHandler extends HandlerBase {
	public function __construct() {
		parent::__construct();
		parent::registerExtension(array(
			'pdf'
		));
	}
	
	protected function invokeHandler() {
		$this->smarty->display('handler/pdf.tpl');
	}
}

new PDFHandler;
?>