<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify OAuth credentials.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_API_Verify_OAuth
{
	/**
	 * @var array an associative array of OAuth configuration options
	 **/
	protected $_auth_config = array();

	/**
	 * @var boolean turn debugging on?
	 **/
	protected $_debug;

	/**
	 * @var string the service name
	 */
	protected $_service = '?';

	/**
	 * @var array an associative array of service-specific configuration options
	 **/
	protected $_service_config = array();

	/**
	 * Initialize debugging (using the Request instance).
	 * Include the OAuth vendor files.
	 * Load the configuration settings.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		require_once Kohana::find_file('vendor', 'oauth/oauth_required');

		$this->_debug = class_exists('MMI_Request') ? MMI_Request::debug() : FALSE;
		$config = MMI_API::get_config();
		$this->_service_config = $config->get($this->_service, array());
		$this->_auth_config = Arr::get($this->_service_config, 'auth', array());
	}

	/**
	 * Verify the OAuth credentials.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the service name
	 * @return	boolean
	 */
	public function verify($service = NULL)
	{
		// Set the service
		if ( ! isset($service))
		{
			$service = $this->_service;
		}
		if (empty($service))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Service not set');
			throw new Kohana_Exception('Service not set in :method.', array
			(
				':method' => __METHOD__,
			));
		}

		$auth_config = $this->_auth_config;
		$require_verification_code = Arr::get($auth_config, 'require_verification_code', TRUE);

		// Ensure the verification parameters are set
		$verification_code = NULL;
		if (array_key_exists('oauth_verifier', $_GET))
		{
			$verification_code = urldecode(Security::xss_clean($_GET['oauth_verifier']));
		}
		$token_key = NULL;
		if (array_key_exists('oauth_token', $_GET))
		{
			$token_key = urldecode(Security::xss_clean($_GET['oauth_token']));
		}
		if (empty($token_key) OR ($require_verification_code AND empty($verification_code)))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Verification parameter missing. OAuth token:'.$token_key.'. Verification code:'.$verification_code);
			throw new Kohana_Exception('Verification parameter missing in :method. OAuth token: :token_key. Verification code: :verification_code.', array
			(
				':method'				=> __METHOD__,
				':token_key'			=> $token_key,
				':verification_code'	=> $verification_code,
			));
		}

		// Load existing data from the database
		$username = Arr::get($auth_config, 'username');
		$model;
		if ( ! empty($username))
		{
			$model = Model_MMI_API_Tokens::select_by_service_and_username($service, $username, FALSE);
		}
		else
		{
			$consumer_key = Arr::get($auth_config, 'consumer_key');
			$model = Model_MMI_API_Tokens::select_by_service_and_consumer_key($service, $consumer_key, FALSE);
		}

		$success = FALSE;
		if ($model->loaded())
		{
			// Check if the credentials were previously verified
			$previously_verified = $model->verified;
			if ($previously_verified)
			{
				$success = TRUE;
			}
			elseif ( ! $require_verification_code)
			{
				// Create a dummy verification code
				$verification_code = $service.'-'.time();
			}

			// Do database update
			if ( ! $previously_verified AND $model->token_key === $token_key)
			{
				// Get an access token
				$svc = MMI_API::factory($service);
				$token = $svc->get_access_token($verification_code, array
				(
					'token_key'		=> $token_key,
					'token_secret'	=> Encrypt::instance()->decode($model->token_secret),
				));

				// Update the token credentials in the database
				if (isset($token) AND $svc->is_valid_token($token))
				{
					$model->token_key = $token->key;
					$model->token_secret = Encrypt::instance()->encode($token->secret);
					$model->verified = 1;
					$model->verification_code = $verification_code;
					if ( ! empty($token->attributes))
					{
						$model->attributes = $token->attributes;
					}
					$success = MMI_Jelly::save($model, $errors);
					if ( ! $success AND $this->_debug)
					{
						MMI_Debug::dead($errors);
					}
				}
			}
		}
		return $success;
	}

	/**
	 * Create an OAuth verification instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the name of the service
	 * @return	MMI_API_Verify_OAuth
	 */
	public static function factory($driver = NULL)
	{
		$class = 'MMI_API_Verify_OAuth';
		if ( ! empty($driver))
		{
			$class .= '_'.ucfirst($driver);
		}

		if ( ! class_exists($class))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
			throw new Kohana_Exception(':class class does not exist in :method.', array
			(
				':class'	=> $class,
				':method'	=> __METHOD__
			));
		}
		return new $class;
	}
} // End Kohana_MMI_API_Verify_OAuth
