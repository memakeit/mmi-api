<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Gowalla API calls.
 * Response formats: JSON
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://gowalla.com/api/explorer
 */
class Kohana_MMI_API_Gowalla extends MMI_API_Basic
{
    // Srvice name
    protected $_service = MMI_API::SERVICE_GOWALLA;

    // API settings
    protected $_api_url = 'http://api.gowalla.com/';

    /**
     * Configure the HTTP authorization header sent via cURL.
     *
     * @throws  Kohana_Exception
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_auth_header($curl)
    {
        parent::_configure_auth_header($curl);

        $api_key = Arr::get($this->_auth_config, 'api_key');
        $this->_ensure_parm('API key', $api_key);
        $curl->add_http_header('X-Gowalla-API-Key', $api_key);
    }
} // End Kohana_MMI_API_Gowalla