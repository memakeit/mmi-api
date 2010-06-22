<?php defined('SYSPATH') or die('No direct script access.');

// OAuth verification route
Route::set('oauth/verification', 'oauth/verification/<service>')
->defaults(array
(
    'controller'    => 'verification',
    'directory'     => 'oauth',
));

// API test route
Route::set('test/api', 'test/api/<controller>(/<action>)')
->defaults(array
(
    'action'        => 'index',
    'directory'     => 'test/api',
));