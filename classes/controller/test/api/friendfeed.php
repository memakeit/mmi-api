<?php defined('SYSPATH') or die('No direct script access.');
/**
 * FriendFeed test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_FriendFeed extends Controller_Test_API
{
	/**
	 * Test the FriendFeed API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'friendfeed.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_FRIENDFEED);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('feed/home');

		$requests = array
		(
			'me feed' => array('url' => 'feed/me'),
			'user feed' => array('url' => "feed/{$username}"),
			'friends feed' => array('url' => "feed/{$username}/friends"),
			'comments feed' => array('url' => "feed/{$username}/comments"),
			'likes feed' => array('url' => "feed/{$username}/likes"),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_FriendFeed
