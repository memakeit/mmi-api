<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Picasa test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Picasa extends Controller_MMI_API_Test
{
	/**
	 * Test the Picasa API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'picasa.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_PICASA);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get("user/{$username}", array('kind' => 'photo', 'access' => 'all'));

		$requests = array
		(
			'albums' => array('url' => "user/{$username}", 'parms' => array('kind' => 'album', 'access' => 'all')),
			'recent photos' => array('url' => "user/{$username}", 'parms' => array('kind' => 'photo', 'access' => 'all')),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Picasa
