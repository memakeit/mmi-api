<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Flickr test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Flickr extends Controller_Test_API
{
   /**
     * Test the Flickr API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_FLICKR);
        if ( ! $svc->is_valid_token(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
//        $response = $svc->get(NULL, array('method' => 'flickr.people.getInfo', 'user_id' => '37619738@N07'));

        $requests = array
        (
            'people.getInfo' => array('parms' => array('method' => 'flickr.people.getInfo', 'user_id' => '37619738@N07')),
            'activity.userPhotos' => array('parms' => array('method' => 'flickr.activity.userPhotos')),
            'activity.userComments' => array('parms' => array('method' => 'flickr.activity.userComments')),
            'galleries.getList' => array('parms' => array('method' => 'flickr.galleries.getList', 'user_id' => '37619738@N07')),
            'photosets.getList' => array('parms' => array('method' => 'flickr.photosets.getList', 'user_id' => '37619738@N07')),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Flickr