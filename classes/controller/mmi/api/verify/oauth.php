<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller to verify OAuth credentials.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_API_Verify_OAuth extends Controller
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
		// Verify the credentials
		$service = $this->request->param('service');
		$success = FALSE;
		if ( ! empty($service))
		{
			switch (strtolower($service))
			{
				case MMI_API::SERVICE_FACEBOOK:
					$success = MMI_API_Verify_OAuth::factory($service)->verify();
					break;

				default:
					$success = MMI_API_Verify_OAuth::factory()->verify($service);
					break;
			}
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
} // End Controller_MMI_API_Verify_OAuth
