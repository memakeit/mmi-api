<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Delicious API calls.
 * Response formats: XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://delicious.com/help/api
 */
class Kohana_MMI_API_Delicious extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_DELICIOUS;

	// API settings
	protected $_api_url = 'http://api.del.icio.us/v2/';

	// OAuth settings
	protected $_request_token_url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
	protected $_access_token_url = 'https://api.login.yahoo.com/oauth/v2/get_token';
	protected $_authorize_url = 'https://api.login.yahoo.com/oauth/v2/request_auth';

	/**
	 * Ensure the request token has been verified and an access token received.
	 *
	 * @return	void
	 */
	protected function _check_token()
	{
		parent::_check_token();
		$token = $this->_token;
		if (isset($token->attributes) AND is_array($token->attributes))
		{
			$attributes = $token->attributes;
			$oauth_session_handle = Arr::get($attributes, 'oauth_session_handle');
			$oauth_expires_in = Arr::get($attributes, 'oauth_expires_in');

			// Get the date the token was last updated
			$date_updated = 0;
			$model= $this->_model;
			if ($model instanceof Jelly_Model)
			{
				$date_updated = $model->date_updated;
			}

			if ( ! empty($oauth_session_handle) AND ($date_updated + $oauth_expires_in < time()))
			{
				// Refresh the access token
				$token = $this->_refresh_access_token($oauth_session_handle, array
				(
					'token_key'		=> $token->key,
					'token_secret'	=> $token->secret,
				));
				if (isset($token) AND $this->is_valid_token($token))
				{
					$success = $this->_update_token($token);
				}
			}
		}
	}

	/**
	 * Refresh the access token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	an authorization session handle
	 * @param	array	an associative array of auth settings
	 * @return	OAuthToken
	 * @link	http://developer.yahoo.com/oauth/guide/oauth-refreshaccesstoken.html
	 */
	protected function _refresh_access_token($oauth_session_handle, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = MMI_HTTP::METHOD_POST;
		$url = $this->_access_token_url;
		$this->_ensure_parm('Access token URL', $url);

		// Configure the request parameters
		$parms['oauth_session_handle'] = $oauth_session_handle;

		// Make the request and extract the token
		$response = $this->_auth_request($auth_config, $http_method, $url, $parms);
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid refresh token'))
		{
			$token = $this->_extract_token($response);
		}
		return $token;
	}
} // End Kohana_MMI_API_Delicious
