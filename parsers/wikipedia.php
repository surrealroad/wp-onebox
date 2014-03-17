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

	$data['sitename'] = "Wikipedia";
	$data['favicon'] = "http://bits.wikimedia.org/apple-touch/wikipedia.png";

	phpQuery::newDocument($onebox->getHTML());

	$title = pq("html body h1")->text();
	if($title) $data['title'] = $title;

	// remove sups
	pq("sup")->remove();
	$desc = pq("#mw-content-text p:first")->text();
	if($desc) $data['description'] = $desc;

	$img = pq(".infobox .image img")->attr("src");
	if(!$img) $img = pq(".thumb.tright .image img")->attr("src");
	if($img)$data['image']= $img;

	return $data;
}
