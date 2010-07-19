<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Google API calls.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API_Google extends MMI_API_OAuth
{
	// OAuth settings
	protected $_request_token_url = 'https://www.google.com/accounts/OAuthGetRequestToken';
	protected $_access_token_url = 'https://www.google.com/accounts/OAuthGetAccessToken';
	protected $_authorize_url = 'https://www.google.com/accounts/OAuthAuthorizeToken';

	/**
	 * Get a request token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the callback URL
	 * @param	array	an associative array of auth settings
	 * @return	OAuthToken
	 */
	public function get_request_token($oauth_callback = NULL, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = $this->_request_token_http_method;
		$url = $this->_request_token_url;
		$this->_ensure_parm('Request token URL', $url);

		// Configure the auth scope parameter
		$scope = Arr::get($auth_config, 'scope');
		$this->_ensure_parm('Authorization scope', $scope);
		$parms['scope'] = $scope;

		// Configure the OAuth callback URL
		if ( ! isset($oauth_callback))
		{
			$oauth_callback = $this->auth_callback_url();
		}
		if ( ! empty($oauth_callback))
		{
			$parms['oauth_callback'] = $oauth_callback;
		}

		// Make the request and extract the token
		$response = $this->_auth_request($auth_config, $http_method, $url, $parms);
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid request token'))
		{
			$token = $this->_extract_token($response);
		}
		return $token;
	}

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

		$name = 'alt';
		$temp = Arr::get($parms, $name);
		if (empty($temp))
		{
			$parms[$name] = $this->_format;
		}
		return $parms;
	}
} // End Kohana_MMI_API_Google
