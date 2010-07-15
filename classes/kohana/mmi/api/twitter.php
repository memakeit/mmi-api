<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Twitter API calls.
 * Response formats: Atom, JSON, RSS, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://apiwiki.twitter.com/Twitter-API-Documentation
 */
class Kohana_MMI_API_Twitter extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_TWITTER;

	// API settings
	protected $_api_url = 'http://api.twitter.com/1/';

	// OAuth settings
	protected $_request_token_url = 'https://api.twitter.com/oauth/request_token';
	protected $_request_token_http_method = MMI_HTTP::METHOD_GET;
	protected $_access_token_url = 'https://api.twitter.com/oauth/access_token';
	protected $_access_token_http_method = MMI_HTTP::METHOD_GET;
	protected $_authenticate_url = 'https://api.twitter.com/oauth/authenticate';
	protected $_authorize_url = 'https://api.twitter.com/oauth/authorize';

	/**
	 * Ensure the request token has been verified and an access token received.
	 *
	 * @throws	Kohana_Exception
	 * @return	void
	 */
	protected function _check_token()
	{
		if ( ! $this->is_valid_token())
		{
			$service = $this->_service;
			MMI_API::log_error(__METHOD__, __LINE__, 'Request token not valid for '.$service);
			throw new Kohana_Exception('Request token not valid for :service in :method.', array
			(
				':service'	=> $service,
				':method'	=> __METHOD__,
			));
		}
	}

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
		foreach (array(MMI_API::FORMAT_ATOM, MMI_API::FORMAT_JSON, MMI_API::FORMAT_RSS, MMI_API::FORMAT_XML) as $ext)
		{
			if ($this->_url_ends_with($path, '.'.$ext))
			{
				$has_extension = TRUE;
				break;
			}
		}
		return ($has_extension) ? $url.$path : "$url$path.{$this->_format}";
	}
} // End Kohana_MMI_API_Twitter
