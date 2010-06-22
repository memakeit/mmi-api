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
     * @var boolean turn debugging on?
     **/
    public $debug = TRUE;

    /**
     * Set the request response to the cURL debugging output.
     *
     * @param   MMI_Curl_Response   the cURL response object
     * @param   string              the service name
     * @return  void
     */
    protected function _set_response($response, $service)
    {
        $output = '';
        if (is_array($response) AND count($response) > 0)
        {
            $responses = $response;
            foreach ($responses as $id => $response)
            {
                $output .= MMI_Debug::get($response, $service.' response ['.$id.']');
            }
        }
        else
        {
            $output .= MMI_Debug::get($response, $service.' response');
        }
        $this->request->response = $output;
    }
} // End Controller_Test_API