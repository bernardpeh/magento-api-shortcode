<?php
/**
 * Main shortcode class of the plugin
 * 
 * Takes care of getting results via api
 */
class Magento 
{

	/**
	 * process shortcode stored in post or page.
	 * 
	 * use cached data whenever possible.
	 * 
	 * @param array $tag
	 * @return String
	 */
	public static function processShortcode($tag)
	{
		$conn = false;
		$error = '';
		$cacheVar = 'mage_pid_'.$tag['pid'].'_'.$tag['template'];		
		// check for cach first, if not cache it
		$cacheData = get_transient($cacheVar);

		if ($cacheData) {
			return $cacheData;
		}
		else {
			$cacheData = self::processAPI($tag);
			set_transient($cacheVar, $cacheData, get_option('magento-caching-time'));
		}
		return $cacheData;
	}

	/**
	 * login to magento api
	 * 
	 * @param array $tag
	 * @return string
	 */
	public static function processAPI($tag) {
		
		try {
			$client = new SoapClient(get_option('magento-api-url'));
			$session = $client->login(get_option('magento-api-username'), get_option('magento-api-passwd'));
			$conn = true;
		}
		catch(Exception $e) {
			$error= $e->getMessage();
			$conn = false;
		}
	
		// if user successfully authenticated, continue processing
		if($conn) {
			return self::getProduct($tag, $client, $session);
		}
		else {
			return $error;
		}
	}

	/**
	 * get products via magento api.
	 *
	 * process and display product information using templates.
	 * @param array $tag
	 * @param SoapClient $client 
	 * @param string $session
	 * @return string
	 */
	public static function getProduct($tag, $client, $session)
	{
		// create dummy object
		$product = $client->call($session, 'catalog_product.info', $tag['pid']);
		$image = $client->call($session, 'catalog_product_attribute_media.list', $tag['pid']);
		$cdn = get_option('magento-cdn');
		// now parse url for image if cdn is available
		if ($cdn) {
			foreach ($image as $k => $v) {
				$url = $image[$k]['url'];
				if ($url) {
					$url = parse_url($url);
					$image[$k]['url'] = $cdn.$url['path'];
				}
			}
		}

		if ($tag['custom']) {
			$template = TEMPLATEPATH.'/mage/'.$tag['template'];
		} 
		else {
			$template = WP_PLUGIN_DIR.'/magento-api-shortcode/View/frontend/'.$tag['template'];
		}
		if (!is_file($template)) {
			throw new Exception('template file not found.');
		} 

		return View::factory($template)->setVar('product',$product)->setVar('image',$image)->render(1);
	}

	/**
	 * add javascript to wysiwyg form
	 * 
	 * @return string 
	 */
	public static function addWysiwygForm ()
  	{
    	// get view
    	$template = WP_PLUGIN_DIR.'/magento-api-shortcode/View/adminhtml/shortcode-popup.phtml';
		return View::factory($template)->render();
	}

	/**
	 * add magento button to wysiwyg form
	 * 
	 * @param string
	 */
	public static function addWysiwygButton($context)
	{
		$button = plugins_url('/magento-api-shortcode/View/adminhtml/images/magento-icon.png');
		// get view
		$template = WP_PLUGIN_DIR.'/magento-api-shortcode/View/adminhtml/shortcode-icon.phtml';
		// return layout
		$content = View::factory($template)->setVar('button',$button)->render();
		return $context.$content;
	}
}

