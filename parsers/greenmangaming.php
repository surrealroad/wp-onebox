<?php

// Green Man Gaming Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.greenmangaming\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-greenmangaming");
		$data = get_greenmangaming_data($onebox);
		$onebox->update($data);
	}
}


function get_greenmangaming_data($onebox) {

	$data=array();

	$data['favicon']='http://www.greenmangaming.com/static/favicon.ico';
	$data['sitename'] = "Green Man Gaming";
	$data['displayurl']='http://www.anrdoezrs.net/click-5748306-10913188?URL='.urlencode($onebox->data['url']);
	if($onebox->affiliateLinks) $displayurl = $data['displayurl'];
	else $displayurl = $onebox->data['url'];

	phpQuery::newDocument($onebox->getHTML());

	$title = pq(".prod_det")->text();
	if($title) $data['title'] = $title;

	$price = pq(".curPrice")->text();
	if($price) $data['footerbutton']= '<a href="'.$displayurl.'">'.$price.'</a>';

	$additional = array();
	$genrelist = pq(".game_details tr td:contains('Genres:')")->parent()->find("td:eq(1) a");
	if(count($genrelist)) {
		$genres = array();
		foreach($genrelist as $genre) {
			$genres[]=pq($genre)->text();
		}
		$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
	}

	$footer = array();
	$releaseDate = pq(".game_details tr td:contains('Released:')")->parent()->find("td:eq(1)")->text();
	if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.$releaseDate.'</strong>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}

	return $data;
}
