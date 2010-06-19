<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Google API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API_Google extends MMI_API_OAuth
{
    /**
     * Get a request token.
     *
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
            $msg = 'Request token URL not configured for '.$this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, $msg);
            throw new Kohana_Exception($msg);
        }

        // Configure the auth scope parameter
        $scope = Arr::get($auth_config, 'scope');
        if (empty($scope))
        {
            $service = $this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, 'Authorization scope not set for '.$service);
            throw new Kohana_Exception('Authorization scope not set for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }
        $parms['scope'] = $scope;

        // Configure the OAuth callback URL
        if ( ! empty($oauth_callback))
        {
            $parms['oauth_callback'] = $oauth_callback;
        }

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $http_method, $url, $parms);
        $this->_validate_curl_response($response, 'Invalid request token');
        return $this->_extract_token($response);
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

        $name = 'alt';
        if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
        {
            $parms[$name] = $this->_format;
        }
        return parent::_configure_parameters($parms);
    }
} // End Kohana_MMI_API_Google