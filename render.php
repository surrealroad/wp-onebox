<?php
/*
Generate Onebox HTML
*/

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

require_once("lib/OpenGraph.php");
require_once("lib/Encoding.php");

$url = urldecode($_GET["url"]);
$data = onebox_data($url);

$onebox = array('url'=>$url, 'data'=>$data, 'onebox'=>onebox_generate($data, true));
echo json_encode($onebox);


// get user country code

function user_cc() {
	/*
	include_once ("library/geo/geoip.inc");
	$gi = geoip_open("library/geo/GeoIP.dat",GEOIP_STANDARD);
	return geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']); // http://stackoverflow.com/questions/55768/how-do-i-find-a-users-ip-address-with-php
	*/
	//return "CA";
	// http://www.electrictoolbox.com/php-geoip-notice-ip-address-not-found/
	return @geoip_country_code_by_name($_SERVER['HTTP_X_FORWARDED_FOR']/*$_SERVER['REMOTE_ADDR']*/);
}

// http://snipplr.com/view.php?codeview&id=36437

function country_currency( $bc, $amount = 0 ) {
    $currency_before = '';
    $currency_after = '';

    if( $bc == 'GB' || $bc == 'IE' || $bc == 'CY' ) $currency_before = '&pound;';
    if( $bc == 'AT' || $bc == 'BE' || $bc == 'FI' || $bc == 'FR' ||
        $bc == 'DE' || $bc == 'GR' || $bc == 'GP' || $bc == 'IT' ||
        $bc == 'LU' || $bc == 'NL' || $bc == 'PT' || $bc == 'SI' ||
        $bc == 'ES') $currency_before = '&euro;';
    if( $bc == 'BR' ) $currency_before = 'R$';
    if( $bc == 'CN' || $bc == 'JP' ) $currency_before = '&yen;';
    if( $bc == 'CR' ) $currency_before = '&cent;';
    if( $bc == 'HR' ) $currency_after = ' kn';
    if( $bc == 'CZ' ) $currency_after = ' kc';
    if( $bc == 'DK' ) $currency_before = 'DKK ';
    if( $bc == 'EE' ) $currency_after = ' EEK';
    if( $bc == 'HK' ) $currency_before = 'HK$';
    if( $bc == 'HU' ) $currency_after = ' Ft';
    if( $bc == 'IS' || $bc == 'SE' ) $currency_after = ' kr';
    if( $bc == 'IN' ) $currency_before = 'Rs. ';
    if( $bc == 'ID' ) $currency_before = 'Rp. ';
    if( $bc == 'IL' ) $currency_after = ' NIS';
    if( $bc == 'LV' ) $currency_before = 'Ls ';
    if( $bc == 'LT' ) $currency_after = ' Lt';
    if( $bc == 'MY' ) $currency_before = 'RM';
    if( $bc == 'MT' ) $currency_before = 'Lm';
    if( $bc == 'NO' ) $currency_before = 'kr ';
    if( $bc == 'PH' ) $currency_before = 'PHP';
    if( $bc == 'PL' ) $currency_after = ' z';
    if( $bc == 'RO' ) $currency_after = ' lei';
    if( $bc == 'RU' ) $currency_before = 'RUB';
    if( $bc == 'SK' ) $currency_after = ' Sk';
    if( $bc == 'ZA' ) $currency_before = 'R ';
    if( $bc == 'KR' ) $currency_before = 'W';
    if( $bc == 'CH' ) $currency_before = 'SFr. ';
    if( $bc == 'SY' ) $currency_after = ' SYP';
    if( $bc == 'TH' ) $currency_after = ' Bt';
    if( $bc == 'TT' ) $currency_before = 'TT$';
    if( $bc == 'TR' ) $currency_after = ' TL';
    if( $bc == 'AE' ) $currency_before = 'Dhs. ';
    if( $bc == 'VE' ) $currency_before = 'Bs. ';

    if( $currency_before == '' && $currency_after == '' ) $currency_before = '$';

    return $currency_before . number_format( $amount, 2 ) . $currency_after;
}

// generate onnebox
function onebox($url, $full=true) {

	$data = onebox_data($url);
	return onebox_generate($data, $full);
}

function onebox_generate($data, $full=true) {
	if(!$full) {
		return '<p><a href="'.$data['url'].'" target="_blank" rel="nofollow">'.$data['title'].'</a></p>';
	} else {
		$result = '<div class="onebox-result">';
		$result .='<div class="onebox-source"><div class="onebox-info">';
		$result .='<a href="'.$data['url'].'" target="_blank" rel="nofollow">';
		if($data['favicon']) $result .='<img class="onebox-favicon" src="'.$data['favicon'].'">';
		$result .= '<span>'.$data['sitename'].'</span></a>';
		$result .='</div></div>';
		$result .='<div class="onebox-result-body">';
		if(isset($data['image'])) $result .='<a href="'.$data['url'].'" target="_blank" rel="nofollow"><img src="'.$data['image'].'" class="onebox-thumbnail"></a>';
		$result .='<h4><a href="'.$data['url'].'" target="_blank" rel="nofollow">'.$data['title'].'</a></h4>';
		$result .='<p class="onebox-description">'.\ForceUTF8\Encoding::toUTF8($data['description']).'</p>';
		$result .='</div>';
		$result .='<div class="onebox-clearfix"></div>';
		$result .='</div>';
		return $result;
	}
}

// generate onebox data from url

function onebox_data($url) {

	$doc = new DomDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTMLFile($url);
	libxml_use_internal_errors(false);

	//$file = file_get_contents($url);
	//$doc = HTML5_Parser::parse($file);
	// try to get open graph data
	$data =  get_opengraph_data($url);
	//var_dump($data);
	if(!$data['url'] || !$data['title'] || !$data['description'] || !$data['image']) {
		// try to get twitter data
		$tdata = get_twittercard_data($doc);
		$data = populate_onebox_data($data, $tdata);
	}
	if(!$data['title'] || !$data['description']) {
		// try to get html data
		$hdata = get_html_meta_data($doc);
		$data = populate_onebox_data($data, $hdata);
	}
	if(!$data['url']) $data['url']=$url;
	elseif(substr($data['url'],0,18)=='http://www.gog.com') $data['url']=$url; // don't override gog tags
	elseif(substr($data['url'],0,5)=='http%') $data['url']=$url; // fix for weird gog issue
	if(!$data['sitename']) $data['sitename']= str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
	$favicon=get_favicon($doc);
	// Prepend "http://" to any link missing the HTTP protocol text.
	if ($favicon && preg_match('|^https*://|', $favicon) === 0)
	{
		if (substr($favicon, 0, 1) !== '/') $favicon = "/".$favicon;
		$favicon = parse_url($url, PHP_URL_SCHEME)."://".parse_url($url, PHP_URL_HOST) . $favicon;
	}
	$data['favicon'] = $favicon;

	// force utf8 description
	$data['description'] = \ForceUTF8\Encoding::toUTF8($data['description']);

	return $data;
}

// update missing data values with new data values
function populate_onebox_data($data="", $newdata) {
	if(isset($newdata)) {
		foreach ($newdata as $key => $value) {
			if(!$data[$key]) $data[$key]=$value;
		}
	}
	return $data;
}

function get_opengraph_data($url) {
	//try to get open graph data
	// http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php
	$graph = OpenGraph::fetch($url);
	//foreach ($graph as $key => $value) echo "$key => $value";
	$data = array();
	$data['url']=$graph->url;
	$data['title']=$graph->title;
	$data['description']=$graph->description;
	$data['image']=$graph->image;
	if(isset($data['image'])) {
		@$data['imagewidth']=$graph->image->width;
		@$data['imageheight']=$graph->image->height;
	}
	$data['sitename']=$graph->site_name;
	return $data;
}

function get_twittercard_data($doc) {
	//try to twitter data
	// http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php
	$xpath = new DOMXPath($doc);
	$query = '//*/meta[starts-with(@name, \'twitter:\')]';
	$metas = $xpath->query($query);
	$rmetas=array();
	foreach ($metas as $meta) {
		$name = $meta->getAttribute('name');
		$content = $meta->getAttribute('content');
		// don't overwrite existing properties
		if(!$rmetas[$name]) $rmetas[$name] = $content;
	}
	$data = "";
	if($rmetas) {
		$data['url']=$rmetas['twitter:url'];
		$data['title']=$rmetas['twitter:title'];
		$data['description']=$rmetas['twitter:description'];
		$data['image']=$rmetas['twitter:image'];
	}
	return $data;
}

function get_favicon($doc) {
	// http://stackoverflow.com/questions/5701593/how-to-get-a-websites-favicon-with-php
	$xpath = new DOMXPath($doc);
	$xml = simplexml_import_dom($doc);
	if($xml) {
		$arr = $xml->xpath('//link[@rel="shortcut icon"]');
		if(isset($arr[0]['href'])) $icon = $arr[0]['href'];
		else {
			$arr = $xml->xpath('//link[@rel="icon"]');
			if(isset($arr[0]['href'])) $icon = $arr[0]['href'];
		}
	}
	return $icon;
}

function get_html_meta_data($doc) {
	// http://stackoverflow.com/questions/3711357/get-title-and-meta-tags-of-external-site
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





