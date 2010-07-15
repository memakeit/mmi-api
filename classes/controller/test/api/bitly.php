<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bitly test controller.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_API_Bitly extends Controller_Test_API
{
	/**
	 * Test the Bitly API.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$svc = MMI_API::factory(MMI_API::SERVICE_BITLY);
//		$response = $svc->get('shorten', array('longUrl' => 'http://www.memakeit.com'));

		$requests = array
		(
			'memakeit' => array('url' => 'shorten', 'parms' => array('longUrl' => 'http://www.memakeit.com')),
			'google' => array('url' => 'shorten', 'parms' => array('longUrl' => 'http://www.google.com')),
		);
		$response = $svc->mget($requests);
		$this->_set_response($response, $svc->service());
	}
} // End Controller_Test_API_Bitly
