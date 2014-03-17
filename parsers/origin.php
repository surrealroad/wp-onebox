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

	$finder = new DomXPath($onebox->getDoc());

	@$title = strip_tags($finder->query("//h1")->item(0)->nodeValue);
	if($title) $data['title'] = $title;

	@$price = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' actual-price ')]")->item(0)->nodeValue);
	if($price) $data['footerbutton']= '<a href="'.$displayurl.'">'.$price.'</a>';

	$additional = array();
	@$genrelist = $finder->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' game-info ')]/tr[1]/td[contains(concat(' ', normalize-space(@class), ' '), ' detail ')]/a");
	if(count($genrelist)) {
		$genres = array();
		foreach($genrelist as $genre) {
			$genres[]=strip_tags($genre->nodeValue);
		}
		$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
	}
	@$contentRating = $finder->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' game-info ')]/tr[3]/td[contains(concat(' ', normalize-space(@class), ' '), ' detail ')]")->item(0)->nodeValue;
	if($contentRating) $additional[]= __('Content advisory rating: ', "onebox").trim($contentRating);

	$footer = array();
	@$releaseDate = strip_tags($finder->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' game-info ')]/tr[2]/td[contains(concat(' ', normalize-space(@class), ' '), ' detail ')]")->item(0)->nodeValue);
	if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.trim($releaseDate).'</strong>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}

	return $data;
}
