<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make LinkedIn API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://developer.linkedin.com/community/apis
 */
class Kohana_MMI_API_LinkedIn extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_LINKEDIN;
} // End Kohana_MMI_API_LinkedIn