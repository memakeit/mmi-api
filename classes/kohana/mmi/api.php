<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Perform API calls to external services.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class (http://github.com/abraham/twitteroauth).
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API
{
    // Class constants
    const FORMAT_ATOM = 'atom';
    const FORMAT_JSON = 'json';
    const FORMAT_RSS = 'rss';
    const FORMAT_XML = 'xml';
    const FORMAT_YAML = 'yaml';

    const SERVICE_DELICIOUS = 'delicious';
    const SERVICE_DIGG = 'digg';
    const SERVICE_FACEBOOK = 'facebook';
    const SERVICE_FLICKR = 'flickr';
    const SERVICE_GITHUB = 'github';
    const SERVICE_READERNAUT = 'readernaut';
    const SERVICE_TWITTER = 'twitter';

    /**
     * @var string the root API URL
     **/
    protected $_api_url = '';

    /**
     * @var integer the CURL connection timeout
     **/
    protected $_connection_timeout = 10;

    /**
     * @var boolean turn debugging on?
     **/
    protected $_debug;

    /**
     * @var boolean decode json results?
     * */
    protected $_decode_json = TRUE;

    /**
     * @var string the output format
     **/
    protected $_format = self::FORMAT_JSON;

    /**
     * @var array the last HTTP headers returned
     **/
    protected $_http_headers;

    /**
     * @var integer the last HTTP status code returned
     **/
    protected $_http_status;

    /**
     * @var array information about the last API call made
     **/
    protected $_last_api_call;

    /**
     * @var string the service name
     */
    protected $_service = '?';

    /**
     * @var boolean verify the SSL certificate during CURL requests?
     **/
    protected $_ssl_verifypeer = FALSE;

    /**
     * @var integer the CURL timeout
     **/
    protected $_timeout = 30;

    /**
     * @var string the user-agent sent by CURL requests
     **/
    protected $_useragent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';

    protected $_last_response;
    protected $_username;
    protected $_password;


    /**
     * Configure debugging (using the Request instance).
     *
     * @return  void
     */
    public function __construct()
    {
        $this->_debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
    }

    /**
     * Get or set the API URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function api_url($value = NULL)
    {
        return $this->_get_set('_api_url', $value, 'is_string');
    }

    /**
     * Get or set the CURL connection timeout.
     * This method is chainable when setting a value.
     *
     * @param   integer the value to set
     * @return  mixed
     */
    public function connection_timeout($value = NULL)
    {
        return $this->_get_set('_connection_timeout', $value, 'is_int');
    }

    /**
     * Get or set whether to decode JSON results.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function decode_json($value = NULL)
    {
        return $this->_get_set('_decode_json', $value, 'is_bool');
    }

    /**
     * Get or set the output format.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function format($value = NULL)
    {
        return $this->_get_set('_format', $value, 'is_string');
    }

    /**
     * Get the last HTTP headers returned.
     *
     * @return  array
     */
    public function http_headers()
    {
        return $this->_get_set('_http_headers');
    }

    /**
     * Get the last HTTP status code returned.
     *
     * @return  integer
     */
    public function http_status()
    {
        return intval($this->_http_status);
    }

    /**
     * Get the last response received.
     *
     * @return  array
     */
    public function last_response()
    {
        return $this->_get_set('_last_response');
    }

    /**
     * Get the service name.
     *
     * @return  string
     */
    public function service()
    {
        return $this->_service;
    }

    /**
     * Get or set whether to verify the SSL certificate during CURL requests.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function ssl_verifypeer($value = NULL)
    {
        return $this->_get_set('_ssl_verifypeer', $value, 'is_bool');
    }

    /**
     * Get or set the CURL timeout.
     * This method is chainable when setting a value.
     *
     * @param   integer the value to set
     * @return  mixed
     */
    public function timeout($value = NULL)
    {
        return $this->_get_set('_timeout', $value, 'is_int');
    }

    /**
     * Get or set the useragent sent by CURL requests.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function useragent($value = NULL)
    {
        return $this->_get_set('_useragent', $value, 'is_string');
    }

    public function username($value)
    {
        return $this->_get_set('_username', $value, 'is_string');
    }

    public function password($value)
    {
        return $this->_get_set('_password', $value, 'is_string');
    }

    /**
     * Perform a GET request.
     *
     * @param   string  the URL
     * @param   array   the request parameters
     * @return  string
     */
    public function get($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_GET);
    }

    /**
     * Perform multiple GET requests.
     *
     * @param   array   the request details (url and parameters)
     * @return  array
     */
    public function mget($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_GET);
    }

    /**
     * Perform multiple GET requests.
     *
     * @param   array   the request details (url and parameters)
     * @return  array
     */
    public function mexec($requests)
    {
        return $this->_mrequest($requests, 'EXEC');
    }

    /**
     * Perform a POST request.
     *
     * @param   string  the URL
     * @param   array   the request parameters
     * @return  string
     */
    public function post($url, $parms = array())
    {
        $custom_headers = array('Authorization' => 'Basic '.base64_encode($this->_username.':'.$this->_password));
//        MMI_Debug::dump($url);
//        $parms = NULL;
        return $this->_request($url, $parms, MMI_HTTP::METHOD_POST, $custom_headers);
    }

    /**
     * Perform a HEAD request.
     *
     * @param   string  the URL
     * @param   array   the request parameters
     * @return  string
     */
    public function head($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_HEAD);
    }

    /**
     * Perform a DELETE request.
     *
     * @param   string  the URL
     * @param   array   the request parameters
     * @return  string
     */
    public function delete($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_DELETE);
    }


    /**
     * Execute the API call.
     *
     * @param   string  the HTTP method
     * @param   string  the URL
     * @param   array   the request parameters
     * @return  mixed
     */
    protected function _request($url, $parms, $method = MMI_HTTP::METHOD_GET, $custom_http_headers = NULL)
    {
        if (strrpos($url, 'https://') !== 0 AND strrpos($url, 'http://') !== 0)
        {
            $url = $this->_build_url($url);
        }

        $remote = new MMI_Curl;

        $remote->add_curl_option(CURLOPT_USERAGENT, $this->_useragent);
        if (is_array($custom_http_headers) AND count($custom_http_headers) > 0)
        {
            foreach ($custom_http_headers as $name => $value)
            {
                $remote->add_http_header($name, $value);
            }
        }

        $method = strtolower($method);
        $response = $remote->$method($url, $parms);

        // Format and return the response
        if ( ! empty($respone) AND $this->_format === self::FORMAT_JSON AND $this->_decode_json)
        {
            $response = json_decode($response, TRUE);
        }

        $this->_last_response = $response;
        return $response;
    }

    /**
     * Execute multiple API calls.
     *
     * @param   string  the HTTP method
     * @param   array   the request details (url and parameters)
     * @return  array
     */
    protected function _mrequest($requests, $method = MMI_HTTP::METHOD_GET, $custom_http_headers = NULL)
    {
        foreach ($requests as $id => $request)
        {
            $url = Arr::get($request, 'url');
            if (strrpos($url, 'https://') !== 0 AND strrpos($url, 'http://') !== 0)
            {
                $url = $this->_build_url($url);
            }
            $requests[$id]['url'] = $url;
        }

        $remote = new MMI_Curl;
        $remote->add_curl_option(CURLOPT_USERAGENT, $this->_useragent);
        if (is_array($custom_http_headers) AND count($custom_http_headers) > 0)
        {
            foreach ($custom_http_headers as $name => $value)
            {
                $remote->add_http_header($name, $value);
            }
        }

        $method = 'm'.strtolower($method);
        $responses = $remote->$method($requests);

        // Format and return the response
        if ( ! empty($responses) AND is_array($responses) AND count($responses) > 0)
        {
            if ($this->_format === self::FORMAT_JSON AND $this->_decode_json)
            {
                foreach ($responses as $id => $response)
                {
                   $responses[$id] = json_decode($response->body, TRUE);
                }
            }
        }

        if (is_array($responses))
        {
            $this->_last_response = end($responses);
        }
        return $responses;
    }

    /**
     * Build the request URL.
     *
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($path)
    {
        return $this->_api_url.$path;
    }

    /**
     * Get or set a class property.
     * This method is chainable when setting a value.
     *
     * @param   string  the name of the class property to set
     * @param   mixed   the value to set
     * @param   string  the name of the data verification method
     * @return  mixed
     */
    protected function _get_set($name, $value = NULL, $verify_method = 'is_string')
    {
        if ($verify_method($value))
        {
            $this->$name = $value;
            return $this;
        }
        return $this->$name;
    }

    /**
     * Create an API instance.
     *
     * @param   string  the service name
     * @return  MMI_API
     */
    public static function factory($driver)
    {
        $class = 'MMI_API_'.ucfirst($driver);
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
} // End Kohana_MMI_API