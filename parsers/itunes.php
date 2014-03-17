<?php

// iTunes Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/itunes\\.apple\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-itunes");
		$onebox->shouldCacheLocation = true;
		$data = get_itunes_data($onebox, $onebox->data['countrycode']);
		$onebox->update($data);
	}
}


function get_itunes_data($onebox, $cc="") {

	$url = $onebox->data['url'];

	$data=array();

	$data['favicon']='http://www.apple.com/apple-touch-icon.png';

	$query = parse_url($url, PHP_URL_QUERY);
	if($query) {
	    $data['displayurl']=$url.'&at=10l5Th';
	} else {
	    $data['displayurl']=$url.'?at=10l5Th';
	}

	preg_match('#/id(\d{4,12})#', $url, $regex);
	@$ID = $regex[1];

	if($ID) {
		preg_match('#apple.com/([a-zA-Z]{2})/#', $url, $regex);
		@$country = $regex[1];
		if($country && $cc) $data['displayurl'] = preg_replace('#apple.com/([a-zA-Z]{2})/#', 'apple.com/'.$cc.'/', $data['displayurl']);
		elseif($country) $cc = $country;
		else $data['displayurl'] = 'http://target.georiot.com/Proxy.ashx?tsid=2822&GR_URL='.urlencode($url);

		$info = json_decode(file_get_contents("http://itunes.apple.com/lookup?id=".$ID."&country=".$cc), true);

		// test for not available in user's region
		if(isset($info['resultCount']) && $info['resultCount'] <1 && strtoupper($cc) != strtoupper($country)) {
			$info = json_decode(file_get_contents("http://itunes.apple.com/lookup?id=".$ID."&country=".$country), true);
			$available = false;
			$data['displayurl'] = 'http://target.georiot.com/Proxy.ashx?tsid=2822&GR_URL='.urlencode($url);
		} else {
			$available = true;
		}

		if($onebox->affiliateLinks && $data['displayurl']) $displayurl = $data['displayurl'];
		else $displayurl = $data['url'];

		if(isset($info['results'][0])) {
			if(isset($info['results'][0]['kind'])) $type = $info['results'][0]['kind'];
			elseif(isset($info['results'][0]['collectionType'])) $type = $info['results'][0]['collectionType'];

			if($type=="ebook" && isset($info['results'][0]['trackCensoredName'])) $data['title']= $info['results'][0]['artistName']." &mdash; ".$info['results'][0]['trackCensoredName'];
			elseif(isset($info['results'][0]['trackCensoredName'])) $data['title']= $info['results'][0]['trackCensoredName'];
			elseif($type=="TV Season" && isset($info['results'][0]['collectionCensoredName'])) $data['title']= $info['results'][0]['collectionCensoredName'];
			elseif(isset($info['results'][0]['collectionCensoredName'])) $data['title']= $info['results'][0]['artistName']." &mdash; ".$info['results'][0]['collectionCensoredName'];

			$desc ="";
			if(isset($info['results'][0]['description'])) $desc= strip_tags($info['results'][0]['description']);
			elseif(isset($info['results'][0]['longDescription'])) $desc= strip_tags($info['results'][0]['longDescription']);
			if(strlen($desc)>300) $desc=substr($desc,0,300);

			$additional = array();
			if($type=="TV Season") $trackLabel = __("episodes", "onebox");
			else $trackLabel = __("tracks", "onebox");

			if($type != "feature-movie" && isset($info['results'][0]['trackCount']) && $info['results'][0]['trackCount']>1) $additional[]= $info['results'][0]['trackCount'].' '.$trackLabel;
			if(isset($info['results'][0]['primaryGenreName'])) $additional[]= __('Genre: ', "onebox").$info['results'][0]['primaryGenreName'];
			if(isset($info['results'][0]['contentAdvisoryRating'])) $additional[]= __('Content advisory rating: ', "onebox").$info['results'][0]['contentAdvisoryRating'];
			if(isset($info['results'][0]['trackTimeMillis'])) $additional[]= __('Running time: ', "onebox").gmdate("H:i:s", ($info['results'][0]['trackTimeMillis']/1000));

			$footer = array();
			if(isset($info['results'][0]['releaseDate'])) $footer[]= __('Released: ', "onebox").'<strong>'.date('F jS Y', strtotime($info['results'][0]['releaseDate'])).'</strong>';
			if(isset($info['results'][0]['version'])) $footer[]= __('Current version: ', "onebox").'<strong>'.$info['results'][0]['version'].'</strong>';

			if(isset($info['results'][0]['averageUserRating'])) $data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$info['results'][0]['averageUserRating'].'</span> ('.intval($info['results'][0]['userRatingCount']).')</div>';
			if($available && isset($info['results'][0]['formattedPrice'])) $data['footerbutton']= '<a href="'.$displayurl.'">'.$info['results'][0]['formattedPrice'].'</a>';
			elseif($available && isset($info['results'][0]['collectionPrice'])) $data['footerbutton']= '<a href="'.$displayurl.'">'.$onebox->country_currency($cc, $info['results'][0]['collectionPrice']).'</a>';
			elseif(!$available) $data['footerbutton']=__('May not be available in your region', "onebox");

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
