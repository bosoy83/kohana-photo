<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	'photo' => array(
		'uri_callback' => '(/<album_uri>(/<element_id>))(?<query>)',
		'regex' => array(
			'element_uri' => '[^/.,;?\n]+',
			'element_id' => '[0-9]+',
		),
		'defaults' => array(
			'directory' => 'modules',
			'controller' => 'photo',
			'action' => 'index',
		)
	),
);

