<?php defined('SYSPATH') or die('No direct script access.');
/**
 * GoogleBuzz test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_GoogleBuzz extends Controller_Test_API
{
    /**
     * Test the GoogleBuzz API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_GOOGLEBUZZ);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get('people/@me/@self');

        $requests = array
        (
            'profile' => array('url' => 'people/@me/@self'),
            'streams' => array('url' => 'activities/@me/@self'),
            'followers' => array('url' => 'people/@me/@groups/@followers'),
            'following' => array('url' => 'people/@me/@groups/@following'),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_GoogleBuzz