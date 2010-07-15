<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make LinkedIn API calls.
 * Response formats: XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://developer.linkedin.com/community/apis
 */
class Kohana_MMI_API_LinkedIn extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_LINKEDIN;

	// API settings
	protected $_api_url = 'http://api.linkedin.com/v1/people/';

	// OAuth settings
	protected $_request_token_url = 'https://api.linkedin.com/uas/oauth/requestToken';
	protected $_access_token_url = 'https://api.linkedin.com/uas/oauth/accessToken';
	protected $_authenticate_url = 'https://www.linkedin.com/uas/oauth/authenticate';
	protected $_authorize_url = 'https://www.linkedin.com/uas/oauth/authorize';
} // End Kohana_MMI_API_LinkedIn
