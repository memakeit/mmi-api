<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gowalla test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Gowalla extends Controller_Test_API
{
    /**
     * Test the Gowalla API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_GOWALLA);
        $response = $svc->get('users/memakeit');

        $requests = array
        (
            'memakeit' => array('url' => 'user/show/memakeit'),
            'shadowhand' => array('url' => 'user/show/shadowhand'),
        );
//        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Gowalla