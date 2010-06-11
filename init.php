<?php defined('SYSPATH') or die('No direct script access.');

// OAuth verification route
Route::set('oauth/verification', 'oauth/verification/<service>')
->defaults(array
(
    'controller'    => 'verification',
    'directory'     => 'oauth',
));