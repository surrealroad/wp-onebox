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

	$finder = new DomXPath($onebox->getDoc());

	@$title = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' course-title ')]")->item(0)->nodeValue);
	if($title) $data['title'] = $title;

	@$desc = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' course-description ')]")->item(0)->nodeValue);
	if($desc) {
		//if(strlen($desc)>300) $desc=substr($desc,0,300);
		$data['description'] = $desc;
	}


	@$image = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' banner-thumb ')]")->item(0)->getAttribute('style');
	if($image) {
		preg_match('#url\(([a-zA-Z0-9_:/.-]+)\)#', $image, $regex);
		$data['image'] = $regex[1];
	}

	$additional = array();
	@$runningTime = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' course-meta ')]/span")->item(0)->nodeValue);
	if($runningTime) $additional[]= __('Running time: ', "onebox").$runningTime;
	@$level = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' course-meta ')]/span")->item(1)->nodeValue);
	if($level) $additional[]= __('Recommended level: ', "onebox").$level;

	$footer = array();
	@$releaseDate = strip_tags($finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' course-meta ')]/span")->item(3)->nodeValue);
	if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.$releaseDate.'</strong>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}

	return $data;
}
