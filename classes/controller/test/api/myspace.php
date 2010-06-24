<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySpace test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_MySpace extends Controller_Test_API
{
    /**
     * Test the MySpace API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_MYSPACE);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get('v1/users/469257560/details');

        $requests = array
        (
            'activities' => array('url' => 'v1/users/469257560/activities.atom'),
            'friends' => array('url' => 'v1/users/469257560/friends'),
            'profile' => array('url' => 'v1/users/469257560/details'),
            'status' => array('url' => 'v1/users/469257560/status'),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_MySpace