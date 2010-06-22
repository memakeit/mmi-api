<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API test controller.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Controller_Test_API extends Controller
{
    /**
     * @var string configure the cache type
     **/
    public $cache_type = MMI_Cache::CACHE_TYPE_NONE;

    /**
     * @var boolean turn debugging on?
     **/
    public $debug = TRUE;

    /**
     * Display the cURL response
     *
     * @param   MMI_Curl_Response   the cURL response object
     * @param   string              the service name
     * @return  void
     */
    protected function _display_response($response, $service)
    {
        if (is_array($response) AND count($response) > 0)
        {
            $responses = $response;
            foreach ($responses as $id => $response)
            {
                MMI_Debug::dump($response, $service.' response ['.$id.']');
            }
        }
        else
        {
            MMI_Debug::dump($response, $service.' response');
        }
        die();
    }
} // End Controller_Test_API