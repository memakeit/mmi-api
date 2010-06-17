<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Readernaut API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://groups.google.com/group/readernaut-api/web/restful-api-overview
 */
class Kohana_MMI_API_Readernaut extends MMI_API
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_READERNAUT;

    /**
     * Build the request URL.
     *
     * @param   string  the base URL
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($url, $path)
    {
        return "$url{$this->_format}/$path";
    }
} // End Kohana_MMI_API_Readernaut