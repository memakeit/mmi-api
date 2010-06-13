<?php defined('SYSPATH') or die('No direct script access.');

// API configuration
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
        'useragent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3',
    ),

    'github' => array
    (
        'api' => array('api_url' => 'https://github.com/api/v2/'),
        'auth' => array
        (
            'username' => 'XXXXXXXXXXXXXXXXXXXX',
            'password' => 'XXXXXXXXXXXXXXXXXXXX',
        ),
        'custom' => array
        (
            'curl_options'  => array
            (
                'remove'    => array(),
                'add'       => array(),
            ),
            'http_headers'  => array
            (
                'remove'    => array(),
                'add'       => array('X-Powered-By' => 'Me Make It'),
            )
        ),
    ),
);