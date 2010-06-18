<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify Vimeo authorization.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_OAuth_Verification_Vimeo extends MMI_OAuth_Verification
{
    /**
     * @var string the service name
     */
    protected $_service = MMI_API::SERVICE_VIMEO;
} // End Kohana_MMI_OAuth_Verification_Vimeo