<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'photo' => array(
		'alias' => 'kubikrubik-photo',
		'name' => 'Photo module',
		'type' => Helper_Module::MODULE_SINGLE,
		'controller' => 'photo_album'
	),
);