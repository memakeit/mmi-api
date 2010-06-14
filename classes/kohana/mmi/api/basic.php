<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls using basic authentication.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API_Basic extends MMI_API
{
    /**
     * Get the string to be sent via the authorization header.
     *
     * @return  string
     */
    protected function _get_auth_header()
    {
        $auth_config = $this->_auth_config;
        $username = Arr::get($auth_config, 'username');
        $password = Arr::get($auth_config, 'password');

        $auth_header = '';
        if ( ! empty($username) AND ! empty($password))
        {
            $auth_header = 'Basic '.base64_encode($username.':'.$password);
        }
        return $auth_header;
    }
} // End Kohana_MMI_API_Basic