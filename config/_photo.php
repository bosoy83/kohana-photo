<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'groups' => array(
		'common' => __('Common'),
	),
	'status'	=>	array(
		0 => __('status inactive'),
		1 => __('status hidden'),
		2 => __('status active'),
	),
	'status_codes' => array(
		'inactive' => 0,
		'hidden' => 1,
		'active' => 2,
	),
	'multiupload_dir' => 'upload/multiupload',
);