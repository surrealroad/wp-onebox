<?php

// OpenGraph Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("title", $properties) || !in_array("description", $properties) || !in_array("image", $properties) || !in_array("sitename", $properties);

	if($match) {
		$data =  get_microdata_data($onebox->data['url']);
		$onebox->update($data);
	}
}


function get_microdata_data($url) {
	require_once(WP_PLUGIN_DIR.'/onebox/lib/MicrodataPhp.php');
	$md = new MicrodataPhp($url);
	$data = $md->obj();
	var_dump($data);
}
