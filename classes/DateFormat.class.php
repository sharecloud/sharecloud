<?php
final class DateFormat {
	/** 
	 * Formats a given timestamp to a localised date format
	 * @static
	 * @param int Timestamp
	 * @return string Formatted date
	 */
	public static function format($timestamp) {
		$day = date(System::getLanguage()->_('DateFormat'), $timestamp);
		$time = date(System::getLanguage()->_('TimeFormat'), $timestamp);
		
		if($timestamp >= strtotime('yesterday')) {
			$day = System::getLanguage()->_('Yesterday');	
		}
		
		if($timestamp >= strtotime('today') && $timestamp < strtotime('tomorrow')) {
			$day = System::getLanguage()->_('Today');
		}
		
		return $day . ' ' . $time;
	}
}
?>