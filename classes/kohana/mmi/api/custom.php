<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls using custom authentication.
 * This class is based on Abraham Williams' Twitter OAuth class.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @copyright	(c) 2009 Abraham Williams
 * @license		http://www.memakeit.com/license
 * @link		http://github.com/abraham/twitteroauth
 */
abstract class Kohana_MMI_API_Custom extends MMI_API
{
	// Abstract functions
	abstract public function get_request_token($auth_callback = NULL, $auth_config = array());
	abstract public function get_access_token($auth_verifier = NULL, $auth_config = array());
	abstract public function get_auth_redirect();

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
	 * @var Jelly_Model the authorization credentials model
	 **/
	protected $_model = NULL;

	/**
	 * @var object the token object
	 **/
	protected $_token;

	/**
	 * @var string the username associated with the authorization credentials
	 **/
	protected $_username = NULL;

	/**
	 * Load configuration settings.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
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

		// Ensure the access token is valid
		if ( ! $this->is_valid_token())
		{
			$this->_load_token();
		}
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
		if (is_string($value))
		{
			$this->_auth_callback_url = $value;
			return $this;
		}
		elseif (Kohana::$environment === Kohana::PRODUCTION)
		{
			return URL::site(Route::get('api/verify')->uri(array('controller' => 'custom', 'service' => $this->_service)), TRUE);
		}
		else
		{
			return $this->_auth_callback_url;
		}
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
		return $this->_get_set('_authenticate_url', $value, 'is_string');
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
		return $this->_get_set('_authorize_url', $value, 'is_string');
	}

	/**
	 * Get or set the username associated with the authorization credentials.
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
	 * Check if the token is valid.
	 *
	 * @param	object	the token object
	 * @param	boolean	check the token's verified flag?
	 * @return	boolean
	 */
	public function is_valid_token($token = NULL, $check_verified = FALSE)
	{
		if ( ! is_object($token))
		{
			$token = $this->_token;
		}
		if ( ! is_object($token))
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
	 * Make an API call.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @param	string	the HTTP method
	 * @return	mixed
	 */
	protected function _request($url, $parms, $method = MMI_HTTP::METHOD_GET)
	{
		$this->_check_token();
		return parent::_request($url, $parms, $method);
	}

	/**
	 * Make multiple API calls.
	 *
	 * @param	array	an associative array containing the request details
	 * (URL, request parameters, HTTP headers, and cURL options)
	 * @param	string	the HTTP method
	 * @return	array
	 */
	protected function _mrequest($requests, $method = MMI_HTTP::METHOD_GET)
	{
		$this->_check_token();
		return parent::_mrequest($requests, $method);
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
	 * Load the authorization credentials.
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
			$token = new stdClass;
			$token->key = $model->token_key;
			$token->secret = Encrypt::instance()->decode($model->token_secret);
			$token->attributes = $model->attributes;
			$token->verified = $model->verified;
			$this->_token = $token;
		}
		$this->_model = $model;
	}

	/**
	 * Get the auth credentials model from the database.
	 *
	 * @return	Jelly_Model
	 */
	protected function _get_db_model()
	{
		$model;
		$username = $this->_username;
		if ( ! empty($username))
		{
			$model = Model_MMI_API_Tokens::select_by_service_and_username($this->_service, $username, FALSE);
		}
		else
		{
			$model = Jelly::factory('MMI_API_Tokens');
		}
		return $model;
	}

	/**
	 * Update the authorization credentials.
	 *
	 * @param	object	the token object
	 * @param	boolean	save the extended token attributes?
	 * @return	boolean
	 */
	protected function _update_token($token, $save_attributes = TRUE)
	{
		if ( ! is_object($token))
		{
			return FALSE;
		}

		// Update the token object
		$this->_token = new stdClass;
		$this->_token->key = $token->key;
		$this->_token->secret = $token->secret;

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
	 * Delete the authorization credentials.
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
	 * Initialize and return the authorization credentials data.
	 *
	 * @param	Jelly_Model	the exisiting model
	 * @return	Jelly_Model
	 */
	protected function _init_model($model)
	{
		$service = $this->_service;
		$username = $this->_username;
		if ( ! $model instanceof Jelly_Model)
		{
			$model = Jelly::factory('MMI_API_Tokens');
		}
		if ($model instanceof Jelly_Model)
		{
			$model->service = $service;
			$model->consumer_key = 'consumer-'.$service;
			$model->consumer_secret = Encrypt::instance()->encode($service.'-'.time());
			if ( ! empty($username))
			{
				$model->username = $username;
			}
		}
		return $model;
	}
} // End Kohana_MMI_API_Custom
