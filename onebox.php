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

class Onebox {

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
	}

	static function uninstall(){
		delete_option('onebox_version');
	}


	public function init() {
		//Allow translations
		load_plugin_textdomain('onebox', false, basename(dirname(__FILE__)).'/languages');

		add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));

		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
	}

	public function admin_init() {
	    wp_register_style( 'oneboxStylesheet', plugins_url( '/style/onebox.css' , __FILE__ ) );
	    add_settings_section('onebox-general', __( 'General Settings', 'onebox' ), array($this, 'initGeneralSettings'), 'onebox');
    }

    function registerSettings() {

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
			"renderURL" => plugins_url( '/render.php' , __FILE__ )
		);
		wp_localize_script( 'onebox', 'OneboxParams', $params );
	}

	// Enqueue Static CSS

	function enqueueStyles() {

		wp_enqueue_script(
			'onebox',
			plugins_url( '/style/onebox.css' , __FILE__ )
		);
	}

	// Enqueue Static CSS in admin area

	function enqueueAdminStyles() {
       /*
        * It will be called only on your plugin admin page, enqueue our stylesheet here
        */
       wp_enqueue_style( 'oneboxStylesheet' );
   }


	// add [onebox] shortcode

	function renderOneboxShortcode($atts) {
	   extract(shortcode_atts(array('url' => ""), $atts));
	   return '<div class="onebox-container" data-onebox-type="normal"><a href="'.$url.'">Link</a></div>' ;
	}

	// add admin options page

	function pluginSettings() {
	    $page = add_options_page( 'Onebox', 'Onebox', 'manage_options', 'onebox', array ( $this, 'optionsPage' ));
	    add_action( 'admin_print_styles-' . $page, array ( $this, 'enqueueAdminStyles' ) );
	}
	function optionsPage() {
		?>
    <div class="wrap">
    	<?php screen_icon(); ?>
    	<?php if(self::isCurlInstalled()) { ?>
        <h2><?php _e( 'Onebox Options', 'onebox' ) ?></h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'onebox' ); ?>
            <?php do_settings_sections('onebox'); ?>
            <?php submit_button(); ?>
        </form>
        <h2><?php _e( 'Onebox Example', 'onebox' ) ?></h2>
        <pre>[onebox url="<?php echo self::$sampleLink; ?>"]</pre>
        <?php
        $atts = array('url' =>self::$sampleLink);
        echo self::renderOneboxShortcode($atts); ?>
        <?php } else { ?>
	    <h2>Error</h2>
	    <p>The cURL extension for PHP is required and not installed.</p>
	    <p>See <a href="http://www.php.net/manual/en/curl.installation.php">this page</a> for more information</p>
        <?php } ?>
        <hr/>
        <p><?php _e( 'Onebox Plugin for Wordpress by', 'onebox' ) ?> <a href="http://www.surrealroad.com">Surreal Road</a>. <?php echo self::surrealTagline(); ?>.</p>
        <p><?php _e( 'Plugin version', 'onebox' ) ?> <?php echo self::$version; ?></p>
    </div>
    <?php
	}

    function initGeneralSettings() {

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

}

$onebox = new Onebox();

// shortcodes (must be declared outside of class)
add_shortcode('onebox', array('Onebox', 'renderOneboxShortcode'));

