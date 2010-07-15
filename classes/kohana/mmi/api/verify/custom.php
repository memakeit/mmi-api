<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Verify custom credentials.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_API_Verify_Custom
{
	// Abstract methods
	abstract public function verify();

	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug;

	/**
	 * @var string the service name
	 */
	protected $_service = '?';

	/**
	 * @var array an associative array of OAuth configuration options
	 **/
	protected $_auth_config = array();

	/**
	 * @var array an associative array of service-specific configuration options
	 **/
	protected $_service_config = array();

	/**
	 * Initialize debugging (using the Request instance).
	 * Load the configuration settings.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
		$config = MMI_API::get_config(TRUE);
		$this->_service_config = Arr::get($config, $this->_service, array());
		$this->_auth_config = Arr::get($this->_service_config, 'auth', array());
	}

	/**
	 * Create a custom verification instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	the name of the service
	 * @return  MMI_API_Verify_Custom
	 */
	public static function factory($driver)
	{
		$class = 'MMI_API_Verify_Custom_'.ucfirst($driver);
		if ( ! class_exists($class))
		{
			MMI_API::log_error(__METHOD__, __LINE__, $class.' class does not exist');
			throw new Kohana_Exception(':class class does not exist in :method.', array
			(
				':class'	=> $class,
				':method'	=> __METHOD__
			));
		}
		return new $class;
	}
} // End Kohana_MMI_API_Verify_Custom
