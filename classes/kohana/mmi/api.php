<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls to 3rd-party services.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class (http://github.com/abraham/twitteroauth).
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API
{
    // Output format constants
    const FORMAT_ATOM = 'atom';
    const FORMAT_JAVASCRIPT = 'javascript';
    const FORMAT_JSON = 'json';
    const FORMAT_PHP = 'php';
    const FORMAT_RSS = 'rss';
    const FORMAT_XML = 'xml';
    const FORMAT_YAML = 'yaml';

    // Read write constants
    const READ_ONLY = 'ro';
    const READ_WRITE = 'rw';

    // Service name constants
    const SERVICE_DELICIOUS = 'delicious';
    const SERVICE_DIGG = 'digg';
    const SERVICE_FACEBOOK = 'facebook';
    const SERVICE_FLICKR = 'flickr';
    const SERVICE_GITHUB = 'github';
    const SERVICE_LASTFM = 'lastfm';
    const SERVICE_READERNAUT = 'readernaut';
    const SERVICE_TWITTER = 'twitter';

    /**
     * @var Kohana_Config API settings
     */
    protected static $_config;

    /**
     * @var MMI_Curl_Response the last cURL response received
     **/
    protected static $_last_response;

    /**
     * @var mixed the API URL (usually a string but an array can be used to specify a read-only URL and a read-write URL)
     **/
    protected $_api_url = '';

    /**
     * @var array an associative array of authorization settings
     */
    protected $_auth_config;

    /**
     * @var integer the cURL connection timeout
     **/
    protected $_connect_timeout = 10;

    /**
     * @var boolean turn debugging on?
     **/
    protected $_debug;

    /**
     * @var boolean decode results?
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
     * @var array an associative array of the HTTP headers returned by cURL
     **/
    protected $_http_headers;

    /**
     * @var integer the last HTTP status code returned by cURL
     **/
    protected $_http_status;

    /**
     * @var boolean the api mode is read-only?
     **/
    protected $_read_only = TRUE;

    /**
     * @var string the service name
     */
    protected $_service = '?';

    /**
     * @var array san associative array of service-specific settings
     */
    protected $_service_config;

    /**
     * @var boolean sign read-only requests?
     **/
    protected $_sign_read_only = FALSE;

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
     * Configure debugging (using the Request instance).
     * Load the configuration settings.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->_debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
        $config = self::get_config(TRUE);
        $service_config = Arr::get($config, $this->_service, array());
        $this->_auth_config = Arr::get($service_config, 'auth', array());
        $this->_service_config = $service_config;

        $api_global = Arr::get($config, 'api', array());
        $api_service = Arr::get($service_config, 'api', array());
        $settings = array
        (
            'api_url',
            'connect_timeout',
            'decode',
            'decode_as_array',
            'format',
            'read_only',
            'sign_read_only',
            'ssl_verifypeer',
            'timeout',
            'useragent',
        );
        foreach ($settings as $name)
        {
            $value = Arr::get($api_service, $name, Arr::get($api_global, $name));
            $this->$name($value);
        }
    }

    /**
     * Get or set the API URL.
     * The API URL is usually a string, but an array can be used to specify a read-only URL and a read-write URL.
     * This method is chainable when setting a value.
     *
     * @param   mixed  the API URL value(s)
     * @return  mixed
     */
    public function api_url($value = NULL)
    {
        return $this->_get_set('_api_url', $value);
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
     * Get or set whether to decode results.
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
     * Get an associative array of the HTTP headers returned by cURL.
     *
     * @return  array
     */
    public function http_headers()
    {
        return $this->_get_set('_http_headers');
    }

    /**
     * Get the last HTTP status code returned by cURL.
     *
     * @return  integer
     */
    public function http_status()
    {
        return intval($this->_http_status);
    }

    /**
     * Get or set whether the api mode is read-only.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function read_only($value = NULL)
    {
        return $this->_get_set('_read_only', $value, 'is_bool');
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
     * Get or set whether to sign read-only requests.
     * This method is chainable when setting a value.
     *
     * @param   boolean the value to set
     * @return  mixed
     */
    public function sign_read_only($value = NULL)
    {
        return $this->_get_set('_sign_read_only', $value, 'is_bool');
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
     * Get or set the useragent sent by cURL requests.
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
     * @return  string
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
     * @return  string
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
     * @return  string
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
     * @return  string
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
     * @return  string
     */
    public function put($url, $parms = array())
    {
        return $this->_request($url, $parms, MMI_HTTP::METHOD_PUT);
    }

    /**
     * Make multiple DELETE requests.
     * See the mget method for the format of the requests data.
     *
     * @see     mget
     * @param   array   the request details (URL and parameters)
     * @return  array
     */
    public function mdelete($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_DELETE);
    }

    /**
     * Make multiple HEAD requests.
     * See the mget method for the format of the requests data.
     *
     * @see     mget
     * @param   array   the request details (URL and parameters)
     * @return  array
     */
    public function mhead($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_HEAD);
    }

    /**
     * Make multiple GET requests.
     * Each request is an associative array containing a URL (key = 'url') and optional request parameters (key = 'parms').
     * Each request can be associated with a key (recommended for easier extraction of results):
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
     * @param   array   the request details (URL and parameters)
     * @return  array
     */
    public function mget($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_GET);
    }

    /**
     * Make multiple POST requests.
     * See the mget method for the format of the requests data.
     *
     * @see     mget
     * @param   array   the request details (URL and parameters)
     * @return  array
     */
    public function mpost($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_POST);
    }

    /**
     * Make multiple PUT requests.
     * See the mget method for the format of the requests data.
     *
     * @see     mget
     * @param   array   the request details (URL and parameters)
     * @return  array
     */
    public function mput($requests)
    {
        return $this->_mrequest($requests, MMI_HTTP::METHOD_PUT);
    }

    /**
     * Make multiple requests.
     *
     * Each request is an associative array containing an HTTP method (key = 'method'), URL (key = 'url'), and optional request parameters (key = 'parms').
     * Each request can be associated with a key (recommended for easier extraction of results):
     *      $requests = array
     *      (
     *          'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
     *          'shadowhand' => array('method' => 'POST', 'url' => 'user/show/shadowhand'),
     *      );
     *
     * or the keys can be ommited:
     *      $requests = array
     *      (
     *          array('method' => 'GET', 'url' => 'user/show/memakeit'),
     *          array('method' => 'POST', 'url' => 'user/show/shadowhand'),
     *      );
     *
     * @param   array   the request details (HTTP method, URL, and parameters)
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
     * @return  mixed
     */
    protected function _request($url, $parms, $method = MMI_HTTP::METHOD_GET)
    {
        // Configure URL
        $url = $this->_configure_url($url);

        // Configure parameters
        $parms = $this->_configure_parameters($parms);

        // Create and configure the cURL object
        $curl = new MMI_Curl;
        $this->_configure_curl_options($curl);
        $this->_configure_http_headers($curl);

        // Execute the cURL request
        $method = strtolower($method);
        $response = $curl->$method($url, $parms);
        unset($curl);

        // Format and return the response
        if ( ! empty($response) AND $this->_decode)
        {
            $method  = '_decode_'.$this->_format;
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
     * @param   array   an associative array containing the request details (URL and parameters)
     * @param   string  the HTTP method
     * @return  array
     */
    protected function _mrequest($requests, $method = MMI_HTTP::METHOD_GET)
    {
        foreach ($requests as $id => $request)
        {
            // Configure URLs
            $url = Arr::get($request, 'url');
            $requests[$id]['url'] = $this->_configure_url($url);;

            // Configure parameters
            $parms = Arr::get($request, 'parms');
            $requests[$id]['parms'] = $this->_configure_parameters($parms);
        }

        // Create and configure the cURL object
        $curl = new MMI_Curl;
        $this->_configure_curl_options($curl);
        $this->_configure_http_headers($curl);

        // Execute the cURL request
        $method = 'm'.strtolower($method);
        $responses = $curl->$method($requests);
        unset($curl);

        // Format and return the response
        if ( ! empty($responses) AND is_array($responses) AND count($responses) > 0)
        {
            if ($this->_decode)
            {
                foreach ($responses as $id => $response)
                {
                    $method  = '_decode_'.$this->_format;
                    if (method_exists($this, $method))
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
     * If both a read-only and a read-write API URL are specified, the correct one is implemented.
     *
     * @param   string  the request URL
     * @return  string
     */
    protected function _configure_url($url)
    {
        if (strrpos($url, 'https://') !== 0 AND strrpos($url, 'http://') !== 0)
        {
            $path = $url;
            $url = $this->_api_url;
            if (is_array($url) AND count($url) === 1)
            {
                $url = end($url);
            }
            elseif (is_array($url) AND count($url) > 1)
            {
                $key = ($this->_read_only) ? self::READ_ONLY : self::READ_WRITE;
                $url = Arr::get($url, $key);
            }
            $url = $this->_build_url($url, $path);
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
     * Configure the cURL options as specified in the configuration file.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_curl_options($curl)
    {
        $curl->add_curl_option(CURLOPT_CONNECTTIMEOUT, $this->_connect_timeout);
        $curl->add_curl_option(CURLOPT_TIMEOUT, $this->_timeout);
        $curl->add_curl_option(CURLOPT_USERAGENT, $this->_useragent);
        $curl->add_curl_option(CURLOPT_SSL_VERIFYPEER, $this->_ssl_verifypeer);

        // Customize cURL options as specified in the configuration file
        $custom = Arr::path($this->_service_config, 'custom.curl_options', array());
        if (is_array($custom) AND count($custom) > 0)
        {
            // Process defaults
            $defaults = Arr::get($custom, 'defaults', FALSE);
            if (is_array($defaults) AND count($defaults) > 0)
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
     * Configure the HTTP headers sent via cURL as specified in the configuration file.
     * If specified, an authorization header is also added.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_http_headers($curl)
    {
        // Customize HTTP headers as specified in the configuration file
        $custom = Arr::path($this->_service_config, 'custom.http_headers', array());
        if (is_array($custom) AND count($custom) > 0)
        {
            // Process defaults
            $defaults = Arr::get($custom, 'defaults', FALSE);
            if (is_array($defaults) AND count($defaults) > 0)
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

        // Set an auth header, if necessary
        $auth_config = $this->_auth_config;
        if (is_array($auth_config) AND count($auth_config) > 0)
        {
            if ( ! $this->_read_only OR ($this->_read_only AND $this->_sign_read_only))
            {
                $auth = $this->_get_auth_string();
                if ( ! empty($auth))
                {
                    $curl->add_http_header('Authorization', $auth);
                }
            }
        }
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

        // Configure authentication parameters (that are passed as request parameters instead of HTTP authorization headers)
        return $this->_configure_auth_parms($parms);
    }

    /**
     * Configure authentication parameters that are passed as request parameters instead of HTTP authorization headers.
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_auth_parms($parms)
    {
        return $parms;
    }

    /**
     * Decode a JSON response.
     *
     * @param   string  the response string
     * @return  mixed
     */
    protected function _decode_json($input)
    {
        return json_decode($input, $this->_decode_as_array);
    }

    /**
     * Decode an XML response.
     * Returns a SimpleXMLElement object or an associative array depending on the value of the _decode_as_array property.
     *
     * @param   string  the response string
     * @return  mixed
     */
    protected function _decode_xml($input)
    {
        $response = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($this->_decode_as_array)
        {
            $response = json_decode(json_encode($response), TRUE);
        }
        return $response;
    }

    /**
     * Get the string to be sent in the authorization header.
     *
     * @return  string
     */
    protected function _get_auth_string()
    {
        return '';
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
        elseif ( ! empty($value))
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
     * Get the last cURL response received.
     *
     * @return  MMI_Curl_Response
     */
    public static function last_response()
    {
        return self::$_last_response;
    }
} // End Kohana_MMI_API