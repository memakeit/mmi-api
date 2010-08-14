<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mixx test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Mixx extends Controller_MMI_API_Test
{
	/**
	 * Test the Mixx API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'mixx.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_MIXX);
//		$response = $svc->get('users/show', array('user_key' => $username));

		$requests = array
		(
			'profile' => array('url' => 'users/show', 'parms' => array('user_key' => $username)),
			'real-life-size-bus-transformer' => array('url' => 'thingies/show', 'parms' => array('url' => 'http://www.atcrux.com/2010/03/11/real-life-size-bus-transformer/', 'comments' => 1, 'tags' => 1)),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Mixx
