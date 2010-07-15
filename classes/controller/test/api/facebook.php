<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Facebook test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Facebook extends Controller_Test_API
{
	/**
	 * Test the Facebook API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_FACEBOOK);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('me');

		$requests = array
		(
			'me' => array('url' => 'me'),
			'friends' => array('url' => 'me/friends'),
			'events' => array('url' => 'me/events'),
			'photos' => array('url' => 'me/photos'),
			'videos' => array('url' => 'me/videos'),
			'home' => array('url' => 'me/home'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Facebook
