<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make Twitter API calls.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_API_Twitter extends MMI_API_OAuth
{
    /**
     * @var string the access token URL
     **/
    protected $_access_token_url = '';

    /**
     * @var string the authentication URL
     **/
    protected $_authenticate_url = '';

    /**
     * @var string the authorization URL
     **/
    protected $_authorize_url = '';

    /**
     * @var string the OAuth realm
     **/
    protected $_realm = 'yahooapis.com';

    /**
     * @var string the request token URL
     **/
    protected $_request_token_url = '';

    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_TWITTER;
} // End Kohana_MMI_API_Twitter