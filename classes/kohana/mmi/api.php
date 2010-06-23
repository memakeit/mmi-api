<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls to 3rd-party services.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @copyright   (c) 2009 Abraham Williams
 * @license     http://www.memakeit.com/license
 * @link        http://github.com/abraham/twitteroauth
 */
abstract class Kohana_MMI_API
{
    // Output format constants
    const FORMAT_ATOM = 'atom';
    const FORMAT_JAVASCRIPT = 'javascript';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_PHP = 'php';
    const FORMAT_RSS = 'rss';
    const FORMAT_TEXT = 'txt';
    const FORMAT_XML = 'xml';
    const FORMAT_YAML = 'yaml';

    /**
     * @var Kohana_Config API settings
     */
    protected static $_config;

    /**
     * @var MMI_Curl_Response the last cURL response received
     **/
    protected static $_last_response;

    /**
     * @var boolean turn debugging on?
     **/
    public $debug;

    /**
     * @var string the API URL
     **/
    protected $_api_url = '';

    /**
     * @var array an associative array of authorization settings
     */
    protected $_auth_config;

    /**
     * @var integer the cURL connection timeout
     **/
    protected $_connect_timeout = 5;

    /**
     * @var boolean decode the results?
     **/
    protected $_decode = TRUE;

    /**
     * @var boolean return the decoded results as an associative array?
     * */
    protected $_decode_as_array = TRUE;

    /**
     * @var string the output format
     **/
    protected $_format = self::FORMAT_JSON;

    /**
     * @var string the service name
     */
    protected $_service = '?';

    /**
     * @var array an associative array of service-specific settings
     */
    protected $_service_config;

    /**
     * @var boolean send the HTTP accept header?
     **/
    protected $_send_accept_header = FALSE;

    /**
     * @var boolean send the HTTP authorization header?
     **/
    protected $_send_auth_header = FALSE;

    /**
     * @var boolean verify the SSL certificate during cURL requests?
     **/
    protected $_ssl_verifypeer = FALSE;

    /**
     * @var integer the cURL timeout
     **/
    protected $_timeout = 30;

    /**
     * @var string the user-agent sent by cURL requests
     **/
    protected $_useragent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';

    /**
     * Initialize debugging (using the Request instance).
     * Load the configuration settings.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
        $config = self::get_config(TRUE);
        $service_config = Arr::get($config, $this->_service, array());
        $this->_auth_config = Arr::get($service_config, 'auth', array());
        $this->_service_config = $service_config;

        $api_globals = Arr::get($config, 'api', array());
        $api_service = Arr::get($service_config, 'api', array());
        $settings = array
        (
            'api_url',
            'connect_timeout',
            'decode',
            'decode_as_array',
            'format',
            'send_accept_header',
            'send_auth_header',
            'ssl_verifypeer',
            'timeout',
            'useragent',
        );
        foreach ($settings as $name)
        {
            $value = Arr::get($api_service, $name, Arr::get($api_globals, $name));
            $this->$name($value);
        }
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
     * Get or set the cURL connection timeout.
     * This method is chainable when setting a value.
     *
     * @param   integer the value to set
     * @return  mixed
     */
    public function connect_timeout($value = NULL)
    {
        return $this->_get_set('_connect_timeout', $value, 'is_int');
    }

    /**
     * Get or set whether to decode the results.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function decode($value = NULL)
    {
        return $this->_get_set('_decode', $value, 'is_bool');
    }

    /**
     * Get or set whether to return decoded results as an array.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function decode_as_array($value = NULL)
    {
        return $this->_get_set('_decode_as_array', $value, 'is_bool');
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
     * Get the service name.
     *
     * @return  string
     */
    public function service()
    {
        return $this->_get_set('_service');
    }

    /**
     * Get or set whether to send the HTTP accept header.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function send_accept_header($value = NULL)
    {
        return $this->_get_set('_send_accept_header', $value, 'is_bool');
    }

    /**
     * Get or set whether to send the HTTP authorization header.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function send_auth_header($value = NULL)
    {
        return $this->_get_set('_send_auth_header', $value, 'is_bool');
    }

    /**
     * Get or set whether to verify the SSL certificate during cURL requests.
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
     * Get or set the cURL timeout.
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
     * Get or set the user-agent sent by cURL requests.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function useragent($value = NULL)
    {
        return $this->_get_set('_useragent', $value, 'is_string');
    }

    /**
     * Make a DELETE request.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    public function delete($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_DELETE);
    }

    /**
     * Make a GET request.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    public function get($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_GET);
    }

    /**
     * Make a HEAD request.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    public function head($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_HEAD);
    }

    /**
     * Make a POST request.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    public function post($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_POST);
    }

    /**
     * Make a PUT request.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    public function put($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_PUT);
    }

    /**
     * Make multiple DELETE requests.
     * See the mget method for the format of the request data.
     *
     * @see     mget
     * @param   array   the request details (URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mdelete($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_DELETE);
    }

    /**
     * Make multiple HEAD requests.
     * See the mget method for the format of the request data.
     *
     * @see     mget
     * @param   array   the request details (URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mhead($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_HEAD);
    }

    /**
     * Make multiple GET requests.
     * Each request is an associative array containing a URL (key = url) and optional request parameters, HTTP headers and cURL options (keys = parms, http_headers, curl_options).
     * Each array of request settings can be associated with a key (recommended for easier extraction of results):
     *      $requests = array
     *      (
     *          'memakeit' => array('url' => 'user/show/memakeit'),
     *          'shadowhand' => array('url' => 'user/show/shadowhand'),
     *      );
     *
     * or the keys can be ommited:
     *      $requests = array
     *      (
     *          array('url' => 'user/show/memakeit'),
     *          array('url' => 'user/show/shadowhand'),
     *      );
     *
     * @param   array   the request details (URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mget($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_GET);
    }

    /**
     * Make multiple POST requests.
     * See the mget method for the format of the request data.
     *
     * @see     mget
     * @param   array   the request details (URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mpost($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_POST);
    }

    /**
     * Make multiple PUT requests.
     * See the mget method for the format of the request data.
     *
     * @see     mget
     * @param   array   the request details (URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mput($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_PUT);
    }

    /**
     * Make multiple requests.
     * Each request is an associative array containing an HTTP method (key = method), a URL (key = url) and optional request parameters, HTTP headers and cURL options (keys = parms, http_headers, curl_options).
     * Each array of request settings can be associated with a key (recommended for easier extraction of results):
     *      $requests = array
     *      (
     *          'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
     *          'shadowhand' => array('method' => 'GET', 'url' => 'user/show/shadowhand'),
     *      );
     *
     * or the keys can be ommited:
     *      $requests = array
     *      (
     *          array('method' => 'GET', 'url' => 'user/show/memakeit'),
     *          array('method' => 'GET', 'url' => 'user/show/shadowhand'),
     *      );
     *
     * @param   array   the request details (HTTP method, URL, request parameters, HTTP headers, and cURL options)
     * @return  array
     */
    public function mexec($requests)
    {
        return $this->_mrequest($requests, 'EXEC');
    }

    /**
     * Make an API call.
     *
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @param   string  the HTTP method
     * @return  MMI_Curl_Response
     */
    protected function _request($url, $parms, $method = MMI_HTTP::METHOD_GET)
    {
        // Configure the URL
        $url = $this->_configure_url($url);

        // Configure the request parameters
        $parms = $this->_configure_parameters($parms);

        // Create and configure the cURL object
        $curl = MMI_Curl::factory();
        $this->_configure_curl_options($curl);
        $this->_configure_auth_header($curl);
        $this->_configure_http_headers($curl);

        // Execute the cURL request
        $method = strtolower($method);
        $response = $curl->$method($url, $parms);
        unset($curl);

        // Format the response
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
     * @param   array   an associative array containing the request details (URL, request parameters, HTTP headers, and cURL options)
     * @param   string  the HTTP method
     * @return  array
     */
    protected function _mrequest($requests, $method = MMI_HTTP::METHOD_GET)
    {
        foreach ($requests as $id => $request)
        {
            // Configure the URLs
            $url = Arr::get($request, 'url');
            $requests[$id]['url'] = $this->_configure_url($url);;

            // Configure the request parameters
            $parms = Arr::get($request, 'parms');
            $requests[$id]['parms'] = $this->_configure_parameters($parms);
        }

        // Create and configure the cURL object
        $curl = new MMI_Curl;
        $this->_configure_curl_options($curl);
        $this->_configure_auth_header($curl);
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
     * Configure the request URL.
     * If both a read-only and a read-write API URL are specified, the select the correct one.
     *
     * @param   string  the request URL
     * @return  string
     */
    protected function _configure_url($url)
    {
        if (strrpos($url, 'https://') !== 0 AND strrpos($url, 'http://') !== 0)
        {
            $path = $url;
            $url = $this->_build_url($this->_api_url, $path);
        }
        return $url;
    }

    /**
     * Build the request URL.
     *
     * @param   string  the base URL
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($url, $path)
    {
        return $url.$path;
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
        if ( ! is_array($parms))
        {
            $parms = array();
        }

        $custom = Arr::path($this->_service_config, 'custom.parms', array());
        if (is_array($custom) AND count($custom) > 0)
        {
            // Process removals
            $remove = Arr::get($custom, 'remove', FALSE);
            if (is_array($remove) AND count($remove) > 0)
            {
                foreach ($remove as $name)
                {
                    if (array_key_exists($name, $parms))
                    {
                        unset($parms[$name]);
                    }
                }
            }

            // Process additions
            $add = Arr::get($custom, 'add', FALSE);
            if (is_array($add) AND count($add) > 0)
            {
                foreach ($add as $name => $value)
                {
                    if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
                    {
                        $parms[$name] = $value;
                    }
                }
            }
        }

        // Configure authentication parameters that are passed as request parameters (instead of via HTTP headers)
        return $this->_configure_auth_parms($parms);
    }

    /**
     * Configure authentication parameters that are passed as request parameters (instead of via HTTP headers).
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_auth_parms($parms)
    {
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
        $curl->add_curl_option(CURLOPT_CONNECTTIMEOUT, $this->_connect_timeout);
        $curl->add_curl_option(CURLOPT_SSL_VERIFYPEER, $this->_ssl_verifypeer);
        $curl->add_curl_option(CURLOPT_TIMEOUT, $this->_timeout);
        $curl->add_curl_option(CURLOPT_USERAGENT, $this->_useragent);

        // Customize cURL options as specified in the configuration file
        $custom = Arr::path($this->_service_config, 'custom.curl_options', array());
        if (is_array($custom) AND count($custom) > 0)
        {
            // Process defaults
            $defaults = Arr::get($custom, 'defaults', FALSE);
            if (is_array($defaults))
            {
                $curl->curl_options($defaults);
            }

            // Process removals
            $remove = Arr::get($custom, 'remove', FALSE);
            if (is_array($remove) AND count($remove) > 0)
            {
                foreach ($remove as $name)
                {
                    $curl->remove_curl_option($name);
                }
            }

            // Process additions
            $add = Arr::get($custom, 'add', FALSE);
            if (is_array($add) AND count($add) > 0)
            {
                foreach ($add as $name => $value)
                {
                    $curl->add_curl_option($name, $value);
                }
            }
        }
    }

    /**
     * Configure the HTTP headers sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_http_headers($curl)
    {
        // Configure the HTTP accept header
        $this->_configure_accept_header($curl);

        // Customize HTTP headers as specified in the configuration file
        $custom = Arr::path($this->_service_config, 'custom.http_headers', array());
        if (is_array($custom) AND count($custom) > 0)
        {
            // Process defaults
            $defaults = Arr::get($custom, 'defaults', FALSE);
            if (is_array($defaults))
            {
                $curl->http_headers($defaults);
            }

            // Process removals
            $remove = Arr::get($custom, 'remove', FALSE);
            if (is_array($remove) AND count($remove) > 0)
            {
                foreach ($remove as $name)
                {
                    $curl->remove_http_header($name);
                }
            }

            // Process additions
            $add = Arr::get($custom, 'add', FALSE);
            if (is_array($add) AND count($add) > 0)
            {
                foreach ($add as $name=> $value)
                {
                    $curl->add_http_header($name, $value);
                }
            }
        }
    }

    /**
     * Configure the HTTP accept header sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_accept_header($curl)
    {
        // Set an accept header, if necessary
        if ($this->_send_accept_header)
        {
            $accept = $this->_get_accept_header();
            if ( ! empty($accept))
            {
                $curl->add_http_header('Accept', $accept);
            }
        }
    }

    /**
     * Get the string to be sent via the accept header.
     *
     * @return  string
     */
    protected function _get_accept_header()
    {
        $accept_header;
        $format = strtolower($this->_format);
        switch($format)
        {
            case MMI_API::FORMAT_JAVASCRIPT:
                $accept_header = 'text/javascript';
                break;

            case self::FORMAT_JSONP:
                $accept_header = 'text/javascript';
                break;

            default:
                $accept_header = File::mime_by_ext($format);
                break;
        }
        return $accept_header;
    }

    /**
     * Configure the HTTP authorization header sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_auth_header($curl)
    {
        // Set an auth header, if necessary
        if ($this->_send_auth_header)
        {
            $auth = $this->_get_auth_header();
            if ( ! empty($auth))
            {
                $curl->add_http_header('Authorization', $auth);
            }
        }
    }

    /**
     * Get the string to be sent via the authorization header.
     *
     * @return  string
     */
    protected function _get_auth_header()
    {
        return '';
    }

    /**
     * Decode a JSON response.
     *
     * @param   string  the response string
     * @param   boolean return the decoded results as an associative array?
     * @return  mixed
     */
    protected function _decode_json($input, $decode_as_array = NULL)
    {
        if ( ! isset($decode_as_array))
        {
            $decode_as_array = $this->_decode_as_array;
        }
        return json_decode($input, $decode_as_array);
    }

    /**
     * Decode an XML response.
     * Returns a SimpleXMLElement object or an associative array depending on the value of the _decode_as_array property.
     *
     * @param   string  the response string
     * @param   boolean return the decoded results as an associative array?
     * @return  mixed
     */
    protected function _decode_xml($input, $decode_as_array = NULL)
    {
        if ( ! isset($decode_as_array))
        {
            $decode_as_array = $this->_decode_as_array;
        }
        try
        {
            $response = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($decode_as_array)
            {
                $response = json_decode(json_encode($response), TRUE);
            }
        }
        catch (Exception $e)
        {
            $response = $input;
        }
        return $response;
    }

    /**
     * Ensure a valid MMI_Curl_Response object was received and check the HTTP status code.
     *
     * @param   MMI_Curl_Response   the response to validate
     * @param   string              an error message for invalid responses
     * @return  boolean
     */
    protected function _validate_curl_response($response, $msg)
    {
        $valid = TRUE;
        if ( ! $response instanceof MMI_Curl_Response)
        {
            $valid = FALSE;
            MMI_API::log_error(__METHOD__, __LINE__, 'No cURL response object.');
        }

        $http_status_code = $response->http_status_code();
        if (intval($http_status_code) !== 200)
        {
            $valid = FALSE;
            MMI_API::log_error(__METHOD__, __LINE__, $msg.'.  HTTP status code:' .$http_status_code. '.  Response: '.$response->body());
        }
        return $valid;
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
    protected function _get_set($name, $value = NULL, $verify_method = NULL)
    {
        if ( ! empty($verify_method) AND $verify_method($value))
        {
            $this->$name = $value;
            return $this;
        }
        elseif (isset($value))
        {
            $this->$name = $value;
            return $this;
        }
        return $this->$name;
    }

    /**
     * Create an API instance.
     *
     * @throws  Kohana_Exception
     * @param   string  the service name
     * @return  MMI_API
     */
    public static function factory($driver)
    {
        $class = 'MMI_API_'.ucfirst($driver);
        if ( ! class_exists($class))
        {
            self::log_error(__METHOD__, __LINE__, $class.' class does not exist');
            throw new Kohana_Exception(':class class does not exist in :method.', array
            (
                ':class'    => $class,
                ':method'   => __METHOD__
            ));
        }
        return new $class;
    }

    /**
     * Get the configuration settings.
     *
     * @param   boolean return the configuration as an array?
     * @return  mixed
     */
    public static function get_config($as_array = FALSE)
    {
        (self::$_config === NULL) AND self::$_config = Kohana::config('api');
        $config = self::$_config;
        if ($as_array)
        {
            $config = $config->as_array();
        }
        return $config;
    }

    /**
     * Log a formatted error message.
     *
     * @param   string  the method name
     * @param   string  the line number
     * @param   string  the error message
     * @return  void
     */
    public static function log_error($method, $line, $msg)
    {
        Kohana::$log->add(Kohana::ERROR, '['.$method.' @ line '.$line.'] '.$msg)->write();
    }

    /**
     * Get the last cURL response received.
     *
     * @return  MMI_Curl_Response
     */
    public static function last_response()
    {
        return self::$_last_response;
    }
} // End Kohana_MMI_API