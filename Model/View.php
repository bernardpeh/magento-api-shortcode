<?php

/**
 * Simple View renderer
 */
class View extends stdClass
{
	private $_path;

	/**
	 * empty constructor for now
	 */
	public function __construct() {}

	/**
	 * factory method to be called
	 * 
	 * Initialising view using this method highly recommended
	 * @param string $path
	 * @return View
	 */
	public static function factory($path)
	{
		$v = new View();
		$v->_path = $path;
		return $v;
	}

	/**
	 * set variables for template
	 * 
	 * @param string $key
	 * @param mixed $val
	 */
	public function setVar($key,$val)
	{
		$this->$key = $val;
		return $this;
	}

	/**
	 * render template
	 * 
	 * return html if $return is true
	 * 
	 * @param boolean $return
	 * @return string
	 */
	public function render($return = 0)
	{
		ob_start();
		require_once($this->_path);
		$content = ob_get_contents();
		ob_end_clean();
		if ($return) {
			return $content;
		}
		else {
			echo $content;
		}
	}
}
