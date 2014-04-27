<?php
function smarty_modifier_dateformat($input) {
	return DateFormat::format($input);	
}
?>