<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Twitter test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Twitter extends Controller_Test_API
{
    /**
     * Test the Twitter API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_TWITTER);
        if ( ! $svc->is_token_valid(NULL, FALSE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get('statuses/user_timeline', array('since_id' => '15635444694'));

        $requests = array
        (
            'user_timeline' => array('url' => 'statuses/user_timeline'),
            'retweeted_by_me' => array('url' => 'statuses/retweeted_by_me'),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Twitter