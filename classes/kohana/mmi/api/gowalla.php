<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Gowalla API calls.
 * Response formats: JSON
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://develop.github.com/
 */
class Kohana_MMI_API_Gowalla extends MMI_API_Basic
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_GOWALLA;

    /**
     * Configure the HTTP authorization header sent via cURL.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_auth_header($curl)
    {
        parent::_configure_auth_header($curl);

        $api_key = Arr::get($this->_auth_config, 'api_key');
        if (empty($api_key))
        {
            $msg = 'API key not configured for '.$this->_service;
            MMI_Log::log_error(__METHOD__, __LINE__, $msg);
            throw new Kohana_Exception($msg);
        }
        $curl->add_http_header('X-Gowalla-API-Key', $api_key);
    }
} // End Kohana_MMI_API_Gowalla