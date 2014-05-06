<?php
function smarty_modifier_lang($string, $param = NULL) {
	if($param == NULL) {	
		return System::getLanguage()->_($string);	
	}
	
	return sprintf(System::getLanguage()->_($string), $param);
}
?>