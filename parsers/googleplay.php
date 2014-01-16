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

	$data['favicon']='http://ssl.gstatic.com/android/market_images/web/favicon.ico';

	preg_match('#id=(\w+.\w+.\w+)#', $url, $regex);
	@$ID = $regex[1];

	if($ID) {
		require_once(WP_PLUGIN_DIR.'/onebox/lib/google-play-store-api/playStoreApi.php'); // including class file
        $class_init = new PlayStoreApi; // initiating class

        $itemInfo = $class_init->itemInfo($ID); // calling itemInfo

        if($itemInfo !== 0)
        {
            print_r($itemInfo); // it will show all data inside an array
        }
	}
	return $data;
}
