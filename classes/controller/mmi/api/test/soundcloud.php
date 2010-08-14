<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SoundCloud test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_SoundCloud extends Controller_MMI_API_Test
{
	/**
	 * Test the SoundCloud API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_SOUNDCLOUD);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('me');

		$requests = array
		(
			'profile' => array('url' => 'me'),
			'tracks' => array('url' => 'me/tracks'),
			'following' => array('url' => 'me/followings'),
			'followers' => array('url' => 'me/followers'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_SoundCloud
