<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Digg test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Digg extends Controller_Test_API
{
	/**
	 * Test the Digg API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'digg.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_DIGG);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get(NULL, array('method' => 'user.getInfo', 'username' => $username));

		$requests = array
		(
			'user.getDiggs' => array('url' => '', 'parms' => array('method' => 'user.getDiggs', 'username' => $username)),
			'user.getInfo' => array('url' => '', 'parms' => array('method' => 'user.getInfo', 'username' => $username)),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Digg
