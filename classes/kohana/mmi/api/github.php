<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make GitHub API calls.
 * Response formats: JSON, XML, YAML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://develop.github.com/
 */
class Kohana_MMI_API_GitHub extends MMI_API_Basic
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_GITHUB;

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
} // End Kohana_MMI_API_GitHub