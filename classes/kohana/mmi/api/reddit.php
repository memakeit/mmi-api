<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Reddit API calls.
 * Response formats: JSON, RSS, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://code.reddit.com/wiki/API
 * @link		http://wiki.github.com/talklittle/reddit-is-fun/api-all-functions
 */
class Kohana_MMI_API_Reddit extends MMI_API
{
	// Service name
	protected $_service = MMI_API::SERVICE_REDDIT;

	// API settings
	protected $_api_url = 'http://www.reddit.com/';

	/**
	 * @var string the cookie value
	 **/
	protected $_cookie = NULL;

	/**
	 * @var Jelly_Model the authorization credentials model
	 **/
	protected $_model = NULL;

	/**
	 * @var string the user modhash value
	 **/
	protected $_usermodhash = NULL;

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
		$this->_username = Arr::get($this->_auth_config, 'username');

		// Load the cookie and user modhash values from the database
		$model = $this->_get_db_model();
		if ($model->loaded())
		{
			$this->_cookie = $model->token_key;
			$this->_usermodhash = Encrypt::instance()->decode($model->token_secret);
		}
		$this->_model = $model;
	}

	/**
	 * Get or set the cookie value.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function cookie($value = NULL)
	{
		return $this->_get_set('_cookie', $value, 'is_string');
	}

	/**
	 * Get or set the user modhash value.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function usermodhash($value = NULL)
	{
		return $this->_get_set('_usermodhash', $value, 'is_string');
	}

	/**
	 * Log the user into Reddit using the username and password from the config
	 * file.  Save the cookie and user modhash in the corresponding class
	 * properties.
	 *
	 * @param	boolean	save the cookie to the database?
	 * @return	void
	 */
	public function login($save_cookie_to_db = TRUE)
	{
		// Get username and password
		$auth_config = $this->_auth_config;
		$username = Arr::get($auth_config, 'username');
		$this->_ensure_parm('Username', $username);
		$password = Arr::get($auth_config, 'password');
		$this->_ensure_parm('Password', $password);

		// Login
		$url = $this->_api_url.'api/login/memakeit';
		$response = MMI_Curl::factory()->post($url, array('api_type' => 'json', 'user' => $username, 'passwd' => $password));
		$response = ($response instanceof MMI_Curl_Response) ? $response->body() : NULL;
		$data = NULL;
		if ( ! empty($response))
		{
			$response = $this->_decode_json($response, TRUE);
			$data = Arr::path($response, 'json.data');
		}

		// Extract cookie and user modhash
		$cookie = NULL;
		$usermodhash = NULL;
		if ( ! empty($data))
		{
			$cookie = Arr::get($data, 'cookie');
			$usermodhash = Arr::get($data, 'modhash');
		}

		// Save cookie and user modhash
		if ( ! empty($cookie) AND ! empty($usermodhash))
		{
			if ($save_cookie_to_db)
			{
				$this->_save_cookie_to_db($cookie, $usermodhash);
			}
			$this->_cookie = $cookie;
			$this->_usermodhash = $usermodhash;
		}
	}

	/**
	 * Make a POST request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function post($url, $parms = array())
	{
		if ( ! is_array($parms))
		{
			$parms = array();
		}

		$name = 'uh';
		$temp = Arr::get($parms, $name);
		if (empty($temp))
		{
			$parms[$name] = $this->_usermodhash;
		}
		return parent::post($url, $parms);
	}

	/**
	 * Make multiple POST requests.
	 * See the mget method for the format of the request data.
	 *
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mpost($requests)
	{
		$name = 'uh';
		foreach ($requests as $key => $request)
		{
			$parms = Arr::get($request, 'parms', array());
			$temp = Arr::get($parms, $name);
			if (empty($temp))
			{
				$parms[$name] = $this->_usermodhash;
				$requests[$key]['parms'] = $parms;
			}
		}
		return parent::mpost($requests);
	}

	/**
	 * Make multiple requests.
	 * Each request is an associative array containing an HTTP method (key = method), a URL (key = url) and optional request parameters, HTTP headers and cURL options (keys = parms, http_headers, curl_options).
	 * Each array of request settings can be associated with a key (recommended for easier extraction of results):
	 *		$requests = array
	 *		(
	 *			'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
	 *			'shadowhand' => array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * or the keys can be ommited:
	 *		$requests = array
	 *		(
	 *			array('method' => 'GET', 'url' => 'user/show/memakeit'),
	 *			array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * @param	array	the request details (HTTP method, URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mexec($requests)
	{
		$name = 'uh';
		foreach ($requests as $key => $request)
		{
			$method = Arr::get($request, 'method', MMI_HTTP::METHOD_GET);
			if (strtoupper($method) === MMI_HTTP::METHOD_POST)
			{
				$parms = Arr::get($request, 'parms', array());
				$temp = Arr::get($parms, $name);
				if (empty($temp))
				{
					$parms[$name] = $this->_usermodhash;
					$requests[$key]['parms'] = $parms;
				}
			}
		}
		return parent::mexec($requests);
	}

	/**
	 * Set the Reddit cookie.
	 *
	 * @param	MMI_Curl	the cURL object instance
	 * @return	void
	 */
	protected function _configure_curl_options($curl)
	{
		parent::_configure_curl_options($curl);
		$curl->add_curl_option(CURLOPT_COOKIE, "reddit_session={$this->_cookie}; Domain=reddit.com; Path=/");
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
		foreach (array(MMI_API::FORMAT_JSON, MMI_API::FORMAT_RSS, MMI_API::FORMAT_XML) as $ext)
		{
			if ($this->_url_ends_with($path, '.'.$ext))
			{
				$has_extension = TRUE;
				break;
			}
		}
		return ($has_extension) ? $url.$path : "$url$path.{$this->_format}";
	}

	/**
	 * Save the cookie and user modhash to the database.
	 *
	 * @param	string	the cookie value
	 * @param	string	the user modhash value
	 * @return	boolean
	 */
	protected function _save_cookie_to_db($cookie, $usermodhash)
	{
		$service = $this->_service;
		$model = $this->_model;
		if ( ! $model instanceof Jelly_Model)
		{
			$model = $this->_get_db_model();
		}
		if ($model instanceof Jelly_Model)
		{
			$encrypt = Encrypt::instance();
			$username = $this->_username;

			$model->service = $service;
			$model->consumer_key = 'consumer-'.$service;
			$model->consumer_secret = $encrypt->encode($service.'-'.time());
			if ( ! empty($username))
			{
				$model->username = $username;
			}
			$model->token_key = $cookie;
			$model->token_secret = $encrypt->encode($usermodhash);
			$model->verified = TRUE;
			$model->verification_code = $service.'-'.time();
			unset($encrypt);
		}

		$success = MMI_Jelly::save($model, $errors);
		if ( ! $success AND $this->_debug)
		{
			MMI_Debug::dead($errors);
		}
		$this->_model = $model;
		return $success;
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
} // End Kohana_MMI_API_Reddit
