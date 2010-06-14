<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls using OAuth authentication.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class (http://github.com/abraham/twitteroauth).
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @copyright   (c) 2009 Abraham Williams
 * @license     http://www.memakeit.com/license
 * @link        http://github.com/abraham/twitteroauth
 */
abstract class Kohana_MMI_API_OAuth extends MMI_API
{
    // Signature constants
    const SIGN_HMAC_SHA1 = 'HMAC-SHA1';
    const SIGN_PLAINTEXT = 'PLAINTEXT';
    const SIGN_RSA_SHA1 = 'RSA-SHA1';

    /**
     * @var string the access token URL
     **/
    protected $_access_token_url;

    /**
     * @var string the authentication URL
     **/
    protected $_authenticate_url;

    /**
     * @var string the authorization URL
     **/
    protected $_authorize_url;

    /**
     * @var OAuthConsumer the OAuth consumer object
     **/
    protected $_consumer;

    /**
     * @var string the OAuth realm
     **/
    protected $_realm = '';

    /**
     * @var string the request token URL
     **/
    protected $_request_token_url;

    /**
     * @var boolean send an OAuth HTTP header?
     **/
    protected $_send_auth_header = FALSE;

    /**
     * @var OAuthSignatureMethod the signature object used to sign the request
     **/
    protected $_signature_method;

    /**
     * @var OAuthToken the OAuth token object
     **/
    protected $_token;


    protected $_auth_callback_url;



    /**
     * Get or set the authentication URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function authenticate_url($value = NULL)
    {
        return $this->_get_set('_authenticate_url', $value, 'is_string');
    }

    /**
     * Get or set the authorization URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function authorize_url($value = NULL)
    {
        return $this->_get_set('_authorize_url', $value, 'is_string');
    }

    /**
     * Get or set the OAuth realm.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function realm($value = NULL)
    {
        return $this->_get_set('_realm', $value, 'is_string');
    }

    /**
     * Get or set the request token URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function request_token_url($value = NULL)
    {
      return $this->_get_set('_request_token_url', $value, 'is_string');
    }

    /**
     * Include the OAuth vendor files.
     * Create the signature, consumer, and token objects.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        require_once Kohana::find_file('vendor', 'oauth/oauth_required');
        $auth_config = $this->_auth_config;

        // Configure URLs
        $settings = array('access_token_url', 'auth_callback_url', 'authorize_url', 'request_token_url');
        foreach ($settings as $setting)
        {
            $var = '_'.$setting;
            $this->$var = Arr::get($auth_config, $setting);
        }

        // Set the signature method
        $signature_method = Arr::get($auth_config, 'signature_method', MMI_API_OAuth::SIGN_HMAC_SHA1);
        switch($signature_method)
        {
            case self::SIGN_HMAC_SHA1:
                $this->_signature_method = new OAuthSignatureMethod_HMAC_SHA1;
                break;

            case self::SIGN_PLAINTEXT:
                $this->_signature_method = new OAuthSignatureMethod_PLAINTEXT;
                break;

            case self::SIGN_RSA_SHA1:
                // Not supported
                break;
        }

        // Create the consumer and token objects
        $consumer_key = Arr::get($auth_config, 'consumer_key');
        $consumer_secret = Arr::get($auth_config, 'consumer_secret');
        $this->_consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        $token_key = Arr::get($auth_config, 'token_key');
        $token_secret = Arr::get($auth_config, 'token_secret');
        $this->_token = NULL;
        if ( ! empty($token_key) AND ! empty($token_secret))
        {
            $this->_token = new OAuthToken($token_key, $token_secret);
        }

        // Set OAuth realm
        $this->_realm = Arr::get($auth_config, 'realm');
        MMI_Debug::dead($this);
    }

    /**
     * Get a request_token.
     *
     * @param   string  the callback URL
     * @return  OAuthToken
     */
    public function get_request_token($oauth_callback = NULL)
    {
        $parms = array();
        if ( ! empty($oauth_callback))
        {
            $parms['oauth_callback'] = $oauth_callback;
        }
        $response = $this->_request('GET', $this->_request_token_url, $parms);
        $token = OAuthUtil::parse_parameters($response);
        $this->_token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

//    /**
//    * Get the authorization URL.
//    *
//    * @returns a string
//    */
//    function get_authorize_url($token, $sign_in_with_twitter = TRUE)
//    {
//        if (is_array($token))
//        {
//            $token = $token['oauth_token'];
//        }
//        if (empty($sign_in_with_twitter))
//        {
//            return $this->_authorize_url."?oauth_token={$token}";
//        }
//        else
//        {
//            return $this->_authenticate_url."?oauth_token={$token}";
//        }
//    }

    /**
     * Exchange the request token for an access token.
     *
     * @param   string  the verification code
     * @return  OAuthToken
     */
    public function get_access_token($oauth_verifier = NULL)
    {
        $parms = array();
        if ( ! empty($oauth_verifier))
        {
            $parms['oauth_verifier'] = $oauth_verifier;
        }
        $response = $this->_request('GET', $this->_access_token_url, $parms);
        $token = OAuthUtil::parse_parameters($response);
        $this->_token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * Exchange a username and password for an access token.
     *
     * @param   string  the username
     * @param   string  the password
     * @return  OAuthToken
     */
    public function get_xauth_token($username, $password)
    {
        $parms = array();
        $parms['x_auth_username'] = $username;
        $parms['x_auth_password'] = $password;
        $parms['x_auth_mode'] = 'client_auth';
        $response = $this->_request('POST', $this->_access_token_url, $parms);
        $token = OAuthUtil::parse_parameters($response);
        $this->_token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
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

        // Sign the request
        $request = OAuthRequest::from_consumer_and_token($this->_consumer, $this->_token, $method, $url, $parms);
        $request->sign_request($this->_signature_method, $this->_consumer, $this->_token);
        switch ($method)
        {
            case MMI_HTTP::METHOD_GET:
                $url = $request->to_url();
                $parms = NULL;

            default:
                $url = $request->get_normalized_http_url();
                $parms = $request->to_postdata();
        }

        // Create and configure the cURL object
        $curl = new MMI_Curl;
        $this->_configure_curl_options($curl);
        $this->_configure_auth_header($curl, $request);
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
            $url = $this->_configure_url($url);

            // Configure parameters
            $parms = Arr::get($request, 'parms');
            $parms = $this->_configure_parameters($parms);

            // Sign the request
            $request = OAuthRequest::from_consumer_and_token($this->_consumer, $this->_token, $method, $url, $parms);
            $request->sign_request($this->_signature_method, $this->_consumer, $this->_token);
            switch ($method)
            {
                case MMI_HTTP::METHOD_GET:
                    $url = $request->to_url();
                    $parms = NULL;

                default:
                    $url = $request->get_normalized_http_url();
                    $parms = $request->to_postdata();
            }

            // Get the HTTP authorization header
            $auth = $this->_get_auth_header($request);
            if ( ! empty($auth))
            {
                $requests[$id]['http_headers'] = array('Authorization', $auth);
            }

            $requests[$id]['url'] = $url;
            $requests[$id]['parms'] = $parms;

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
     * Configure the HTTP authorization header sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_auth_header($curl)
    {
        if (func_num_args() < 2)
        {
            return;
        }
        $request = func_get_arg(1);

        // Set an auth header, if necessary
        $auth = $this->_get_auth_header($request);
        if ( ! empty($auth))
        {
            $curl->add_http_header('Authorization', $auth);
        }
    }

    /**
     * Get the string to be sent via the authorization header.
     *
     * @return  string
     */
    protected function _get_auth_header()
    {
        if (func_num_args() !== 1)
        {
            return;
        }
        $request = func_get_arg(0);
        $auth_header = $request->to_header($this->_realm);
        $temp = explode(': ', $auth_header);
        return $temp[1];
    }

    /**
     * Create an API instance that implements OAuth.
     *
     * @param   string  the service name
     * @return  MMI_API_OAuth
     */
    public static function factory($driver)
    {
        $class = 'MMI_API_OAuth_'.ucfirst($driver);
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
} // End Kohana_MMI_API_OAuth