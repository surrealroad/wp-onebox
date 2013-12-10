<?php

// Favicon Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("favicon", $properties);

	if($match) {
		$data =  get_favicon($onebox->getDoc());
		if($data) $onebox->update($data);
	}
}


function get_favicon($doc) {
	// http://stackoverflow.com/questions/5701593/how-to-get-a-websites-favicon-with-php
	$data = array();
	$xpath = new DOMXPath($doc);
	$xml = simplexml_import_dom($doc);
	if($xml) {
		$arr = $xml->xpath('//link[@rel="shortcut icon"]');
		if(isset($arr[0]['href'])) $data['favicon'] = (string)$arr[0]['href'];
		else {
			$arr = $xml->xpath('//link[@rel="icon"]');
			if(isset($arr[0]['href'])) $data['favicon'] = (string)$arr[0]['href'];
		}
	}
	return $data;
}
