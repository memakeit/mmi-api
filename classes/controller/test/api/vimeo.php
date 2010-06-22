<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Vimeo test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Vimeo extends Controller_Test_API
{
   /**
     * Test the Vimeo API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_VIMEO);
        if ( ! $svc->is_token_valid(NULL, TRUE))
        {
            die(HTML::anchor($svc->get_auth_redirect(), $svc->service().' authorization required'));
        }
        $response = $svc->get('', array('method' => 'vimeo.people.getInfo', 'user_id' => 'memakeit'));

        $requests = array
        (
            'profile' => array('url' => '~'),
            'current-status' => array('url' => '~/current-status'),
            'network' => array('url' => '~/network'),
        );
//        $response = $svc->mget($requests);
        $this->_display_response($response, $svc->service());
    }
} // End Controller_Test_API_Vimeo