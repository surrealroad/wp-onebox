<?php

// OpenGraph Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();
	$match = !in_array("title", $properties) || !in_array("description", $properties) || !in_array("image", $properties) || !in_array("sitename", $properties);

	if($match) {
		$data =  pq_get_opengraph_data($onebox->getHTML());
		$onebox->update($data);

		// verify we succeeded with this method, otherwise try with pq
		$properties = $onebox->availableProperties();
		$match = !in_array("title", $properties) || !in_array("description", $properties) || !in_array("image", $properties) || !in_array("sitename", $properties);
		if($match) {
			$data =  get_opengraph_data($onebox->getDoc());
			$onebox->update($data);
		}
	}
}


function get_opengraph_data($doc) {
	require_once(WP_PLUGIN_DIR.'/onebox/lib/OpenGraph.php');
	//try to get open graph data
	// http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php
	$graph = OpenGraph::process($doc);
	//foreach ($graph as $key => $value) echo "$key => $value";
	$data = array();
	//$data['url']=$graph->url;
	$data['title']=$graph->title;
	$data['description']=$graph->description;
	$data['image']=$graph->image;
	/*if(isset($data['image'])) {
		@$data['imagewidth']=$graph->image->width;
		@$data['imageheight']=$graph->image->height;
	}*/
	$data['sitename']=$graph->site_name;

	return $data;
}

function pq_get_opengraph_data($html) {
	$data = array();
	phpQuery::newDocument($html);
	$imageProp = pq("meta[property=og:image]")->attr("content");
	$imageName = pq("meta[name=og:image]")->attr("content");
	$titleProp = pq("meta[property=og:title]")->attr("content");
	$titleName = pq("meta[name=og:title]")->attr("content");
	$descProp = pq("meta[property=og:description]")->attr("content");
	$descName = pq("meta[name=og:description]")->attr("content");
	$siteProp = pq("meta[property=og:site_name]")->attr("content");
	$siteName = pq("meta[name=og:site_name]")->attr("content");
	if($imageProp) $data['image'] = $imageProp;
	elseif($imageName) $data['image'] = $imageName;
	if($titleProp) $data['title'] = $titleProp;
	elseif($titleName) $data['title'] = $titleName;
	if($descProp) $data['description'] = $descProp;
	elseif($descName) $data['description'] = $descName;
	if($siteProp) $data['sitename'] = $siteProp;
	elseif($siteName) $data['sitename'] = $siteName;

	return $data;
}