<?php

// iTunes Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/itunes\\.apple\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-itunes");
		$data = get_itunes_data($onebox, $onebox->data['countrycode']);
		$onebox->update($data);
	}
}


function get_itunes_data($onebox, $cc="") {

	$url = $onebox->data['url'];

	$data=array();

	$data['favicon']='http://www.apple.com/favicon.ico';

	$query = parse_url($url, PHP_URL_QUERY);
	if($query) {
	    $data['displayurl']=$url.'&at=10l5Th';
	} else {
	    $data['displayurl']=$url.'?at=10l5Th';
	}

	preg_match('#/id(\d{4,12})#', $url, $regex);
	$ID = $regex[1];

	if($ID) {
		$info = json_decode(file_get_contents("http://itunes.apple.com/lookup?id=".$ID."&country=".$cc), true);

		if(isset($info['results'][0])) {
			if(isset($info['results'][0]['kind'])) $type = $info['results'][0]['kind'];
			elseif(isset($info['results'][0]['collectionType'])) $type = $info['results'][0]['collectionType'];

			if(isset($info['results'][0]['trackCensoredName'])) $data['title']= $info['results'][0]['trackCensoredName'];
			elseif(isset($info['results'][0]['collectionCensoredName'])) $data['title']= $info['results'][0]['artistName']." &mdash; ".$info['results'][0]['collectionCensoredName'];

			$desc ="";
			if(isset($info['results'][0]['description'])) $desc=$info['results'][0]['description'];
			if(strlen($desc)>300) $desc=substr($desc,0,300);

			$additional = array();
			if(isset($info['results'][0]['trackCount']) && $info['results'][0]['trackCount']>1) $additional[]= $info['results'][0]['trackCount'].' '.__('tracks', "onebox");
			if(isset($info['results'][0]['primaryGenreName'])) $additional[]= __('Genre: ', "onebox").$info['results'][0]['primaryGenreName'];
			if(isset($info['results'][0]['contentAdvisoryRating'])) $additional[]= __('Content advisory rating: ', "onebox").$info['results'][0]['contentAdvisoryRating'];

			$footer = array();
			if(isset($info['results'][0]['releaseDate'])) $footer[]= __('Released: ', "onebox").'<strong>'.date('F jS Y', strtotime($info['results'][0]['releaseDate'])).'</strong>';
			if(isset($info['results'][0]['version'])) $footer[]= __('Current version: ', "onebox").'<strong>'.$info['results'][0]['version'].'</strong>';

			if(isset($info['results'][0]['averageUserRating'])) $data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$info['results'][0]['averageUserRating'].'</span> ('.intval($info['results'][0]['userRatingCount']).')</div>';
			if(isset($info['results'][0]['formattedPrice'])) $data['footerbutton']= '<a href="'.$data['displayurl'].'">'.$info['results'][0]['formattedPrice'].'</a>';
			elseif(isset($info['results'][0]['collectionPrice'])) $data['footerbutton']= '<a href="'.$data['displayurl'].'">'.$onebox->country_currency($cc, $info['results'][0]['collectionPrice']).'</a>';

			$data['description'] = $desc;
			if(count($additional)) {
				$data['additional'] = implode("<br/>", $additional);
			}
			if(count($footer)) {
				$data['footer'] = implode(" &middot; ", $footer);
			}
		}
	}
	return $data;
}
