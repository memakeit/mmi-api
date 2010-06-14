<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Delicious API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_API_Delicious extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_DELICIOUS;
} // End Kohana_MMI_API_Delicious