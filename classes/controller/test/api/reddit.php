<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reddit test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Reddit extends Controller_Test_API
{
    /**
     * Test the Reddit API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_REDDIT);
        $svc->login();
//        $response = $svc->get('api/info', array('url' => 'http://www.1stwebdesigner.com/tutorials/create-stay-on-top-menu-css3-jquery/'));

        $requests = array
        (
            'user about' => array('url' => 'user/memakeit/about'),
            'clear vote' => array('method' => MMI_HTTP::METHOD_POST, 'url' => 'api/vote', 'parms' => array('api_type' => 'json', 'dir' => '0', 'id' => 't3_ckvqi', 'r' => 'web_design')),
        );
        $response = $svc->mexec($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Reddit