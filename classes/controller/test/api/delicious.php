<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Delicious test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Delicious extends Controller_Test_API
{
	/**
	 * Test the Delicious API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_DELICIOUS);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('posts/recent');

		$requests = array
		(
			'posts/recent' => array('url' => 'posts/recent'),
			'posts/dates' => array('url' => 'posts/dates'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Delicious
