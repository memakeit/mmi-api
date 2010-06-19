<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Bitly API calls.
 * Response formats: JSON, Text, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://code.google.com/p/bitly-api/wiki/ApiDocumentation
 */
class Kohana_MMI_API_Bitly extends MMI_API
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_BITLY;
} // End Kohana_MMI_API_Bitly