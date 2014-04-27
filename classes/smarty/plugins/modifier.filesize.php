<?php
function smarty_modifier_filesize($filesize) {
	return Utils::formatBytes($filesize);	
}
?>