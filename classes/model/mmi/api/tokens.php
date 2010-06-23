<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API authorization tokens.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_MMI_API_Tokens extends Jelly_Model
{
    protected static $_table_name = 'mmi_api_tokens';
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
                    'rules' => array
                    (
                        'max_length' => array(65535),
                    ),
                    'null' => TRUE,
                )),
                'token_secret' => new Field_Text(array
                (
                    'rules' => array
                    (
                        'max_length' => array(65535),
                    ),
                    'null' => TRUE,
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
                    'rules' => array
                    (
                        'max_length' => array(64),
                    ),
                    'null' => TRUE,
                )),
                'user_id' => new Field_String(array
                (
                    'rules' => array
                    (
                        'max_length' => array(64),
                    ),
                    'null' => TRUE,
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

    public static function select_by_id($ids, $as_array = TRUE, $array_key = NULL, $limit = 1)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['id'] = $ids;
        }
        $query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
        return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
    }

    public static function select_by_service_and_consumer_key($service, $consumer_key, $as_array = TRUE, $array_key = NULL, $limit = 1)
    {
        $where_parms = array
        (
            'service'       => $service,
            'consumer_key'  => $consumer_key,
        );
        $query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
        return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
    }

    public static function select_by_service_and_token_key($service, $token_key, $as_array = TRUE, $array_key = NULL, $limit = 1)
    {
        $where_parms = array
        (
            'service'   => $service,
            'token_key' => $token_key,
        );
        $query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
        return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
    }
} // End Model_MMI_API_Tokens