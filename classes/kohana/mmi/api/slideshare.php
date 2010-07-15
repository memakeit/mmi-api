<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make SlideShare API calls.
 * Response formats: XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.slideshare.net/developers/documentation
 */
class Kohana_MMI_API_SlideShare extends MMI_API
{
	// Service name
	protected $_service = MMI_API::SERVICE_SLIDESHARE;

	// API settings
	protected $_api_url = 'http://www.slideshare.net/api/2/';

	/**
	 * @var string the API key
	 */
	protected $_api_key = NULL;

	/**
	 * @var string the API secret
	 */
	protected $_api_secret = NULL;

	/**
	 * Load configuration settings.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		$auth_config = $this->_auth_config;
		$this->_api_key = Arr::get($auth_config, 'api_key');
		$this->_api_secret = Arr::get($auth_config, 'api_secret');
	}

	/**
	 * Customize the request parameters as specified in the configuration file.
	 * When processing additions, if a parameter value exists, it will not be overwritten.
	 *
	 * @param	array	an associative array of request parameters
	 * @return	array
	 */
	protected function _configure_parameters($parms)
	{
		$parms = parent::_configure_parameters($parms);

		// Ensure the API key and API secret are set
		$api_key = $this->_api_key;
		$this->_ensure_parm('API key', $api_key);
		$api_secret = $this->_api_secret;
		$this->_ensure_parm('API secret', $api_secret);

		// Set the API and generate the signature
		$ts = time();
		$parms['api_key'] = $api_key;
		$parms['ts'] = $ts;
		$parms['hash'] = sha1($api_secret.$ts);
		return $parms;
	}
} // End Kohana_MMI_API_SlideShare
