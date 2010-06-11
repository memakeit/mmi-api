<?php defined('SYSPATH') or die('No direct script access.');

class Controller_OAuth_Verification extends Controller
{
    public function action_index()
    {
        // Process verification
        $service = $this->request->param('service');
        $success = FALSE;
        if ( ! empty($service))
        {
            $success = MMI_OAuth_Verification::factory($service)->insert_verification();
        }

        // Send response
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