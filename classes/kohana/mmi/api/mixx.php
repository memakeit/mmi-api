<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Mixx API calls.
 * Response formats: JSON, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://help.mixx.com/API:v1r1:main
 */
class Kohana_MMI_API_Mixx extends MMI_API
{
    // Service name
    protected $_service = MMI_API::SERVICE_MIXX;

    // API settings
    protected $_api_url = 'http://api.mixx.com/services/v1r1/';

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

        $name = 'format';
        if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
        {
            $parms[$name] = $this->_format;
        }
        return parent::_configure_parameters($parms);
    }
} // End Kohana_MMI_API_Mixx