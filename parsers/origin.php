<?php

// Origin Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.origin\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-origin");
		$data = get_origin_data($onebox);
		$onebox->update($data);
	}
}


function get_origin_data($onebox) {

	$data=array();

	$data['favicon']='http://www.origin.com/favicon.ico';
	$data['sitename'] = "Origin";
	$data['displayurl']='http://clkuk.tradedoubler.com/click?p(123350)a(2204255)g(19995808)url('.$onebox->data['url'].')';
	if($onebox->affiliateLinks) $displayurl = $data['displayurl'];
	else $displayurl = $onebox->data['url'];

	phpQuery::newDocument($onebox->getHTML());

	$title = pq("h1:first")->text();
	if($title) $data['title'] = $title;

	$price = pq(".actual-price")->text();
	if($price) $data['footerbutton']= '<a href="'.$displayurl.'">'.$price.'</a>';

	$additional = array();
	$genrelist = pq(".game-info tr:eq(0) td.detail a");
	if(count($genrelist)) {
		$genres = array();
		foreach($genrelist as $genre) {
			$genres[]=pq($genre)->text();
		}
		$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
	}
	$contentRating = pq(".game-info tr:eq(2) td.detail")->text();
	if($contentRating) $additional[]= __('Content advisory rating: ', "onebox").trim($contentRating);

	$footer = array();
	$releaseDate = pq(".game-info tr:eq(1) td.detail")->text();
	if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.trim($releaseDate).'</strong>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}

	return $data;
}
