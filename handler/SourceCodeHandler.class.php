<?php
class SourceCodeHandler extends HandlerBase {
	// Source: http://www.php.net/manual/en/function.mb-detect-encoding.php#68607
	
	protected $content = "";
	
    private $languageArray = array(
        'htm' => 'xml', 'html' => 'xml', // HTML
        'css' => 'css', // CSS
        'java' => 'java', // Java
        'c' => '', // C
        'cpp' => 'cpp', // C++
        'h' => 'objectivec', 'm' => 'objectivec', // Objective C
        'cs' => 'cs', // C#
        'xaml' => '', // K.A.
        'xml' => 'xml', // XML
        'mobileconfig' => 'xml', // Apple mobileconfig
        'patch' => 'diff', 'diff' => 'diff', // git
        'vb' => 'vbscript', // VisualBasic
        'csv' => '', // CSV
        'py' => 'python', // Python
        'rb' => 'ruby', // Ruby
        'pl' => 'perl', // Perl
        'php' => 'php', // PHP
        'scala' => 'scala', // Scala
        'go' => 'go', // Go
        'markdown' => 'markdown', 'mdown' => 'markdown', 'mkdn' => 'markdown', 'mkd' => 'markdown', 'md' => 'markdown', // Markdown
        'json' => 'json', // JSON
        'js' => 'javascript', // JavaScript
        'coffee' => 'coffeescript', // Coffeescript
        'actionscript' => 'actionscript', 'as' => 'actionscript', // ActionScript
        'http' => 'http', // HTTP
        'lua' => 'lua', // LUA Script
        'scpt' => 'applescript', 'applescript' => 'applescript', // AppleScript
        'sql' => 'sql',
        'p' => 'delphi', 'pp' => 'delphi', 'pas' => 'delphi', // Delphi / Pascal
        'vala' => 'vala', // Vala
        'd' => 'd', // D
        'shader' => 'rsl', 'sl' => 'rsl', 'rib' => 'rib', // RenderMan RSL / RenderMan RIB
        'mel' => 'mel', // Maya Embedded Language
        'glslv' => 'glsl', 'glsl' => 'glsl', 'vert' => 'glsl', // GLSL
        'st' => 'smalltalk', // SmallTalk
        'lisp' => 'lisp', // LISP
        'ini' => 'ini', // INI
        'bat' => 'dos', // Batch
        'sh' => 'bash', // Shell
        'cmake' => 'cmake', // CMAKE
        'b' => 'brainfuck', // BrainFuck
        'hs' => 'haskell', // Haskell
        '.' => 'apache', // u.a. Apache
        
        'txt' => 'no-highlight'
    );
    
	protected static function isUTF8($string)
	{
		return preg_match('%(?:
		[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
		|\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
		|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
		|\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
		|\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
		|[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
		|\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
		)+%xs', $string);
	}
	
	public function __construct() {
		parent::__construct();
        parent::registerExtension(array_keys($this->languageArray));
        
	}
	
	protected function setup() {
		$this->content = $this->file->getContent();
		/* Might not be the best solution
		 * but works for both ANSI and UTF-8
		 * files
		 */
		if(!self::isUTF8($this->content)) {
			$this->content = utf8_encode($this->content);
		}
	}
	
	protected function invokeHandler() {
        
        
        /*
         * if(empty($this->file->ext)) {}
         * is not working!!!
         * 
         */
        $ext = $this->file->ext;
        if(empty($ext)) {
            $htmlClass = '';
        } else {
            $htmlClass = $this->languageArray[$this->file->ext];
        }

        
        
		$this->smarty->assign('extension', $htmlClass);
        $this->smarty->assign('content', htmlentities($this->content));
        
        $this->smarty->display('handler/sourcecode.tpl');
	}
}

new SourceCodeHandler;
?>
