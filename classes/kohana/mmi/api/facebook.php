<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Facebook API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://developers.facebook.com/docs/api
 */
class Kohana_MMI_API_Facebook extends MMI_API_OAuth
{
    // Service name
    protected $_service = MMI_API::SERVICE_FACEBOOK;

    // API settings
    protected $_api_url = 'https://graph.facebook.com/';

    // OAuth settings
    protected $_request_token_url = NULL;
    protected $_access_token_url = 'https://graph.facebook.com/oauth/access_token';
    protected $_access_token_http_method = MMI_HTTP::METHOD_GET;
    protected $_authorize_url = 'https://graph.facebook.com/oauth/authorize';
    protected $_version = '2.0';

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
        return NULL;
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
       return NULL;
    }

    /**
     * After obtaining a new request token, return the authorization URL.
     *
     * @throws  Kohana_Exception
     * @param   OAuthToken  the OAuth token object
     * @return  string
     */
    public function get_auth_redirect($token = NULL)
    {
        $redirect = $this->authenticate_url();
        if (empty($redirect))
        {
            $redirect = $this->authorize_url();
        }
        $auth_config = $this->_auth_config;
        $parms = array
        (
            'client_id'     => Arr::get($auth_config, 'application_id'),
            'display'       => 'page',
            'redirect_uri'  => Arr::get($auth_config, 'auth_callback_url'),
            'scope'         => 'offline_access,publish_stream',
            'type'          => 'user_agent',
        );
        return $redirect.'?'.http_build_query($parms);
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

        // Set the access key
        $access_token = NULL;
        if ($this->is_valid_token(NULL, TRUE))
        {
            $access_token = $this->_token->key;
            $name = 'access_token';
            if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
            {
                $parms[$name] = $access_token;
            }
        }
        return $parms;
    }
} // End Kohana_MMI_API_Facebook