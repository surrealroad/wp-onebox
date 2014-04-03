<?php
/*
Plugin Name: Onebox
Plugin URI: https://github.com/surrealroad/wp-onebox
Description: Replaces a boring hyperlink with a lovely Facebook/Twitter-style box with additional information about the destination page
Version: 0.7.1
Author: Surreal Road Limited
Author URI: http://www.surrealroad.com
Text Domain: onebox
Domain Path: /languages
License: MIT
*/

/*
// debug
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class OneboxPlugin {

	//Version
	static $version ='0.7.1';
	static $sampleLink = "https://github.com/surrealroad/wp-onebox";
	static $enableAffiliate = true; // if you change this, you may need to deactive/reactivate the plugin to see the changes

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
		update_option("onebox_affiliate_links", self::$enableAffiliate);
		add_option("onebox_template_html", '<div class="onebox-result {class}">
<div class="onebox-header-wrapper">
<div class="onebox-header">
<a href="{url}" target="_blank" rel="nofollow">{favicon}<span class="onebox-sitename">{sitename}</span></a> / <span class="onebox-title"><a href="{url}" target="_blank">{title}</a></span>
<span class="onebox-title-button">{title-button}</span>
</div>
</div>
<div class="onebox-body-wrapper">
<a href="{url}" target="_blank">{image}</a>
<div class="onebox-body">
<p class="onebox-description">{description} — <a href="{url}">Read More</a></p><p class="onebox-additional">{additional}</p>
</div>
</div>
<div class="onebox-footer-wrapper">
<div class="onebox-footer"><span class="onebox-footer-info">{footer}</span><span class="onebox-footer-button">{footer-button}</span></div>
</div>
<div class="onebox-clearfix"></div>
</div>');
		add_option("onebox_enable_css", true);
		add_option("onebox_enable_dark_css", false);
		add_option("onebox_selector", ".onebox-container");
		add_option("onebox_enable_apc_cache", true);
		add_option("onebox_github_apikey", '');
	}

	static function uninstall(){
		delete_option('onebox_version');
		delete_option('onebox_affiliate_links');
		delete_option('onebox_template_html');
		delete_option('onebox_enable_css');
		delete_option('onebox_enable_dark_css');
		delete_option('onebox_selector');
		delete_option('onebox_enable_apc_cache');
		delete_option('onebox_github_apikey');
	}


	public function init() {
		//Allow translations
		load_plugin_textdomain('onebox', false, basename(dirname(__FILE__)).'/languages');

		add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));

		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

		add_filter( 'query_vars', array($this, 'onebox_query_vars_filter') );
		add_action( 'template_redirect', array($this, 'renderOnebox'), 1 );

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", array($this, 'onebox_settings_link') );
	}

	public function admin_init() {
	    add_settings_section('onebox-template', __( 'Template Configuration', 'onebox' ), array($this, 'initTemplateSettings'), 'onebox');
	    add_settings_field('onebox-template-html', __( 'Template HTML', 'onebox' ), array($this, 'templateHTMLInput'), 'onebox', 'onebox-template');
	    add_settings_field('onebox-enable-css', __( 'Enable Styles', 'onebox' ), array($this, 'templateCSSInput'), 'onebox', 'onebox-template');
	    add_settings_field('onebox-enable-dark-css', __( 'Use Dark Theme', 'onebox' ), array($this, 'templateDarkCSSInput'), 'onebox', 'onebox-template');
	    add_settings_field('onebox-selector', __( 'jQuery Selector for Oneboxes', 'onebox' ), array($this, 'templateSelectorInput'), 'onebox', 'onebox-template');
	    add_settings_section('onebox-features', __( 'Special Features', 'onebox' ), array($this, 'initFeatureSettings'), 'onebox');
	    add_settings_field('onebox-apc-cache', __( 'Enable APC Caching', 'onebox' ), array($this, 'apcCacheInput'), 'onebox', 'onebox-features');
	    add_settings_field('onebox-github-api', __( 'GitHub API Token', 'onebox' ), array($this, 'githubAPIInput'), 'onebox', 'onebox-features');
    }

    function registerSettings() {
		register_setting('onebox', 'onebox_template_html');
		register_setting('onebox', 'onebox_enable_css');
		register_setting('onebox', 'onebox_enable_dark_css');
		register_setting('onebox', 'onebox_selector');
		register_setting('onebox', 'onebox_enable_apc_cache');
		register_setting('onebox', 'onebox_github_apikey');
    }

	// Enqueue Javascript

	function enqueueScripts() {

		wp_enqueue_script(
			'onebox',
			plugins_url( '/js/onebox.min.js' , __FILE__ ),
			array( 'jquery' )
		);

		// build settings to use in script http://ottopress.com/2010/passing-parameters-from-php-to-javascripts-in-plugins/
		$params = array(
			"renderURL" => site_url('/?onebox_render=1'),
			"template" => wp_kses_post(get_option('onebox_template_html')),
			"dark" => get_option('onebox_enable_dark_css'),
			"selector" => get_option('onebox_selector'),
		);
		wp_localize_script( 'onebox', 'OneboxParams', $params );
	}

	// Enqueue Static CSS

	function enqueueStyles() {
       wp_register_style( 'oneboxStylesheet', plugins_url( '/style/onebox.min.css' , __FILE__ ) );
       if(get_option('onebox_enable_css')) wp_enqueue_style( 'oneboxStylesheet' );
   }


	// add [onebox] shortcode

	static function renderOneboxShortcode($atts) {
	   extract(shortcode_atts(array(
	   		'url' => "",
	   		'title' => "",
	   		'description' => "",
	   ), $atts));
	   if(!$url) return;
	   if($title) {
		   $data = ' data-title="'.esc_attr($title).'"';
	   } else {
		   $data = "";
		   $title = __( 'Link', 'onebox');
	   }
	   if($description) $data .= ' data-description="'.esc_attr($description).'"';

	   $link = '<a href="'.$url.'">'.esc_attr($title).'</a>';
	   if(is_feed()) return $link;
	   else return '<div class="onebox-container"'.$data.'>'.$link.'</div>' ;
	}

	// register query vars
	static function onebox_query_vars_filter($vars) {
		$vars[] = "onebox_render";
		$vars[] = "onebox_url";
		$vars[] = "onebox_title";
		$vars[] = "onebox_description";
		return $vars;
	}

	// add template redirect
	static function renderOnebox() {
		if(get_query_var('onebox_render')) {
			include(WP_PLUGIN_DIR.'/onebox/render.php');
			exit;
		}
	}

	// add settings link
	static function onebox_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=onebox">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	// add admin options page

	function pluginSettings() {
	    $page = add_options_page( 'Onebox', 'Onebox', 'manage_options', 'onebox', array ( $this, 'optionsPage' ));
	    if(get_option('onebox_enable_css')) add_action( 'admin_print_styles-' . $page, array ( $this, 'enqueueStyles' ) );
	}
	function optionsPage() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', "onebox" ) );
		}
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
	    	<?php if(!self::isAPCCacheInstalled()) { ?>
		    	<div id="message" class="error">
	    		<p><strong><?php _e( 'Notice: ', 'onebox'); ?></strong> <?php _e( 'The <a href="http://php.net/manual/en/book.apc.php">Alternative PHP Cache Extension</a> is not installed.', 'onebox'); ?> <?php _e( 'Caching will be disabled.', 'onebox'); ?></p>
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
	        <small><?php _e( 'Actual hyperlink colours and fonts will be based on your theme, and are not represented here', 'onebox'); ?></small>
        <?php } else { ?>
        	<div id="message" class="error">
        	<p><strong><?php _e( 'Error:', 'onebox'); ?></strong> <?php _e( 'The cURL extension for PHP is required and not installed.', 'onebox'); ?></p>
		    <p><?php _e( 'See <a href="http://www.php.net/manual/en/curl.installation.php">this page</a> for more information', 'onebox'); ?></p>
        <?php } ?>
        <hr/>
        <p><?php _e( 'Onebox Plugin for Wordpress by', 'onebox' ) ?> <a href="http://www.surrealroad.com">Surreal Road</a>. <?php echo self::surrealTagline(); ?>.</p>
        <p><?php _e( 'Plugin version', 'onebox' ) ?> <?php echo self::$version; ?></p>
        <?php if(get_option('onebox_affiliate_links')) { ?>
        <small><?php _e( 'This plugin generates affiliate links in some cases in order to support its development (it does this through Javascript, so the original links are stored/indexed by bots). Modify the source, or do not use it if that makes you uncomfortable.', 'onebox' ) ?></small>
        <?php } ?>
    </div>
    <?php
	}

    function initTemplateSettings() {

    }

    function initFeatureSettings() {

    }

    function templateHTMLInput(){
    	self::text_area('onebox_template_html', __( 'HTML template to use for Oneboxes', 'onebox' ) );
    }

    function templateCSSInput(){
    	self::checkbox_input('onebox_enable_css', __( 'Enable built-in styles', 'onebox' ) );
    }

    function templateDarkCSSInput(){
    	self::checkbox_input('onebox_enable_dark_css', __( 'Enable dark theme', 'onebox' ) );
    }

    function templateSelectorInput(){
    	self::text_input('onebox_selector', __( 'jQuery selector to use to locate Oneboxes, e.g. ".onebox-container"<br/>Use this if you want to constrain where Oneboxes may be rendered, e.g. ".post .onebox-container"', 'onebox' ) );
    }


    function apcCacheInput(){
    	self::checkbox_input('onebox_enable_apc_cache', __( 'Enable APC caching<br/>This is highly recommended for end-user performance.', 'onebox' ), !self::isAPCCacheInstalled() );
    }

	function githubAPIInput(){
    	self::text_input('onebox_github_apikey', __( 'GitHub API token (<a href="https://github.com/settings/tokens/new">generate one</a>)<br/>This is required if you plan to use GitHub links on a busy site.', 'onebox' ) );
    }

    // utility functions

	function checkbox_input($option, $description, $disabled=false) {
	    if (get_option($option)) {
	    	$value = 'checked="checked"';
	    } else {
	    	$value = '';
	    }
	    if($disabled) {
		    $disabled = 'disabled="disabled"';
		} else {
			$disabled = '';
	    }
	    ?>
	<input id='<?php echo $option?>' name='<?php echo $option?>' type='checkbox' value='1' <?php echo $value?> <?php echo $disabled?> /> <?php echo $description ?>
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
	<textarea cols=100 rows=6 id='<?php echo $option?>' name='<?php echo $option?>'><?php echo esc_textarea( $value ); ?></textarea><br><?php echo $description ?>
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

	function isAPCCacheInstalled() {
		return extension_loaded('apc');
	}

}


// shortcodes (must be declared outside of class)
add_shortcode('onebox', array('OneboxPlugin', 'renderOneboxShortcode'));

$oneboxPlugin = new OneboxPlugin();

