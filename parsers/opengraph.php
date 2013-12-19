<?php

// OpenGraph Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("title", $properties) || !in_array("description", $properties) || !in_array("image", $properties) || !in_array("sitename", $properties);

	if($match) {
		$data =  get_opengraph_data($onebox->getDoc());
		$onebox->update($data);
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
