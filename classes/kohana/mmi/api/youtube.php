<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make YouTube API calls.
 * Response formats: Atom, JSON, RSS
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://code.google.com/apis/youtube/2.0/reference.html
 */
class Kohana_MMI_API_YouTube extends MMI_API_Google
{
	// Service name
	protected $_service = MMI_API::SERVICE_YOUTUBE;

	// API settings
	protected $_api_url = 'http://gdata.youtube.com/feeds/api/';
} // End Kohana_MMI_API_YouTube
