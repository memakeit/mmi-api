<?php defined('SYSPATH') or die('No direct script access.');

// API verification routes
Route::set('mmi/api/verify', 'mmi/api/verify/<controller>/<service>')
->defaults(array
(
	'directory' => 'mmi/api/verify',
));

// Test routes
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('mmi/api/test', 'mmi/api/test/<controller>(/<action>)')
	->defaults(array
	(
		'directory' => 'mmi/api/test',
	));
}
