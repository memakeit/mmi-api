<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Goodreads API calls.
 * Response formats: JSON, XML
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.goodreads.com/api
 */
class Kohana_MMI_API_Goodreads extends MMI_API_OAuth
{
	// Service name
	protected $_service = MMI_API::SERVICE_GOODREADS;

	// API settings
	protected $_api_url = 'http://www.goodreads.com/';

	// OAuth settings
	protected $_request_token_url = 'http://www.goodreads.com/oauth/request_token';
	protected $_access_token_url = 'http://www.goodreads.com/oauth/access_token';
	protected $_authorize_url = 'http://www.goodreads.com/oauth/authorize';
} // End Kohana_MMI_API_Goodreads
