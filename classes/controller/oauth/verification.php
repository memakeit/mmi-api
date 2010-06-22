<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller to process OAuth verifications.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Controller_OAuth_Verification extends Controller
{
    /**
     * @var boolean turn debugging on?
     **/
    public $debug = FALSE;

    /**
     * Process the verification.
     *
     * @return  void
     */
    public function action_index()
    {
        // Process the verification
        $service = $this->request->param('service');
        $success = FALSE;
        if ( ! empty($service))
        {
            $success = MMI_OAuth_Verification::factory()->process_verification($service);
        }

        // Send the response
        $this->request->headers['Content-Type'] = File::mime_by_ext('txt');
        if ($success)
        {
            $this->request->response = 'Success';
        }
        else
        {
            $this->request->response = 'Unauthorized';
            $this->request->status = 401;
        }
    }
} // End Controller_OAuth_Verification