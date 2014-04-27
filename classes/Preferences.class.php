<?php
final class Preferences {    
    private $preferences = array();
    
    public function __construct() {
        $preferences = Preference::find('*');
		
		if(is_array($preferences)) {
			$this->preferences = $preferences;	
		} else if($preferences != NULL) {
			$this->preferences = array($preferences);	
		} else {
			$this->preferences = array();	
		}
    }
	
    public function save() {
        //TODO: CHECK IF USER IS ALLOWED TO CHANGE GLOBAL SETTINGS
        
        foreach ($this->preferences as $preference) {
            $preference->save();
        }        
    }
    
    public function getPreferences() {
        return $this->preferences;
    }
	
	public function getValue($key) {
		$preference = self::getPreference($key);
		
		if($preference instanceof Preference) {
			return $preference->value;	
		}
		
		return NULL;
	}
    
    public function __get($property) {
		$preference = self::getPreference($property);
		
		if($preference instanceof Preference) {
			return $preference->value;
		}
		
		throw new InvalidArgumentException('Property $property does not exist');
    }
    
    public function __set($property, $value) {
        // TODO: CHECK HERE IF USER IS ALLOWED, TOO!!!
		
		$preference = self::getPreference($property);
		
		if($preference instanceof Preference) {
			$preference->value = $value;
		}
    }
    
    private function getPreference($key) {
		foreach($this->preferences as $preference) {
			if($preference->key == $key) {
				return $preference;
			}
		}
		
		return NULL;
	}
}
?>