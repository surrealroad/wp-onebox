<?php

// Steam Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.greenmangaming\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-greenmangaming");
		$data = get_greenmangaming_data($onebox);
		$onebox->update($data);
	}
}


function get_greenmangaming_data($onebox) {

	$data=array();

	$data['favicon']='http://www.greenmangaming.com/static/favicon.ico';
	$data['sitename'] = "Green Man Gaming";
	$data['displayurl']='http://www.anrdoezrs.net/click-5748306-10913188?URL='.urlencode($onebox->data['url']);

	$finder = new DomXPath($onebox->getDoc("utf-8"));

	@$title = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' prod_det ')]")->item(0)->nodeValue;
	if($title) $data['title'] = $title;

	@$price = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' curPrice ')]")->item(0)->nodeValue;
	if($price) $data['footerbutton']= '<a href="'.$data['displayurl'].'">'.$price.'</a>';

	return $data;
}
