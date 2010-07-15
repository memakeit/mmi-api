<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Vimeo API calls.
 * Response formats: JSON, JSONP, PHP, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://vimeo.com/api/docs/methods
 */
class Kohana_MMI_API_Vimeo extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_VIMEO;

	// API settings
	protected $_api_url = 'http://vimeo.com/api/rest/v2/';

	// OAuth settings
	protected $_request_token_url = 'http://vimeo.com/oauth/request_token';
	protected $_request_token_http_method = MMI_HTTP::METHOD_GET;
	protected $_access_token_url = 'http://vimeo.com/oauth/access_token';
	protected $_access_token_http_method = MMI_HTTP::METHOD_GET;
	protected $_authorize_url = 'http://vimeo.com/oauth/authorize';

	/**
	 * Configure the request parameters as specified in the configuration file.
	 * When processing additions, if a parameter value exists, it will not be
	 * overwritten.
	 *
	 * @param	array	an associative array of request parameters
	 * @return	array
	 */
	protected function _configure_parameters($parms)
	{
		$parms = parent::_configure_parameters($parms);

		$name = 'format';
		if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
		{
			$parms[$name] = $this->_format;
		}
		return $parms;
	}
} // End Kohana_MMI_API_Vimeo
