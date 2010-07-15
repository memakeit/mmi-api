<?php defined('SYSPATH') or die('No direct script access.');

// API verification route
Route::set('api/verify', 'api/verify/<controller>/<service>')
->defaults(array
(
	'directory' => 'api/verify',
));

// // Test route
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('test/api', 'test/api/<controller>(/<action>)')
	->defaults(array
	(
		'directory' => 'test/api',
	));
}
