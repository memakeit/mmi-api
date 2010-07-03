<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Posterous test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_Posterous extends Controller_Test_API
{
    /**
     * Test the Posterous API.
     *
     * @return  void
     */
    public function action_index()
    {
        $svc = MMI_API::factory(MMI_API::SERVICE_POSTEROUS);
//        $response = $svc->get('readposts');

        $requests = array
        (
            'getsites' => array('url' => 'getsites'),
            'gettags' => array('url' => 'gettags'),
            'readposts' => array('url' => 'readposts'),
        );
        $response = $svc->mget($requests);
        $this->_set_response($response, $svc->service());
    }
} // End Controller_Test_API_Posterous