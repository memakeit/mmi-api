<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls using OAuth authentication.
 * This class is based on Abraham Williams' Twitter OAuth class.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @copyright	(c) 2009 Abraham Williams
 * @license		http://www.memakeit.com/license
 * @link		http://github.com/abraham/twitteroauth
 */
abstract class Kohana_MMI_API_OAuth extends MMI_API
{
	// Signature constants
	const SIGN_HMAC_SHA1 = 'HMAC-SHA1';
	const SIGN_PLAINTEXT = 'PLAINTEXT';
	const SIGN_RSA_SHA1 = 'RSA-SHA1';

	/**
	 * @var string the HTTP method used to process access token requests
	 **/
	protected $_access_token_http_method = MMI_HTTP::METHOD_POST;

	/**
	 * @var string the access token URL
	 **/
	protected $_access_token_url;

	/**
	 * @var string the authorization callback URL
	 **/
	protected $_auth_callback_url;

	/**
	 * @var string the authentication URL
	 **/
	protected $_authenticate_url;

	/**
	 * @var string the authorization URL (where the user will be forced to login)
	 **/
	protected $_authorize_url;

	/**
	 * @var OAuthConsumer the OAuth consumer object
	 **/
	protected $_consumer = NULL;

	/**
	 * @var Jelly_Model the OAuth credentials model
	 **/
	protected $_model = NULL;

	/**
	 * @var string the OAuth realm
	 **/
	protected $_realm = '';

	/**
	 * @var string the HTTP method used to process request token requests
	 **/
	protected $_request_token_http_method = MMI_HTTP::METHOD_POST;

	/**
	 * @var string the request token URL
	 **/
	protected $_request_token_url;

	/**
	 * @var boolean send the OAuth data as part of the request (instead of via an HTTP header)
	 **/
	protected $_send_auth_as_data = FALSE;

	/**
	 * @var OAuthSignatureMethod the signature object (used to sign the request)
	 **/
	protected $_signature_method = NULL;

	/**
	 * @var string the signature type (used to sign the request)
	 **/
	protected $_signature_type = MMI_API_OAuth::SIGN_HMAC_SHA1;

	/**
	 * @var OAuthToken the OAuth token object
	 **/
	protected $_token = NULL;

	/**
	 * @var string the username associated with the OAuth credentials
	 **/
	protected $_username = NULL;

	/**
	 * @var string the OAuth version
	 **/
	protected $_version = '1.0';

	/**
	 * Include the OAuth vendor files.  Load configuration settings.
	 * Create the signature, consumer, and token objects.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		require_once Kohana::find_file('vendor', 'oauth/oauth_required');
		$auth_config = $this->_auth_config;

		// Configure the auth URLs
		$settings = array
		(
			'auth_callback_url',
			'username'
		);
		foreach ($settings as $setting)
		{
			$var = '_'.$setting;
			$value = Arr::get($auth_config, $setting);
			if ( ! empty($value))
			{
				$this->$var = $value;
			}
		}

		// Create the consumer, token, and signature method objects
		$this->_consumer = $this->_get_consumer($auth_config);
		$this->_token = $this->_get_token($auth_config);
		$this->_signature_method = $this->_get_signature_method();

		// Configure other OAuth settings
		$this->_realm = Arr::get($auth_config, 'realm');
		$this->_send_auth_as_data = Arr::get($auth_config, 'send_auth_as_data', FALSE);

		// Ensure the access token is valid
		if ( ! $this->is_valid_token())
		{
			$this->_load_token();
		}
	}

	/**
	 * Get or set the HTTP method used to process access token requests.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function access_token_http_method($value = NULL)
	{
		return $this->_get_set('_access_token_http_method', $value, 'is_string');
	}

	/**
	 * Get or set the access token URL.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function access_token_url($value = NULL)
	{
		return $this->_get_set('_access_token_url', $value, 'is_string');
	}

	/**
	 * Get or set the authorization callback URL.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function auth_callback_url($value = NULL)
	{
		return $this->_get_set('_auth_callback_url', $value, 'is_string');
	}

	/**
	 * Get or set the authentication URL.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function authenticate_url($value = NULL)
	{
		if (is_string($value))
		{
			$this->_authenticate_url = $value;
			return $this;
		}
		else
		{
			$url = $this->_authenticate_url;
			if ( ! empty($url) AND isset($this->_token) AND ! empty($this->_token->key))
			{
				$url .= '?oauth_token='.$this->_token->key;
			}
			return $url;
		}
	}

	/**
	 * Get or set the authorization URL (where the user will be forced to login).
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function authorize_url($value = NULL)
	{
		if (is_string($value))
		{
			$this->_authorize_url = $value;
			return $this;
		}
		else
		{
			$url = $this->_authorize_url;
			if ( ! empty($url) AND isset($this->_token) AND ! empty($this->_token->key))
			{
				$url .= '?oauth_token='.$this->_token->key;
			}
			return $url;
		}
	}

	/**
	 * Get or set the OAuth realm.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function realm($value = NULL)
	{
		return $this->_get_set('_realm', $value, 'is_string');
	}

	/**
	 * Get or set the HTTP method used to process request token requests.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function request_token_http_method($value = NULL)
	{
		return $this->_get_set('_request_token_http_method', $value, 'is_string');
	}

	/**
	 * Get or set the request token URL.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function request_token_url($value = NULL)
	{
	  return $this->_get_set('_request_token_url', $value, 'is_string');
	}

	/**
	 * Get or set whether to send the OAuth data as part of the request
	 * (instead of via an HTTP header).
	 * This method is chainable when setting a value.
	 *
	 * @param	boolean	the value to set
	 * @return	mixed
	 */
	public function send_auth_as_data($value = NULL)
	{
	  return $this->_get_set('_send_auth_as_data', $value, 'is_bool');
	}

	/**
	 * Get or set the username associated with the OAuth credentials.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function username($value = NULL)
	{
		return $this->_get_set('_username', $value, 'is_string');
	}

	/**
	 * Get or set the OAuth version.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function version($value = NULL)
	{
		return $this->_get_set('_version', $value, 'is_string');
	}

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

		// Configure the request parameters
		$parms = array();
		if ( ! isset($oauth_callback))
		{
			$oauth_callback = $this->_auth_callback_url;
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
	 * Exchange the request token for an access token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the verification code
	 * @param	array	an associative array of auth settings
	 * @return	OAuthToken
	 */
	public function get_access_token($oauth_verifier = NULL, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = $this->_access_token_http_method;
		$url = $this->_access_token_url;
		$this->_ensure_parm('Access token URL', $url);

		// Configure the request parameters
		$parms = array();
		if ( ! empty($oauth_verifier))
		{
			$parms['oauth_verifier'] = $oauth_verifier;
		}

		// Make the request and extract the token
		$response = $this->_auth_request($auth_config, $http_method, $url, $parms);
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid access token'))
		{
			$token = $this->_extract_token($response);
		}
		return $token;
	}

	/**
	 * Exchange a username and password for an access token.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the username
	 * @param	string	the password
	 * @param	array	an associative array of auth settings
	 * @return	OAuthToken
	 */
	public function get_xauth_token($username, $password, $auth_config = array())
	{
		// Configure the auth settings
		if ( ! is_array($auth_config))
		{
			$auth_config = array();
		}
		$auth_config = Arr::merge($this->_auth_config, $auth_config);

		// Configure the HTTP method and the URL
		$http_method = Arr::get($auth_config, 'xauth_token_http_method', MMI_HTTP::METHOD_POST);
		$url = $this->_access_token_url;
		$this->_ensure_parm('Access token URL', $url);

		// Configure the request parameters
		$parms = array
		(
			'x_auth_username'	=> $username,
			'x_auth_password'	=> $password,
			'x_auth_mode'		=> 'client_auth',
		);

		// Make the request and extract the token
		$response = $this->_auth_request($auth_config, $http_method, $url, $parms);
		$token = NULL;
		if ($this->_validate_curl_response($response, 'Invalid xauth access token'))
		{
			$token = $this->_extract_token($response);
		}
		return $token;
	}

	/**
	 * Check if the token is valid.
	 *
	 * @param	OAuthToken	the OAuth token object
	 * @param	boolean		check the token's verified flag?
	 * @return	boolean
	 */
	public function is_valid_token($token = NULL, $check_verified = FALSE)
	{
		if ( ! $token instanceof OAuthToken)
		{
			$token = $this->_token;
		}
		if ( ! $token instanceof OAuthToken)
		{
			return FALSE;
		}

		$valid = FALSE;
		if ($check_verified AND isset($token->verified) AND is_bool($token->verified))
		{
			$valid = $token->verified;
		}
		elseif ( ! $check_verified)
		{
			$valid = ( ! empty($token->key) AND ! empty($token->secret));
		}
		return $valid;
	}

	/**
	 * After obtaining a new request token, redirect to the authorization URL.
	 *
	 * @return	void
	 */
	public function do_auth_redirect()
	{
		$redirect = NULL;
		try
		{
			$redirect = $this->get_auth_redirect();
		}
		catch (Exception $e)
		{
			$redirect = NULL;
		}

		if ( ! empty($redirect))
		{
			Request::$instance->redirect($redirect);
		}
	}

	/**
	 * After obtaining a new request token, return the authorization URL.
	 *
	 * @throws	Kohana_Exception
	 * @param	OAuthToken	the OAuth token object
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
			MMI_API::log_error(__METHOD__, __LINE__, 'Invalid request token for '.$service);
			throw new Kohana_Exception('Invalid request token for :service in :method.', array
			(
				':service'	=> $service,
				':method'	=> __METHOD__,
			));
		}

		// Get the x-oauth redirect, if present
		if (isset($this->token->attributes))
		{
			$redirect = Arr::get($this->token->attributes, 'xoauth_request_auth_url');
		}

		// Validate the redirect
		if (empty($redirect) OR ! $this->_is_valid_redirect($redirect))
		{
			$redirect = $this->authenticate_url();
		}
		if (empty($redirect) OR ! $this->_is_valid_redirect($redirect))
		{
			$redirect = $this->authorize_url();
		}
		return $redirect;
	}

	/**
	 * Make an API call.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @param	string	the HTTP method
	 * @return	mixed
	 */
	protected function _request($url, $parms, $method = MMI_HTTP::METHOD_GET)
	{
		// Ensure the token is valid
		$this->_check_token();

		// Configure URL
		$url = $this->_configure_url($url);

		// Configure parameters
		$parms = $this->_configure_parameters($parms);

		// Sign the request
		$consumer = $this->_consumer;
		$token = $this->_token;
		$request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parms);
		$request->sign_request($this->_signature_method, $consumer, $token);
		$url = $request->get_normalized_http_url();

		// Send the OAuth parameters as part of the request?
		if ($this->_send_auth_as_data)
		{
			switch (strtoupper($method))
			{
				case MMI_HTTP::METHOD_GET:
					$url = $request->to_url();
					$parms = NULL;
					break;

				default:
					$url = $request->get_normalized_http_url();
					$parms = $request->to_postdata();
					break;
			}
		}

		// Create and configure the cURL object
		$curl = new MMI_Curl;
		$this->_configure_curl_options($curl);
		$this->_configure_auth_header($curl, $request);
		$this->_configure_http_headers($curl);
		unset($request);

		// Execute the cURL request
		$method = strtolower($method);
		$response = $curl->$method($url, $parms);
		unset($curl);

		// Format and return the response
		if ($response instanceof MMI_Curl_Response AND $this->_decode)
		{
			$method  = '_decode_'.strtolower($this->_format);
			if (method_exists($this, $method))
			{
				$decoded = $this->$method($response->body());
				$response->body($decoded);
			}
		}

		self::$_last_response = $response;
		return $response;
	}

	/**
	 * Make multiple API calls.
	 *
	 * @param	array	an associative array containing the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @param	string	the HTTP method
	 * @return	array
	 */
	protected function _mrequest($requests, $method = MMI_HTTP::METHOD_GET)
	{
		// Ensure the token is valid
		$this->_check_token();

		$consumer = $this->_consumer;
		$signature_method = $this->_signature_method;
		$token = $this->_token;

		foreach ($requests as $id => $request)
		{
			// Configure the HTTP methods
			if ( ! isset($request['method']))
			{
				$requests[$id]['method'] = (strtoupper($method) === 'EXEC') ? MMI_HTTP::METHOD_GET : $method;
			}
			$method = Arr::get($request, 'method');

			// Configure URLs
			$url = Arr::get($request, 'url');
			$url = $this->_configure_url($url);

			// Configure parameters
			$parms = Arr::get($request, 'parms');
			$parms = $this->_configure_parameters($parms);

			// Sign the request
			$request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parms);
			$request->sign_request($signature_method, $consumer, $token);
			$url = $request->get_normalized_http_url();

			// Send the OAuth parameters as part of the request?
			if ($this->_send_auth_as_data)
			{
				switch (strtoupper($method))
				{
					case MMI_HTTP::METHOD_GET:
						$url = $request->to_url();
						$parms = NULL;
						break;

					default:
						$url = $request->get_normalized_http_url();
						$parms = $request->to_postdata();
						break;
				}
			}

			// Get the HTTP authorization header
			$auth = $this->_get_auth_header($request);
			if ( ! empty($auth))
			{
				$requests[$id]['http_headers']['Authorization'] = $auth;
			}

			$requests[$id]['url'] = $url;
			$requests[$id]['parms'] = $parms;
		}

		// Create and configure the cURL object
		$curl = new MMI_Curl;
		$this->_configure_curl_options($curl);
		$this->_configure_http_headers($curl);

		// Execute the cURL request
		$method = 'm'.strtolower($method);
		$responses = $curl->$method($requests);
		unset($curl);

		// Format the response
		if ($this->_decode AND is_array($responses) AND count($responses) > 0)
		{
			$method  = '_decode_'.strtolower($this->_format);
			if (method_exists($this, $method))
			{
				foreach ($responses as $id => $response)
				{
					if ($response instanceof MMI_Curl_Response)
					{
						$decoded = $this->$method($response->body());
						$responses[$id]->body($decoded);
					}
				}
			}
		}

		if (is_array($responses))
		{
			self::$_last_response = end($responses);
		}
		return $responses;
	}

	/**
	 * Ensure the request token has been verified and an access token received.
	 *
	 * @throws	Kohana_Exception
	 * @return	void
	 */
	protected function _check_token()
	{
		if ( ! $this->is_valid_token(NULL, TRUE))
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
	 * Does the redirect URL contains an oauth_token parameter?
	 *
	 * @param	string	the redirect URL
	 * @return	boolean
	 */
	protected function _is_valid_redirect($url)
	{
		if (empty($url))
		{
			return FALSE;
		}
		parse_str(parse_url($url, PHP_URL_QUERY), $parms);
		$oauth_token = Arr::get($parms, 'oauth_token');
		return ( ! empty($oauth_token));
	}

	/**
	 * Load the OAuth credentials.
	 *
	 * @return	void
	 */
	protected function _load_token()
	{
		$model = $this->_model;
		if ( ! $model instanceof Jelly_Model)
		{
			$model = $this->_get_db_model();
		}
		if ($model->loaded())
		{
			$token = new OAuthToken($model->token_key, Encrypt::instance()->decode($model->token_secret));
			$token->attributes = $model->attributes;
			$token->verified = $model->verified;
			$this->_token = $token;
		}
		$this->_model = $model;
	}

	/**
	 * Get the OAuth credentials model from the database.
	 *
	 * @return	Jelly_Model
	 */
	protected function _get_db_model()
	{
		$model;
		$service = $this->_service;
		$username = $this->_username;
		if ( ! empty($username))
		{
			$model = Model_MMI_API_Tokens::select_by_service_and_username($service, $username, FALSE);
		}
		else
		{
			$model = Model_MMI_API_Tokens::select_by_service_and_consumer_key($service, $this->_consumer->key, FALSE);
		}
		return $model;
	}

	/**
	 * Update the OAuth credentials.
	 *
	 * @param	OAuthToken	the OAuth token object
	 * @param	boolean		save the extended token attributes?
	 * @return	boolean
	 */
	protected function _update_token($token, $save_attributes = TRUE)
	{
		if ( ! $token instanceof OAuthToken)
		{
			return FALSE;
		}
		$this->_token = new OAuthToken($token->key, $token->secret);

		// Load the data model
		$model = $this->_model;
		if ( ! $model instanceof Jelly_Model)
		{
			$model = $this->_get_db_model();
		}
		if ( ! $model->loaded())
		{
			$model = $this->_init_model($model);
		}

		// Update the data model
		$model->token_key = $token->key;
		$model->token_secret = Encrypt::instance()->encode($token->secret);
		if ($save_attributes AND ! empty($token->attributes))
		{
			$model->attributes = $token->attributes;
			$this->_token->attributes = $token->attributes;
		}
		$success = MMI_Jelly::save($model, $errors);
		if ( ! $success AND $this->_debug)
		{
			MMI_Debug::dead($errors);
		}
		$this->_model = $model;

		// Update the token's verified flag
		if ($success)
		{
			$this->_token->verified = $model->verified;
		}
		return $success;
	}

	/**
	 * Delete the OAuth credentials.
	 *
	 * @return	boolean
	 */
	protected function _delete_token()
	{
		$success = FALSE;
		$model = $this->_model;
		if ($model instanceof Jelly_Model)
		{
			$model->delete();
			$success = TRUE;
		}
		$this->_model = Jelly::factory('MMI_API_Tokens');
		return $success;
	}

	/**
	 * Initialize and return the OAuth credentials data.
	 *
	 * @param	Jelly_Model	the exisiting model
	 * @return	Jelly_Model
	 */
	protected function _init_model($model)
	{
		$consumer = $this->_consumer;
		$username = $this->_username;
		if ( ! $model instanceof Jelly_Model)
		{
			$model = Jelly::factory('MMI_API_Tokens');
		}
		if ($model instanceof Jelly_Model)
		{
			$model->service = $this->_service;
			$model->consumer_key = $consumer->key;
			$model->consumer_secret = Encrypt::instance()->encode($consumer->secret);
			if ( ! empty($username))
			{
				$model->username = $username;
			}
		}
		return $model;
	}

	/**
	 * Configure the HTTP authorization header sent via cURL.
	 *
	 * @param	MMI_Curl	the cURL object instance
	 * @return	void
	 */
	protected function _configure_auth_header($curl)
	{
		if ($this->_send_auth_as_data OR func_num_args() < 2)
		{
			return;
		}

		// Set an auth header, if necessary
		if ($this->_send_auth_header)
		{
			$request = func_get_arg(1);
			$auth = $this->_get_auth_header($request);
			if ( ! empty($auth))
			{
				$curl->add_http_header('Authorization', $auth);
			}
		}
	}

	/**
	 * Get the string to be sent via the authorization header.
	 *
	 * @return	string
	 */
	protected function _get_auth_header()
	{
		if ($this->_send_auth_as_data OR func_num_args() !== 1)
		{
			return;
		}

		$request = func_get_arg(0);
		$auth_header = $request->to_header($this->_realm);
		$temp = explode(': ', $auth_header);
		$auth_header = NULL;
		if (count($temp) === 2)
		{
			$auth_header = $temp[1];
		}
		return $auth_header;
	}

	/**
	 * Perform an authorization-related request.
	 *
	 * @param	array	an associative array of auth settings
	 * @param	string	the HTTP request method
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	protected function _auth_request($auth_config, $method, $url, $parms = array())
	{
		// Create the consumer, token, and signature method objects
		$consumer = $this->_get_consumer($auth_config);
		$token = $this->_get_token($auth_config);
		$signature_method = $this->_get_signature_method();

		// Prepare and sign the OAuth request
		$request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parms);
		$request->sign_request($signature_method, $consumer, $token);
		if (strtoupper($method) === MMI_HTTP::METHOD_GET)
		{
			$url = $request->to_url();
		}
		else
		{
			$url = $request->get_normalized_http_url();
			$parms = $request->to_postdata();
		}
		unset($consumer, $token, $signature_method, $request);

		// Execute the cURL request
		$method = strtolower($method);
		return MMI_Curl::factory()->$method($url, $parms);
	}

	/**
	 * Extract token data from a MMI_Curl_Response object and create an
	 * OAuthToken object.
	 *
	 * @param	MMI_Curl_Response	the response object
	 * @return	OAuthToken
	 */
	protected function _extract_token($response)
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
				$parms = OAuthUtil::parse_parameters($body);
				$oauth_token = Arr::get($parms, 'oauth_token');
				$oauth_token_secret = Arr::get($parms, 'oauth_token_secret');
				if ( ! empty($oauth_token) AND ! empty($oauth_token_secret))
				{
					$token = new OAuthToken($oauth_token, $oauth_token_secret);
					unset($parms['oauth_token'], $parms['oauth_token_secret']);
					$token->attributes = $parms;
				}
			}
		}
		return $token;
	}

	/**
	 * Create an OAuth consumer object using the auth configuration settings.
	 *
	 * @param	array	an associative array of auth settings
	 * @return	OAuthConsumer
	 */
	protected function _get_consumer($auth_config)
	{
		$consumer_key = Arr::get($auth_config, 'consumer_key');
		$consumer_secret = Arr::get($auth_config, 'consumer_secret');
		$auth_callback_url = Arr::get($auth_config, 'auth_callback_url');
		return new OAuthConsumer($consumer_key, $consumer_secret, $auth_callback_url);
	}

	/**
	 * Create an OAuth token object using the auth configuration settings.
	 *
	 * @param	array	an associative array of auth settings
	 * @return	OAuthToken
	 */
	protected function _get_token($auth_config)
	{
		$token_key = Arr::get($auth_config, 'token_key');
		$token_secret = Arr::get($auth_config, 'token_secret');
		$token = NULL;
		if ( ! empty($token_key))
		{
			$token = new OAuthToken($token_key, $token_secret);
		}
		return $token;
	}

	/**
	 * Create an OAuth signature object.
	 *
	 * @return	OAuthSignatureMethod
	 */
	protected function _get_signature_method()
	{
		$type = strtoupper($this->_signature_type);
		$signature_method = NULL;
		switch($type)
		{
			case self::SIGN_HMAC_SHA1:
				$signature_method = new OAuthSignatureMethod_HMAC_SHA1;
				break;

			case self::SIGN_PLAINTEXT:
				$signature_method = new OAuthSignatureMethod_PLAINTEXT;
				break;

			case self::SIGN_RSA_SHA1:
				// Not supported
				break;
		}
		return $signature_method;
	}
} // End Kohana_MMI_API_OAuth
