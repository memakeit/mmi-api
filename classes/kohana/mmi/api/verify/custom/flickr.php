<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify Flickr credentials.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_API_Verify_Custom_Flickr extends MMI_API_Verify_Custom
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_FLICKR;

    /**
     * Verify the Flickr credentials.
     *
     * @throws  Kohana_Exception
     * @return  boolean
     */
    public function verify()
    {
        // Set the service
        $service = $this->_service;
        if (empty($service))
        {
            MMI_API::log_error(__METHOD__, __LINE__, 'Service not set');
            throw new Kohana_Exception('Service not set in :method.', array
            (
                ':method'   => __METHOD__,
            ));
        }

        // Ensure the frob is set
        $frob = NULL;
        if (array_key_exists('frob', $_GET))
        {
            $frob = urldecode(Security::xss_clean($_GET['frob']));
        }
        if (empty($frob))
        {
            MMI_API::log_error(__METHOD__, __LINE__, 'Frob parameter missing');
            throw new Kohana_Exception('Frob parameter missing in :method.', array
            (
                ':method'   => __METHOD__,
            ));
        }

        // Load existing data from the database
        $auth_config = Arr::path(MMI_API::get_config(TRUE), $service.'.auth', array());
        $username = Arr::get($auth_config, 'username');
        $model;
        if ( ! empty($username))
        {
            $model = Model_MMI_API_Tokens::select_by_service_and_username($service, $username, FALSE);
        }
        else
        {
            $model = Jelly::factory('MMI_API_Tokens');
        }

        $success = FALSE;
        if ($model->loaded())
        {
            // Check if the credentials were previously verified
            $previously_verified = $model->verified;
            if ($previously_verified)
            {
                $success = TRUE;
            }
            else
            {
                // Create a dummy verification code
                $verification_code = $service.'-'.time();
            }

            // Do database update
            if ( ! $previously_verified)
            {
                // Get an access token
                $svc = MMI_API::factory($service);
                $token = $svc->get_access_token($verification_code, array
                (
                    'token_key'     => $frob,
                    'token_secret'  => $service.'-'.time(),
                ));

                // Update the token credentials in the database
                if (isset($token) AND $svc->is_valid_token($token))
                {
                    $model->token_key = $token->key;
                    $model->token_secret = Encrypt::instance()->encode($token->secret);
                    $model->verified = 1;
                    $model->verification_code = $verification_code;
                    if ( ! empty($token->attributes))
                    {
                        $model->attributes = $token->attributes;
                    }
                    $success = MMI_Jelly::save($model, $errors);
                    if ( ! $success AND $this->debug)
                    {
                        MMI_Debug::dead($errors);
                    }
                }
            }
        }
        return $success;
    }
} // End Kohana_MMI_API_Verify_Custom_Flickr