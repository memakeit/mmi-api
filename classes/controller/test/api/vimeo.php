<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Vimeo test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Vimeo extends Controller_Test_API
{
   /**
	 * Test the Vimeo API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_VIMEO);
		if ( ! $svc->is_valid_token(NULL, TRUE))
		{
			die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
		}
//		$response = $svc->get('', array('method' => 'vimeo.people.getInfo'));

		$requests = array
		(
			'profile' => array('parms' => array('method' => 'vimeo.people.getInfo')),
			'user did' => array('parms' => array('method' => 'vimeo.activity.userDid')),
			'videos uploaded' => array('parms' => array('method' => 'vimeo.videos.getUploaded')),
			'videos liked' => array('parms' => array('method' => 'vimeo.videos.getLikes')),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Vimeo
