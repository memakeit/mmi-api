<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Goodreads test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Goodreads extends Controller_MMI_API_Test
{
	/**
	 * Test the Goodreads API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$user_id = '3865951';

		$svc = MMI_API::factory(MMI_API::SERVICE_GOODREADS);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get("user/show/{$user_id}");

		$requests = array
		(
			'profile' => array('url' => "user/show/{$user_id}"),
			'followers' => array('url' => "user/{$user_id}/followers"),
			'following' => array('url' => "user/{$user_id}/following"),
			'owned books' => array('url' => 'owned_books/user', 'parms' => array('id' => $user_id)),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Goodreads
