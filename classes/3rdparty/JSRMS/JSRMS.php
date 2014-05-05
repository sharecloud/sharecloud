<?php 
class JSRMS {
    
    private $jsonLocation;
    private $required = array();
    private $requiredSource = array();
    private $requiredStylesheets = array();
    
    public function __construct(){
        
        $this->jsonLocation = dirname(realpath( __FILE__ )).'/resources/';
        
    }

    public function requireResource($resourceName) {
        if(in_array($resourceName, $this->required)) {
            return true;
        }
        
        $filepath = $this->jsonLocation.$resourceName.'.json';
        
        if(file_exists($filepath)) {
            $resource = json_decode(file_get_contents($filepath), true);
        } else {
            return false;
        }
        
        
        $result;
        
        if(empty($resource['type'])) {
            $resource['type'] = '';
        }
        
        switch ($resource['type']) {
            case 'js':
                $result = $this->requireJS($resource);
                break;
                
            case 'css':
                $result = $this->requireCSS($resource);
                break;
                
            default:
                $result = $this->requireJS($resource);
                break;
        }
        if($result) {
            $this->required[] = $resourceName;
        } 
        
        return $result;
    }
    
    public function requireCSS($resource) {
            
        $requires = array();
        if(!empty($resource['requires']) && is_array($resource['requires'])) {
            foreach ($resource['requires'] as $key => $value) {
                if(!$this->requireresource($value['name'])) {
                    return false;
                }
            }
        }
        
        $this->requiredStylesheets[] = $resource['location'];
            
        
        
        return true; 
    }
    
    public function requireJS($resource) {
        $requires = array();
        if(!empty($resource['requires']) && is_array($resource['requires'])) {
            foreach ($resource['requires'] as $key => $value) {
                if(!$this->requireresource($value['name'])) {
                    return false;
                }
            }
        }
        
        if(!empty($resource["stylesheets"]) && is_array($resource["stylesheets"])) {
            foreach($resource["stylesheets"] as $key => $value) {
                $this->requiredStylesheets[] = $value;
            }
        }
		
		if(!empty($resource['location'])) {
			$this->requiredSource[] = $resource['location'];
		}
        
        return true; 
    }
    
    public function renderHTMLTags() {
        $html = '';
         
       
        foreach($this->requiredStylesheets as $key => $value) {
            $html .= '<link rel="stylesheet" href="'.System::getBaseURL().$value.'" type="text/css" />';
            
        }
        
        $html .= "\n";
        
        foreach ($this->requiredSource as $key => $value) {
            $html .= '<script data-name="'.$this->required[$key].'" src="'.System::getBaseURL().$value.'" type="text/javascript"></script>' . "\n";
        }
       
        return $html;
    }
    
    
}
?>