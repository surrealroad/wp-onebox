<?php
/*
Generate Onebox HTML
*/

/*
// debug
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
*/

require_once("lib/Encoding.php");

//Allow translations
load_plugin_textdomain('onebox', false, basename(dirname(__FILE__)).'/languages');

// set up list of parsers to include
$parsers = array(
	"origin",
	"greenmangaming",
	"macgamestore",
	"steam",
	"github",
	"gog",
	"itunes",
	"opengraph",
	"twittercard",
	"microdata",
	"meta",
	"favicon",
);

class Onebox {

	// variables
	public $properties = array(
		"url",
		"displayurl",
		"countrycode",
		"sitename",
		"title",
		"image",
		"favicon",
		"description",
		"additional",
		"footer",
		"footerbutton",
		"titlebutton",
	);

	public $data = array();
	private $classes = array();
	private $HTML = NULL;
	private $doc = NULL;
	public $cached = false;
	public $shouldCacheLocation = false;

	public function __construct($url) {

		foreach($this->properties as $property) {
			$this->data[$property] = "";
		}

		$this->data['url'] = esc_url_raw($url);
		$this->data['countrycode'] = self::user_cc();
	}

	public function outputjson() {
		$cache = $this->readCache();
		if($cache) {
			$output = $cache;
		} else {
			$this->data['favicon'] = self::sanitize_favicon($this->data['favicon'], $this->data['url']);
			if($this->data['description']) {
				$this->data['description'] = \ForceUTF8\Encoding::toUTF8($this->data['description']);
			} else {
				$this->data['description'] = __('No description available for this site', "onebox");
			}
			$this->data['additional'] = \ForceUTF8\Encoding::toUTF8($this->data['additional']);
			if(!$this->data['sitename']) $this->data['sitename']= str_ireplace('www.', '', parse_url($this->data['url'], PHP_URL_HOST));
			if(!get_option('onebox_affiliate_links')) $this->data['displayurl']="";
			$output = array('data'=>$this->data, 'classes'=>self::writeClasses());
			// cache result
			$this->writeCache($output);
		}
		return json_encode($output);
	}

	public function addClass($class) {
		$this->classes[] = $class;
	}

	private function writeClasses() {
		return implode(" ", $this->classes);
	}

	public function getHTML($forceencoding="") {

		if(!$this->HTML && isset($this->data['url'])) {

			if($forceencoding == "utf-8") {
				$html = file_get_contents($this->data['url']);
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
				$this->HTML = $html;
			} else {
				// from opengraph helper
				$curl = curl_init($this->data['url']);

		        curl_setopt($curl, CURLOPT_FAILONERROR, true);
		        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

		        $response = curl_exec($curl);

		        curl_close($curl);

		        if (!empty($response)) {
		            $this->HTML = $response;
		        } else {
		            $this->HTML = false;
		        }
	        }
        }
        return $this->HTML;
	}

	public function getDoc($forceencoding="") {
		if(!isset($this->doc) && isset($this->data['url'])) {
			$this->doc = new DomDocument();
			$old_libxml_error = libxml_use_internal_errors(true);
			$this->doc->loadHTML(self::getHTML($forceencoding));
			libxml_use_internal_errors($old_libxml_error);
		}
		return $this->doc;
	}

	public function availableProperties() {
		$found = array();
		foreach($this->properties as $property) {
			if($this->data[$property]) $found[] = $property;
		}
		return $found;
	}


	// get user country code

	private function user_cc() {
		if(function_exists("geoip_country_code_by_name")) {
			// http://www.electrictoolbox.com/php-geoip-notice-ip-address-not-found/
			return @geoip_country_code_by_name($_SERVER['HTTP_X_FORWARDED_FOR']/*$_SERVER['REMOTE_ADDR']*/);
		}
		return "";
	}

	// http://snipplr.com/view.php?codeview&id=36437

	public function country_currency( $bc, $amount = 0 ) {
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

	private function sanitize_favicon($favicon, $url) {
		// Prepend "http://" to any link missing the HTTP protocol text.
		if ($favicon && preg_match('|^https*://|', $favicon) === 0) {
			if (substr($favicon, 0, 1) !== '/') $favicon = "/".$favicon;
			$favicon = parse_url($url, PHP_URL_SCHEME)."://".parse_url($url, PHP_URL_HOST) . $favicon;
		}
		return $favicon;
	}

	// update missing data values with new data values
	public function update($newdata) {
		if(isset($newdata)) {
			foreach ($newdata as $key => $value) {
				if((!isset($this->data[$key]) || !$this->data[$key]) && $value) $this->data[$key]=$value;
			}
		}
	}

	private function isAPCCacheInstalled() {
		return extension_loaded('apc');
	}

	public function readCache() {
		if(!get_option('onebox_enable_apc_cache') || !$this->isAPCCacheInstalled()) return false;

		if($this->data['countrycode'] && apc_fetch(md5($this->data['url']."|".$this->data['countrycode']))) return apc_fetch(md5($this->data['url']."|".$this->data['countrycode']));
		elseif($this->data['countrycode']) return false;
		else return apc_fetch(md5($this->data['url']));
	}

	private function writeCache($output) {
		if(!get_option('onebox_enable_apc_cache') || !$this->isAPCCacheInstalled()) return false;

		if($this->data['countrycode']) {
			$id = md5($this->data['url']."|".$this->data['countrycode']);
		} else {
			$id = md5($this->data['url']);
		}
		$ttl = 43200; //12 * 60 * 60;
		$this->cached = apc_store($id, $output, $ttl);
	}
}

if(get_query_var("onebox_url")) {

	$onebox = new Onebox(urldecode(get_query_var("onebox_url")));

	if($onebox->readCache()) {
		echo json_encode($onebox->readCache());
	} else {
		// run parsers
		foreach($parsers as $parser) {
		include("parsers/".$parser.".php");
		}

		echo $onebox->outputjson();
	}
}
