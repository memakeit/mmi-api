<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Zootool test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Zootool extends Controller_Test_API
{
    /**
     * Test the Zootool API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_ZOOTOOL);
//        $response = $svc->get('/items/popular/', array('type' => 'week'));

        $requests = array
        (
            'user info' => array('url' => 'users/info/', 'parms' => array('username' => 'memakeit')),
            'user items' => array('url' => 'users/items/', 'parms' => array('username' => 'memakeit')),
            'followers' => array('url' => 'users/followers/', 'parms' => array('username' => 'memakeit')),
            'friends' => array('url' => 'users/friends/', 'parms' => array('username' => 'memakeit')),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Zootool