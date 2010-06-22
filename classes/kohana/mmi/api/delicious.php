<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Delicious API calls.
 * Response formats: XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://delicious.com/help/api
 */
class Kohana_MMI_API_Delicious extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_DELICIOUS;

    /**
     * Ensure the request token has been verified and an access token received.
     *
     * @throws  Kohana_Exception
     * @return  void
     */
    protected function _check_access_token()
    {
        parent::_check_access_token();
        $token = $this->_token;
        if (isset($token->attributes) AND is_array($token->attributes))
        {
            $oauth_session_handle = Arr::get($token->attributes, 'oauth_session_handle');
            if ( ! empty($oauth_session_handle))
            {
                $token = $this->_refresh_access_token($oauth_session_handle, array
                (
                    'token_key'     => $token->key,
                    'token_secret'  => $token->secret,
                ));
                if ($this->is_token_valid($token))
                {
                    // Update the token
                    $success = $this->_update_token($token);
                }
            }
        }
    }

    /**
     * Refresh the access token.
     *
     * @throws  Kohana_Exception
     * @param   string  an authorization session handle
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     * @link    http://developer.yahoo.com/oauth/guide/oauth-refreshaccesstoken.html
     */
    protected function _refresh_access_token($oauth_session_handle, $auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method and the URL
        $http_method = MMI_HTTP::METHOD_POST;
        $url = $this->_access_token_url;
        if (empty($url))
        {
            $service = $this->_service;
            $this->_log_error(__METHOD__, __LINE__, 'Access token URL not configured for '.$service);
            throw new Kohana_Exception('Access token URL not configured for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }

        // Configure the request parameters
        $parms['oauth_session_handle'] = $oauth_session_handle;

        // Make the request and extract the token
        $response = $this->_auth_request($auth_config, $http_method, $url, $parms);
        $this->_validate_curl_response($response, 'Invalid refresh token');
        return $this->_extract_token($response);
    }
} // End Kohana_MMI_API_Delicious