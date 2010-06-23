<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Goodreads test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Goodreads extends Controller_Test_API
{
    /**
     * Test the Goodreads API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_GOODREADS);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
        $response = $svc->get('user/show/3865951');

        $requests = array
        (
            'profile' => array('url' => 'user/show/3865951'),
            'followers' => array('url' => 'user/3865951/followers'),
            'following' => array('url' => 'user/3865951/following'),
            'owned books' => array('url' => 'owned_books/user', 'parms' => array('id'=>'3865951')),
        );
//        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Goodreads