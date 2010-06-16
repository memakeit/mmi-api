<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify OAuth authorization.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_OAuth_Verification
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
     * @return  boolean
     */
    public function insert_verification()
    {
        $oauth_verifier = Arr::get($_GET, $this->_key_verifier);
        $token_key = Arr::get($_GET, $this->_key_token);
        if (empty($oauth_verifier) OR empty($token_key))
        {
            return FALSE;
        }

        $success = FALSE;
        $auth_config = $this->_auth_config;

        // Load existing data from the database
        $consumer_key = Arr::get($auth_config, 'consumer_key');
        $model = Model_MMI_OAuth_Tokens::select_by_consumer_key($consumer_key, FALSE);
        if ($model->loaded())
        {
            $model->token_key = $token_key;
            $model->oauth_verifier = $oauth_verifier;
            $success = MMI_Jelly::save($model, $errors);
            if ( ! $success AND $this->_debug)
            {
                MMI_Debug::dead($errors);
            }
        }

        if ($success)
        {
            $success = FALSE;

            // Get an access token
            $auth_config['token_key'] = $model->token_key;
            $auth_config['token_secret'] = Encrypt::instance()->decode($model->token_secret);
            $token = MMI_API::factory($this->_service)->get_access_token($model->oauth_verifier, $auth_config);

            // Save access token in the database
            if ( ! empty($token) AND ! empty($token->key) AND ! empty($token->secret))
            {
                $model->token_key = $token->key;
                $model->token_secret = Encrypt::instance()->encode($token->secret);
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
     * @param   string  the name of the service
     * @return  MMI_OAuth_Verifcation
     */
    public static function factory($driver)
    {
        $class = 'MMI_OAuth_Verification_'.ucfirst($driver);
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