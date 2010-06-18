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
     * Ensure there is a valid access token.
     * If the token is missing credentials, query the database.
     * If token credentials are found in the database along with an oauth_session_handle, the refresh the access token.
     * Otherwise perform the logic in the parent method.
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
                $model = Model_MMI_OAuth_Tokens::select_by_consumer_key($this->_consumer->key, FALSE);
            }
            if ($model->loaded())
            {
                $this->_token = new OAuthToken($model->token_key, Encrypt::instance()->decode($model->token_secret));
            }
        }

        // Refresh the access token
        if ($model->loaded() AND ! empty($model->oauth_verifier))
        {
            $attributes = isset($model->attributes) ? $model->attributes : array();
            $oauth_session_handle = Arr::get($attributes, 'oauth_session_handle');
            if ( ! empty($oauth_session_handle) AND ! empty($model->token_key) AND ! empty($model->token_secret))
            {
                $token = $this->_refresh_access_token($oauth_session_handle, array
                (
                    'token_key'     => $model->token_key,
                    'token_secret'  => Encrypt::instance()->decode($model->token_secret),
                ));

                // Update the token in the database
                if ($this->_is_token_set($token))
                {
                    if ($this->_update_token($token, $model, TRUE))
                    {
                        return;
                    }
                }
                $this->_delete_token($model);
                $model = NULL;
            }
        }
        parent::_check_access_token($model);
    }

    /**
     * Refresh the access token.
     *
     * @param   string  an authorization session handle
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     * @link    http://developer.yahoo.com/oauth/guide/oauth-refreshaccesstoken.html
     */
    protected function _refresh_access_token($oauth_session_handle = NULL, $auth_config = array())
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
            $msg = 'Access token URL not configured for '.$this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, $msg);
            throw new Kohana_Exception($msg);
        }

        // Configure the request parameters
        $parms = array();
        if ( ! empty($oauth_session_handle))
        {
            $parms['oauth_session_handle'] = $oauth_session_handle;
        }

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $http_method, $url, $parms);
        return $this->_extract_token($response);
    }
} // End Kohana_MMI_API_Delicious