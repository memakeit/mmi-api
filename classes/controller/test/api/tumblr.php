<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tumblr test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Tumblr extends Controller_Test_API
{
	/**
	 * Test the Tumblr API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$email = 'XXXXXXXXXX';
		$password = 'XXXXXXXXXX';
		$username = Arr::path($config, 'tumblr.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_TUMBLR);
//		$response = $svc->get('http://memakeit.tumblr.com/api/read');

		$requests = array
		(
			'posts' => array('url' => "http://{$username}.tumblr.com/api/read"),
			'pages' => array('url' => "http://{$username}.tumblr.com/api/pages"),
			'posts (private)' => array('method' => MMI_HTTP::METHOD_POST, 'url' => "http://{$username}.tumblr.com/api/read", 'parms' => array('email' => $email, 'password' => $password)),
			'likes' => array('method' => MMI_HTTP::METHOD_POST, 'url' => 'likes', 'parms' => array('email' => $email, 'password' => $password)),
			'settings' => array('method' => MMI_HTTP::METHOD_POST, 'url' => 'authenticate', 'parms' => array('email' => $email, 'password' => $password)),
		);
		$response = $svc->mexec($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Tumblr
