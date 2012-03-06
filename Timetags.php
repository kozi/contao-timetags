<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao webCMS
 * Copyright (C) 2011-2012 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2011-2012
 * @author     Martin Kozianka <http://kozianka-online.de> * @package    timetags 
 * @license    LGPL
 */


/**
 * Class Timetags
 *
 * @copyright  Martin Kozianka 2011-2012 
 * @author     Martin Kozianka <http://kozianka-online.de>
 * @package    timetags
 */
class Timetags extends Frontend {
	public static $oneDay  = 86400;
	public static $oneHour =  3600;	
	public static $oneMin  =    60;	
	private $tagname   = null;
	private $date      = null;	
	private $fmtString = null;
	private $message   = "";

	public function replaceTags($strTag) {
		$tagValues = trimsplit('::', $strTag);
		$this->tagname = $tagValues[0];

		if ($this->tagname == 'timesince' || $this->tagname == 'countdown') {

			if (sizeof($tagValues) !== 4 && sizeof($tagValues) !== 3) {
				return "{{".$strTag."}} - Missing parameter(s).";
			}

			// Format String
			$this->fmtString = $tagValues[2];
			if (strpos($this->fmtString, "%s") === false) {
				return "{{".$strTag."}} - Missing %s in format string";
			}
			
			// Optionale Nachricht
			if (sizeof($tagValues) === 4) {
				$this->message = $tagValues[3];
			}
			else {
				$this->message = "";
			}

			$timeStr = trim($tagValues[1]);
			
			if (strpos($timeStr, "tstamp=") !== false) {
				$this->date = new Date(str_replace("tstamp=", "", $timeStr));
			} else {
				// Datum parsen
				try {
					$dt = new DateTime($timeStr);
					$this->date = new Date($dt->getTimestamp());
				} catch (Exception $e) {
					return "{{".$strTag."}} - Error parsing date";
				}	
			}

			if ($this->tagname == 'countdown') {
				return $this->countdown();
			}
			else if ($this->tagname == 'timesince') {
				return $this->timesince();
			}
		}
		// Nicht unser inserttag;
		return false;
	}
	
	private function countdown() {
		$diff = $this->date->timestamp - time();

		if ($diff <= 0) {
			return $this->message;
		}
		$cd = new stdClass();
		$cd->days  = 0;
		$cd->hours = 0;
		$cd->min   = 0;
		$cd->sec   = 0;
		
		// Tage
		if ($diff > Timetags::$oneDay) {
			$cd->days = floor($diff / Timetags::$oneDay);
			$diff = $diff % Timetags::$oneDay;
		}

		// Stunden
		if ($diff > Timetags::$oneHour) {
			$cd->hours = floor($diff / Timetags::$oneHour);
			$diff = $diff % Timetags::$oneHour;
		}

		// Minuten und Sekunden
		if ($diff > Timetags::$oneMin) {
			$cd->min = floor($diff / Timetags::$oneMin);
			$cd->sec = $diff % Timetags::$oneMin;
		}

		
		$lang   = &$GLOBALS['TL_LANG']['FMD']['timetags_countdown'];
		$langPl = &$GLOBALS['TL_LANG']['FMD']['timetags_countdown_plural'];
		$countdownStr = "";
		$prefix = "";

		// Ausgabe Tage
		if ($cd->days > 0) {
			$countdownStr .= $cd->days." ".(($cd->days == 1) ? $lang[3] : $langPl[3]);
			$prefix = ", ";
		}
		
		// Ausgabe Stunden
		if ($cd->hours > 0) {
			$countdownStr .= $prefix.$cd->hours." ".(($cd->hours == 1) ? $lang[2] : $langPl[2]);
			$prefix = ", ";
		}

		// Ausgabe Minuten
		if ($cd->min > 0) {
			$countdownStr .= $prefix.$cd->min." ".(($cd->min == 1) ? $lang[1] : $langPl[1]);
			$prefix = ", ";
		}

		// Ausgabe Sekunden
		if ($cd->sec > 0) {
			$countdownStr .= $prefix.$cd->sec." ".(($cd->sec == 1) ? $lang[0] : $langPl[0]);
		}

		return sprintf($this->fmtString, $countdownStr);
	}



	private function timesince() {
		if (time() <= $this->date->timestamp) {			return $this->message;
		}
		return $this->relativeTime($tstamp);
	}
	
	
	private function relativeTime($timestamp){
	    $difference = time() - $this->date->timestamp;

		$lengths = array("60","60","24","7","4.35","12","10");
		
	
	    for($j = 0; $difference >= $lengths[$j] && $j < sizeof($lengths); $j++) {
	    	$difference /= $lengths[$j];
		}
		
	    $difference = round($difference);
	    		
	    $period = $GLOBALS['TL_LANG']['FMD']['timetags_timesince_plural'][$j];
	    if($difference == 1) {
	    	$period = $GLOBALS['TL_LANG']['FMD']['timetags_timesince'][$j];
	    }
	    
	    return sprintf($this->fmtString, $difference.' '.$period);
	}	
}

?>