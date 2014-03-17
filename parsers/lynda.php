<?php

// Lynda.com Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.lynda\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-lynda");
		$data = get_lynda_data($onebox);
		$onebox->update($data);
	}
}


function get_lynda_data($onebox) {

	$data=array();

	$data['favicon']='http://www.lynda.com/apple-touch-icon.png';
	$data['sitename'] = "lynda.com";
	$query = parse_url($onebox->data['url'], PHP_URL_QUERY);
	if($query) {
	    $data['displayurl']=$onebox->data['url'].'&utm_medium=ldc-partner&utm_source=SSPRC&utm_content=524&utm_campaign=CD15086&bid=524&aid=CD15086';
	} else {
	    $data['displayurl']=$onebox->data['url'].'?utm_medium=ldc-partner&utm_source=SSPRC&utm_content=524&utm_campaign=CD15086&bid=524&aid=CD15086';
	}

	phpQuery::newDocument($onebox->getHTML());

	$title = pq(".course-title")->text();
	if($title) $data['title'] = $title;

	$desc = pq(".course-description")->text();
	if($desc) {
		//if(strlen($desc)>300) $desc=substr($desc,0,300);
		$data['description'] = $desc;
	}


	$image = pq(".banner-thumb")->attr("style");
	if($image) {
		preg_match('#url\(([a-zA-Z0-9_:/.-]+)\)#', $image, $regex);
		$data['image'] = $regex[1];
	}

	$additional = array();
	$runningTime = pq(".course-meta span:eq(0)")->text();
	if($runningTime) $additional[]= __('Running time: ', "onebox").$runningTime;
	$level = pq(".course-meta span:eq(1)")->text();
	if($level) $additional[]= __('Recommended level: ', "onebox").$level;

	$footer = array();
	$releaseDate = pq(".course-meta span:eq(3)")->text();
	if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.$releaseDate.'</strong>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}

	return $data;
}
