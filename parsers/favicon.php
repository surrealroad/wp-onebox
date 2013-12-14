<?php

// Favicon Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("favicon", $properties);

	if($match) {
		$data =  get_favicon($onebox);
		if($data) $onebox->update($data);
	}
}


function get_favicon($onebox) {
	// http://stackoverflow.com/questions/5701593/how-to-get-a-websites-favicon-with-php
	$data = array();
	$touchicon = parse_url($onebox->data['url'], PHP_URL_SCHEME)."://".parse_url($onebox->data['url'], PHP_URL_HOST)."/apple-touch-icon.png";
	$file_headers = @get_headers($touchicon);
	if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
		$doc = $onebox->getDoc();
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
	}
	else {
	    $data['favicon'] = $touchicon;
	}

	return $data;
}
