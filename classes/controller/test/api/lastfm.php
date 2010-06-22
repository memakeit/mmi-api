<?php defined('SYSPATH') or die('No direct script access.');
/**
 * LastFM test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_Test_API_LastFM extends Controller_Test_API
{
    /**
     * Test the LastFM API.
     *
     * @return  void
     */
    public function action_index()
    {
        $parms1 = array
        (
            'method' => 'tag.gettopalbums',
            'tag' => 'disco',
            'format' => 'json',
        );
        $parms2 = array
        (
            'method' => 'user.getinfo',
            'user' => 'memakeit',
            'format' => 'json',
        );

        $svc = MMI_API::factory(MMI_API::SERVICE_LASTFM);
        $response = $svc->get('', $parms2);
        $requests = array
        (
            'tag.gettopalbums' => array('url' => '', 'parms' => $parms1),
            'user.getinfo' => array('url' => '', 'parms' => $parms2),
        );
//        $response = $svc->mget($requests);
        $this->_display_response($response, $svc->service());
    }
} // End Controller_Test_API_LastFM