<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Digg API calls.
 * Response formats: Javascript, JSON, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://digg.com/api/docs/1.0/groups/
 */
class Kohana_MMI_API_Digg extends MMI_API_OAuth
{
    // Service name
    protected $_service = MMI_API::SERVICE_DIGG;

    // API settings
    protected $_api_url = 'http://services.digg.com/1.0/endpoint';

    // OAuth settings
    protected $_request_token_url = 'http://services.digg.com/1.0/endpoint?method=oauth.getRequestToken';
    protected $_access_token_url = 'http://services.digg.com/1.0/endpoint?method=oauth.getAccessToken';
    protected $_authenticate_url = 'http://digg.com/oauth/authenticate';
    protected $_authorize_url = 'http://digg.com/oauth/authorize';

    /**
     * Verify the access token.
     *
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     * @link    http://digg.com/api/docs/1.0/detail/oauth.verify
     */
    public function verify_access_token()
    {
        // Configure the auth settings
        $auth_config = array();
        if ($this->is_valid_token(NULL, TRUE))
        {
            $token = $this->_token;
            $auth_config = array
            (
                'token_key'     => $token->key,
                'token_secret'  => $token->secret,
            );
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method, URL, and request parameters
        $http_method = MMI_HTTP::METHOD_POST;
        $url = 'http://services.digg.com/1.0/endpoint?';
        $parms = array('method' => 'oauth.verify');

        // Verify the request token
        $verified = 0;
        $response = $this->_auth_request($auth_config, $http_method, $url, $parms);
        if ($response instanceof MMI_Curl_Response)
        {
            $http_status_code = $response->http_status_code();
            if (intval($http_status_code) === 200)
            {
                $data = $this->_decode_xml($response->body(), TRUE);
                if (is_array($data))
                {
                    $verified = intval(Arr::path($data, '@attributes.verified', 0));
                }
            }
        }
        unset($response);
        return ($verified === 1);
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
        if ( ! is_array($parms))
        {
            $parms = array();
        }

        if ( ! $this->_send_accept_header)
        {
            $name = 'type';
            if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
            {
                $parms[$name] = $this->_format;
            }
        }
        return parent::_configure_parameters($parms);
    }
} // End Kohana_MMI_API_Digg