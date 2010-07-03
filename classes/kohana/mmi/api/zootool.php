<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Zootool API calls.
 * Response formats: JSON
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://zootool.com/api/docs
 */
class Kohana_MMI_API_Zootool extends MMI_API
{
    // Service name
    protected $_service = MMI_API::SERVICE_ZOOTOOL;

    // API settings
    protected $_api_url = 'http://zootool.com/api/';

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
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        $auth_config = $this->_auth_config;
        $this->_api_key = Arr::get($auth_config, 'api_key');
        $this->_api_secret = Arr::get($auth_config, 'api_secret');
    }

    /**
     * Configure the request parameters as specified in the configuration file.
     * When processing additions, if a parameter value exists, it will not be overwritten.
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_parameters($parms)
    {
        $parms = parent::_configure_parameters($parms);

        $api_key = $this->_api_key;
        $this->_ensure_parm('API key', $api_key);
        $name = 'apikey';
        if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
        {
            $parms[$name] = $api_key;
        }
        return $parms;
    }

    /**
     * Configure the cURL options.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_curl_options($curl)
    {
        parent::_configure_curl_options($curl);

        // Get username and password
        $auth_config = $this->_auth_config;
        $username = Arr::get($auth_config, 'username');
        $password = Arr::get($auth_config, 'password');

        // Configure auth options
        if ( ! empty($username) AND ! empty($password))
        {
            $curl->add_curl_option(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            $curl->add_curl_option(CURLOPT_USERPWD, strtolower($username).':'.sha1($password));
        }
    }
} // End Kohana_MMI_API_Zootool