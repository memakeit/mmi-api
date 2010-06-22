<?php defined('SYSPATH') or die('No direct script access.');
/**
 * YouTube test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_YouTube extends Controller_Test_API
{
    /**
     * Test the YouTube API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_YOUTUBE);
        if ( ! $svc->is_token_valid(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
        $svc->format(MMI_API::FORMAT_RSS);
//        $response = $svc->get('users/default', array('v' => 2));

        $requests = array
        (
            'user' => array('url' => 'users/default'),
            'subscriptions' => array('url' =>'users/default/subscriptions'),
        );
        $response = $svc->mget($requests);
        $this->_display_response($response, $svc->service());
    }
} // End Controller_Test_API_YouTube