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

	preg_match('#/(game|gamecard|movie)/(\w+)/?\??#', $url, $regex);
	$type = $regex[1];
	$ID = $regex[2];

	if($ID) {

		phpQuery::newDocument($onebox->getHTML());

		$description = pq("read-more .description__text:eq(0)");
		if($description) {
			$description->find("b, .description__more")->remove();
			if($type == "game" || $type == "gamecard") $data['description'] = $description->text();
			else {
				$description->find("br:eq(0)")->remove();
				$data['description'] = $description->html();
			}
		}

		$additional = array();
		if($type == "game" || $type == "gamecard") {
			$genrelist = pq(".product-details .product-details__data:eq(0) a");
			if(count($genrelist)) {
				$genres = array();
				foreach($genrelist as $genre) {
					$genres[]=pq($genre)->text();
				}
				$additional[]= __('Genre: ', "onebox").implode(", ", $genres);
			}
			$contentRating = pq(".product-details .product-details__data:eq(7)")->text();
			if($contentRating) $additional[]= trim($contentRating);
		}


		$fullrating = pq(".header__rating i.icon-star-full");
		$halfrating = pq(".header__rating i.icon-star-half");
		@$rating = $fullrating->length + 0.5 * $halfrating->length;
		$ratingCountText = pq(".header__info .header__votes")->text();
		$data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$rating.'</span> ('.intval($ratingCountText).')</div>';

		$footer = array();
		if($type == "game" || $type == "gamecard") $releaseDate = pq(".product-details .product-details__data:eq(4)")->text();
		else $releaseDate = pq(".product-details .product-details__data:eq(1)")->text();
		if($releaseDate) $footer[]= __('Released: ', "onebox").'<strong>'.$onebox->oneboxdate(strtotime($releaseDate)).'</strong>';
		if($type == "game" || $type == "gamecard") $size = pq(".product-details .product-details__data:eq(5)")->text();
		else $size = pq(".product-details .product-details__data:eq(2)")->text();
		if($size) $footer[]= __('Download size: ', "onebox").'<strong>'.$size.'</strong>';


		$salePrice = pq(".module--buy .buy-price--discount:eq(0) .buy-price__new")->text();
		$buyPrice = pq(".module--buy .buy-price:eq(0)")->text();
		if($salePrice) $data['footerbutton']= '<a href="'.$displayurl.'">'.$salePrice.'</a>';
		elseif($buyPrice) $data['footerbutton']= '<a href="'.$displayurl.'">'.$buyPrice.'</a>';

		if(count($additional)) {
			$data['additional'] = implode("<br/>", $additional);
		}
		if(count($footer)) {
			$data['footer'] = implode(" &middot; ", $footer);
		}
	}
	return $data;
}
