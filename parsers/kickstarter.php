<?php

// Kickstarter Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\\/\\/www\\.kickstarter\\.com\\/.+$/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-kickstarter");
		$data = get_kickstarter_data($onebox);
		$onebox->update($data);
	}
}


function get_kickstarter_data($onebox) {

	$data=array();

	$data['sitename'] = "Kickstarter";
	$url_parts = parse_url($onebox->data['url']);
	$baseURL = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path'])?$url_parts['path']:'');

	phpQuery::newDocument($onebox->getHTML());

	$data['favicon']=pq("link[rel=apple-touch-icon-precomposed]:first")->attr("href");
	$data['image']=pq("meta[property=og:image]")->attr("content");

	$title = pq("#title")->text();
	if($title) $data['title'] = $title;

	$desc = pq(".short_blurb")->text();
	if($desc) {
		//if(strlen($desc)>300) $desc=substr($desc,0,300);
		$data['description'] = $desc;
	}

	$additional = array();
	$target = pq("#stats .money:last")->text();
	if($target) $additional[]= __('Target: ', "onebox").$target;
	$current = pq("#pledged")->text();
	if($current) $additional[]= __('Raised: ', "onebox").$current;
	$time = pq("#stats .poll")->text();
	if($current) $additional[]= $time;

	$footer = array();
	$statusEl = pq(".NS_projects__funding_bar p");
	$status = $statusEl->find("b")->remove()->text();
	$statusText = $statusEl->text();
	if($status) $footer[]= '<strong>'.$status.'</strong>';
	if($statusText) $footer[]= $statusText;

	$titlebutton = array();
	$updateCount = pq("#updates_nav span.count")->text();
	if($updateCount) $titlebutton[]='<a href="'.$baseURL.'/posts" title="'.$updateCount.' '.__('updates', "onebox").'"><i class="onebox-icon onebox-note-icon"></i> '.$updateCount.'</a>';
	$backerCount = pq("#backers_nav span.count")->text();
	if($backerCount) $titlebutton[]='<a href="'.$baseURL.'/backers" title="'.$backerCount.' '.__('backers', "onebox").'"><i class="onebox-icon onebox-thumbsup-icon"></i> '.$backerCount.'</a>';
	$commentsCount = pq("#comments_nav span.count")->text();
	if($commentsCount) $titlebutton[]='<a href="'.$baseURL.'/comments" title="'.$commentsCount.' '.__('comments', "onebox").'"><i class="onebox-icon onebox-comment-icon"></i> '.$commentsCount.'</a>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}
	if(count($titlebutton)) {
		$data['titlebutton'] = implode(" ", $titlebutton);
	}

	return $data;
}
