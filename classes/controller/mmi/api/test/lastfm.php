<?php defined('SYSPATH') or die('No direct script access.');
/**
 * LastFM test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_LastFM extends Controller_MMI_API_Test
{
	/**
	 * Test the LastFM API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'lastfm.auth.username', 'memakeit');

		$parms1 = array
		(
			'method' => 'user.getinfo',
			'user' => $username,
		);
		$parms2 = array
		(
			'method' => 'artist.gettoptracks',
			'artist' => 'the fall',
		);

		$svc = MMI_API::factory(MMI_API::SERVICE_LASTFM);
//		$response = $svc->get(NULL, $parms1);

		$requests = array
		(
			$parms1['method'] => array('parms' => $parms1),
			$parms2['method'] => array('parms' => $parms2),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_LastFM
