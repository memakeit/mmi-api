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
    // Service name
    protected $_service = MMI_API::SERVICE_FOURSQUARE;

    // API settings
    protected $_api_url = 'http://api.foursquare.com/v1/';

    // OAuth settings
    protected $_request_token_url = 'http://foursquare.com/oauth/request_token';
    protected $_request_token_http_method = MMI_HTTP::METHOD_GET;
    protected $_access_token_url = 'http://foursquare.com/oauth/access_token';
    protected $_access_token_http_method = MMI_HTTP::METHOD_GET;
    protected $_authorize_url = 'http://foursquare.com/oauth/authorize';

    /**
     * Build the request URL.
     *
     * @param   string  the base URL
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($url, $path)
    {
        // Ensure the URL does not already have an extension
        $has_extension = FALSE;
        foreach (array(MMI_API::FORMAT_JSON, MMI_API::FORMAT_XML) as $ext)
        {
            if ($this->_url_ends_with($path, '.'.$ext))
            {
                $has_extension = TRUE;
                break;
            }
        }
        return ($has_extension) ? $url.$path : "$url$path.{$this->_format}";
    }
} // End Kohana_MMI_API_Foursquare