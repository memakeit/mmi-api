<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Tumblr API calls.
 * Response formats: XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://www.tumblr.com/docs/en/api
 */
class Kohana_MMI_API_Tumblr extends MMI_API
{
    // Service name
    protected $_service = MMI_API::SERVICE_TUMBLR;

    // API settings
    protected $_api_url = 'http://www.tumblr.com/api/';
} // End Kohana_MMI_API_Tumblr