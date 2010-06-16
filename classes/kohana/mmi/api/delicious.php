<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Delicious API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_API_Delicious extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_DELICIOUS;

    /**
     * Ensure there is a valid access token.
     * If the token is missing credentials, the database is queried.
     * If token credentials are found in the database along with an oauth_session_handle, the access token is refreshed.
     * If token credentials are NOT found in the database, an API call is made to obtain a new request token.
     *
     * @return  void
     */
    protected function _check_access_token()
    {
        $token = NULL;
        if ( ! $this->_is_token_set())
        {
            // Load existing data from the database
            $model = Model_MMI_OAuth_Tokens::select_by_consumer_key($this->_consumer->key, FALSE);
            if ($model->loaded())
            {
                // Refresh the access token
                $attributes = isset($model->attributes) ? $model->attributes : array();
                $oauth_session_handle = Arr::get($attributes, 'oauth_session_handle');
                if ( ! empty($oauth_session_handle) AND ! empty($model->token_key) AND ! empty($model->token_secret))
                {
                    $auth_config = array
                    (
                        'token_key'     => $model->token_key,
                        'token_secret'  => Encrypt::instance()->decode($model->token_secret),
                    );
                    $token = $this->_refresh_access_token($oauth_session_handle, $auth_config);

                    if ($this->_is_token_set($token))
                    {
                        $model->token_key = $token->key;
                        $model->token_secret = Encrypt::instance()->encode($token->secret);
                        if ( ! empty($token->attributes))
                        {
                            $model->attributes = $token->attributes;
                        }
                        unset($this->_token->attributes);
                        $success = MMI_Jelly::save($model, $errors);
                        if ( ! $success AND $this->_debug)
                        {
                            MMI_Debug::dead($errors);
                        }
                        $this->_token = $token;
                    }
                }
                else
                {
                    $this->_token = new OAuthToken($model->token_key, Encrypt::instance()->decode($model->token_secret));
                }
            }

            if ( ! $this->_is_token_set())
            {
                // Initialize the model
                if ( ! $model->loaded())
                {
                    $this->_init_model($model);
                }

                // Get a request token
                $token = self::get_request_token($this->_auth_callback_url, $this->_auth_config);
                if ($this->_is_token_set($token))
                {
                    $this->_token = $token;
                    $model->token_key = $token->key;
                    $model->token_secret = Encrypt::instance()->encode($token->secret);
                    $success = MMI_Jelly::save($model, $errors);

                    // Get the redirect URL
                    $xoauth_request_auth_url = Arr::get($token->attributes, 'xoauth_request_auth_url');
                    unset($this->_token->attributes);
                    if ($success AND ! empty($xoauth_request_auth_url))
                    {
                        // Redirect to authorization URL
                        Request::$instance->redirect($xoauth_request_auth_url);
                    }
                    elseif ($this->_debug)
                    {
                        MMI_Debug::dead($errors);
                    }
                }
            }
        }
    }

    /**
     * Refresh the access token.
     *
     * @param   string  an authorization session handle
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     */
    protected function _refresh_access_token($oauth_session_handle = NULL, $auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the request parameters
        $parms = array();
        if ( ! empty($oauth_session_handle))
        {
            $parms['oauth_session_handle'] = $oauth_session_handle;
        }

        // Configure the HTTP method and URL
        $method = MMI_HTTP::METHOD_GET;
        $url = $this->_access_token_url;
        if (empty($url))
        {
            $msg = 'Access token URL not configured for '.$this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, $msg);
            throw new Kohana_Exception($msg);
        }

        // Make the request and extract the token
        $response = $this->_isolated_request($auth_config, $method, $url, $parms);
        return $this->_extract_token($response);
    }
} // End Kohana_MMI_API_Delicious