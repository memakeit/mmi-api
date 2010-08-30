<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Flickr API calls.
 * Response formats: JSON, PHP, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.flickr.com/services/api/
 */
class Kohana_MMI_API_Flickr extends MMI_API_Custom
{
	// Service name
	protected $_service = MMI_API::SERVICE_FLICKR;

	// API settings
	protected $_api_url = 'http://api.flickr.com/services/rest/';

	// Auth settings
	protected $_authorize_url = 'http://flickr.com/services/auth/';

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
	 * Get a request token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the callback URL
	 * @param	array	an associative array of auth settings
	 * @return	object
	 */
	public function get_request_token($auth_callback = NULL, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = MMI_HTTP::METHOD_POST;
		$url = $this->_api_url;
		$this->_ensure_parm('Request token URL', $url);

		// Get the API key
		$api_key = $this->_api_key;
		$this->_ensure_parm('API key', $api_key);

		// Configure the request parameters
		$parms = array
		(
			'api_key'			=> $api_key,
			'format'			=> 'json',
			'nojsoncallback'	=> 1,
			'method'			=> 'flickr.auth.getFrob',
		);
		$parms['api_sig'] = $this->_get_signature($parms);

		// Execute the cURL request
		$http_method = strtolower($http_method);
		$response = MMI_Curl::factory()->$http_method($url, $parms);

		// Extract the token
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid frob'))
		{
			$token = $this->_extract_request_token($response);
		}
		return $token;
	}

	/**
	 * Exchange the request token for an access token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the verification code
	 * @param	array	an associative array of auth settings
	 * @return	object
	 */
	public function get_access_token($auth_verifier = NULL, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = MMI_HTTP::METHOD_POST;
		$url = $this->_api_url;
		$this->_ensure_parm('Access token URL', $url);

		// Get the API key
		$api_key = $this->_api_key;
		$this->_ensure_parm('API key', $api_key);

		// Configure the request parameters
		$frob = Arr::get($auth_config, 'token_key');
		$parms = array
		(
			'api_key'			=> $api_key,
			'format'			=> 'json',
			'frob'				=> $frob,
			'nojsoncallback'	=> 1,
			'method'			=> 'flickr.auth.getToken',
		);
		$parms['api_sig'] = $this->_get_signature($parms);

		// Execute the cURL request
		$http_method = strtolower($http_method);
		$response = MMI_Curl::factory()->$http_method($url, $parms);

		// Extract the token
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid access token'))
		{
			$token = $this->_extract_access_token($response);
		}
		return $token;
	}

	/**
	 * After obtaining a new request token, return the authorization URL.
	 *
	 * @throws	Kohana_Exception
	 * @param	object	the token object
	 * @return	string
	 */
	public function get_auth_redirect($token = NULL)
	{
		$redirect = NULL;

		// Get a new request token
		if ( ! isset($token))
		{
			$token = $this->get_request_token();
		}
		if (isset($token) AND $this->is_valid_token($token))
		{
			$success = $this->_update_token($token);
		}
		else
		{
			$service = $this->_service;
			MMI_Log::log_error(__METHOD__, __LINE__, 'Invalid token for '.$service);
			throw new Kohana_Exception('Invalid token for :service in :method.', array
			(
				':service'	=> $service,
				':method'	=> __METHOD__,
			));
		}

		// Get the API key
		$api_key = $this->_api_key;
		$this->_ensure_parm('API key', $api_key);

		// Build the redirect URL
		$redirect = $this->authenticate_url();
		if (empty($redirect))
		{
			$redirect = $this->authorize_url();
		}
		$parms = array
		(
			'api_key'	=> $api_key,
			'frob'		=> $this->_token->key,
			'perms'		=> 'delete',
		);
		$parms['api_sig'] = $this->_get_signature($parms);
		return $redirect.'?'.http_build_query($parms);
	}

	/**
	 * Extract request token data from a MMI_Curl_Response object and create
	 * a token object.
	 *
	 * @param	MMI_Curl_Response	the response object
	 * @return	object
	 */
	protected function _extract_request_token($response)
	{
		if ( ! $response instanceof MMI_Curl_Response)
		{
			return NULL;
		}

		$token = NULL;
		if (intval($response->http_status_code()) === 200)
		{
			$body = $response->body();
			if ( ! empty($body))
			{
				$frob = NULL;
				$data = $this->_decode_json($body, TRUE);
				if (is_array($data) AND count($data) > 0)
				{
					$frob = Arr::path($data, 'frob._content');
				}
				if ( ! empty($frob))
				{
					$token = new stdClass;
					$token->key = $frob;
					$token->secret = $this->_service.'-'.time();
				}
			}
		}
		return $token;
	}

	/**
	 * Extract access token data from a MMI_Curl_Response object and create
	 * a token object.
	 *
	 * @param	MMI_Curl_Response	the response object
	 * @return	object
	 */
	protected function _extract_access_token($response)
	{
		if ( ! $response instanceof MMI_Curl_Response)
		{
			return NULL;
		}

		$token = NULL;
		if (intval($response->http_status_code()) === 200)
		{
			$body = $response->body();
			if ( ! empty($body))
			{
				$permissions = NULL;
				$token_key = NULL;
				$username = NULL;
				$data = $this->_decode_json($body, TRUE);
				if (is_array($data) AND count($data) > 0)
				{
					$auth = Arr::get($data, 'auth', array());
					$stat = Arr::get($data, 'stat');
					if (strcasecmp($stat, 'ok') === 0 AND is_array($auth) AND count($auth) > 0)
					{
						$token_key = Arr::path($auth, 'token._content');
						$permissions = Arr::path($auth, 'perms._content');
						$user = Arr::get($auth, 'user');
					}
				}

				if ( ! empty($token_key))
				{
					$token = new stdClass;
					$token->key = $token_key;
					$token->secret = $this->_service.'-'.time();
					$attributes = array();
					if ( ! empty($user))
					{
						$attributes['username'] = $user;
					}
					if ( ! empty($permissions))
					{
						$attributes['permissions'] = $permissions;
					}
					if (is_array($attributes) AND count($attributes) > 0)
					{
						$token->attributes = $attributes;
					}
				}
			}
	   }
		return $token;
	}

	/**
	 * Customize the request parameters as specified in the configuration file.
	 * When processing additions, if a parameter value exists, it will not be
	 * overwritten.
	 *
	 * @param	array	an associative array of request parameters
	 * @return	array
	 */
	protected function _configure_parameters($parms)
	{
		$parms = parent::_configure_parameters($parms);

		// Set the response format
		$name = 'format';
		$temp = Arr::get($parms, $name);
		if (empty($temp))
		{
			$parms[$name] = $format;

			$format = strtolower($this->_format);
			switch ($format)
			{
				case MMI_API::FORMAT_JSON:
					$name = 'nojsoncallback';
					$temp = Arr::get($parms, $name);
					if (empty($temp))
					{
						$parms[$name] = 1;
					}
					break;

				case MMI_API::FORMAT_XML:
					$format = 'rest';
					break;
			}
		}

		// Set the auth token
		if (is_object($this->_token))
		{
			$token_key = $this->_token->key;
			if ( ! empty($token_key))
			{
				$parms['auth_token'] = $token_key;
			}
		}

		// Set the API and generate the signature
		$parms['api_key'] = $this->_api_key;
		$parms['api_sig'] = $this->_get_signature($parms);
		return $parms;
	}

	/**
	 * Generate a signature using the value of the request parameters and
	 * the API secret.
	 *
	 * @param	array	an associative array of request parameters
	 * @return	string
	 */
	protected function _get_signature($parms)
	{
		if ( ! is_array($parms))
		{
			$parms = array();
		}

		// Ensure the API secret is set
		$api_secret = $this->_api_secret;
		$this->_ensure_parm('API secret', $api_secret);

		ksort($parms);
		$signature = $api_secret;
		foreach ($parms as $name => $value)
		{
			$signature .= $name.$value;
		}
		return md5($signature);
	}
} // End Kohana_MMI_API_Flickr
