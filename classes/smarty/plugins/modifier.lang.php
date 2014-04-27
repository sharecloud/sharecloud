<?php
function smarty_modifier_lang($string) {
	return System::getLanguage()->_($string);	
}
?>