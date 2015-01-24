<?php
class MarkdownHandler extends HandlerBase {
	public function __construct() {
		parent::__construct();
		parent::registerExtension(array(
			'markdown', 'mdown', 'mkdn', 'mkd', 'md'
		));
	}	
	
	protected function invokeHandler() {
		$content = $this->file->getContent();
		$parsedown = new Parsedown();
		
		$this->smarty->assign('content', $parsedown->text($content));
        
        $this->smarty->display('handler/markdown.tpl');
	}
}

new MarkdownHandler;
?>