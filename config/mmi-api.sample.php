<?php defined('SYSPATH') or die('No direct script access.');

// MMI sample API configuration
return array
(
	'api' => array
	(
		'connect_timeout' => 5,
		'decode' => TRUE,
		'decode_as_array' => TRUE,
		'format' => MMI_API::FORMAT_JSON,
		'ssl_verifypeer' => FALSE,
		'timeout' => 30,
		'useragent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6',
	),

	'github' => array
	(
		'api' => array
		(
			'api_url' => 'https://github.com/api/v2/'
		),
		'auth' => array
		(
			'username'	=> 'XXXXXXXXXXXXXXXXXXXX',
			'password'	=> 'XXXXXXXXXXXXXXXXXXXX',
		),
		'custom' => array
		(
			'curl_options'	=> array
			(
				'defaults'	=> array(),
				'remove'	=> array(),
				'add'		=> array(),
			),
			'http_headers'	=> array
			(
				'defaults'	=> array(),
				'remove'	=> array(),
				'add'		=> array(),
			),
			'parms' => array
			(
				'remove'	=> array(),
				'add'		=> array(),
			)
		),
	),

	'lastfm' => array
	(
		'api' => array
		(
			'api_url' => 'http://ws.audioscrobbler.com/2.0/'
		),
		'auth' => array
		(
			'secret' => 'XXXXXXXXXXXXXXXXXXXX',
		),
		'defaults' => array
		(
			'api_key' => 'XXXXXXXXXXXXXXXXXXXX',
		),
	),

	'readernaut' => array
	(
		'api' => array
		(
			'api_url'	=> 'http://readernaut.com/api/v1/',
			'format'	=> MMI_API::FORMAT_XML
		),
	),
);
