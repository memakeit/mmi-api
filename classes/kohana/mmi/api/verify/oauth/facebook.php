<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify Facebook credentials.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_API_Verify_OAuth_Facebook extends MMI_API_Verify_OAuth
{
	/**
	 * @var string the service name
	 */
	protected $_service = MMI_API::SERVICE_FACEBOOK;

	/**
	 * Verify the Facebook credentials.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the service name
	 * @return	boolean
	 */
	public function verify($service = MMI_API::SERVICE_FACEBOOK)
	{
		$access_token = NULL;
		if ( ! array_key_exists('fragment', $_GET))
		{
			$this->_convert_fragment_to_parameter();
		}
		else
		{
			$fragment = urldecode(Security::xss_clean($_GET['fragment']));
			parse_str($fragment, $parms);
			$access_token = Arr::get($parms, 'access_token');
			unset($parms);
		}

		// Ensure the access token is set
		if (empty($access_token))
		{
			MMI_API::log_error(__METHOD__, __LINE__, 'Access token parameter missing');
			throw new Kohana_Exception('Access token parameter missing in :method.', array
			(
				':method' => __METHOD__,
			));
		}

		// Load existing data from the database
		$auth_config = $this->_auth_config;
		$username = Arr::get($auth_config, 'username');
		$model;
		if ( ! empty($username))
		{
			$model = Model_MMI_API_Tokens::select_by_service_and_username($service, $username, FALSE);
		}
		else
		{
			$consumer_key = Arr::get($auth_config, 'api_key');
			$model = Model_MMI_API_Tokens::select_by_service_and_consumer_key($service, $consumer_key, FALSE);
		}

		$success = FALSE;
		$previously_verified = FALSE;
		if ($model->loaded())
		{
			// Check if the credentials were previously verified
			$previously_verified = $model->verified;
			$success = $previously_verified;
		}

		if ( ! $previously_verified)
		{
			// Create an access token
			$token = new OAuthToken($access_token, $service.'-'.time());

			// Update the token credentials in the database
			$svc = MMI_API::factory($service);
			if (isset($token) AND $svc->is_valid_token($token))
			{
				$encrypt = Encrypt::instance();
				$model->service = $service;
				$model->consumer_key = 'consumer-'.$service;
				$model->consumer_secret = $encrypt->encode($service.'-'.time());
				$model->token_key = $token->key;
				$model->token_secret = $encrypt->encode($token->secret);
				unset($encrypt);
				$model->verified = 1;
				$model->verification_code = $service.'-'.time();
				$model->username = $username;
				if (array_key_exists('expires_in', $_GET))
				{
					$model->attributes = array('expires_in' => urldecode(Security::xss_clean($_GET['expires_in'])));
				}
				$success = MMI_Jelly::save($model, $errors);
				if ( ! $success AND $this->_debug)
				{
					MMI_Debug::dead($errors);
				}
			}
		}
		return $success;
	}

	/**
	 * Echo JavaScript to convert hash parameters to query string parameters and reload the page.
	 *
	 * @return	void
	 */
	protected function _convert_fragment_to_parameter()
	{
$js = <<<EOJS
<script type="text/javascript">
// <![CDATA[
var parts = location.href.split('#');
if(parts.length > 1)
{
	var parms = parts[0].split('?');
	var mark = '?';
	if(parms.length > 1)
	{
		mark = '&';
	}
	location.href = parts[0] + mark + 'fragment=' + parts[1];
}
// ]]>
</script>
EOJS;

		$request = Request::$instance;
		$request->headers['Content-Type'] = File::mime_by_ext('htm');
		$request->send_headers();
		die($js);
	}
} // End Kohana_MMI_API_Verify_OAuth_Facebook
