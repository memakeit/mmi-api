<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Object representation of a cURL response.
 * This work is based on Ryan Parman's requestcore library.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @copyright   (c) 2006-2010 Ryan Parman, Foleeo Inc., and contributors. All rights reserved.
 * @license     http://www.memakeit.com/license
 * @link        http://github.com/skyzyx/requestcore
 */
class Kohana_MMI_Curl_Response
{
    /**
     * @var mixed the response body returned by cURL
     **/
    protected $_body;

    /**
     * @var array an associative array of the options returned by cURL
     **/
    protected $_curl_info;

    /**
     * @var boolean turn debugging on?
     **/
    protected $_debug;

    /**
     * @var string the error message returned by cURL
     **/
    protected $_error_msg;

    /**
     * @var integer the error number returned by cURL
     **/
    protected $_error_num;

    /**
     * @var array the HTTP response headers returned by cURL
     **/
    protected $_http_headers;

    /**
     * @var integer the HTTP status code returned by cURL
     **/
    protected $_http_status_code;

    /**
     * @var array an associative array containing details of the last cURL request
     **/
    protected $_request;

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
     * Get or set the response body returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   mixed   the response body
     * @return  mixed
     */
    public function body($value = NULL)
    {
        return $this->_get_set('_body', $value);
    }

    /**
     * Get or set an associative array of the options returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   array   an associative array of cURL options
     * @return  mixed
     */
    public function curl_info($value = NULL)
    {
        return $this->_get_set('_curl_info', $value, 'is_array');
    }

    /**
     * Get or set the error message returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   string  the error message
     * @return  mixed
     */
    public function error_msg($value = NULL)
    {
        return $this->_get_set('_error_msg', $value, 'is_string');
    }

    /**
     * Get or set the error number returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   integer the error number
     * @return  mixed
     */
    public function error_num($value = NULL)
    {
        return $this->_get_set('_error_num', $value, 'is_int');
    }

    /**
     * Get or set the HTTP headers returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   array   an associative array of HTTP headers
     * @return  mixed
     */
    public function http_headers($value = NULL)
    {
        return $this->_get_set('_http_headers', $value, 'is_array');
    }

    /**
     * Get or set the HTTP status code returned by cURL.
     * This method is chainable when setting a value.
     *
     * @param   integer the HTTP status code
     * @return  mixed
     */
    public function http_status_code($value = NULL)
    {
        return $this->_get_set('_http_status_code', $value, 'is_int');
    }

    /**
     * Get or set an associative array containing details of the last cURL request
     * This method is chainable when setting a value.
     *
     * @param   array   an associative array of request details
     * @return  mixed
     */
    public function request($value = NULL)
    {
        return $this->_get_set('_request', $value, 'is_array');
    }

    /**
     * Create a cURL response instance.
     *
     * @return  MMI_Curl_Response
     */
    public static function factory()
    {
        return new MMI_Curl_Response;
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
} // End Kohana_MMI_Curl_Response