<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify OAuth authorization.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_OAuth_Verification
{
    /**
     * @var boolean turn debugging on?
     **/
    protected $_debug;

    /**
     * @var string the key used to locate the OAuth token
     **/
    protected $_key_token = 'oauth_token';

    /**
     * @var string the key used to locate the OAuth verifier
     **/
    protected $_key_verifier = 'oauth_verifier';

    /**
     * @var string the service name
     */
    protected $_service = '?';

    /**
     * @var array an associative array of OAuth configuration options
     **/
    protected $_auth_config = array();

    /**
     * @var array an associative array of service-specific configuration options
     **/
    protected $_service_config = array();

    /**
     * Configure debugging (using the Request instance) and retrieve the configuration options.
     *
     * @return  void
     */
    public function __construct()
    {
        require_once Kohana::find_file('vendor', 'oauth/oauth_required');

        $this->_debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
        $config = MMI_API::get_config(TRUE);
        $this->_service_config = Arr::get($config, $this->_service, array());
        $this->_auth_config = Arr::get($this->_service_config, 'auth', array());
    }

    /**
     * Insert the OAuth verification details into the database.
     *
     * @throws  Kohana_Exception
     * @param   string  the service name
     * @return  boolean
     */
    public function insert_verification($service = NULL)
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
                ':method'   => __METHOD__,
            ));
        }

        // Ensure the verification parameters are set
        $oauth_verifier = Arr::get($_GET, $this->_key_verifier);
        $token_key = Arr::get($_GET, $this->_key_token);
        if (empty($oauth_verifier) OR empty($token_key))
        {
            MMI_Log::log_error(__METHOD__, __LINE__, 'Verification parameter missing.  OAuth token:'.$token_key.'.  OAuth verifier:'.$oauth_verifier);
            throw new Kohana_Exception('Verification parameter missing in :method.  OAuth token: :token_key.  OAuth verifier: :oauth_verifier.', array
            (
                ':method'           => __METHOD__,
                ':token_key'        => $token_key,
                ':oauth_verifier'   => $oauth_verifier,
            ));
        }

        $success = FALSE;
        $auth_config = Arr::path(MMI_API::get_config(TRUE), $service.'.auth', array());

        // Load existing data from the database
        $consumer_key = Arr::get($auth_config, 'consumer_key');
        $model = Model_MMI_Auth_Tokens::select_by_service_and_consumer_key($service, $consumer_key, FALSE);
        if ($model->loaded())
        {
            // Get an access token
            $auth_config['token_key'] = $token_key;
            $auth_config['token_secret'] = Encrypt::instance()->decode($model->token_secret);
            $token = MMI_API::factory($service)->get_access_token($oauth_verifier, $auth_config);

            // Save the token credentials in the database
            if ($token instanceof OAuthToken AND ! empty($token->key) AND ! empty($token->secret))
            {
                $model->token_key = $token->key;
                $model->token_secret = Encrypt::instance()->encode($token->secret);
                $model->oauth_verifier = $oauth_verifier;
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
        return $success;
    }

    /**
     * Create an OAuth verification instance.
     *
     * @throws  Kohana_Exception
     * @param   string  the name of the service
     * @return  MMI_OAuth_Verifcation
     */
    public static function factory($driver = NULL)
    {
        $class = 'MMI_OAuth_Verification';
        if ( ! empty($driver))
        {
            $class .= '_'.ucfirst($driver);
        }

        if ( ! class_exists($class))
        {
            MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
            throw new Kohana_Exception(':class class does not exist in :method.', array
            (
                ':class'    => $class,
                ':method'   => __METHOD__
            ));
        }
        return new $class;
    }
} // End Kohana_MMI_OAuth_Verification