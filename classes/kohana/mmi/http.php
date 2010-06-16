<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HTTP constants.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_HTTP
{
    // Authorization type constants
    const AUTH_BASIC = 'basic';
    const AUTH_DIGEST = 'digest';
    const AUTH_OAUTH = 'oauth';

	// HTTP method constants
    const METHOD_DELETE = 'DELETE';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'GET';
} // End Kohana_MMI_HTTP