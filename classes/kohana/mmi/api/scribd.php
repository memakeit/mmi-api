<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Scribd API calls.
 * Response formats: XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://www.scribd.com/developers/api
 */
class Kohana_MMI_API_Scribd extends MMI_API
{
    // Service name
    protected $_service = MMI_API::SERVICE_SCRIBD;

    // API settings
    protected $_api_url = 'http://api.scribd.com/api';

    /**
     * @var string the API key
     */
    protected $_api_key = NULL;

    /**
     * @var string the API secret
     */
    protected $_api_secret = NULL;

    /**
     * @var boolean sign the API requests?
     */
    protected $_sign_requests = FALSE;

    /**
     * Load configuration settings.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        $auth_config = $this->_auth_config;
        $this->_api_key = Arr::get($auth_config, 'api_key');
        $this->_api_secret = Arr::get($auth_config, 'api_secret');
        $this->_sign_requests = Arr::get($auth_config, 'sign_requests', FALSE);
    }

    /**
     * Customize the request parameters as specified in the configuration file.
     * When processing additions, if a parameter value exists, it will not be overwritten.
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_parameters($parms)
    {
        $parms = parent::_configure_parameters($parms);

        // Ensure the API key is set
        $api_key = $this->_api_key;
        $this->_ensure_parm('API key', $api_key);

        // Set the API and generate the signature
        $parms['api_key'] = $api_key;
        if ($this->_sign_requests)
        {
            $parms['api_sig'] = $this->_get_signature($parms);
        }
        return $parms;
    }

    /**
     * Generate a sinature using the value of the request parameters and the API secret.
     *
     * @param   array   an associative array of request parameters
     * @return  string
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
} // End Kohana_MMI_API_Scribd