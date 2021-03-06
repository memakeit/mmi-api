<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Twitter test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Twitter extends Controller_MMI_API_Test
{
	/**
	 * Test the Twitter API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_TWITTER);
		if ( ! $svc->is_valid_token(NULL, FALSE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('statuses/user_timeline', array('since_id' => '15635444694'));

		$requests = array
		(
			'user_timeline' => array('url' => 'statuses/user_timeline'),
			'retweeted_by_me' => array('url' => 'statuses/retweeted_by_me'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Twitter
