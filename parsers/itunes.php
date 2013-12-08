<?php

// iTunes Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/itunes\\.apple\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-itunes");
		$data = get_itunes_data($onebox->data['url'], $onebox->data['countrycode']);
		$onebox->update($data);
	}
}


function get_itunes_data($url, $cc="") {

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
			elseif(isset($info['results'][0]['collectionCensoredName'])) $data['title']= $info['results'][0]['collectionCensoredName'];

			$desc ="";
			if(isset($info['results'][0]['description'])) $desc=$info['results'][0]['description'];
			if(strlen($desc)>300) $desc=substr($desc,0,300)."&hellip;";
			$additional = array();


			if($type=="mac-software") {
				$url = 'http://itunes.apple.com/app/id'.$ID;
			}

			if(isset($info['results'][0]['version'])) $additional[]= 'Current version: '.$info['results'][0]['version'];
			if(isset($info['results'][0]['contentAdvisoryRating'])) $additional[]= 'Content advisory rating: '.$info['results'][0]['contentAdvisoryRating'];
			if(isset($info['results'][0]['averageUserRating'])) $additional[]= '<span class="onebox-stars">'.$info['results'][0]['averageUserRating'].'</span> Based on '.intval($info['results'][0]['userRatingCount']).' ratings';
			if(isset($info['results'][0]['formattedPrice'])) $additional[]= 'Price: '.$info['results'][0]['formattedPrice'];

			$data['description'] = $desc;
			if(count($additional)) {
				$data['additional'] = implode("<br/>", $additional);
			}
		}
	}
	return $data;
}
