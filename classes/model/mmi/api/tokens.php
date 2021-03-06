<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API authorization tokens.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_MMI_API_Tokens extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'mmi_api_tokens';

	/**
	 * Initialize the model settings.
	 *
	 * @param	Jelly_Meta	meta data for the model
	 * @return	void
	 */
	public static function initialize(Jelly_Meta $meta)
	{
		$meta
			->table(self::$_table_name)
			->primary_key('id')
			->foreign_key('id')
			->fields(array
			(
				'id' => new Field_Primary,
				'service' => new Field_String(array
				(
					'rules' => array
					(
						'max_length' => array(64),
						'not_empty' => NULL,
					),
				)),
				'consumer_key' => new Field_String(array
				(
					'rules' => array
					(
						'max_length' => array(255),
						'not_empty' => NULL,
					),
				)),
				'consumer_secret' => new Field_Text(array
				(
					'rules' => array
					(
						'max_length' => array(65535),
						'not_empty' => NULL,
					),
				)),
				'token_key' => new Field_Text(array
				(
					'null' => TRUE,
					'rules' => array
					(
						'max_length' => array(65535),
					),
				)),
				'token_secret' => new Field_Text(array
				(
					'null' => TRUE,
					'rules' => array
					(
						'max_length' => array(65535),
					),
				)),
				'verified' => new Field_Boolean(array
				(
					'default' => 0,
					'rules' => array
					(
						'range' => array(0, 1)
					),
				)),
				'verification_code' => new Field_String(array
				(
					'null' => TRUE,
					'rules' => array
					(
						'max_length' => array(64),
					),
				)),
				'username' => new Field_String(array
				(
					'null' => TRUE,
					'rules' => array
					(
						'max_length' => array(128),
					),
				)),
				'attributes' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'date_created' => new Field_Timestamp(array
				(
					'auto_now_create' => TRUE,
					'pretty_format' => 'Y-m-d G:i:s',
				)),
				'date_updated' => new Field_Timestamp(array
				(
					'auto_now_create' => TRUE,
					'auto_now_update' => TRUE,
					'pretty_format' => 'Y-m-d G:i:s',
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by id.
	 *
	 * @param	mixed	one or more id's
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_id($id, $as_array = TRUE, $limit = 1)
	{
		$where_parms = array();
		if (MMI_Util::is_set($id))
		{
			$where_parms['id'] = $id;
		}
		$query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
		return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
	}

	/**
	 * Select a row from the database by service name and consumer key.
	 *
	 * @param	string	the service name
	 * @param	string	the consumer key
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_service_and_consumer_key($service, $consumer_key, $as_array = TRUE, $limit = 1)
	{
		$where_parms = array
		(
			'service'		=> $service,
			'consumer_key'	=> $consumer_key,
		);
		$query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
		return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
	}

	/**
	 * Select a row from the database by service name and user name.
	 *
	 * @param	string	the service name
	 * @param	string	the user name
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_service_and_username($service, $username, $as_array = TRUE, $limit = 1)
	{
		$where_parms = array
		(
			'service'	=> $service,
			'username'	=> $username,
		);
		$query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
		return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
	}
} // End Model_MMI_API_Tokens
