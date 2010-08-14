<?php defined('SYSPATH') or die('No direct script access.');
/**
 * GitHub test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_GitHub extends Controller_MMI_API_Test
{
	/**
	 * Test the GitHub API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'github.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_GITHUB);
//		$response = $svc->get("user/show/{$username}");

		$requests = array
		(
			$username => array('url' => "user/show/{$username}"),
			'shadowhand' => array('url' => 'user/show/shadowhand'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_GitHub
