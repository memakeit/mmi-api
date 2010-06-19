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
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_DIGG;

    /**
     * Ensure there is a valid access token.
     * If the token is missing credentials, query the database.
     * If credentials are loaded from the database, verify the token.
     * If the token is invalid, delete it from the database.
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
                $model = Model_MMI_Auth_Tokens::select_by_service_and_consumer_key($this->_service, $this->_consumer->key, FALSE);
            }
            if ($model->loaded())
            {
                $this->_token = new OAuthToken($model->token_key, Encrypt::instance()->decode($model->token_secret));
            }
        }

        // Verify the token
        if ($model->loaded() AND ! empty($model->oauth_verifier))
        {
            $token_key = $model->token_key;
            $token_secret = Encrypt::instance()->decode($model->token_secret);
            $verified = $this->_verify_access_token(array
            (
                'token_key'     => $token_key,
                'token_secret'  => $token_secret,
            ));
            if ($verified)
            {
                $this->_token = new OAuthToken($token_key, $token_secret);
                return;
            }
            $this->_delete_token($model);
            $model = NULL;
        }
        parent::_check_access_token($model);
    }

    /**
     * Verify the access token.
     *
     * @param   array   an associative array of auth settings
     * @return  OAuthToken
     * @link    http://digg.com/api/docs/1.0/detail/oauth.verify
     */
    protected function _verify_access_token($auth_config = array())
    {
        // Configure the auth settings
        if ( ! is_array($auth_config))
        {
            $auth_config = array();
        }
        $auth_config = Arr::merge($this->_auth_config, $auth_config);

        // Configure the HTTP method and the URL
        $http_method = MMI_HTTP::METHOD_POST;
        $url = 'http://services.digg.com/1.0/endpoint?method=oauth.verify';

        // Verify the request token
        $verified = 0;
        $response = $this->_isolated_request($auth_config, $http_method, $url);
        if ($response instanceof MMI_Curl_Response)
        {
            $data = $this->_decode_xml($response->body(), TRUE);
            if (is_array($data))
            {
                $verified = intval(Arr::path($data, '@attributes.verified', 0));
            }
        }
        return ($verified === 1);
    }

    /**
     * Get the string to be sent via the accept header.
     *
     * @return  string
     */
    protected function _get_accept_header()
    {
        if ($this->_format === MMI_API::FORMAT_JAVASCRIPT)
        {
            return 'text/javascript';
        }
        return parent::_get_accept_header();
    }
} // End Kohana_MMI_API_Digg