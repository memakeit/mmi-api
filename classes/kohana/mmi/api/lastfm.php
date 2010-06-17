<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make LastFM API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://www.last.fm/api/intro
 */
class Kohana_MMI_API_LastFM extends MMI_API
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_LASTFM;
} // End Kohana_MMI_API_LastFM