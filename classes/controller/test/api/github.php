<?php defined('SYSPATH') or die('No direct script access.');
/**
 * GitHub test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_GitHub extends Controller_Test_API
{
    /**
     * Test the GitHub API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_GITHUB);
//        $response = $svc->post('repos/show/memakeit/mmi-api', array('values[description]' => 'api module (for making api calls to 3rd-party services)'));
//        $response = $svc->get('user/show/memakeit');

        $requests = array
        (
            'memakeit' => array('url' => 'user/show/memakeit'),
            'shadowhand' => array('url' => 'user/show/shadowhand'),
        );
        $response = $svc->mget($requests);
        $this->_display_response($response, $svc->service());
    }
} // End Controller_Test_API_GitHub