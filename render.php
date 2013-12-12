<?php
/*
Generate Onebox HTML
*/

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

require_once("lib/Encoding.php");
// load wordpress for access to stored options
define( 'WP_USE_THEMES', false );
require_once( dirname( dirname( dirname( dirname( __FILE__ )))) . '/wp-load.php' );
//Allow translations
load_plugin_textdomain('onebox', false, basename(dirname(__FILE__)).'/languages');

// set up list of parsers to include
$parsers = array(
	"steam",
	"github",
	"gog",
	"itunes",
	"opengraph",
	"twittercard",
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
	private $doc = NULL;

	public function __construct($url) {

		foreach($this->properties as $property) {
			$this->data[$property] = "";
		}

		$this->data['url'] = $url;
		$this->data['countrycode'] = self::user_cc();
		if(get_option('onebox_enable_dark_css')) $this->classes[] = "dark";
	}

	public function outputjson() {
		$this->data['favicon'] = self::sanitize_favicon($this->data['favicon'], $this->data['url']);
		$this->data['description'] = \ForceUTF8\Encoding::toUTF8($this->data['description']);
		$this->data['additional'] = \ForceUTF8\Encoding::toUTF8($this->data['additional']);
		if(!$this->data['sitename']) $this->data['sitename']= str_ireplace('www.', '', parse_url($this->data['url'], PHP_URL_HOST));
		if(!get_option('onebox_affiliate_links')) $this->data['displayurl']="";
		$output = array('data'=>$this->data, 'classes'=>self::writeClasses());
		return json_encode($output);
	}

	public function addClass($class) {
		$this->classes[] = $class;
	}

	private function writeClasses() {
		return implode(" ", $this->classes);
	}

	public function getDoc() {
		if(!isset($this->doc) && isset($this->data['url'])) {
			$this->doc = new DomDocument();
			libxml_use_internal_errors(true);
			$this->doc->loadHTMLFile($this->data['url']);
			libxml_use_internal_errors(false);
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
				if(!isset($this->data[$key]) || !$this->data[$key]) $this->data[$key]=$value;
			}
		}
	}
}

$onebox = new Onebox(urldecode($_GET["url"]));

// run parsers
foreach($parsers as $parser) {
	include("parsers/".$parser.".php");
}

echo $onebox->outputjson();

//onebox_data($onebox);





