<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySpace test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_MySpace extends Controller_MMI_API_Test
{
	/**
	 * Test the MySpace API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$user_id = '469257560';

		$svc = MMI_API::factory(MMI_API::SERVICE_MYSPACE);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get("v1/users/{$user_id}/details");

		$requests = array
		(
			'activities' => array('url' => "v1/users/{$user_id}/activities.atom"),
			'friends' => array('url' => "v1/users/{$user_id}/friends"),
			'profile' => array('url' => "v1/users/{$user_id}/details"),
			'status' => array('url' => "v1/users/{$user_id}/status"),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_MySpace
