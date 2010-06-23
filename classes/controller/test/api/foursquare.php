<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Foursquare test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Foursquare extends Controller_Test_API
{
    /**
     * Test the Foursquare API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_FOURSQUARE);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get('user');

        $requests = array
        (
            'user' => array('url' => 'user'),
            'friends' => array('url' => 'friends'),
            'checkins' => array('url' => 'checkins'),
            'test' => array('url' => 'test'),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Foursquare