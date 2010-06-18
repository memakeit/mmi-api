<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Vimeo API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://vimeo.com/api/docs/methods
 */
class Kohana_MMI_API_Vimeo extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_VIMEO;

    /**
     * Configure the request parameters as specified in the configuration file.
     * When processing additions, if a parameter value exists, it will not be overwritten.
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_parameters($parms)
    {
        $name = 'format';
        if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
        {
            $parms[$name] = $this->_format;
        }
        return parent::_configure_parameters($parms);
    }
} // End Kohana_MMI_API_Vimeo