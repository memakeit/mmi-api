<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Process OAuth authorization.
 *
 * @package     MMI Social
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
     * @var string service name
     */
    protected $_service = '?';

    /**
     * @var array OAuth configuration options
     **/
    protected $_oauth_config = array();

    /**
     * @var array service configuration options
     **/
    protected $_service_config = array();

    /**
     * Configure debugging (using the Request instance) and retrieve the configuration options.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->_debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
        $config = MMI_Social::get_config(TRUE);
        $service_config = Arr::path($config, 'services.'.$this->_service, array());
        $this->_oauth_config = Arr::get($service_config, 'oauth', array());
        $this->_service_config = $service_config;
    }

    /**
     * Insert the OAuth verification details into the database.
     *
     * @return  boolean
     */
    public function insert_verification()
    {
        $oauth_config = Arr::get($this->_service_config, 'oauth', array());
        $consumer_key = Arr::get($oauth_config, 'consumer_key');
        $model = Model_MMI_OAuth_Tokens::select_by_consumer_key($consumer_key, FALSE);
        $model->oauth_token = Arr::get($_GET, 'oauth_token');
        $model->oauth_verifier = Arr::get($_GET, 'oauth_verifier');
        $success = MMI_Jelly::save($model, $errors);
        if ( ! $success AND $this->_debug)
        {
            MM_Debug::dead($errors);
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