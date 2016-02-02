<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
	'photo_albums' => array(
		'title' => __('Albums list'),
		'link' => Route::url('modules', array(
			'controller' => 'photo_album',
			'query' => 'group={GROUP_KEY}',
		)),
		'sub' => array(),
	),
	'photo_elements' => array(),
	'multiupload' => array(
		'title' => __('Multiupload'),
		'link' => Route::url('modules', array(
			'controller' => 'photo_multiupload',
		)),
	),
);
