<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Brightkite API calls.
 * Response formats: JSON, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://api.brightkite.com/index.html
 */
class Kohana_MMI_API_Brightkite extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_BRIGHTKITE;

	// API settings
	protected $_api_url = 'http://brightkite.com/';

	// OAuth settings
	protected $_request_token_url = 'http://brightkite.com/oauth/request_token';
	protected $_access_token_url = 'http://brightkite.com/oauth/access_token';
	protected $_authorize_url = 'http://brightkite.com/oauth/authorize';

	/**
	 * Build the request URL.
	 *
	 * @param	string	the base URL
	 * @param	string	the path portion of the URL
	 * @return	string
	 */
	protected function _build_url($url, $path)
	{
		// Ensure the URL does not already have an extension
		$has_extension = FALSE;
		foreach (array(MMI_API::FORMAT_JSON, MMI_API::FORMAT_XML) as $ext)
		{
			if ($this->_url_ends_with($path, '.'.$ext))
			{
				$has_extension = TRUE;
				break;
			}
		}
		return ($has_extension) ? $url.$path : "$url$path.{$this->_format}";
	}
} // End Kohana_MMI_API_Brightkite
