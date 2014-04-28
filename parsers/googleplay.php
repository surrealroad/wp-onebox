<?php

// Google Play Store Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/play\\.google\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-googleplay");
		$data = get_googleplay_data($onebox);
		$onebox->update($data);
	}
}


function get_googleplay_data($onebox) {

	$url = $onebox->data['url'];

	$data=array();

	$data['sitename'] = "Google Play";
	$data['favicon']='http://ssl.gstatic.com/android/market_images/web/favicon.ico';

	preg_match('#id=(\w+.\w+.\w+)#', $url, $regex);
	@$ID = $regex[1];

	phpQuery::newDocument($onebox->getHTML());

	$title = pq(".document-title div")->text();
	if($title) $data['title'] = $title;

	$desc = pq(".text-body div p:first")->text();
	if(!$desc) $desc = pq(".description meta[itemprop=description]")->attr("content");
	if($desc) $data['description'] = substr($desc,0,300);

	$additional = array();
	$metas = pq(".meta-info");
	if($metas) {
		foreach ($metas as $meta) {
			$additional[] = pq(".title", $meta)->text().": ".pq(".content", $meta)->text();
		}
	}

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}

	$img = pq(".cover-image")->attr("src");
	if($img) $data['image']= $img;

	$rating = pq(".score-container meta[itemprop=ratingValue]")->attr("content");
	$ratingCount = pq(".score-container meta[itemprop=ratingCount]")->attr("content");
	if($rating) $data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.$rating.'</span> ('.intval($ratingCount).')</div>';

	$price = pq(".price.buy meta[itemprop=price]")->attr("content");
	if(!$price) $price = pq(".price.buy:first")->text();
	if($price) $data['footerbutton']= '<a href="'.$url.'">'.$price.'</a>';

	return $data;
}
