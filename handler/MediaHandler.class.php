<?php
final class MediaHandler extends HandlerBase {
    
    private $extensionArray = array(
                                     'video/mp4' => 'mp4',
                                     'video/x-m4v' => 'm4v',
                                     'video/webm' => 'webm',
                                     'video/ogg' => 'ogg',
                                     'video/x-flv' => 'flv',
                                     'audio/m4a' => 'm4a',
                                     'audio/mpeg' => 'mp3'
                                    );
    
	public function __construct() {
		parent::__construct();
		parent::registerExtension(array_values($this->extensionArray));
	}
	
	protected function invokeHandler() {
	    
        $pos = strpos($this->file->mime, '/');
        $mediatype = substr($this->file->mime, 0, strlen($this->file->mime) - $pos);
        $this->smarty->assign('mediatype', $mediatype);
        
        $specific = array();
        
        $getid3 = new getID3;
        $infos = $getid3->analyze($this->file->getAbsPath());
        
        $width = 320;
        $height = 240;
        
        if($mediatype == 'video') {
            
            $specific['audioBitrate'] = Utils::formatBitrate($infos['audio']['bitrate']);
            
            if(!empty($infos['video']['display_x']) && !empty($infos['video']['display_y'])) {
                $width = $infos['video']['display_x'];
                $height = $infos['video']['display_y'];
                
                $specific['ResolutionX'] = $infos['video']['resolution_x'] . ' px';
                $specific['ResolutionY'] = $infos['video']['resolution_y'] . ' px';
                $specific['Length'] = $infos['playtime_string'] . ' min';
            }
            
        	$this->smarty->assign("options", 'id="player1" width="' . $width . '" height="' . $height . '" controls="controls"');
        } elseif($mediatype == 'audio') {
            
            $specific['Bitrate'] = Utils::formatBitrate($infos['bitrate']);
            $specific['Length'] = $infos['playtime_string'] . ' min';

            
            $width = 400;
            $height = 30;
			$this->smarty->assign("options", 'id="player1" controls="controls" type="'.$this->file->mime.'"');
		}
        
        
        $this->smarty->assign('specific', $specific);
        $this->smarty->assign('mime', $this->file->mime);
        $this->smarty->assign('width', $width);
        $this->smarty->requireResource('mediaelement');
        
		$this->smarty->display('handler/media.tpl');
	}
}

new MediaHandler;
?>