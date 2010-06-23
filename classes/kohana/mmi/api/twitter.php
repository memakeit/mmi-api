<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Twitter API calls.
 * Response formats: Atom, JSON, RSS, XML
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://apiwiki.twitter.com/Twitter-API-Documentation
 */
class Kohana_MMI_API_Twitter extends MMI_API_OAuth
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_TWITTER;

        /**
     * Ensure the request token has been verified and an access token received.
     *
     * @throws  Kohana_Exception
     * @return  void
     */
    protected function _check_token()
    {
        if ( ! $this->is_valid_token())
        {
            $service = $this->_service;
            MMI_API::log_error(__METHOD__, __LINE__, 'Request token not valid for '.$service);
            throw new Kohana_Exception('Request token not valid for :service in :method.', array
            (
                ':service'  => $service,
                ':method'   => __METHOD__,
            ));
        }
    }

    /**
     * Build the request URL.
     *
     * @param   string  the base URL
     * @param   string  the path portion of the URL
     * @return  string
     */
    protected function _build_url($url, $path)
    {
        return "$url$path.{$this->_format}";
    }
} // End Kohana_MMI_API_Twitter