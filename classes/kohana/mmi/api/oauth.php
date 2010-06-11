<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Perform API calls using OAuth authentication.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class (http://github.com/abraham/twitteroauth).
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://github.com/abraham/twitteroauth
 */
abstract class Kohana_MMI_API_OAuth extends MMI_API
{
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
     * Get or set whether to send an OAUTH HTTP header.
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
     * Include the OAuth vendor files.
     * Create the signature, consumer, and token objects.
     *
     * @return  void
     */
    public function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL, $signature_type = 'HMAC_SHA1')
    {
        parent::__construct();
        require_once Kohana::find_file('vendor', 'oauth/oauth_required');

        // Set the signature method
        if (strcasecmp($signature_type, 'HMAC_SHA1') === 0)
        {
            $this->_signature_method = new OAuthSignatureMethod_HMAC_SHA1;
        }
        else
        {
            $this->_signature_method = new OAuthSignatureMethod_PLAINTEXT;
        }

        // Create the consumer and token objects
        $this->_consumer = new OAuthConsumer($consumer_key, $consumer_secret);
        $this->_token = NULL;
        if ( ! empty($oauth_token) AND ! empty($oauth_token_secret))
        {
            $this->_token = new OAuthToken($oauth_token, $oauth_token_secret);
        }
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
     * Execute the API call.
     *
     * @param   string  the HTTP method
     * @param   string  the URL
     * @param   array   the query string parameters
     * @return  mixed
     */
    protected function _request($method, $url, $parms)
    {
        if (strrpos($url, 'https://') !== 0 AND strrpos($url, 'http://') !== 0)
        {
            $url = $this->_api_url.$url;
        }

        // Sign the request
        $request = OAuthRequest::from_consumer_and_token($this->_consumer, $this->_token, $method, $url, $parms);
        $request->sign_request($this->_signature_method, $this->_consumer, $this->_token);

        // Generate the HTTP authentication header, if necessary
        $auth_header = array();
        if ($this->_send_auth_header)
        {
            $auth_header = $request->to_header($this->_realm);
            $temp = explode(': ', $auth_header);
            $auth_header = array($temp[0] => $temp[1]);
        }

        // Make the API call
        $response;
        switch ($method)
        {
            case 'GET':
                MMI_Debug::dump($request->to_url());
                $response = $this->_http($request->to_url(), 'GET', NULL, $auth_header);

            default:
                $response = $this->_http($request->get_normalized_http_url(), $method, $request->to_postdata(), $auth_header);
        }

        // Format and return the results
        if ($this->_format === self::FORMAT_JSON AND $this->_decode_json)
        {
            $response = json_decode($response, TRUE);
        }
        return $response;
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