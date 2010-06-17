<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Twitter API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://apiwiki.twitter.com/Twitter-API-Documentation
 */
class Kohana_MMI_API_Twitter extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_TWITTER;

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
} // End Kohana_MMI_API_Twitter