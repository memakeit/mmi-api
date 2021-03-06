<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Flickr test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Flickr extends Controller_MMI_API_Test
{
	/**
	 * Test the Flickr API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$user_id = '37619738@N07';

		$svc = MMI_API::factory(MMI_API::SERVICE_FLICKR);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get(NULL, array('method' => 'flickr.people.getInfo', 'user_id' => $user_id));

		$requests = array
		(
			'people.getInfo' => array('parms' => array('method' => 'flickr.people.getInfo', 'user_id' => $user_id)),
			'activity.userPhotos' => array('parms' => array('method' => 'flickr.activity.userPhotos')),
			'activity.userComments' => array('parms' => array('method' => 'flickr.activity.userComments')),
			'galleries.getList' => array('parms' => array('method' => 'flickr.galleries.getList', 'user_id' => $user_id)),
			'photosets.getList' => array('parms' => array('method' => 'flickr.photosets.getList', 'user_id' => $user_id)),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Flickr
