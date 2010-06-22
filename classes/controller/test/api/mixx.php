<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mixx test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Mixx extends Controller_Test_API
{
    /**
     * Test the Mixx API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_MIXX);
        if ( ! $svc->is_token_valid(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
        $response = $svc->get('users/show', array('format' => 'json', 'user_key' => 'memakeit'));

        $requests = array
        (
            'profile' => array('url' => 'me'),
            'tracks' => array('url' => 'me/tracks'),
            'following' => array('url' => 'me/followings'),
            'followers' => array('url' => 'me/followers'),
        );
//        $response = $svc->mget($requests);
        $this->_display_response($response, $svc->service());
    }
} // End Controller_Test_API_Mixx