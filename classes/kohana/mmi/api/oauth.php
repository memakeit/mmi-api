<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls using OAuth authentication.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class.
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
     * @var string the authorization callback URL
     **/
    protected $_auth_callback_url;

    /**
     * @var string the authentication URL
     **/
    protected $_authenticate_url;

    /**
     * @var string the authorization URL (where the user will be forced to login)
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
     * @var boolean send the OAuth data as part of the request (instead via an HTTP header)
     **/
    protected $_send_auth_as_data = FALSE;

    /**
     * @var OAuthSignatureMethod the signature object (used to sign the request)
     **/
    protected $_signature_method;

    /**
     * @var OAuthToken the OAuth token object
     **/
    protected $_token;

    /**
     * @var string the OAuth version
     **/
    protected $_version = '1.0';

    /**
     * Include the OAuth vendor files.  Load configuration settings.
     * Create the signature, consumer, and token objects.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        require_once Kohana::find_file('vendor', 'oauth/oauth_required');
        $auth_config = $this->_auth_config;

        // Configure the auth URLs
        $settings = array('access_token_url', 'auth_callback_url', 'authenticate_url', 'authorize_url', 'request_token_url');
        foreach ($settings as $setting)
        {
            $var = '_'.$setting;
            $this->$var = Arr::get($auth_config, $setting);
        }

        // Create the consumer, token, and signature method objects
        $this->_consumer = self::_get_consumer($auth_config);
        $this->_token = self::_get_token($auth_config);
        $this->_signature_method = self::_get_signature_method($auth_config);

        // Configure other OAuth settings
        $this->_realm = Arr::get($auth_config, 'realm');
        $this->_send_auth_as_data = Arr::get($auth_config, 'send_auth_as_data', FALSE);

        // Check access token
        $this->_check_access_token();
    }

    /**
     * Get or set the access token URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function access_token_url($value = NULL)
    {
        return $this->_get_set('_access_token_url', $value, 'is_string');
    }

    /**
     * Get or set the authorization callback URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function auth_callback_url($value = NULL)
    {
        return $this->_get_set('_auth_callback_url', $value, 'is_string');
    }

    /**
     * Get or set the authentication URL.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function authenticate_url($value = NULL)
    {
        if (is_string($value))
        {
            $this->_authenticate_url = $value;
            return $this;
        }
        else
        {
            $url = $this->_authenticate_url;
            if ( ! empty($url))
            {
                $token = isset($this->_token) ? $this->_token->key : '';
                return $url.'?oauth_token='.$token;
            }
            return;
        }
    }

    /**
     * Get or set the authorization URL (where the user will be forced to login).
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function authorize_url($value = NULL)
    {
        if (is_string($value))
        {
            $this->_authorize_url = $value;
            return $this;
        }
        else
        {
            $url = $this->_authorize_url;
            if ( ! empty($url))
            {
                $token = isset($this->_token) ? $this->_token->key : '';
                return $url.'?oauth_token='.$token;
            }
            return;
        }
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
     * Get or set whether to send the OAuth data as part of the request (instead via an HTTP header).
     * This method is chainable when setting a value.
     *
     * @param   boolean  the value to set
     * @return  mixed
     */
    public function send_auth_as_data($value = NULL)
    {
      return $this->_get_set('_send_auth_as_data', $value, 'is_bool');
    }

    /**
     * Get or set the OAuth version.
     * This method is chainable when setting a value.
     *
     * @param   string  the value to set
     * @return  mixed
     */
    public function version($value = NULL)
    {
        return $this->_get_set('_version', $value, 'is_string');
    }

    /**
     * Get a request token.
     *
     * @throws  Kohana_Exception
     * @param   string  the callback URL
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     */
    public function get_request_token($oauth_callback = NULL, $auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method and the URL
        $http_method = Arr::get($auth_config, 'request_token_http_method', MMI_HTTP::METHOD_POST);
        $url = $this->_request_token_url;
        if (empty($url))
        {
            $service = $this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, 'Request token URL not configured for '.$service);
            throw new Kohana_Exception('Request token URL not configured for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }

        // Configure the request parameters
        $parms = array();
        if ( ! empty($oauth_callback))
        {
            $parms['oauth_callback'] = $oauth_callback;
        }

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $http_method, $url, $parms);
        return $this->_extract_token($response);
    }

    /**
     * Exchange the request token for an access token.
     *
     * @throws  Kohana_Exception
     * @param   string  the verification code
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     */
    public function get_access_token($oauth_verifier = NULL, $auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method and the URL
        $http_method = Arr::get($auth_config, 'access_token_http_method', MMI_HTTP::METHOD_POST);
        $url = $this->_access_token_url;
        if (empty($url))
        {
            $service = $this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, 'Access token URL not configured for '.$service);
            throw new Kohana_Exception('Access token URL not configured for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }

        // Configure the request parameters
        $parms = array();
        if ( ! empty($oauth_verifier))
        {
            $parms['oauth_verifier'] = $oauth_verifier;
        }

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $http_method, $url, $parms);
        return $this->_extract_token($response);
    }

    /**
     * Exchange a username and password for an access token.
     *
     * @throws  Kohana_Exception
     * @param   string  the username
     * @param   string  the password
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     */
    public function get_xauth_token($username, $password, $auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method and the URL
        $http_method = Arr::get($auth_config, 'xauth_token_http_method', MMI_HTTP::METHOD_POST);
        $url = $this->_access_token_url;
        if (empty($url))
        {
            $service = $this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, 'Access token URL not configured for '.$service);
            throw new Kohana_Exception('Access token URL not configured for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }

        // Configure the request parameters
        $parms = array
        (
            'x_auth_username'   => $username,
            'x_auth_password'   => $password,
            'x_auth_mode'       => 'client_auth',
        );

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $http_method, $url, $parms);
        return $this->_extract_token($response);
    }

    /**
     * Ensure a valid MMI_Curl_Response object was received and check the HTTP status code.
     *
     * @throws  Kohana_Exception
     * @param   MMI_Curl_Response   the response to validate
     * @param   string              an error message for invalid responses
     * @return  void
     */
    protected function _validate_curl_response($response, $msg)
    {
        if ( ! $response instanceof MMI_Curl_Response)
        {
            MMI_Log::log_error(__METHOD__, __LINE__, 'No cURL response.');
            throw new Kohana_Exception('No cURL response in :method.', array
            (
                ':method'   => __METHOD__
            ));
        }

        $http_status_code = $response->http_status_code();
        if (intval($http_status_code) !== 200)
        {
            $body = $response->body();
            MMI_Log::log_error(__METHOD__, __LINE__, $msg.'.  HTTP status code:' .$http_status_code. '.  Response: '.$body);
            throw new Kohana_Exception(':msg in :method.  HTTP status code: :http_status_code.  Response: :body', array
            (
                ':msg'              => $msg,
                ':method'           => __METHOD__,
                ':http_status_code' => $http_status_code,
                ':body'             => $body,
            ));
        }
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
        $url = $request->get_normalized_http_url();

        // Send the OAuth parameters as part of the request?
        if ($this->_send_auth_as_data)
        {
            switch ($method)
            {
                case MMI_HTTP::METHOD_GET:
                    $url = $request->to_url();
                    $parms = NULL;
                    break;

                default:
                    $url = $request->get_normalized_http_url();
                    $parms = $request->to_postdata();
                    break;
            }
        }

        // Create and configure the cURL object
        $curl = new MMI_Curl;
        $this->_configure_curl_options($curl);
        $this->_configure_auth_header($curl, $request);
        $this->_configure_http_headers($curl);
        unset($request);

        // Execute the cURL request
        $method = strtolower($method);
        $response = $curl->$method($url, $parms);
        unset($curl);

        // Format and return the response
        if ($this->_decode AND $response instanceof MMI_Curl_Response)
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
     * @param   array   an associative array containing the request details (URL, request parameters, HTTP headers, and cURL options)
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
            $url = $request->get_normalized_http_url();

            // Send the OAuth parameters as part of the request?
            if ($this->_send_auth_as_data)
            {
                switch ($method)
                {
                    case MMI_HTTP::METHOD_GET:
                        $url = $request->to_url();
                        $parms = NULL;
                        break;

                    default:
                        $url = $request->get_normalized_http_url();
                        $parms = $request->to_postdata();
                        break;
                }
            }

            // Get the HTTP authorization header
            $auth = $this->_get_auth_header($request);
            if ( ! empty($auth))
            {
                $requests[$id]['http_headers'] = array('Authorization' => $auth);
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

        // Format the response
        if ($this->_decode AND is_array($responses) AND count($responses) > 0)
        {
            $method  = '_decode_'.$this->_format;
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
     * Ensure there is a valid access token.
     * If the token is missing credentials, the query the database.
     * If no token credentials are found in the database, make an API call to obtain a new request token.
     *
     * @param   Jelly_Model the model representing the OAuth credentials
     * @return  void
     */
    protected function _check_access_token($model = NULL)
    {
        // Attempt to load the token from the database
        if ( ! $this->_is_token_set())
        {
            if ( ! $model instanceof Jelly_Model)
            {
                $model = Model_MMI_Auth_Tokens::select_by_service_and_consumer_key($this->_service, $this->_consumer->key, FALSE);
            }
            if ($model->loaded())
            {
                $this->_token = new OAuthToken($model->token_key, Encrypt::instance()->decode($model->token_secret));
            }
        }

        // Get a new request token
        if ( ! $this->_is_token_set())
        {
            // Initialize the model
            if ( ! $model->loaded())
            {
                $this->_init_model($model);
            }

            // Get a request token
            $token = $this->get_request_token($this->_auth_callback_url, $this->_auth_config);
            if ($this->_is_token_set($token))
            {
                // Update the token in the database
                $success = $this->_update_token($token, $model, TRUE);

                // Get the x-oauth redirect, if present
                $redirect = Arr::get($token->attributes, 'xoauth_request_auth_url');
                unset($this->_token->attributes);

                // Process the redirect
                if (empty($redirect) OR ! $this->_is_valid_redirect($redirect))
                {
                    $redirect = $this->authenticate_url();
                }
                if (empty($redirect))
                {
                    $redirect = $this->authorize_url();
                }
                if ( ! empty($redirect))
                {
                    Request::$instance->redirect($redirect);
                }
            }
        }
    }

    /**
     * ensure the redirect URL contains an oauth_token parameter
     *
     * @param   string  the redirect URL
     * @return  boolean
     */
    protected function _is_valid_redirect($url)
    {
        if (empty($url))
        {
            return FALSE;
        }
        parse_str(parse_url($url, PHP_URL_QUERY), $parms);
        $oauth_token = Arr::get($parms, 'oauth_token');
        return ( ! empty($oauth_token));
    }

    /**
     * Update the OAuth token class property and update the OAuth credentials in the database.
     *
     * @param   OAuthToken  the OAuth token object
     * @param   Jelly_Model the model representing the OAuth credentials
     * @param   boolean     save the extended token attributes in the database?
     * @return  boolean
     */
    protected function _update_token($token, $model, $save_attributes = FALSE)
    {
        $success = FALSE;
        if ($this->_is_token_set($token))
        {
            $this->_token = $token;
            $model->token_key = $token->key;
            $model->token_secret = Encrypt::instance()->encode($token->secret);
            if ($save_attributes AND ! empty($token->attributes))
            {
                $model->attributes = $token->attributes;
            }
            $success = MMI_Jelly::save($model, $errors);
            if ( ! $success AND $this->_debug)
            {
                MMI_Debug::dead($errors);
            }
        }
        return $success;
    }

    /**
     * Delete the OAuth credentials from the database.
     *
     * @param   Jelly_Model the model representing the OAuth credentials
     * @return  boolean
     */
    protected function _delete_token($model)
    {
        $success = FALSE;
        if ($model instanceof Jelly_Model)
        {
            $model->delete();
            $success = TRUE;
        }
        return $success;
    }

    /**
     * Ensure the token has a key and secret.
     *
     * @return  boolean
     */
    protected function _is_token_set($token = NULL)
    {
        if ( ! ($token instanceof OAuthToken))
        {
            $token = $this->_token;
        }
        return ( ! empty($token->key) AND ! empty($token->secret));
    }

    /**
     * Initialize the OAuth token data model.
     *
     * @return  boolean
     */
    protected function _init_model($model)
    {
        $consumer = $this->_consumer;
        $model->service = $this->_service;
        $model->consumer_key = $consumer->key;
        $model->consumer_secret = Encrypt::instance()->encode($consumer->secret);
        $model->oauth_version = $this->_version;
        $success = MMI_Jelly::save($model, $errors);
        if ( ! $success AND $this->_debug)
        {
            MMI_Debug::dead($errors);
        }
    }

    /**
     * Configure the HTTP authorization header sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_auth_header($curl)
    {
        if ($this->_send_auth_as_data OR func_num_args() < 2)
        {
            return;
        }

        // Set an auth header, if necessary
        $request = func_get_arg(1);
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
        if ($this->_send_auth_as_data OR func_num_args() !== 1)
        {
            return;
        }

        $request = func_get_arg(0);
        $auth_header = $request->to_header($this->_realm);
        $temp = explode(': ', $auth_header);
        return $temp[1];
    }

    /**
     * Perform an isolated cURL request.
     *
     * @param   array   an associative array of auth settings
     * @param   string  the HTTP request method
     * @param   string  the URL
     * @param   array   an associative array of request parameters
     * @return  MMI_Curl_Response
     */
    protected function _isolated_request($auth_config, $method, $url, $parms = array())
    {
        // Create the consumer, token, and signature method objects
        $consumer = self::_get_consumer($auth_config);
        $token = self::_get_token($auth_config);
        $signature_method = self::_get_signature_method($auth_config);

        // Prepare and sign the OAuth request
        $request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parms);
        $request->sign_request($signature_method, $consumer, $token);
        if ($method === MMI_HTTP::METHOD_GET)
        {
            $url = $request->to_url();
        }
        else
        {
            $url = $request->get_normalized_http_url();
            $parms = $request->to_postdata();
        }
        unset($consumer, $token, $signature_method, $request);

        // Execute the cURL request
        $curl = new MMI_Curl;
        $method = strtolower($method);
        $response = $curl->$method($url, $parms);
        unset($curl);

        return $response;
    }

    /**
     * Extract token data from a MMI_Curl_Response object and create a OAuthToken object.
     *
     * @param   MMI_Curl_Response   the response object
     * @return  OAuthToken
     */
    protected function _extract_token($response)
    {
        if ( ! $response instanceof MMI_Curl_Response)
        {
            return NULL;
        }

        $token = NULL;
        if (intval($response->http_status_code()) === 200)
        {
            $body = $response->body();
            if ( ! empty($body))
            {
                $parms = OAuthUtil::parse_parameters($body);
                if ( ! empty($parms['oauth_token']) AND ! empty($parms['oauth_token_secret']))
                {
                    $token = new OAuthToken($parms['oauth_token'], $parms['oauth_token_secret']);
                    unset($parms['oauth_token'], $parms['oauth_token_secret']);
                    $token->attributes = $parms;
                }
            }
        }
        return $token;
    }

    /**
     * Create an OAuth consumer object using the auth config settings.
     *
     * @param   array   an associative array of auth settings
     * @return  OAuthConsumer
     */
    protected static function _get_consumer($auth_config)
    {
        $consumer_key = Arr::get($auth_config, 'consumer_key');
        $consumer_secret = Arr::get($auth_config, 'consumer_secret');
        $auth_callback_url = Arr::get($auth_config, 'auth_callback_url');
        return new OAuthConsumer($consumer_key, $consumer_secret, $auth_callback_url);
    }

    /**
     * Create an OAuth token object using the auth config settings.
     *
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     */
    protected static function _get_token($auth_config)
    {
        $token_key = Arr::get($auth_config, 'token_key');
        $token_secret = Arr::get($auth_config, 'token_secret');
        $token = NULL;
        if ( ! empty($token_key))
        {
            $token = new OAuthToken($token_key, $token_secret);
        }
        return $token;
    }

    /**
     * Create an OAuth signature object using the auth config settings.
     *
     * @param   array   an associative array of auth settings
     * @return  OAuthSignatureMethod
     */
    protected static function _get_signature_method($auth_config)
    {
        $type = Arr::get($auth_config, 'signature_method', MMI_API_OAuth::SIGN_HMAC_SHA1);
        $signature_method = NULL;
        switch($type)
        {
            case self::SIGN_HMAC_SHA1:
                $signature_method = new OAuthSignatureMethod_HMAC_SHA1;
                break;

            case self::SIGN_PLAINTEXT:
                $signature_method = new OAuthSignatureMethod_PLAINTEXT;
                break;

            case self::SIGN_RSA_SHA1:
                // Not supported
                break;
        }
        return $signature_method;
    }
} // End Kohana_MMI_API_OAuth