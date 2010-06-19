<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Foursquare API calls.
 * Response formats: JSON, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://groups.google.com/group/foursquare-api/web/api-documentation
 */
class Kohana_MMI_API_Foursquare extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_FOURSQUARE;

    /**
     * Build the request URL.
     *
     * @param   string  the base URL
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($url, $path)
    {
        return "$url$path.{$this->_format}";
    }
} // End Kohana_MMI_API_Foursquare