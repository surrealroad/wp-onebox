<?php

// TwitterCard Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("title", $properties) || !in_array("description", $properties) || !in_array("image", $properties);

	if($match) {
		$data =  get_twittercard_data($onebox->getDoc());
		$onebox->update($data);
	}
}


function get_twittercard_data($doc) {
	//try to twitter data
	// http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php
	$xpath = new DOMXPath($doc);
	$query = '//*/meta[starts-with(@name, \'twitter:\')]';
	$metas = $xpath->query($query);
	$rmetas=array();
	foreach ($metas as $meta) {
		$name = $meta->getAttribute('name');
		$content = $meta->getAttribute('content');
		// don't overwrite existing properties
		if(!$rmetas[$name]) $rmetas[$name] = $content;
	}
	$data=array();
	if($rmetas) {
		//$data['url']=$rmetas['twitter:url'];
		$data['title']=$rmetas['twitter:title'];
		$data['description']=$rmetas['twitter:description'];
		$data['image']=$rmetas['twitter:image'];
	}
	return $data;
}
