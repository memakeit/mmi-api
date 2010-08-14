<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Scribd test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Test_Scribd extends Controller_MMI_API_Test
{
	/**
	 * Test the Scribd API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_SCRIBD);
//		$response = $svc->get(NULL, array('method' => 'docs.getList'));

		$requests = array
		(
			'user documents' => array('parms' => array('method' => 'docs.getList')),
			'user collections' => array('parms' => array('method' => 'docs.getCollections')),
			'categories' => array('parms' => array('method' => 'docs.getCategories')),
			'featured' => array('parms' => array('method' => 'docs.featured')),
			'auto signin url' => array('parms' => array('method' => 'user.getAutoSigninUrl', 'next_url' => 'http://www.memakeit.com/')),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_MMI_API_Test_Scribd
