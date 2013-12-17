<?php

// Steam Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.macgamestore\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-macgamestore");
		$data = get_macgamestore_data($onebox->data['url']);
		$onebox->update($data);
	}
}


function get_macgamestore_data($url) {

	$data=array();

	$data['favicon']='http://www.macgamestore.com/apple-touch-icon.png';
	$data['sitename'] = "Mac Game Store";
	$data['displayurl']='http://click.linksynergy.com/fs-bin/click?id=9zhtBX/DL9w&subid=&offerid=283896.1&type=10&tmpid=11753&RD_PARM1='.urlencode($url);

	preg_match('#/product/(\w+)/?\??#', $url, $regex);
	$ID = $regex[1];

	if($ID) {
		$mgsInfo = json_decode(file_get_contents("https://www.macgamestore.com/api/price-rating/".$ID."/"), true);

		$titlebutton = array();
		if(isset($mgsInfo['Rating'])) $data['titlebutton']= '<div class="onebox-rating"><span class="onebox-stars">'.intval($mgsInfo['Rating']).'</span> ('.intval($mgsInfo['NumRatings']).')</div>';

		if(isset($mgsInfo['Price'])) $data['footerbutton']= '<a href="'.$data['displayurl'].'">$'.$mgsInfo['Price'].'</a>';

		if(count($titlebutton)) {
			$data['titlebutton'] = implode(" ", $titlebutton);
		}
	}
	return $data;
}
