<?php

// Gog.com Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.gog\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-gog");
		$data = get_gog_data($onebox);
		$onebox->update($data);
	}
}


function get_gog_data($onebox) {

	$data=array();
	$url = $onebox->data['url'];

	$query = parse_url($url, PHP_URL_QUERY);
	if($query) {
	    $data['displayurl']=$url.'&pp=ec7f1f65067126f3b2bd1037de8a18d0db2ec84b';
	} else {
	    $data['displayurl']=$url.'?pp=ec7f1f65067126f3b2bd1037de8a18d0db2ec84b';
	}
	if($onebox->affiliateLinks) $displayurl = $data['displayurl'];
	else $displayurl = $onebox->data['url'];

	preg_match('#/(game|gamecard)/(\w+)/?\??#', $url, $regex);
	$ID = $regex[2];

	if($ID) {
		require_once(WP_PLUGIN_DIR.'/onebox/lib/html5lib/library/HTML5/Parser.php');

		phpQuery::newDocument($onebox->getHTML());

		$additional = array();
		$genrelist = pq(".game_top ul.details li:eq(0) a");
		if(count($genrelist)) {
			$genres = array();
			foreach($genrelist as $genre) {
				$genres[]=pq($genre)->text();
			}
			$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
		}

		$fullrating = pq(".game_top ul.details li span.usr_rate span.usr_s_f");
		$halfrating = pq(".game_top ul.details li span.usr_rate span.usr_s_h");
		@$rating = $fullrating->length + 0.5 * $halfrating->length;
		$ratingCountText = pq(".game_top ul.details li span.usr_rate")->parent()->text();
		preg_match_all('/\d+/', $ratingCountText, $matches); // http://stackoverflow.com/questions/11243447/get-numbers-from-string-with-php
		@$ratingCount = $matches[0][0];
		$data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$rating.'</span> ('.intval($ratingCount).')</div>';

		$footer = array();
		$releaseDate = pq(".game_top ul.details li:eq(3)")->text();
		if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.$releaseDate.'</strong>';
		$size = pq(".download_size b")->text();
		if($size) $footer[]= __('Download size: ', "onebox").'<strong>'.$size.'</strong>';


		$title = pq("title")->text();
		$regs = array();
		if (preg_match('/(?<=\$)\d+(\.\d+)?\b/', $title, $regs)) {
	    	$data['footerbutton']= '<a href="'.$displayurl.'">$'.$regs[0].'</a>';
		}

		if(count($additional)) {
			$data['additional'] = implode("<br/>", $additional);
		}
		if(count($footer)) {
			$data['footer'] = implode(" &middot; ", $footer);
		}
	}
	return $data;
}
