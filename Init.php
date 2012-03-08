<?php
/**
 * Plugin Name: Magento API Shortcode
 * Plugin URI: http://www.sitecritic.net
 * Description: Use short-tags within wordpress to get product information via api calls.
 * Version: 1.0
 * Requires at least: 3.0
 * Author: Bernard Peh
 * Author URI: http://www.sitepoint.com/
 *License: GPL
 */

class Init {

 	/** 
	 * constructor
	 */
	public function __construct() {		

		// spl autoload
		self::auto_include(); 
		
		// init shortcode
    add_action('init', array(__CLASS__, 'initShortcode'));
		
		// init admin
		add_action('admin_init', array(__CLASS__, 'initAdmin'));

		// init admin menu
		add_action('admin_menu', array(__CLASS__, 'createMenu'));
		
	}

	/**
	 * init shortcode
	 * 
	 */
	public function initShortcode() {
		add_shortcode('mage', array('Magento', 'processShortcode'));
	}

	/**
	 * init admin
	 */
	public static function initAdmin() {

		// Register vars to be saved from the settings form later
		register_setting('magento-api', 'magento-api-url');
		register_setting('magento-api', 'magento-caching-time');
		register_setting('magento-api', 'magento-cdn');
		register_setting('magento-api', 'magento-api-username');
		register_setting('magento-api', 'magento-api-passwd');
		
		// add shortcode button to wysiwyg
		add_action('media_buttons_context', array('Magento', 'addWysiwygButton'));

		// add jquery for shortcode button
		if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
			add_action('admin_footer', array('Magento', 'addWysiwygForm'));
		}
	}

	/**
	 * create admin menu
	 */
	public static function createMenu() 
	{
		// add submenu under settings menu
		add_submenu_page (
    	'options-general.php',
      	'Magento API',
      	'Magento API',
      	'manage_options' ,
      	'magento-api-shortcode-settings' ,
      	function () {require_once('View/adminhtml/settings.phtml');}
    	);
	}

	/**
	 * use php5 autoload for classes in model dir 
	 */
	public static function auto_include()
	{

		if(function_exists('spl_autoload_register')) {

			function magento_autoload($name) 
			{
				$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
				$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $name . '.php';
				if(is_file($file)) {
					require_once $file;
				}
			}
			spl_autoload_register('magento_autoload');			
		}
	}
	
}

// Init this plugin
new Init;
?>
