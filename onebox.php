<?php
/*
Plugin Name: Onebox
Plugin URI: https://github.com/surrealroad/wp-onebox
Description: Replaces a boring hyperlink with a lovely Facebook/Twitter-style box with additional information about the destination page
Version: 0
Author: Surreal Road Limited
Author URI: http://www.surrealroad.com
Text Domain: onebox
Domain Path: /languages
License: MIT
*/
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class OneboxPlugin {

	//Version
	static $version ='0';
	static $sampleLink = "https://github.com/surrealroad/wp-onebox";

	//Options and defaults

	public function __construct() {
		register_activation_hook(__FILE__,array(__CLASS__, 'install' ));
		register_uninstall_hook(__FILE__,array( __CLASS__, 'uninstall'));
		add_action('init', array($this, 'init'));
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_init', array($this,'registerSettings'));
		add_action('admin_menu', array($this,'pluginSettings'));
	}

	static function install(){
		update_option("onebox_version",self::$version);
		add_option("onebox_template_html", '<div class=" {class}">
<div class="onebox-header-wrapper">
<div class="onebox-header">
<a href="{url}" target="_blank" rel="nofollow">{favicon}<span class="onebox-sitename">{sitename}</span></a> / <span class="onebox-title"><a href="{url}" target="_blank" rel="nofollow">{title}</a></span>
<span class="onebox-title-button">{title-button}</span>
</div>
</div>
<div class="onebox-body-wrapper">
<div class="onebox-body">
<a href="{url}" target="_blank" rel="nofollow">{image}</a>
<p class="onebox-description">{description} — <a href="{url}">Read More</a></p><p class="onebox-additional">{additional}</p>
</div>
</div>
<div class="onebox-footer-wrapper">
<div class="onebox-footer"><span class="onebox-footer-info">{footer}</span><span class="onebox-footer-button">{footer-button}</span></div>
</div>
<div class="onebox-clearfix"></div>
</div>');
	}

	static function uninstall(){
		delete_option('onebox_version');
		delete_option('onebox_template_html');
	}


	public function init() {
		//Allow translations
		load_plugin_textdomain('onebox', false, basename(dirname(__FILE__)).'/languages');

		add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));

		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
	}

	public function admin_init() {
	    add_settings_section('onebox-template', __( 'Template Configuration', 'onebox' ), array($this, 'initTemplateSettings'), 'onebox');
	    add_settings_field('onebox-template-html', __( 'Template HTML', 'onebox' ), array($this, 'templateHTMLInput'), 'onebox', 'onebox-template');
    }

    function registerSettings() {
		register_setting('onebox', 'onebox_template_html');
    }

	// Enqueue Javascript

	function enqueueScripts() {

		wp_enqueue_script(
			'onebox',
			plugins_url( '/js/onebox.js' , __FILE__ ),
			array( 'jquery' )
		);

		// build settings to use in script http://ottopress.com/2010/passing-parameters-from-php-to-javascripts-in-plugins/
		$params = array(
			"renderURL" => plugins_url( '/render.php' , __FILE__ ),
			"template" => get_option('onebox_template_html')
		);
		wp_localize_script( 'onebox', 'OneboxParams', $params );
	}

	// Enqueue Static CSS

	function enqueueStyles() {
       wp_register_style( 'oneboxStylesheet', plugins_url( '/style/onebox.css' , __FILE__ ) );
       wp_enqueue_style( 'oneboxStylesheet' );
   }


	// add [onebox] shortcode

	static function renderOneboxShortcode($atts) {
	   extract(shortcode_atts(array('url' => ""), $atts));
	   return '<div class="onebox-container" data-onebox-type="normal"><a href="'.$url.'">'.__( 'Link', 'onebox').'</a></div>' ;
	}

	// add admin options page

	function pluginSettings() {
	    $page = add_options_page( 'Onebox', 'Onebox', 'manage_options', 'onebox', array ( $this, 'optionsPage' ));
	    add_action( 'admin_print_styles-' . $page, array ( $this, 'enqueueStyles' ) );
	}
	function optionsPage() {
		?>
    <div class="wrap">
    	<?php screen_icon(); ?>
    	<h2><?php _e( 'Onebox Options', 'onebox' ) ?></h2>
    	<?php if(self::isCurlInstalled()) { ?>
	        <?php if(!self::isGeoIPInstalled()) { ?>
	    		<div id="message" class="error">
	    		<p><strong><?php _e( 'Notice: ', 'onebox'); ?></strong> <?php _e( 'The <a href="http://www.php.net/manual/en/book.geoip.php">GeoIP PHP Extension</a> is not installed.', 'onebox'); ?> <?php _e( 'Some functionality will be disabled.', 'onebox'); ?></p>
	    		</div>
	    	<?php } elseif(!self::isGeoIPWorking()) { ?>
	    		<div id="message" class="error">
	    		<p><strong><?php _e( 'Notice: ', 'onebox'); ?></strong> <?php _e( 'The <a href="http://www.php.net/manual/en/book.geoip.php">GeoIP PHP Extension</a> database (GeoIPCity.dat) is not installed.', 'onebox'); ?> <?php _e( 'Some functionality will be disabled.', 'onebox'); ?></p>
	    		</div>
	    	<?php } ?>
	        <form action="options.php" method="POST">
	            <?php settings_fields( 'onebox' ); ?>
	            <?php do_settings_sections('onebox'); ?>
	            <?php submit_button(); ?>
	        </form>
	        <h2><?php _e( 'Onebox Example', 'onebox' ) ?></h2>
	        <pre>[onebox url="<?php echo self::$sampleLink; ?>"]</pre>
	        <?php echo do_shortcode('[onebox url="'.self::$sampleLink.'"]'); ?>
        <?php } else { ?>
        	<div id="message" class="error">
        	<p><strong><?php _e( 'Error:', 'onebox'); ?></strong> <?php _e( 'The cURL extension for PHP is required and not installed.', 'onebox'); ?></p>
		    <p><?php _e( 'See <a href="http://www.php.net/manual/en/curl.installation.php">this page</a> for more information', 'onebox'); ?></p>
        <?php } ?>
        <hr/>
        <p><?php _e( 'Onebox Plugin for Wordpress by', 'onebox' ) ?> <a href="http://www.surrealroad.com">Surreal Road</a>. <?php echo self::surrealTagline(); ?>.</p>
        <p><?php _e( 'Plugin version', 'onebox' ) ?> <?php echo self::$version; ?></p>
    </div>
    <?php
	}

    function initTemplateSettings() {

    }

    function templateHTMLInput(){
    	self::text_area('onebox_template_html', __( 'HTML template to use for Oneboxes', 'onebox' ) );
    }

    // utility functions

	function checkbox_input($option, $description) {
	    if (get_option($option)) {
	      $value = 'checked="checked"';
	    } else {
	      $value = '';
	    }
	    ?>
	<input id='<?php echo $option?>' name='<?php echo $option?>' type='checkbox' value='1' <?php echo $value?> /> <?php echo $description ?>
	    <?php
	}
	function text_input($option, $description) {
	    if (get_option($option)) {
	      $value = get_option($option);
	    } else {
	      $value = '';
	    }
	    ?>
	<input id='<?php echo $option?>' name='<?php echo $option?>' type='text' value='<?php echo esc_attr( $value ); ?>' />
	<br/><?php echo $description ?>
	    <?php
	}
	function text_area($option, $description) {
	    if (get_option($option)) {
	      $value = get_option($option);
	    } else {
	      $value = '';
	    }
	    ?>
	<textarea cols=100 rows=6 id='<?php echo $option?>' name='<?php echo $option?>'><?php echo esc_attr( $value ); ?></textarea><br><?php echo $description ?>
	    <?php
	}

	function surrealTagline() {
		$lines = file(plugins_url("/surreal.strings", __FILE__ ) , FILE_IGNORE_NEW_LINES);
		return "Hyperlink " . $lines[array_rand($lines)];
	}

	// ### Checks for presence of the cURL extension. http://cleverwp.com/function-curl-php-extension-loaded/
	function isCurlInstalled() {
	    if  (in_array  ('curl', get_loaded_extensions())) {
	        return true;
	    }
	    else{
	        return false;
	    }
	}

	function isGeoIPInstalled() {
		return function_exists("geoip_country_code_by_name");
	}

	function isGeoIPWorking() {
		if(@geoip_record_by_name('php.net')) return true;
		return false;
	}

}


// shortcodes (must be declared outside of class)
add_shortcode('onebox', array('OneboxPlugin', 'renderOneboxShortcode'));

$oneboxPlugin = new OneboxPlugin();

