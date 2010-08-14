<?php defined('SYSPATH') or die('No direct script access.');
/**
 * LinkedIn test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_LinkedIn extends Controller_MMI_API_Test
{
	/**
	 * Test the LinkedIn API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_LINKEDIN);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('~:(id,first-name,last-name,location,current-status,current-status-timestamp,num-recommenders,connections)');

		$requests = array
		(
			'profile' => array('url' => '~:(id,first-name,last-name,location,current-status,current-status-timestamp,num-recommenders,connections)'),
			'current-status' => array('url' => '~/current-status'),
			'network' => array('url' => '~/network'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_LinkedIn
