<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Posterous API calls.
 * Does not support the Posterous Twitter API (http://posterous.com/api2).
 * Response formats: XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://posterous.com/api
 */
class Kohana_MMI_API_Posterous extends MMI_API_Basic
{
    // Service name
    protected $_service = MMI_API::SERVICE_POSTEROUS;

    // API settings
    protected $_api_url = 'http://posterous.com/api/';
} // End Kohana_MMI_API_Posterous