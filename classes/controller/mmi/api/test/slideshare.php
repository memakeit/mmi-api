<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SlideShare test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_SlideShare extends Controller_MMI_API_Test
{
	/**
	 * Test the SlideShare API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$config = MMI_API::get_config(TRUE);
		$username = Arr::path($config, 'slideshare.auth.username', 'memakeit');

		$svc = MMI_API::factory(MMI_API::SERVICE_SLIDESHARE);
//		$response = $svc->get('get_slideshows_by_user', array('username_for' => $username, 'detailed' => '1'));

		$requests = array
		(
			'user slideshows' => array('url' => 'get_slideshows_by_user', 'parms' => array('username_for' => $username, 'detailed' => '1')),
			'user groups' => array('url' => 'get_user_groups', 'parms' => array('username_for' => $username)),
			'user contacts' => array('url' => 'get_user_contacts', 'parms' => array('username_for' => $username)),
			'get slideshow' => array('url' => 'get_slideshow', 'parms' => array('slideshow_url' => 'http://www.slideshare.net/vortexau/improving-php-application-performance-with-apc-presentation', 'detailed' => '1')),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_SlideShare
