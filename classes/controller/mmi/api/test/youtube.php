<?php defined('SYSPATH') or die('No direct script access.');
/**
 * YouTube test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_YouTube extends Controller_MMI_API_Test
{
	/**
	 * Test the YouTube API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_YOUTUBE);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('users/default', array('v' => 2));

		$requests = array
		(
			'user' => array('url' => 'users/default'),
			'subscriptions' => array('url' =>'users/default/subscriptions'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_YouTube
