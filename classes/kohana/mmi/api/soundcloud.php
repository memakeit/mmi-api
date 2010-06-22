<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make SoundCloud API calls.
 * Response formats: JavaScript, JSON, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://wiki.github.com/soundcloud/api/
 */
class Kohana_MMI_API_SoundCloud extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_SOUNDCLOUD;
} // End Kohana_MMI_API_SoundCloud