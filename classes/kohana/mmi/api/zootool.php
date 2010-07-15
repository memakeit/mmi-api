<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Zootool API calls.
 * Response formats: JSON
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://zootool.com/api/docs
 */
class Kohana_MMI_API_Zootool extends MMI_API
{
    // Service name
    protected $_service = MMI_API::SERVICE_ZOOTOOL;

    // API settings
    protected $_api_url = 'http://zootool.com/api/';

    /**
     * @var string the API key
     */
    protected $_api_key = NULL;

    /**
     * @var string the API secret
     */
    protected $_api_secret = NULL;

    /**
     * Load configuration settings.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        $auth_config = $this->_auth_config;
        $this->_api_key = Arr::get($auth_config, 'api_key');
        $this->_api_secret = Arr::get($auth_config, 'api_secret');
    }

    /**
     * Configure the request parameters as specified in the configuration file.
     * When processing additions, if a parameter value exists, it will not be overwritten.
     *
     * @param   array   an associative array of request parameters
     * @return  array
     */
    protected function _configure_parameters($parms)
    {
        $parms = parent::_configure_parameters($parms);

        $api_key = $this->_api_key;
        $this->_ensure_parm('API key', $api_key);
        $name = 'apikey';
        if ( ! array_key_exists($name, $parms) OR (array_key_exists($name, $parms) AND empty($parms[$name])))
        {
            $parms[$name] = $api_key;
        }
        return $parms;
    }

    /**
     * Configure the cURL options.
     *
     * @param   MMI_Curl    the cURL object instance
     * @return  void
     */
    protected function _configure_curl_options($curl)
    {
        parent::_configure_curl_options($curl);

        // Get username and password
        $auth_config = $this->_auth_config;
        $username = Arr::get($auth_config, 'username');
        $password = Arr::get($auth_config, 'password');

        // Configure auth options
        if ( ! empty($username) AND ! empty($password))
        {
            $curl->add_curl_option(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
//            $curl->add_curl_option(CURLOPT_USERPWD, strtolower($username).':'.sha1($password));
        }
    }

    /**
     * Get the string to be sent via the authorization header.
     *
     * @return  string
     */
    protected function _get_auth_header()
    {
        $auth_config = $this->_auth_config;
        $username = Arr::get($auth_config, 'username');
        $password = Arr::get($auth_config, 'password');

//    http://stackoverflow.com/questions/3109507/httpwebrequests-sends-parameterless-uri-in-authorization-header
//
//    private static string GetDigestHeader(
//        string dir)
//    {
//        _nc = _nc + 1;
//
//        var ha1 = CalculateMd5Hash(string.Format("{0}:{1}:{2}", _user, _realm, _password));
//        var ha2 = CalculateMd5Hash(string.Format("{0}:{1}", "GET", dir));
//        var digestResponse =
//            CalculateMd5Hash(string.Format("{0}:{1}:{2:00000000}:{3}:{4}:{5}", ha1, _nonce, _nc, _cnonce, _qop, ha2));
//
//        return string.Format("Digest username=\"{0}\", realm=\"{1}\", nonce=\"{2}\", uri=\"{3}\", " +
//            "algorithm=MD5, response=\"{4}\", qop={5}, nc={6:00000000}, cnonce=\"{7}\"",
//            _user, _realm, _nonce, dir, digestResponse, _qop, _nc, _cnonce);
//    }


//$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
//$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
//$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        $auth_header = '';
        if ( ! empty($username) AND ! empty($password))
        {
            $auth_header = 'Digest '.strtolower($username).':'.sha1($password);
        }
        return $auth_header;
    }
} // End Kohana_MMI_API_Zootool
