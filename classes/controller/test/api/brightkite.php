<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Brightkite test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Brightkite extends Controller_Test_API
{
    /**
     * Test the Brightkite API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_BRIGHTKITE);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get('people/memakeit');

        $requests = array
        (
            'profile' => array('url' => 'people/memakeit'),
            'config' => array('url' => 'people/memakeit/config'),
            'friends' => array('url' => 'people/memakeit/friends'),
            'placemarks' => array('url' => 'people/memakeit/placemarks'),
            'objects' => array('url' => 'people/memakeit/objects', 'parms' => array('filters' => 'checkins,notes,photos')),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Brightkite