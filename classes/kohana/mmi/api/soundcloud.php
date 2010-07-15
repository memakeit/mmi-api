<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make SoundCloud API calls.
 * Response formats: JavaScript, JSON, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://wiki.github.com/soundcloud/api/
 */
class Kohana_MMI_API_SoundCloud extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_SOUNDCLOUD;

	// API settings
	protected $_api_url = 'http://api.soundcloud.com/';

	// OAuth settings
	protected $_request_token_url = 'http://api.soundcloud.com/oauth/request_token';
	protected $_access_token_url = 'http://api.soundcloud.com/oauth/access_token';
	protected $_authorize_url = 'http://api.soundcloud.com/oauth/authorize';
} // End Kohana_MMI_API_SoundCloud
