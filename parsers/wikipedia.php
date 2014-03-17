<?php

// Wikipedia Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('#https?:\/\/.*\.wikipedia\.org#', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-wikipedia");
		$data = get_wikipedia_data($onebox);
		$onebox->update($data);
	}
}


function get_wikipedia_data($onebox) {
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

	$data=array();

	$data['favicon']='http://www.lynda.com/apple-touch-icon.png';
	$data['sitename'] = "Wikipedia";

	$raw = new nokogiri($onebox->getHTML());

	$title = $raw->get("html body h1");
	if($title) $data['title'] = $title;

	return $data;
}
