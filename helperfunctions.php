<?php
function smartdate($timestamp) {
	$diff = time() - $timestamp;
 
	if ($diff <= 0) {
		return 'Now';
	}
	else if ($diff < 60) {
		return grammar_date(floor($diff), ' second(s) ago');
	}
	else if ($diff < 60*60) {
		return grammar_date(floor($diff/60), ' minute(s) ago');
	}
	else if ($diff < 60*60*24) {
		return grammar_date(floor($diff/(60*60)), ' hour(s) ago');
	}
	else if ($diff < 60*60*24*7) {
		return grammar_date(floor($diff/(60*60*24)), ' day(s) ago');
	}
    else {
        return date("F j, Y, g:i a", $timestamp);
    }
}
 
 
function grammar_date($val, $sentence) {
	if ($val > 1) {
		return $val.str_replace('(s)', 's', $sentence);
	} else {
		return $val.str_replace('(s)', '', $sentence);
	}
}

function getDateTime() {
	return date("Y-m-d H:i:s");
}

/*
	StarID is in the form of 2 letters followed by 4 numbers ending in another 2 letters
*/
function isStarID($username) {

	return preg_match("/[a-zA-Z]{2}[0-9]{4}[a-zA-Z]{2}/", $username);

}

?>