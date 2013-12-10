<?php

// HTML Meta Tags Parser for OneBox

if(isset($onebox)) {
	$properties = $onebox->availableProperties();

	$match = !in_array("title", $properties) || !in_array("description", $properties);

	if($match) {
		$data =  get_html_meta_data($onebox->getDoc());
		$onebox->update($data);
	}
}


function get_html_meta_data($doc) {
	// http://stackoverflow.com/questions/3711357/get-title-and-meta-tags-of-external-site
	$data=array();
	$nodes = $doc->getElementsByTagName('title');
	$data['title']=$nodes->item(0)->nodeValue;
	$metas = $doc->getElementsByTagName('meta');

	for ($i = 0; $i < $metas->length; $i++)
	{
		$meta = $metas->item($i);
		if($meta->getAttribute('name') == 'description')
			$data['description'] = $meta->getAttribute('content');
	}
	return $data;
}
