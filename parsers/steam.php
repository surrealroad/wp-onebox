<?php

// Steam Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/store\\.steampowered\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-steam");
		$onebox->shouldCacheLocation = true;
		$data = get_steam_data($onebox, $onebox->data['countrycode']);
		$onebox->update($data);
	}
}


function get_steam_data($onebox, $cc="") {

	$url = $onebox->data['url'];

	$data=array();

	$data['favicon']='http://store.steampowered.com/apple-touch-icon.png';
	$data['sitename'] = "Steam";

	preg_match('#/app/(\w+)/?\??#', $url, $regex);
	@$ID = $regex[1];
	if(!$ID) {
		preg_match('#/sub/(\w+)/?\??#', $url, $regex);
		@$ID = $regex[1];
		$type = "package";
	} else {
		$type = "app";
	}

	if($ID) {
		$steamInfo = json_decode(file_get_contents("http://store.steampowered.com/api/".$type."details/?".$type."ids=".$ID."&cc=".$cc), true);

		if(isset($steamInfo[$ID]['data']['name'])) $data['title']= $steamInfo[$ID]['data']['name'];

		if(isset($steamInfo[$ID]['data']['header_image'])) $data['image']= $steamInfo[$ID]['data']['header_image'];

		$desc ="";
		if(isset($steamInfo[$ID]['data']['about_the_game'])) $desc=strip_tags($steamInfo[$ID]['data']['about_the_game']);
		elseif(isset($steamInfo[$ID]['data']['page_content'])) $desc=strip_tags($steamInfo[$ID]['data']['page_content']);
		if(strlen($desc)>300) $desc=substr($desc,0,300);

		$additional = array();
		if(isset($steamInfo[$ID]['data']['genres'])) {
			$genres = array();
			foreach($steamInfo[$ID]['data']['genres'] as $genre) {
				$genres[] = $genre['description'];
			}
			$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
		}
		if(isset($steamInfo[$ID]['data']['required_age'])) $additional[]= __('Required age: ', "onebox").$steamInfo[$ID]['data']['required_age'];

		$footer = array();
		if(isset($steamInfo[$ID]['data']['release_date']['date']) && $steamInfo[$ID]['data']['release_date']['date']) $footer[]= __('Released: ', "onebox").'<strong>'.date('F jS Y', strtotime($steamInfo[$ID]['data']['release_date']['date'])).'</strong>';

		$titlebutton = array();
		if(isset($steamInfo[$ID]['data']['recommendations']['total'])) $titlebutton[]='<a href="http://steamcommunity.com/app/'.$ID.'/reviews/" title="'.__('Read reviews', "onebox").'"><i class="onebox-icon onebox-thumbsup-icon"></i> '.intval($steamInfo[$ID]['data']['recommendations']['total']).'</a>';
		if(isset($steamInfo[$ID]['data']['achievements']['total'])) $titlebutton[]='<a href="http://steamcommunity.com/stats/'.$ID.'/achievements/" title="'.__('View achievements', "onebox").'"><i class="onebox-icon onebox-trophy-icon"></i> '.intval($steamInfo[$ID]['data']['achievements']['total']).'</a>';

		if(isset($steamInfo[$ID]['data']['price_overview']['final'])) $data['footerbutton']= '<a href="'.$url.'">'.$onebox->country_currency($cc, ($steamInfo[$ID]['data']['price_overview']['final']/100)).'</a>';
		elseif(isset($steamInfo[$ID]['data']['price']['final'])) $data['footerbutton']= '<a href="'.$url.'">'.$onebox->country_currency($cc, ($steamInfo[$ID]['data']['price']['final']/100)).'</a>';

		$data['description'] = $desc;
		if(count($additional)) {
			$data['additional'] = implode("<br/>", $additional);
		}
		if(count($footer)) {
			$data['footer'] = implode(" &middot; ", $footer);
		}
		if(count($titlebutton)) {
			$data['titlebutton'] = implode(" ", $titlebutton);
		}
	}
	return $data;
}
