<?php
final class CompressedHandler extends HandlerBase {
	public function __construct() {
		parent::__construct();
		parent::registerExtension(array(
			'zip' , 'rar'/*, 'iso', 'tar', 'gzip'   // these compressedFiles will follow  */ 
		));
	}
	
	protected function invokeHandler() {
    	
        switch ($this->file->ext) {
            case 'zip':
                $this->invokeZipFile();
                break;
            
            case 'rar':
                $this->invokeRarFile();
                break;
                
            default:
                
                break;
        }
        
        $this->smarty->display('handler/compressed.tpl');
	}
    
    private function invokeZipFile() {
        $getID3 = new getID3;
        $fileInfo = $getID3->analyze($this->file->getAbsPath());
        
        foreach ($fileInfo['zip']['entries'] as $key => $value) {
            if(!empty($value['filename'])) {
                $files[$value['filename']] = array(
                    'CompressedSize' => Utils::formatBytes($value['compressed_size']),
                    'UncompressedSize' => Utils::formatBytes($value['uncompressed_size']),
                    'Encrypted' => $value['flags']['encrypted']
                );
            }
        }
        ksort($files);
        
        $specific = array(
            'CompressedSize' => Utils::formatBytes($fileInfo['zip']['compressed_size']),
            'UncompressedSize' => Utils::formatBytes($fileInfo['zip']['uncompressed_size']),
            'Encoding' => $fileInfo['encoding'],
            'CompressionMethod' => $fileInfo['zip']['compression_method'],
            'CompressionSpeed' => $fileInfo['zip']['compression_speed']
        );
        
        $this->smarty->assign("files", $files);
        $this->smarty->assign("specific", $specific);
    }

    private function invokeRarFile() {
        
        if(extension_loaded('rar') && class_exists('RarArchive')) {
         
            $rar = RarArchive::open($this->file->getAbsPath()); 
            foreach ($rar->getEntries() as $key => $value) {
                $files[$value->getName()] = array(
                        'CompressedSize' => Utils::formatBytes($value->getPackedSize()),
                        'UncompressedSize' => Utils::formatBytes($value->getUnpackedSize()),
                        'Encrypted' => $value->isEncrypted()
                    );
            }
            $rar->close();
           
        } else {
            
            $this->smarty->display('handler/default.tpl');
            exit();
            
        }
        
        
        $this->smarty->assign("files", $files);
        
    }
    
}

new CompressedHandler;
?>