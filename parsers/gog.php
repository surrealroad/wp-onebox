<?php

// iTunes Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.gog\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-gog");
		$data = get_gog_data($onebox->data['url']);
		$onebox->update($data);
	}
}


function get_gog_data($url) {

	$query = parse_url($url, PHP_URL_QUERY);
	if($query) {
	    $data['displayurl']=$url.'&pp=ec7f1f65067126f3b2bd1037de8a18d0db2ec84b';
	} else {
	    $data['displayurl']=$url.'?pp=ec7f1f65067126f3b2bd1037de8a18d0db2ec84b';
	}

	preg_match('#/(game|gamecard)/(\w+)/?\??#', $url, $regex);
	$ID = $regex[2];

	if($ID) {
		require_once("lib/html5lib-php/library/HTML5/Parser.php");

		$file = file_get_contents($url);
		$doc = HTML5_Parser::parse($file);
		$finder = new DomXPath($doc);

		//$img = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' game_top ')]/img");
		//$data['image']= $img->item(0)->getAttribute("src");

		$fullrating = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' game_top ')]/ul/li/span[contains(concat(' ', normalize-space(@class), ' '), ' usr_rate ')]/span[contains(concat(' ', normalize-space(@class), ' '), ' usr_s_f ')]");
		$halfrating = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' game_top ')]/ul/li/span[contains(concat(' ', normalize-space(@class), ' '), ' usr_rate ')]/span[contains(concat(' ', normalize-space(@class), ' '), ' usr_s_h ')]");
		$rating = $fullrating->length + 0.5 * $halfrating->length;
		$ratingCountText = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' game_top ')]/ul/li/span[contains(concat(' ', normalize-space(@class), ' '), ' usr_rate ')]/../span")->item(1)->nodeValue;
		preg_match_all('/\d+/', $ratingCountText, $matches); // http://stackoverflow.com/questions/11243447/get-numbers-from-string-with-php
		$ratingCount = $matches[0][0];
		$data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$rating.'</span> ('.intval($ratingCount).')</div>';

		$releaseDate = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' game_top ')]/ul[contains(concat(' ', normalize-space(@class), ' '), ' details ')]/li")->item(3)->nodeValue;
		if($releaseDate) $data['footer']= 'Released: <strong>'.$releaseDate.'</strong>';


		$title = $finder->query('//title')->item(0)->nodeValue;
		$regs = array();
		if (preg_match('/(?<=\$)\d+(\.\d+)?\b/', $title, $regs)) {
	    	$data['footerbutton']= '<a href="'.$data['displayurl'].'">$'.$regs[0].'</a>';
		}
	}
	return $data;
}
