<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make GoogleBuzz API calls.
 * Response formats: Atom, JSON
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://code.google.com/apis/buzz/v1/using_rest.html
 */
class Kohana_MMI_API_GoogleBuzz extends MMI_API_Google
{
    // Service name
    protected $_service = MMI_API::SERVICE_GOOGLEBUZZ;

    // API settings
    protected $_api_url = 'https://www.googleapis.com/buzz/v1/';
} // End Kohana_MMI_API_GoogleBuzz