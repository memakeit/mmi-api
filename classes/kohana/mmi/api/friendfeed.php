<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make FriendFeed API calls.
 * Response formats: JSON, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://friendfeed.com/api/documentation
 */
class Kohana_MMI_API_FriendFeed extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_FRIENDFEED;

	// API settings
	protected $_api_url = 'http://friendfeed-api.com/v2/';

	// OAuth settings
	protected $_request_token_url = 'https://friendfeed.com/account/oauth/request_token';
	protected $_access_token_url = 'https://friendfeed.com/account/oauth/access_token';
	protected $_authorize_url = 'https://friendfeed.com/account/oauth/authorize';

	/**
	 * Configure the request parameters as specified in the configuration file.
	 * When processing additions, if a parameter value exists, it will not be overwritten.
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
} // End Kohana_MMI_API_FriendFeed
