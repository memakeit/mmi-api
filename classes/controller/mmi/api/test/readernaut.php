<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Readernaut test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Readernaut extends Controller_MMI_API_Test
{
	/**
	 * Test the Readernaut API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_READERNAUT);
//		$response = $svc->get('nathan/books/');

		$requests = array
		(
			'nathan' => array('url' => 'nathan/books/'),
			'memakeit' => array('url' => 'memakeit/books/'),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Readernaut
